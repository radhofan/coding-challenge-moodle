<?php

namespace App\Handler;

class CliHandler
{
    public static function parseOptions(): array
    {
        return getopt('', ['file:', 'dry-run', 'create-table', 'help']) ?: [];
    }

    public static function printHelp(): void
    {
        echo "User Upload CLI Tool\n";
        echo "Usage: php user_upload.php [options]\n\n";
        echo "Options:\n";
        echo "  --file <filename>    CSV file to process\n";
        echo "  --dry-run            Parse and validate without importing\n";
        echo "  --create-table       Create/rebuild the users table\n";
        echo "  --help               Display available options\n";
    }

    public static function printError(string $message): void
    {
        fwrite(STDERR, $message . "\n");
    }

    public static function printSummaryTable(array $result, bool $dryRun): void
    {
        echo "\n=== Import Summary ===\n";
        echo "Users found : " . $result['total'] . "\n";
        echo "Valid       : " . $result['valid'] . "\n";
        echo "Invalid     : " . $result['invalid'] . "\n";

        if ($dryRun) {
            echo "Mode        : DRY RUN (No records inserted into database)\n";
        } else {
            echo "Imported    : " . $result['imported'] . " records\n";
        }

        if (!empty($result['db_error'])) {
            echo "Database Error: " . $result['db_error'] . "\n";
        }

        echo "\n=== Detailed Record Results ===\n";
        printf("| %-15s | %-15s | %-35s | %-8s | %-35s |\n", "Name", "Surname", "Email", "Status", "Errors");
        echo str_repeat("-", 120) . "\n";

        foreach ($result['records'] as $record) {
            printf(
                "| %-15s | %-15s | %-35s | %-8s | %-35s |\n",
                substr($record['name'], 0, 15),
                substr($record['surname'], 0, 15),
                substr($record['email'], 0, 35),
                $record['status'],
                substr($record['error_message'], 0, 35)
            );
        }
        echo str_repeat("-", 120) . "\n";
    }
}
