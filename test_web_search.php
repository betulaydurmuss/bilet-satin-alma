<?php
$url = "http://localhost/Bilet-satın-alma/public/search.php?from=Ankara&to=İstanbul&date=2025-10-16";

echo "Testing URL: $url\n";

$content = file_get_contents($url);

if ($content === false) {
    echo "Failed to fetch the page\n";
    exit(1);
}

if (strpos($content, 'Sefer Sonuçları') !== false) {
    echo "Found 'Sefer Sonuçları' section\n";
    
    $tripCount = substr_count($content, 'departure_time');
    echo "Found approximately $tripCount trip results\n";
    
    if ($tripCount > 0) {
        echo "SUCCESS: Web search is working correctly!\n";
    } else {
        echo "ISSUE: Found the section but no trips are displayed\n";
    }
} else {
    echo "ISSUE: 'Sefer Sonuçları' section not found\n";
}

if (strpos($content, 'Seçtiğiniz kriterlere uygun sefer bulunamadı') !== false) {
    echo "ERROR: No trips found for the search criteria\n";
}

echo "\nContent snippet:\n";
echo substr($content, 0, 500) . "...\n";
?>