<?php

namespace App\Parser;

use InvalidArgumentException;
use RuntimeException;

class CsvParser
{
    public function parseFile(string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new InvalidArgumentException("CSV file does not exist or is not readable: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new RuntimeException("Unable to open CSV file: {$filePath}");
        }

        try {
            return $this->parseHandle($handle);
        } finally {
            fclose($handle);
        }
    }

    public function parseString(string $csvContent): array
    {
        // Strip UTF-8 BOM if present at the beginning of the string
        $csvContent = preg_replace('/^\xEF\xBB\xBF/', '', $csvContent);

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $csvContent);
        rewind($handle);

        try {
            return $this->parseHandle($handle);
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param resource $handle
     */
    private function parseHandle($handle): array
    {
        $rows = [];
        $header = null;
        $nameIdx = null;
        $surnameIdx = null;
        $emailIdx = null;

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            // Strip BOM from the first field of the line if present
            if (isset($data[0])) {
                $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]);
            }

            // Skip empty lines or lines with empty elements only
            if (empty(array_filter($data, fn($item) => trim((string)$item) !== ''))) {
                continue;
            }

            if ($header === null) {
                $header = array_map(fn($col) => strtolower(trim((string)$col)), $data);
                
                $nameIdx = array_search('name', $header, true);
                $surnameIdx = array_search('surname', $header, true);
                $emailIdx = array_search('email', $header, true);

                if ($nameIdx === false || $surnameIdx === false || $emailIdx === false) {
                    throw new InvalidArgumentException('Invalid CSV headers. Required columns: name, surname, email');
                }
                continue;
            }

            $name = isset($data[$nameIdx]) ? trim((string)$data[$nameIdx]) : '';
            $surname = isset($data[$surnameIdx]) ? trim((string)$data[$surnameIdx]) : '';
            $email = isset($data[$emailIdx]) ? trim((string)$data[$emailIdx]) : '';

            $rows[] = [
                'name' => $name,
                'surname' => $surname,
                'email' => $email,
            ];
        }

        if ($header === null) {
            throw new InvalidArgumentException('CSV file is empty or missing headers');
        }

        return $rows;
    }
}
