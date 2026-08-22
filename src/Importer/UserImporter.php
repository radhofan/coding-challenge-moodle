<?php

namespace App\Importer;

use App\Database\DatabaseManager;
use App\Model\UserRecord;
use App\Parser\CsvParser;
use App\Validator\UserValidator;
use PDO;
use PDOException;
use Exception;

class UserImporter
{
    private CsvParser $parser;
    private UserValidator $validator;
    private ?DatabaseManager $dbManager;

    public function __construct(
        ?CsvParser $parser = null,
        ?UserValidator $validator = null,
        ?DatabaseManager $dbManager = null
    ) {
        $this->parser = $parser ?? new CsvParser();
        $this->validator = $validator ?? new UserValidator();
        $this->dbManager = $dbManager;
    }

    public function processFile(string $filePath, bool $dryRun = false): array
    {
        $rawRows = $this->parser->parseFile($filePath);
        return $this->processRows($rawRows, $dryRun);
    }

    public function processString(string $csvContent, bool $dryRun = false): array
    {
        $rawRows = $this->parser->parseString($csvContent);
        return $this->processRows($rawRows, $dryRun);
    }

    private function processRows(array $rawRows, bool $dryRun = false): array
    {
        $existingEmails = [];
        if ($this->dbManager !== null) {
            try {
                $existingEmails = $this->dbManager->getExistingEmails();
            } catch (Exception $e) {
                // If DB is unreachable during parse, log/continue without DB email check
            }
        }

        /** @var UserRecord[] $records */
        $records = $this->validator->validateBatch($rawRows, $existingEmails);

        $validCount = 0;
        $invalidCount = 0;
        $validRecords = [];

        foreach ($records as $record) {
            if ($record->isValid()) {
                $validCount++;
                $validRecords[] = $record;
            } else {
                $invalidCount++;
            }
        }

        $importedCount = 0;
        $dbError = null;

        if (!$dryRun && $validCount > 0 && $this->dbManager !== null) {
            try {
                $importedCount = $this->insertRecords($validRecords);
            } catch (PDOException $e) {
                $dbError = $e->getMessage();
            }
        }

        return [
            'total' => count($records),
            'valid' => $validCount,
            'invalid' => $invalidCount,
            'imported' => $importedCount,
            'dry_run' => $dryRun,
            'db_error' => $dbError,
            'records' => array_map(fn(UserRecord $r) => $r->toArray(), $records)
        ];
    }

    /**
     * @param UserRecord[] $records
     */
    private function insertRecords(array $records): int
    {
        if (empty($records)) {
            return 0;
        }

        $pdo = $this->dbManager->getPdo();
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO users (name, surname, email) VALUES (:name, :surname, :email)');
        $count = 0;

        try {
            foreach ($records as $record) {
                $stmt->execute([
                    ':name' => $record->getFormattedName(),
                    ':surname' => $record->getFormattedSurname(),
                    ':email' => $record->getFormattedEmail()
                ]);
                $count++;
            }
            $pdo->commit();
            return $count;
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
