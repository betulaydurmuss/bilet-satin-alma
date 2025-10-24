<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");

echo "Existing tables in database:\n";
foreach ($tables as $table) {
    echo "- " . $table['name'] . "\n";
}

$usersTable = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
if (count($usersTable) > 0) {
    echo "\nUsers table exists\n";
} else {
    echo "\nUsers table does not exist\n";
}
?>