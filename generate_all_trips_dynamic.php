<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

$cities = $db->query("SELECT name FROM cities ORDER BY name");
$city_names = array_column($cities, 'name');

echo "Generating trips between " . count($city_names) . " cities...\n";

$companies = $db->query("SELECT id FROM companies");
$company_ids = array_column($companies, 'id');

if (empty($company_ids)) {
    die("No companies found in the database. Please run the database initialization first.\n");
}

$trip_times = [
    ['departure' => '06:00:00', 'duration_min' => 3, 'duration_max' => 10],
    ['departure' => '12:00:00', 'duration_min' => 3, 'duration_max' => 10],
    ['departure' => '18:00:00', 'duration_min' => 3, 'duration_max' => 10]
];

function generateLicensePlate() {
    $cities_plate = [
        1 => "01", 2 => "02", 3 => "03", 4 => "04", 5 => "05", 6 => "06", 7 => "07", 8 => "08", 9 => "09", 10 => "10",
        11 => "11", 12 => "12", 13 => "13", 14 => "14", 15 => "15", 16 => "16", 17 => "17", 18 => "18", 19 => "19", 20 => "20",
        21 => "21", 22 => "22", 23 => "23", 24 => "24", 25 => "25", 26 => "26", 27 => "27", 28 => "28", 29 => "29", 30 => "30",
        31 => "31", 32 => "32", 33 => "33", 34 => "34", 35 => "35", 36 => "36", 37 => "37", 38 => "38", 39 => "39", 40 => "40",
        41 => "41", 42 => "42", 43 => "43", 44 => "44", 45 => "45", 46 => "46", 47 => "47", 48 => "48", 49 => "49", 50 => "50",
        51 => "51", 52 => "52", 53 => "53", 54 => "54", 55 => "55", 56 => "56", 57 => "57", 58 => "58", 59 => "59", 60 => "60",
        61 => "61", 62 => "62", 63 => "63", 64 => "64", 65 => "65", 66 => "66", 67 => "67", 68 => "68", 69 => "69", 70 => "70",
        71 => "71", 72 => "72", 73 => "73", 74 => "74", 75 => "75", 76 => "76", 77 => "77", 78 => "78", 79 => "79", 80 => "80",
        81 => "81"
    ];
    
    $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $plate_number = rand(1, 81);
    $plate_code = $cities_plate[$plate_number];
    $random_letters = substr(str_shuffle($letters), 0, 3);
    $random_numbers = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    
    return "$plate_code $random_letters $random_numbers";
}

$db->beginTransaction();

try {
    $trip_count = 0;
    
    foreach ($city_names as $departure_city) {
        foreach ($city_names as $arrival_city) {
            if ($departure_city !== $arrival_city) {
                foreach ($trip_times as $time_info) {
                    for ($day = 0; $day < 30; $day++) {
                        $departure_date = date('Y-m-d', strtotime("+$day days"));
                        
                        $company_id = $company_ids[array_rand($company_ids)];
                        
                        $duration_hours = rand($time_info['duration_min'], $time_info['duration_max']);
                        $arrival_time = date('H:i:s', strtotime($time_info['departure'] . " +$duration_hours hours"));
                        
                        $price = rand(150, 600);
                        
                        $license_plate = generateLicensePlate();
                        
                        $db->execute(
                            "INSERT INTO trips (company_id, departure_city, arrival_city, departure_date, departure_time, arrival_time, price, total_seats, available_seats, bus_plate, status) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, 39, 39, ?, 'active')",
                            [
                                $company_id,
                                $departure_city,
                                $arrival_city,
                                $departure_date,
                                $time_info['departure'],
                                $arrival_time,
                                $price,
                                $license_plate
                            ]
                        );
                        
                        $trip_count++;
                        
                        if ($trip_count % 1000 == 0) {
                            $db->commit();
                            $db->beginTransaction();
                            echo "Generated $trip_count trips so far...\n";
                        }
                    }
                }
            }
        }
    }
    
    $db->commit();
    
    echo "Successfully generated $trip_count trips between all cities.\n";
    
} catch (Exception $e) {
    $db->rollback();
    echo "Error generating trips: " . $e->getMessage() . "\n";
}
?>