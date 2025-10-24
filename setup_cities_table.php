<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Setting up cities table...\n";

try {
    $db->execute("CREATE TABLE IF NOT EXISTS cities (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✓ Cities table created or already exists\n";
} catch (Exception $e) {
    echo "✗ Error creating cities table: " . $e->getMessage() . "\n";
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

echo "✓ Inserted $inserted new cities (if any)\n";

$count = $db->queryOne("SELECT COUNT(*) as count FROM cities");
echo "Total cities in database: " . $count['count'] . "\n";

if ($count['count'] >= 81) {
    echo "✓ All 81 Turkish provinces are in the database\n";
} else {
    echo "⚠ Only " . $count['count'] . " cities found (expected 81)\n";
}

echo "Cities table setup completed.\n";
?>