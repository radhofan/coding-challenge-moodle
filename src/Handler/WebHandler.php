<?php

namespace App\Handler;

use InvalidArgumentException;
use RuntimeException;

class WebHandler
{
    public static function setCorsHeaders(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit(0);
        }

        header('Content-Type: application/json; charset=utf-8');
    }

    public static function getRequestUri(): string
    {
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    }

    public static function getCsvBody(): string
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

    public static function sendJson(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data);
        exit(0);
    }

    public static function sendError(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        echo json_encode(['success' => false, 'error' => $message]);
        exit(0);
    }
}
