<?php

require_once __DIR__ . '/src/config.php';

require_once __DIR__ . '/src/Database.php';

echo "Testing trip generation with a small dataset...\n";

try {
    $db = Database::getInstance();
    
    $cities = [
        'Adana', 'Adıyaman', 'Afyonkarahisar', 'Ağrı', 'Ankara', 'İstanbul', 'İzmir'
    ];
    
    $companyIds = [1, 2, 3, 4, 5];
    
    $departureTimes = [
        '06:00:00', '12:00:00', '18:00:00'
    ];
    
    $dates = [];
    for ($i = 0; $i <= 2; $i++) {
        $dates[] = date('Y-m-d', strtotime("+$i days"));
    }
    
    $tripCount = 0;
    
    $db->beginTransaction();
    
    echo "Generating trips...\n";
    
    foreach (array_slice($cities, 0, 3) as $index => $departureCity) {
        echo "Processing trips from $departureCity...\n";
        
        foreach ($cities as $arrivalCity) {
            if ($departureCity === $arrivalCity) {
                echo "  Skipping route from $departureCity to $arrivalCity (same city)\n";
                continue;
            }
            
            echo "  Creating trips from $departureCity to $arrivalCity\n";
            
            foreach ($dates as $date) {
                foreach ($departureTimes as $time) {
                    $exists = $db->queryOne(
                        "SELECT id FROM trips WHERE departure_city = ? AND arrival_city = ? AND departure_date = ? AND departure_time = ?",
                        [$departureCity, $arrivalCity, $date, $time]
                    );
                    
                    if ($exists) {
                        echo "    Trip already exists from $departureCity to $arrivalCity on $date at $time\n";
                    } else {
                        echo "    Creating new trip from $departureCity to $arrivalCity on $date at $time\n";
                        
                        $companyId = $companyIds[array_rand($companyIds)];
                        
                        $travelDuration = rand(3, 10);
                        $arrivalTime = date('H:i:s', strtotime($time) + ($travelDuration * 3600));
                        
                        $price = rand(150, 600);
                        
                        $platePrefix = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                        $plateSuffix = chr(rand(65, 90)) . chr(rand(65, 90)) . rand(100, 999);
                        $busPlate = "$platePrefix $plateSuffix";
                        
                        $status = 'active';
                        
                        echo "    Details: Company=$companyId, Arrival=$arrivalTime, Price=$price, Plate=$busPlate\n";
                        
                        $result = $db->execute(
                            "INSERT INTO trips (company_id, departure_city, arrival_city, departure_date, departure_time, arrival_time, price, total_seats, available_seats, bus_plate, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                            [$companyId, $departureCity, $arrivalCity, $date, $time, $arrivalTime, $price, 39, 39, $busPlate, $status]
                        );
                        
                        if ($result) {
                            $tripCount++;
                            echo "    Trip created successfully\n";
                        } else {
                            echo "    ❌ Failed to insert trip from $departureCity to $arrivalCity on $date at $time\n";
                        }
                    }
                }
            }
        }
        
        $db->commit();
        $db->beginTransaction();
        echo "  Completed trips from $departureCity. Total trips so far: $tripCount\n";
    }
    
    $db->commit();
    
    echo "\n✅ Test completed. Total trips generated: $tripCount\n";
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>