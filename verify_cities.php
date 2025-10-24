<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Verifying cities in database...\n";

$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='cities'");
if (empty($tables)) {
    echo "Cities table does not exist. Creating it now...\n";
    
    $db->execute("CREATE TABLE IF NOT EXISTS cities (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "Cities table created.\n";
}

$turkey_cities = [
    "Adana", "Adıyaman", "Afyonkarahisar", "Ağrı", "Amasya", "Ankara", "Antalya", "Artvin", "Aydın", "Balıkesir",
    "Bilecik", "Bingöl", "Bitlis", "Bolu", "Burdur", "Bursa", "Çanakkale", "Çankırı", "Çorum", "Denizli",
    "Diyarbakır", "Edirne", "Elazığ", "Erzincan", "Erzurum", "Eskişehir", "Gaziantep", "Giresun", "Gümüşhane", "Hakkari",
    "Hatay", "Isparta", "Mersin", "İstanbul", "İzmir", "Kars", "Kastamonu", "Kayseri", "Kırklareli", "Kırşehir",
    "Kocaeli", "Konya", "Kütahya", "Malatya", "Manisa", "Kahramanmaraş", "Mardin", "Muğla", "Muş", "Nevşehir",
    "Niğde", "Ordu", "Rize", "Sakarya", "Samsun", "Siirt", "Sinop", "Sivas", "Tekirdağ", "Tokat",
    "Trabzon", "Tunceli", "Şanlıurfa", "Uşak", "Van", "Yozgat", "Zonguldak", "Aksaray", "Bayburt", "Karaman",
    "Kırıkkale", "Batman", "Şırnak", "Bartın", "Ardahan", "Iğdır", "Yalova", "Karabük", "Kilis", "Osmaniye",
    "Düzce"
];

echo "Ensuring all 81 Turkish provinces are in the database...\n";
$inserted = 0;
foreach ($turkey_cities as $city) {
    try {
        if ($db->execute("INSERT OR IGNORE INTO cities (name) VALUES (?)", [$city])) {
            $inserted++;
        }
    } catch (Exception $e) {
        echo "Warning: Could not insert city $city: " . $e->getMessage() . "\n";
    }
}

if ($inserted > 0) {
    echo "Inserted $inserted new cities.\n";
} else {
    echo "All cities already exist in the database.\n";
}

$count = $db->queryOne("SELECT COUNT(*) as count FROM cities");
echo "Total cities in database: " . $count['count'] . "\n";

if ($count['count'] >= 81) {
    echo "✓ All 81 Turkish provinces are present in the database.\n";
} else {
    echo "⚠ Only " . $count['count'] . " cities found (expected 81).\n";
}

echo "\nSample cities from database:\n";
$cities = $db->query("SELECT name FROM cities ORDER BY name LIMIT 5");
foreach ($cities as $city) {
    echo "- " . $city['name'] . "\n";
}

if ($count['count'] > 10) {
    echo "...\n";
    $cities = $db->query("SELECT name FROM cities ORDER BY name DESC LIMIT 5");
    foreach (array_reverse($cities) as $city) {
        echo "- " . $city['name'] . "\n";
    }
}

echo "\nVerification completed.\n";
?>