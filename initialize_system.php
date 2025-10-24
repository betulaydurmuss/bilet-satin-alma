<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Initializing the dynamic trip system...\n";

echo "1. Creating cities table...\n";
try {
    $db->execute("CREATE TABLE IF NOT EXISTS cities (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "   ✓ Cities table created or already exists\n";
} catch (Exception $e) {
    echo "   ✗ Error creating cities table: " . $e->getMessage() . "\n";
    exit(1);
}

echo "2. Inserting Turkish provinces...\n";
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
        echo "   Warning: Could not insert city $city: " . $e->getMessage() . "\n";
    }
}

echo "   ✓ Inserted $inserted new cities\n";

echo "3. Verifying companies...\n";
$companies_count = $db->queryOne("SELECT COUNT(*) as count FROM companies");
if ($companies_count['count'] == 0) {
    echo "   No companies found. Inserting sample companies...\n";
    $db->execute("INSERT OR IGNORE INTO companies (name, phone, email) VALUES
        ('Kamil Koç', '0850 256 00 53', 'info@kamilkoc.com.tr'),
        ('Metro Turizm', '0850 222 34 55', 'info@metroturizm.com.tr'),
        ('Pamukkale Turizm', '0850 333 35 25', 'info@pamukkale.com.tr'),
        ('Kale Seyahat', '0850 444 55 66', 'info@kaleseyahat.com.tr'),
        ('Metro Otobus', '0850 555 66 77', 'info@metrobus.com.tr')");
    echo "   ✓ Sample companies inserted\n";
} else {
    echo "   ✓ Companies already exist (" . $companies_count['count'] . " companies)\n";
}

echo "4. Checking existing trips...\n";
$trips_count = $db->queryOne("SELECT COUNT(*) as count FROM trips");
echo "   Current trips in database: " . $trips_count['count'] . "\n";

if ($trips_count['count'] == 0) {
    echo "   No trips found. You need to generate trips using generate_dynamic_trips.php\n";
    echo "   Run: php generate_dynamic_trips.php\n";
} else {
    echo "   ✓ Trips already exist in database\n";
}

echo "\nSystem initialization completed!\n";
echo "To generate trips between all cities, run: php generate_dynamic_trips.php\n";
?>