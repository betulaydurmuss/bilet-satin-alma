<?php
require_once 'src/config.php';
require_once 'src/Database.php';

$db = Database::getInstance();

$cities = [
    'Ankara', 'İstanbul', 'İzmir', 'Bursa', 'Antalya'
];

echo "Verifying trip coverage for major cities:\n";
echo "==========================================\n";

$coverageIssues = 0;

foreach ($cities as $departure) {
    foreach ($cities as $arrival) {
        if ($departure !== $arrival) {
            $timeSlots = $db->queryOne(
                "SELECT COUNT(DISTINCT departure_time) as time_count 
                 FROM trips 
                 WHERE departure_city = ? AND arrival_city = ?",
                [$departure, $arrival]
            );
            
            $timeCount = $timeSlots ? $timeSlots['time_count'] : 0;
            
            $dateSlots = $db->queryOne(
                "SELECT COUNT(DISTINCT departure_date) as date_count 
                 FROM trips 
                 WHERE departure_city = ? AND arrival_city = ?",
                [$departure, $arrival]
            );
            
            $dateCount = $dateSlots ? $dateSlots['date_count'] : 0;
            
            echo "$departure → $arrival: $timeCount time slots, $dateCount dates\n";
            
            if ($timeCount < 6) {
                echo "  ⚠️  WARNING: Missing time slots!\n";
                $coverageIssues++;
            }
            
            if ($dateCount < 3) {
                echo "  ⚠️  WARNING: Insufficient date coverage!\n";
                $coverageIssues++;
            }
        }
    }
}

echo "\n==========================================\n";
if ($coverageIssues === 0) {
    echo "✅ ALL ROUTES HAVE ADEQUATE COVERAGE!\n";
    echo "Every city has trips to every other city at different times.\n";
} else {
    echo "❌ Found $coverageIssues coverage issues that need attention.\n";
}

$count = $db->queryOne('SELECT COUNT(*) as count FROM trips');
echo "\nTotal trips in system: " . number_format($count['count']) . "\n";
?>