<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Users tablosu guncelleniyor...\n\n";

try {
    $db->execute("ALTER TABLE users ADD COLUMN company_id INTEGER");
    echo "✓ company_id sutunu eklendi\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'duplicate column name') !== false) {
        echo "✓ company_id sutunu zaten mevcut\n";
    } else {
        echo "✗ Hata: " . $e->getMessage() . "\n";
    }
}

echo "\nMevcut firma kullanicilari guncelleniyor...\n";

$companies = $db->query("SELECT id, name FROM companies ORDER BY id");

if (count($companies) > 0) {
    $firstCompanyId = $companies[0]['id'];
    
    $companyUsers = $db->query("SELECT id, username FROM users WHERE role = 'company' AND company_id IS NULL");
    
    foreach ($companyUsers as $user) {
        $db->execute("UPDATE users SET company_id = ? WHERE id = ?", [$firstCompanyId, $user['id']]);
        echo "  ✓ {$user['username']} -> {$companies[0]['name']}\n";
    }
    
    echo "\nToplam " . count($companyUsers) . " firma kullanicisi guncellendi\n";
} else {
    echo "  ! Henuz firma yok, firma kullanicilari atanamadi\n";
}

echo "\n=== DOGRULAMA ===\n";
$users = $db->query("SELECT username, role, company_id FROM users");
foreach ($users as $user) {
    $companyInfo = $user['company_id'] ? "Firma ID: {$user['company_id']}" : "Firma yok";
    echo "- {$user['username']} ({$user['role']}) - $companyInfo\n";
}

echo "\n✓ Users tablosu guncelleme tamamlandi!\n";
?>
