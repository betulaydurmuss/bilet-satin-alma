<?php

require_once __DIR__ . '/src/config.php';

require_once __DIR__ . '/src/Database.php';

echo "81 il arasında seferler oluşturuluyor...\n";

try {
    $db = Database::getInstance();
    
    
    $cities = [
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
        '06:00:00', '09:00:00', '12:00:00', '15:00:00', '18:00:00', '21:00:00'
    ];
    
    $dates = [];
    for ($i = 0; $i <= 14; $i++) {
        $dates[] = date('Y-m-d', strtotime("+$i days"));
    }
    
    $platePrefixes = [
        '01', '02', '03', '04', '68', '05', '06', '07', '75', '08', '09',
        '10', '74', '72', '69', '11', '12', '13', '14', '15', '16',
        '17', '18', '19', '20', '21', '81',
        '22', '23', '24', '25', '26',
        '27', '28', '29',
        '30', '31',
        '76', '32', '34', '35',
        '46', '78', '70', '36', '37', '38', '79',
        '40', '39', '41', '42', '43', '44',
        '45', '47', '48', '33', '49', '50',
        '51', '52',
        '53',
        '54', '55', '63', '56', '57', '58', '73',
        '59', '60', '61', '62',
        '64',
        '65',
        '77', '66',
        '67'
    ];
    
    $tripCount = 0;
    
    $db->beginTransaction();
    
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
        echo "  Completed trips from $departureCity. Total trips so far: $tripCount\n";
    }
    
    $db->commit();
    
    echo "\n✅ Toplam $tripCount sefer başarıyla oluşturuldu!\n";
    echo "Tüm 81 il arasında farklı tarih ve saatlerde seferler mevcut.\n";
    
} catch (Exception $e) {
    if ($db) {
        $db->rollback();
    }
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>