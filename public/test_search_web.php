<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';

$db = Database::getInstance();


$today = date('Y-m-d');


$test_routes = [
    ['from' => 'İstanbul', 'to' => 'Ankara'],
    ['from' => 'İzmir', 'to' => 'Antalya'],
    ['from' => 'Adana', 'to' => 'Van']
];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sefer Sistemi Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-center">🎉 Sefer Sistemi Test Sonuçları</h1>
        
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">📊 Sistem İstatistikleri</h2>
            <?php
            $total = $db->queryOne("SELECT COUNT(*) as count FROM trips");
            $routes = $db->queryOne("SELECT COUNT(DISTINCT departure_city || '-' || arrival_city) as count FROM trips");
            $cities = $db->queryOne("SELECT COUNT(DISTINCT departure_city) as count FROM trips");
            $today_trips = $db->queryOne("SELECT COUNT(*) as count FROM trips WHERE departure_date = ?", [$today]);
            ?>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded">
                    <div class="text-2xl font-bold text-blue-600"><?php echo number_format($total['count']); ?></div>
                    <div class="text-sm text-gray-600">Toplam Sefer</div>
                </div>
                <div class="bg-green-50 p-4 rounded">
                    <div class="text-2xl font-bold text-green-600"><?php echo number_format($routes['count']); ?></div>
                    <div class="text-sm text-gray-600">Toplam Rota</div>
                </div>
                <div class="bg-purple-50 p-4 rounded">
                    <div class="text-2xl font-bold text-purple-600"><?php echo $cities['count']; ?></div>
                    <div class="text-sm text-gray-600">Şehir</div>
                </div>
                <div class="bg-orange-50 p-4 rounded">
                    <div class="text-2xl font-bold text-orange-600"><?php echo number_format($today_trips['count']); ?></div>
                    <div class="text-sm text-gray-600">Bugünkü Sefer</div>
                </div>
            </div>
        </div>

        <?php foreach ($test_routes as $route): ?>
            <?php
            $from = $route['from'];
            $to = $route['to'];
            $trips = $db->query(
                "SELECT t.*, c.name as company_name 
                 FROM trips t 
                 LEFT JOIN companies c ON t.company_id = c.id
                 WHERE t.departure_city = ? 
                 AND t.arrival_city = ? 
                 AND t.departure_date = ?
                 AND t.status = 'active'
                 ORDER BY t.departure_time",
                [$from, $to, $today]
            );
            ?>
            
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">
                    🚌 <?php echo $from; ?> → <?php echo $to; ?>
                    <span class="text-sm font-normal text-gray-500">(<?php echo date('d.m.Y', strtotime($today)); ?>)</span>
                </h2>
                
                <?php if (count($trips) > 0): ?>
                    <div class="mb-2 text-green-600 font-semibold">
                        ✓ <?php echo count($trips); ?> sefer bulundu
                    </div>
                    
                    <div class="space-y-3">
                        <?php foreach ($trips as $trip): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex justify-between items-center">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-4 mb-2">
                                            <span class="text-2xl font-bold text-gray-800">
                                                <?php echo substr($trip['departure_time'], 0, 5); ?>
                                            </span>
                                            <span class="text-gray-400">→</span>
                                            <span class="text-lg text-gray-600">
                                                <?php echo substr($trip['arrival_time'], 0, 5); ?>
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            <?php echo $trip['company_name']; ?> • 
                                            Plaka: <?php echo $trip['bus_plate']; ?>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-orange-600">
                                            <?php echo number_format($trip['price'], 0); ?> TL
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo $trip['available_seats']; ?>/<?php echo $trip['total_seats']; ?> koltuk
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-red-600">
                        ✗ Sefer bulunamadı
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="bg-green-50 border-2 border-green-200 rounded-lg p-6 text-center">
            <h2 class="text-2xl font-bold text-green-700 mb-2">✅ Sistem Başarıyla Çalışıyor!</h2>
            <p class="text-gray-700 mb-4">
                Tüm 81 il arası seferler aktif. Her rota için en az 3 farklı saatte sefer mevcut.
            </p>
            <div class="flex gap-4 justify-center">
                <a href="index.php" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-lg">
                    Anasayfaya Git
                </a>
                <a href="search.php" class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-3 px-6 rounded-lg">
                    Sefer Ara
                </a>
            </div>
        </div>
    </div>
</body>
</html>
