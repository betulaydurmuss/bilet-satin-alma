<?php

require_once __DIR__ . '/src/config.php';

require_once __DIR__ . '/src/Database.php';

echo "Generating trips for specific cities...\n";

try {
    $db = Database::getInstance();
    
    $cities = [
        'Ankara', 'İstanbul', 'İzmir', 'Bursa', 'Antalya'
    ];
    
    $allCities = [
        'Adana', 'Adıyaman', 'Afyonkarahisar', 'Ağrı', 'Aksaray', 'Amasya', 'Ankara', 'Antalya', 'Ardahan', 'Artvin', 'Aydın',
        'Balıkesir', 'Bartın', 'Batman', 'Bayburt', 'Bilecik', 'Bingöl', 'Bitlis', 'Bolu', 'Burdur', 'Bursa',
        'Çanakkale', 'Çankırı', 'Çorum',
        'Denizli', 'Diyarbakır', 'Düzce',
        'Edirne', 'Elazığ', 'Erzincan', 'Erzurum', 'Eskişehir',
        'Gaziantep', 'Giresun', 'Gümüşhane',
        'Hakkâri', 'Hatay',
        'Iğdır', 'Isparta', 'İstanbul', 'İzmir',
        'Kahramanmaraş', 'Karabük', 'Karaman', 'Kars', 'Kastamonu', 'Kayseri', 'Kilis',
        'Kırıkkale', 'Kırklareli', 'Kırşehir', 'Kocaeli', 'Konya', 'Kütahya',
        'Malatya', 'Manisa', 'Mardin', 'Mersin', 'Muğla', 'Muş',
        'Nevşehir', 'Niğde',
        'Ordu', 'Osmaniye',
        'Rize',
        'Sakarya', 'Samsun', 'Şanlıurfa', 'Siirt', 'Sinop', 'Sivas', 'Şırnak',
        'Tekirdağ', 'Tokat', 'Trabzon', 'Tunceli',
        'Uşak',
        'Van',
        'Yalova', 'Yozgat',
        'Zonguldak'
    ];
    
    $companyIds = [1, 2, 3, 4, 5];
    
    $departureTimes = [
        '06:00:00', '12:00:00', '18:00:00'
    ];
    
    $dates = [];
    for ($i = 0; $i <= 4; $i++) {
        $dates[] = date('Y-m-d', strtotime("+$i days"));
    }
    
    $tripCount = 0;
    
    $db->beginTransaction();
    
    echo "Generating trips...\n";
    
    foreach ($cities as $index => $departureCity) {
        echo "Processing trips from $departureCity...\n";
        
        foreach ($allCities as $arrivalCity) {
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
                        
                        $travelDuration = rand(3, 10);
                        $arrivalTime = date('H:i:s', strtotime($time) + ($travelDuration * 3600));
                        
                        $price = rand(150, 600);
                        
                        $platePrefix = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                        $plateSuffix = chr(rand(65, 90)) . chr(rand(65, 90)) . rand(100, 999);
                        $busPlate = "$platePrefix $plateSuffix";
                        
                        $status = 'active';
                        
                        $result = $db->execute(
                            "INSERT INTO trips (company_id, departure_city, arrival_city, departure_date, departure_time, arrival_time, price, total_seats, available_seats, bus_plate, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                            [$companyId, $departureCity, $arrivalCity, $date, $time, $arrivalTime, $price, 39, 39, $busPlate, $status]
                        );
                        
                        if ($result) {
                            $tripCount++;
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