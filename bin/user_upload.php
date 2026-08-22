<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\DatabaseConfig;
use App\Database\DatabaseManager;
use App\Importer\UserImporter;

$options = getopt('', ['file:', 'dry-run', 'create-table', 'help']);

if (isset($options['help'])) {
    echo "User Upload CLI Tool\n";
    echo "Usage: php user_upload.php [options]\n\n";
    echo "Options:\n";
    echo "  --file <filename>    CSV file to process\n";
    echo "  --dry-run            Parse and validate without importing\n";
    echo "  --create-table       Create/rebuild the users table\n";
    echo "  --help               Display available options\n";
    exit(0);
}

$dbConfig = new DatabaseConfig();
$dbManager = new DatabaseManager($dbConfig);

if (isset($options['create-table'])) {
    try {
        echo "Creating/rebuilding users table in PostgreSQL...\n";
        $dbManager->createTable();
        echo "Users table created successfully.\n";
    } catch (Exception $e) {
        fwrite(STDERR, "Error creating table: " . $e->getMessage() . "\n");
        exit(1);
    }

    if (!isset($options['file'])) {
        exit(0);
    }
}

if (!isset($options['file'])) {
    fwrite(STDERR, "Error: Missing required --file option. Use --help for usage details.\n");
    exit(1);
}

$filePath = $options['file'];
$dryRun = isset($options['dry-run']);

try {
    $importer = new UserImporter(null, null, $dbManager);
    $result = $importer->processFile($filePath, $dryRun);

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

} catch (Exception $e) {
    fwrite(STDERR, "Error processing CSV file: " . $e->getMessage() . "\n");
    exit(1);
}
