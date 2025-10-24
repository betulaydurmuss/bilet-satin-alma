<?php

require_once __DIR__ . '/src/config.php';

require_once __DIR__ . '/src/Database.php';

echo "Veritabanı başlatılıyor...\n";

try {
    $db = Database::getInstance();
    
    $schemaSql = file_get_contents(__DIR__ . '/migration/database_schema.sql');
    
    $statements = explode(';', $schemaSql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            if ($db->execute($statement)) {
                if (strlen($statement) < 100) {
                    echo "✓ " . $statement . "\n";
                } else {
                    echo "✓ " . substr($statement, 0, 50) . "...\n";
                }
            }
        }
    }
    
    echo "\n✅ Veritabanı başarıyla başlatıldı!\n";
    echo "Oluşturulan tablolar:\n";
    
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
    foreach ($tables as $table) {
        echo "  - " . $table['name'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>