<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Ensuring all 81 Turkish provinces are in the database...\n";

try {
    $db->execute("CREATE TABLE IF NOT EXISTS cities (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✓ Cities table ready\n";
} catch (Exception $e) {
    echo "✗ Error ensuring cities table exists: " . $e->getMessage() . "\n";
    exit(1);
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

echo "✓ Inserted $inserted new cities (if any were missing)\n";

$count = $db->queryOne("SELECT COUNT(*) as count FROM cities");
echo "Total cities in database: " . $count['count'] . "\n";

if ($count['count'] >= 81) {
    echo "✓ All 81 Turkish provinces are now in the database!\n";
} else {
    echo "⚠ Only " . $count['count'] . " cities found (expected 81)\n";
    exit(1);
}

echo "\nAll 81 Turkish provinces in the database:\n";
$cities = $db->query("SELECT name FROM cities ORDER BY name");
foreach ($cities as $index => $city) {
    echo sprintf("%2d. %s\n", $index + 1, $city['name']);
}

echo "\nVerification completed successfully!\n";
echo "Both departure and arrival dropdowns will now show all 81 Turkish provinces.\n";
?>