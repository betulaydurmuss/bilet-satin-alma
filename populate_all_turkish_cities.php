<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Populating database with all 81 Turkish provinces...\n";

$db->execute("CREATE TABLE IF NOT EXISTS cities (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

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

$inserted_count = 0;
foreach ($turkey_cities as $city) {
    try {
        if ($db->execute("INSERT OR IGNORE INTO cities (name) VALUES (?)", [$city])) {
            $inserted_count++;
        }
    } catch (Exception $e) {
        echo "Warning: Could not insert $city: " . $e->getMessage() . "\n";
    }
}

echo "Inserted $inserted_count new cities into the database.\n";

$result = $db->query("SELECT COUNT(*) as count FROM cities");
$total_cities = $result[0]['count'];

echo "Total cities in database: $total_cities\n";

if ($total_cities >= 81) {
    echo "✓ All 81 Turkish provinces are now in the database!\n";
    
    echo "\nWhen users click on departure or arrival dropdowns, they will see all 81 Turkish provinces:\n";
    echo "- Adana, Adıyaman, Afyonkarahisar, Ağrı, Amasya, Ankara, Antalya, Artvin, Aydın, Balıkesir\n";
    echo "- Bilecik, Bingöl, Bitlis, Bolu, Burdur, Bursa, Çanakkale, Çankırı, Çorum, Denizli\n";
    echo "- Diyarbakır, Edirne, Elazığ, Erzincan, Erzurum, Eskişehir, Gaziantep, Giresun, Gümüşhane, Hakkari\n";
    echo "- Hatay, Isparta, Mersin, İstanbul, İzmir, Kars, Kastamonu, Kayseri, Kırklareli, Kırşehir\n";
    echo "- Kocaeli, Konya, Kütahya, Malatya, Manisa, Kahramanmaraş, Mardin, Muğla, Muş, Nevşehir\n";
    echo "- Niğde, Ordu, Rize, Sakarya, Samsun, Siirt, Sinop, Sivas, Tekirdağ, Tokat\n";
    echo "- Trabzon, Tunceli, Şanlıurfa, Uşak, Van, Yozgat, Zonguldak, Aksaray, Bayburt, Karaman\n";
    echo "- Kırıkkale, Batman, Şırnak, Bartın, Ardahan, Iğdır, Yalova, Karabük, Kilis, Osmaniye, Düzce\n";
    
    echo "\nUsers can now select any of these provinces as departure or arrival points.\n";
} else {
    echo "⚠ Only $total_cities cities found (expected 81)\n";
}

echo "\nPopulation completed successfully!\n";
?>