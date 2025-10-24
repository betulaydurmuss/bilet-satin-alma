<?php


require_once __DIR__ . '/src/config.php';

require_once __DIR__ . '/src/Database.php';

echo "Test seferler oluşturuluyor...\n";

try {
    $db = Database::getInstance();
    
    $cities = [
        'Ankara', 'İstanbul', 'İzmir', 'Bursa', 'Antalya', 'Adana', 'Trabzon'
    ];
    
    $companyIds = [1, 2, 3, 4, 5];
    
    $departureTimes = [
        '06:00:00', '09:00:00', '12:00:00', '15:00:00', '18:00:00', '21:00:00'
    ];
    
    $dates = [];
    for ($i = 0; $i <= 3; $i++) {
        $dates[] = date('Y-m-d', strtotime("+$i days"));
    }
    
    $platePrefixes = [
        '06', '34', '35', '16', '07', '01', '61'
    ];
    
    $tripCount = 0;
    
    $db->beginTransaction();
    
    echo "Creating trips for " . count($cities) . " cities...\n";
    
    foreach ($cities as $index => $departureCity) {
        echo "Processing trips from $departureCity...\n";
        
        foreach ($cities as $arrivalCity) {
            if ($departureCity === $arrivalCity) {
                continue;
            }
            
            foreach ($dates as $date) {
                foreach ($departureTimes as $time) {
                    $exists = $db->queryOne(
                        "SELECT id FROM trips WHERE departure_city = ? AND arrival_city = ? AND departure_date = ? AND departure_time = ?",
                        [$departureCity, $arrivalCity, $date, $time]
                    );
                    
                    if (!$exists) {
                        $companyId = $companyIds[array_rand($companyIds)];
                        
                        $travelDuration = rand(5, 12);
                        $arrivalTime = date('H:i:s', strtotime($time) + ($travelDuration * 3600));
                        
                        $price = rand(150, 1000);
                        
                        $platePrefix = $platePrefixes[min($index, count($platePrefixes) - 1)];
                        $plateSuffix = chr(rand(65, 90)) . chr(rand(65, 90)) . rand(100, 999);
                        $busPlate = "$platePrefix $plateSuffix";
                        
                        $db->execute(
                            "INSERT INTO trips (company_id, departure_city, arrival_city, departure_date, departure_time, arrival_time, price, total_seats, available_seats, bus_plate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                            [$companyId, $departureCity, $arrivalCity, $date, $time, $arrivalTime, $price, 39, 39, $busPlate]
                        );
                        $tripCount++;
                    }
                }
            }
        }
        
        $db->commit();
        $db->beginTransaction();
        echo "  Completed trips from $departureCity. Total trips created: $tripCount\n";
    }
    
    $db->commit();
    
    echo "\n✅ Toplam $tripCount test seferi başarıyla oluşturuldu!\n";
    
} catch (Exception $e) {
    if ($db) {
        $db->rollback();
    }
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>