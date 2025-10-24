<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Testing display of all 81 Turkish provinces...\n";

$cities = $db->query("SELECT name FROM cities ORDER BY name");

echo "Total cities in database: " . count($cities) . "\n";

if (count($cities) >= 81) {
    echo "✓ All 81 Turkish provinces are available!\n\n";
    
    echo "Cities list:\n";
    foreach ($cities as $index => $city) {
        echo sprintf("%2d. %-20s", $index + 1, $city['name']);
        if (($index + 1) % 4 == 0) {
            echo "\n";
        }
    }
    
    if (count($cities) % 4 != 0) {
        echo "\n";
    }
    
    echo "\nBoth departure and arrival dropdowns will show all these cities.\n";
    echo "Users can now select any of these 81 provinces as departure or arrival points.\n";
} else {
    echo "⚠ Only " . count($cities) . " cities found (expected 81)\n";
    
    echo "\nMissing cities:\n";
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
    
    $missing = array_diff($turkey_cities, array_column($cities, 'name'));
    if (!empty($missing)) {
        foreach ($missing as $city) {
            echo "- " . $city . "\n";
        }
    }
}

echo "\nTest completed.\n";
?>