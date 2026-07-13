<?php
require_once __DIR__ . '/functions.php';

$pdo = getDbConnection($dbError);
if ($pdo === null) {
    die('Database connection failed: ' . htmlspecialchars((string) $dbError));
}
