<?php
require_once __DIR__ . '/functions.php';

$dbError = null;
$pdo = getDbConnection($dbError);
if ($pdo === null) {
    echo "Error: Could not connect to the database." . PHP_EOL;
    if (!empty($dbError)) {
        echo "Details: {$dbError}" . PHP_EOL;
    }
    echo "Please update config.php with your MySQL credentials and ensure the database exists." . PHP_EOL;
    exit(1);
}

try {
    createCarTable($pdo);
    $count = syncCarsCsvToDatabase($pdo, __DIR__ . '/cars.csv');
    if ($count === 0) {
        echo "No rows imported. Check that cars.csv exists and contains valid data.\n";
        exit(1);
    }
    echo "Imported {$count} car rows into the MySQL database successfully.\n";
    echo "Table name: cars\n";
} catch (Exception $e) {
    echo "Import failed: " . $e->getMessage() . "\n";
    exit(1);
}
