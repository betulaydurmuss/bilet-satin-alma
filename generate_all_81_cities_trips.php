<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "=== TÜM 81 İL ARASI SEFER OLUŞTURMA ===\n\n";

$cities_count = $db->queryOne("SELECT COUNT(*) as count FROM cities");
echo "Veritabanında {$cities_count['count']} il bulundu\n";

if ($cities_count['count'] < 81) {
    echo "HATA: 81 il bulunamadı. Önce setup_cities_table.php çalıştırın.\n";
    exit(1);
}

$companies_count = $db->queryOne("SELECT COUNT(*) as count FROM companies");
echo "Veritabanında {$companies_count['count']} firma bulundu\n";

if ($companies_count['count'] == 0) {
    echo "Firmalar ekleniyor...\n";
    $db->execute("INSERT OR IGNORE INTO companies (name, phone, email) VALUES 
        ('Kamil Koç', '0850 256 00 53', 'info@kamilkoc.com.tr'),
        ('Metro Turizm', '0850 222 34 55', 'info@metroturizm.com.tr'),
        ('Pamukkale Turizm', '0850 333 35 25', 'info@pamukkale.com.tr'),
        ('Ulusoy', '0850 811 18 88', 'info@ulusoy.com.tr'),
        ('Varan Turizm', '0850 222 99 99', 'info@varan.com.tr')");
    echo "✓ Firmalar eklendi\n";
}

$cities = $db->query("SELECT name FROM cities ORDER BY name");
$city_names = array_column($cities, 'name');

$companies = $db->query("SELECT id FROM companies");
$company_ids = array_column($companies, 'id');

echo "\n=== SEFER OLUŞTURMA BAŞLIYOR ===\n";
echo "İller: " . count($city_names) . "\n";
echo "Firmalar: " . count($company_ids) . "\n";

$trip_times = [
    ['time' => '08:00:00', 'label' => 'Sabah'],
    ['time' => '14:00:00', 'label' => 'Öğleden Sonra'],
    ['time' => '20:00:00', 'label' => 'Akşam']
];

function generatePlate() {
    $plate_code = str_pad(rand(1, 81), 2, '0', STR_PAD_LEFT);
    $letters = 'ABCDEFGHJKLMNPRSTUVYZ';
    $letter1 = $letters[rand(0, strlen($letters) - 1)];
    $letter2 = $letters[rand(0, strlen($letters) - 1)];
    $letter3 = $letters[rand(0, strlen($letters) - 1)];
    $numbers = str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
    return "$plate_code $letter1$letter2$letter3 $numbers";
}

echo "\nMevcut seferler temizleniyor...\n";
$db->execute("DELETE FROM trips");
echo "✓ Temizlendi\n";

$db->beginTransaction();

try {
    $trip_count = 0;
    $batch_size = 1000;
    
    $total_routes = count($city_names) * (count($city_names) - 1);
    $total_trips_expected = $total_routes * count($trip_times) * 30; // 30 gün
    
    echo "\nBeklenen toplam sefer sayısı: " . number_format($total_trips_expected) . "\n";
    echo "Başlangıç zamanı: " . date('H:i:s') . "\n\n";
    
    foreach ($city_names as $dep_index => $departure_city) {
        $city_trip_count = 0;
        
        foreach ($city_names as $arrival_city) {
            if ($departure_city === $arrival_city) {
                continue;
            }
            
            foreach ($trip_times as $time_info) {
                for ($day = 0; $day < 30; $day++) {
                    $departure_date = date('Y-m-d', strtotime("+$day days"));
                    
                    $company_id = $company_ids[array_rand($company_ids)];
                    
                    $duration_hours = rand(3, 12);
                    $arrival_time = date('H:i:s', strtotime($time_info['time'] . " +$duration_hours hours"));
                    
                    $price = rand(100, 800);
                    
                    $plate = generatePlate();
                    
                    $db->execute(
                        "INSERT INTO trips (company_id, departure_city, arrival_city, departure_date, departure_time, arrival_time, price, total_seats, available_seats, bus_plate, status) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, 39, 39, ?, 'active')",
                        [
                            $company_id,
                            $departure_city,
                            $arrival_city,
                            $departure_date,
                            $time_info['time'],
                            $arrival_time,
                            $price,
                            $plate
                        ]
                    );
                    
                    $trip_count++;
                    $city_trip_count++;
                    
                    if ($trip_count % $batch_size == 0) {
                        $db->commit();
                        $db->beginTransaction();
                        $progress = ($trip_count / $total_trips_expected) * 100;
                        echo sprintf("İlerleme: %d sefer (%0.1f%%) - %s\n", $trip_count, $progress, date('H:i:s'));
                    }
                }
            }
        }
        
        $completed_cities = $dep_index + 1;
        $progress_cities = ($completed_cities / count($city_names)) * 100;
        echo sprintf("✓ %s tamamlandı (%d/%d şehir - %0.1f%%) - %d sefer eklendi\n", 
            $departure_city, $completed_cities, count($city_names), $progress_cities, $city_trip_count);
    }
    
    $db->commit();
    
    echo "\n=== TAMAMLANDI ===\n";
    echo "Toplam oluşturulan sefer: " . number_format($trip_count) . "\n";
    echo "Bitiş zamanı: " . date('H:i:s') . "\n";
    
    $verify = $db->queryOne("SELECT COUNT(*) as count FROM trips");
    echo "\nVeritabanındaki toplam sefer: " . number_format($verify['count']) . "\n";
    
    echo "\n=== ÖRNEK ROTALAR ===\n";
    $samples = $db->query("
        SELECT departure_city, arrival_city, departure_date, departure_time, price, company_id
        FROM trips 
        WHERE departure_city = 'İstanbul' AND arrival_city = 'Ankara'
        ORDER BY departure_date, departure_time
        LIMIT 5
    ");
    
    foreach ($samples as $sample) {
        echo "- İstanbul → Ankara | {$sample['departure_date']} {$sample['departure_time']} | {$sample['price']} TL\n";
    }
    
    echo "\n✅ Tüm iller arası seferler başarıyla oluşturuldu!\n";
    
} catch (Exception $e) {
    $db->rollback();
    echo "\n❌ HATA: " . $e->getMessage() . "\n";
    exit(1);
}
?>
