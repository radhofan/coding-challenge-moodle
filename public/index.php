<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\DatabaseConfig;
use App\Database\DatabaseManager;
use App\Importer\UserImporter;

// Enable error reporting but display no raw HTML errors to avoid breaking JSON output
error_reporting(E_ALL);
ini_set('display_errors', '0');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit(0);
}

header('Content-Type: application/json; charset=utf-8');

try {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $dbConfig = new DatabaseConfig();
    $dbManager = new DatabaseManager($dbConfig);
    $importer = new UserImporter(null, null, $dbManager);

    if ($requestUri === '/api/preview' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $csvContent = getCsvInput();
        $result = $importer->processString($csvContent, true);
        echo json_encode(array_merge(['success' => true], $result));
        exit(0);
    }

    if ($requestUri === '/api/import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $csvContent = getCsvInput();
        $result = $importer->processString($csvContent, false);
        echo json_encode(array_merge(['success' => true], $result));
        exit(0);
    }

    if ($requestUri === '/api/create-table' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $dbManager->createTable();
        echo json_encode(['success' => true, 'message' => 'Users table created successfully']);
        exit(0);
    }

    if ($requestUri === '/api/users' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $pdo = $dbManager->getPdo();
        $stmt = $pdo->query('SELECT id, name, surname, email, created_at FROM users ORDER BY id ASC');
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $users]);
        exit(0);
    }

    // Default static fallback or 404
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Endpoint not found']);

} catch (\Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function getCsvInput(): string
{
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $content = file_get_contents($_FILES['file']['tmp_name']);
        if ($content === false) {
            throw new RuntimeException('Failed to read uploaded file');
        }
        return $content;
    }

    $rawBody = file_get_contents('php://input');
    if (!empty($rawBody)) {
        if (str_starts_with(trim($rawBody), '{')) {
            $json = json_decode($rawBody, true);
            if (isset($json['csv_content'])) {
                return $json['csv_content'];
            }
        }
        return $rawBody;
    }

    throw new InvalidArgumentException('No CSV file or CSV content provided');
}
