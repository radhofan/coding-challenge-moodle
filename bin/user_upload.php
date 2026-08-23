<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\DatabaseConfig;
use App\Database\DatabaseManager;
use App\Importer\UserImporter;
use App\Handler\CliHandler;
use App\Handler\WebHandler;

$dbConfig = new DatabaseConfig();
$dbManager = new DatabaseManager($dbConfig);
$importer = new UserImporter(null, null, $dbManager);

////////////////////////////////////////////////////////////////////////// CLI
if (PHP_SAPI === 'cli') {
    $options = CliHandler::parseOptions();

    // Check for help arg
    if (isset($options['help'])) {
        CliHandler::printHelp();
        exit(0);
    }

    // Check create table arg
    if (isset($options['create-table'])) {
        try {
            echo "Creating/rebuilding users table in PostgreSQL...\n";
            $dbManager->createTable();
            echo "Users table created successfully.\n";
        } catch (Exception $e) {
            CliHandler::printError("Error creating table: " . $e->getMessage());
            exit(1);
        }

        if (!isset($options['file'])) {
            exit(0);
        }
    }

    // Check file arg
    if (!isset($options['file'])) {
        CliHandler::printError("Error: Missing required --file option. Use --help for usage details.");
        exit(1);
    }

    $filePath = $options['file'];
    if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'csv') {
        CliHandler::printError("Error: Invalid file type. File must be a .csv file.");
        exit(1);
    }

    // Check for dry run arg
    $dryRun = isset($options['dry-run']);

    // Run the main process
    try {
        $result = $importer->processFile($filePath, $dryRun);
        CliHandler::printSummaryTable($result, $dryRun);
    } catch (Exception $e) {
        CliHandler::printError("Error processing CSV file: " . $e->getMessage());
        exit(1);
    }

////////////////////////////////////////////////////////////////////////// HTTP
} else {
    WebHandler::setCorsHeaders();
    $uri = WebHandler::getRequestUri();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    // Endpoints
    try {
        if ($uri === '/api/preview' && $method === 'POST') {
            $csvContent = WebHandler::getCsvBody();
            $result = $importer->processString($csvContent, true);
            WebHandler::sendJson(array_merge(['success' => true], $result));
        }

        if ($uri === '/api/import' && $method === 'POST') {
            $csvContent = WebHandler::getCsvBody();
            $result = $importer->processString($csvContent, false);
            WebHandler::sendJson(array_merge(['success' => true], $result));
        }

        if ($uri === '/api/create-table' && $method === 'POST') {
            $dbManager->createTable();
            WebHandler::sendJson(['success' => true, 'message' => 'Users table created successfully']);
        }

        if ($uri === '/api/users' && $method === 'GET') {
            $pdo = $dbManager->getPdo();
            $stmt = $pdo->query('SELECT id, name, surname, email, created_at FROM users ORDER BY id ASC');
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            WebHandler::sendJson(['success' => true, 'data' => $users]);
        }

        WebHandler::sendError('Endpoint not found', 404);

    // Catch error downstream
    } catch (Throwable $e) {
        WebHandler::sendError($e->getMessage(), 400);
    }
}
