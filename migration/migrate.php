<?php


$dbPath = __DIR__ . '/../data/bilet_satin_alma.db';

$dataDir = dirname($dbPath);
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0777, true);
    echo "✓ data klasörü oluşturuldu\n";
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Veritabanı bağlantısı kuruldu\n";
    
    $sqlFile = __DIR__ . '/database_schema.sql';
    
    if (!file_exists($sqlFile)) {
        die("✗ Hata: database_schema.sql dosyası bulunamadı!\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    $db->exec($sql);
    
    echo "✓ Tablolar oluşturuldu\n";
    echo "✓ Örnek veriler eklendi\n";
    echo "\n";
    echo "==============================================\n";
    echo "VERİTABANI BAŞARIYLA OLUŞTURULDU!\n";
    echo "==============================================\n";
    echo "\n";
    echo "Test Kullanıcıları:\n";
    echo "-------------------\n";
    echo "Admin:\n";
    echo "  Kullanıcı: admin\n";
    echo "  Şifre: admin123\n";
    echo "\n";
    echo "Firma Admin (Metro Turizm):\n";
    echo "  Kullanıcı: metro_admin\n";
    echo "  Şifre: firma123\n";
    echo "\n";
    echo "Normal Kullanıcı:\n";
    echo "  Kullanıcı: ahmet_yilmaz\n";
    echo "  Şifre: user123\n";
    echo "\n";
    echo "Veritabanı dosyası: $dbPath\n";
    echo "==============================================\n";
    
} catch (PDOException $e) {
    die("✗ Hata: " . $e->getMessage() . "\n");
}
?>