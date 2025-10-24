<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';



if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? 'user';


$db = Database::getInstance();


$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));


$popular_routes = [
    ['from' => 'İstanbul', 'to' => 'Ankara'],
    ['from' => 'İstanbul', 'to' => 'İzmir'],
    ['from' => 'Ankara', 'to' => 'İstanbul'],
    ['from' => 'İzmir', 'to' => 'İstanbul'],
    ['from' => 'Ankara', 'to' => 'Antalya'],
    ['from' => 'İstanbul', 'to' => 'Antalya']
];

$trips = [];
foreach ($popular_routes as $route) {
    $route_trips = $db->query(
        "SELECT t.*, c.name as company_name 
         FROM trips t 
         LEFT JOIN companies c ON t.company_id = c.id
         WHERE t.departure_city = ? 
         AND t.arrival_city = ? 
         AND t.departure_date IN (?, ?)
         AND t.status = 'active'
         AND t.available_seats > 0
         ORDER BY t.departure_date, t.departure_time
         LIMIT 2",
        [$route['from'], $route['to'], $today, $tomorrow]
    );
    
    if (!empty($route_trips)) {
        $trips = array_merge($trips, $route_trips);
    }
    
    
    if (count($trips) >= 12) {
        $trips = array_slice($trips, 0, 12);
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Otobüs Bileti</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#E67E22',
                        secondary: '#8E44AD',
                        light: '#F8F9FA',
                        dark: '#212529'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .logo-icon {
            width: 36px;
            height: 36px;
            background-color: #8E44AD;
            border-radius: 8px;
        }
        
        /* Toast Notification Styles */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
            max-width: 400px;
        }
        
        .toast-notification.warning {
            border-left: 4px solid #E67E22;
        }
        
        .toast-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        
        .toast-icon.warning {
            background: #FFF4E6;
        }
        
        .toast-content {
            flex: 1;
        }
        
        .toast-title {
            font-weight: 700;
            font-size: 16px;
            color: #212529;
            margin-bottom: 4px;
        }
        
        .toast-message {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 12px;
        }
        
        .toast-actions {
            display: flex;
            gap: 8px;
        }
        
        .toast-btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        
        .toast-btn-primary {
            background: #E67E22;
            color: white;
        }
        
        .toast-btn-primary:hover {
            background: #d35400;
        }
        
        .toast-btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
        }
        
        .toast-btn-secondary:hover {
            background: #e9ecef;
        }
        
        .toast-close {
            position: absolute;
            top: 12px;
            right: 12px;
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 20px;
            line-height: 1;
            padding: 4px;
        }
        
        .toast-close:hover {
            color: #212529;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        .toast-notification.hiding {
            animation: slideOut 0.3s ease-out forwards;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-2">
                    <a href="index.php" class="flex items-center space-x-2">
                        <div class="logo-icon flex items-center justify-center">
                            <span class="text-white font-bold text-lg">B</span>
                        </div>
                        <span class="text-xl font-bold text-dark">Biletly</span>
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#seferler" class="text-dark hover:text-primary font-medium">Seferler</a>
                    <a href="#" class="text-dark hover:text-primary font-medium">Kampanyalar</a>
                </div>
                
                <?php if ($isLoggedIn): ?>
                    <div class="flex items-center space-x-4">
                        <span class="text-dark font-medium">Merhaba, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Kullanıcı'); ?></span>
                        <?php if ($role === 'admin'): ?>
                            <a href="admin_panel.php" class="bg-secondary hover:bg-purple-600 text-white font-semibold py-2 px-4 rounded-full transition duration-300">
                                Admin Paneli
                            </a>
                        <?php elseif ($role === 'company'): ?>
                            <a href="company_panel.php" class="bg-secondary hover:bg-purple-600 text-white font-semibold py-2 px-4 rounded-full transition duration-300">
                                Firma Paneli
                            </a>
                        <?php endif; ?>
                        <a href="my_account.php" class="bg-primary hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-full transition duration-300">
                            Hesabım
                        </a>
                        <a href="logout.php" class="text-dark hover:text-primary font-medium">Çıkış</a>
                    </div>
                <?php else: ?>
                    <div class="relative group">
                        <button class="bg-primary hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-full transition duration-300 flex items-center">
                            Giriş / Kayıt Ol
                            <svg class="w-4 h-4 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-10">
                            <a href="login.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Giriş Yap</a>
                            <a href="register.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Kayıt Ol</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Search Section -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <form method="GET" action="search.php">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kalkış</label>
                        <select name="from" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Kalkış noktası seçin</option>
                            <?php
                            
                            $cities = $db->query("SELECT name FROM cities ORDER BY name");
                            foreach ($cities as $city) {
                                echo "<option value=\"" . htmlspecialchars($city['name']) . "\">" . htmlspecialchars($city['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Varış</label>
                        <select name="to" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Varış noktası seçin</option>
                            <?php
                            
                            $cities = $db->query("SELECT name FROM cities ORDER BY name");
                            foreach ($cities as $city) {
                                echo "<option value=\"" . htmlspecialchars($city['name']) . "\">" . htmlspecialchars($city['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tarih</label>
                        <div class="relative">
                            <input type="date" name="date" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Yolcu</label>
                        <select name="passengers" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="1">1 Yolcu</option>
                            <option value="2">2 Yolcu</option>
                            <option value="3">3 Yolcu</option>
                            <option value="4">4 Yolcu</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="bg-primary hover:bg-orange-600 text-white font-semibold py-3 px-8 rounded-lg transition duration-300 flex items-center">
                        <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                        Sefer Ara
                    </button>
                </div>
            </form>
        </div>

        <!-- Results Section -->
        <div id="seferler" class="bg-white rounded-xl shadow-md p-6 scroll-mt-20">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-dark">🔥 Popüler Seferler</h2>
                <a href="search.php" class="text-primary hover:text-orange-600 font-medium text-sm">
                    Tüm Seferleri Gör →
                </a>
            </div>
            <div class="space-y-4">
                <?php if (!empty($trips)): ?>
                    <?php foreach ($trips as $trip): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg hover:border-primary transition-all duration-300">
                            <div class="flex flex-col md:flex-row md:items-center justify-between">
                                <div class="flex-1">
                                    <!-- Date Badge -->
                                    <div class="mb-2">
                                        <?php
                                        $trip_date = date('d.m.Y', strtotime($trip['departure_date']));
                                        $is_today = $trip['departure_date'] == date('Y-m-d');
                                        $badge_color = $is_today ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800';
                                        $badge_text = $is_today ? '🔥 Bugün' : '📅 ' . $trip_date;
                                        ?>
                                        <span class="<?php echo $badge_color; ?> text-xs font-semibold px-3 py-1 rounded-full">
                                            <?php echo $badge_text; ?>
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center mb-2">
                                        <div class="text-2xl font-bold text-dark mr-4"><?php echo substr($trip['departure_time'], 0, 5); ?></div>
                                        <div class="text-sm text-gray-500">→</div>
                                        <div class="text-lg text-gray-500 ml-4"><?php echo substr($trip['arrival_time'], 0, 5); ?></div>
                                    </div>
                                    
                                    <div class="font-bold text-dark mb-1 text-lg">
                                        <?php echo $trip['departure_city']; ?> 
                                        <span class="text-primary">→</span> 
                                        <?php echo $trip['arrival_city']; ?>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <?php echo $trip['company_name']; ?> • 
                                        <?php 
                                            
                                            $departure = strtotime($trip['departure_time']);
                                            $arrival = strtotime($trip['arrival_time']);
                                            $duration = ($arrival - $departure) / 3600;
                                            if ($duration < 0) $duration += 24; 
                                            echo 'WiFi, TV, ' . number_format($duration, 1) . ' saat';
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="mt-4 md:mt-0 md:text-right">
                                    <div class="text-2xl font-bold text-primary mb-2"><?php echo number_format($trip['price'], 2); ?> TL</div>
                                    <?php if ($trip['available_seats'] > 0): ?>
                                        <button type="button" class="bg-primary hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-300" onclick="toggleSeatSelection(<?php echo $trip['id']; ?>)">
                                            Koltuk Seç
                                        </button>
                                    <?php else: ?>
                                        <button class="bg-gray-200 text-gray-500 font-semibold py-2 px-4 rounded-lg cursor-not-allowed" disabled>
                                            Satıldı
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between items-center">
                                <div class="text-sm text-gray-500">
                                    Koltuk: <?php echo $trip['available_seats']; ?>/<?php echo $trip['total_seats']; ?> boş
                                </div>
                                <div class="text-sm bg-purple-100 text-purple-800 px-2 py-1 rounded">
                                    <?php echo $trip['bus_plate']; ?>
                                </div>
                            </div>
                            
                            <!-- Seat Selection Container (Initially Hidden) -->
                            <div id="seat-selection-<?php echo $trip['id']; ?>" class="mt-4 hidden">
                                <?php
                                
                                $tickets = $db->query("SELECT seat_number FROM tickets WHERE trip_id = ? AND status = 'active'", [$trip['id']]);
                                $occupied = array_column($tickets, 'seat_number');
                                ?>
                                <div class="bg-gray-50 rounded-lg p-4 mt-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-bold text-dark">Koltuk Seçimi</h3>
                                        <button type="button" class="text-gray-500 hover:text-gray-700" onclick="toggleSeatSelection(<?php echo $trip['id']; ?>)">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <!-- Trip Details Button -->
                                    <button type="button" class="w-full bg-white rounded-lg p-4 mb-4 text-left border border-gray-200 hover:border-gray-300 transition-colors duration-200 flex justify-between items-center" onclick="toggleTripDetails(<?php echo $trip['id']; ?>)">
                                        <span class="font-bold text-dark">Sefer Detayları</span>
                                        <svg id="trip-details-arrow-<?php echo $trip['id']; ?>" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    
                                    <!-- Trip Details Content (Initially Hidden) -->
                                    <div id="trip-details-content-<?php echo $trip['id']; ?>" class="bg-white rounded-lg p-4 mb-4 hidden">
                                        <h4 class="font-bold text-dark mb-2">Özellikler</h4>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Kablosuz Internet (WiFi)</span>
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">220 Voltluk Priz</span>
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Koltuk ekraninda TV yayını</span>
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Rahat Koltuk</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col items-center">
                                        <div class="flex flex-col gap-3 w-full max-w-2xl">
                                            <?php
                                            
                                            
                                            echo '<div class="flex gap-1 items-center justify-center">';
                                            for ($i = 1; $i <= 13; $i++) {
                                                $isOccupied = in_array($i, $occupied);
                                                $color = $isOccupied ? 'bg-gray-300' : 'bg-gray-200';
                                                $textColor = $isOccupied ? 'text-gray-500' : 'text-primary';
                                                $cursor = $isOccupied ? 'cursor-not-allowed' : 'cursor-pointer';
                                                echo '<div class="seat-btn w-8 h-8 rounded-lg '.$color.' '.$textColor.' flex items-center justify-center font-semibold border-2 '.($isOccupied ? 'border-gray-500' : 'border-primary').' '.$cursor.'" data-seat="'.$i.'" data-trip="'.$trip['id'].'" onclick="selectSeat('.$i.', '.$trip['id'].')">'.$i.'</div>';
                                            }
                                            echo '</div>';
                                            
                                            
                                            echo '<div class="h-4 flex items-center justify-center text-gray-500 text-xs italic">KORİDOR</div>';
                                            
                                            
                                            echo '<div class="flex flex-col gap-2 items-center">';
                                            
                                            
                                            echo '<div class="flex gap-1 items-center">';
                                            for ($i = 14; $i <= 26; $i++) {
                                                $isOccupied = in_array($i, $occupied);
                                                $color = $isOccupied ? 'bg-gray-300' : 'bg-gray-200';
                                                $textColor = $isOccupied ? 'text-gray-500' : 'text-primary';
                                                $cursor = $isOccupied ? 'cursor-not-allowed' : 'cursor-pointer';
                                                echo '<div class="seat-btn w-8 h-8 rounded-lg '.$color.' '.$textColor.' flex items-center justify-center font-semibold border-2 '.($isOccupied ? 'border-gray-500' : 'border-primary').' '.$cursor.'" data-seat="'.$i.'" data-trip="'.$trip['id'].'" onclick="selectSeat('.$i.', '.$trip['id'].')">'.$i.'</div>';
                                            }
                                            echo '</div>';
                                            
                                            
                                            echo '<div class="flex gap-1 items-center">';
                                            for ($i = 27; $i <= 39; $i++) {
                                                $isOccupied = in_array($i, $occupied);
                                                $color = $isOccupied ? 'bg-gray-300' : 'bg-gray-200';
                                                $textColor = $isOccupied ? 'text-gray-500' : 'text-primary';
                                                $cursor = $isOccupied ? 'cursor-not-allowed' : 'cursor-pointer';
                                                echo '<div class="seat-btn w-8 h-8 rounded-lg '.$color.' '.$textColor.' flex items-center justify-center font-semibold border-2 '.($isOccupied ? 'border-gray-500' : 'border-primary').' '.$cursor.'" data-seat="'.$i.'" data-trip="'.$trip['id'].'" onclick="selectSeat('.$i.', '.$trip['id'].')">'.$i.'</div>';
                                            }
                                            echo '</div>';
                                            
                                            echo '</div>';
                                            ?>
                                        </div>
                                        
                                        <div class="mt-6 w-full max-w-2xl">
                                            <button type="button" class="w-full bg-primary hover:bg-orange-600 text-white font-semibold py-3 px-4 rounded-lg transition duration-300" onclick="proceedToBooking(<?php echo $trip['id']; ?>)">
                                                Bileti Satın Al
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="bg-gradient-to-r from-orange-50 to-purple-50 border-2 border-orange-200 px-4 py-12 rounded-lg text-center">
                        <div class="text-6xl mb-4">🚌</div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Seferler Yükleniyor...</h3>
                        <p class="text-gray-600 mb-6">Yukarıdaki arama formunu kullanarak istediğiniz rotayı arayabilirsiniz.</p>
                        <a href="search.php" class="inline-block bg-primary hover:bg-orange-600 text-white font-semibold py-3 px-8 rounded-lg transition duration-300">
                            Sefer Ara
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="mt-12 py-6 text-center text-gray-500 text-sm">
        © 2025 Biletly. Tüm hakları saklıdır.
    </footer>
    
    <script src="js/seat-selection.js"></script>
    <script>
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        
        // Toast Notification System
        function showLoginNotification() {
            // Remove existing toast if any
            const existingToast = document.querySelector('.toast-notification');
            if (existingToast) {
                existingToast.remove();
            }
            
            // Create toast
            const toast = document.createElement('div');
            toast.className = 'toast-notification warning';
            toast.innerHTML = `
                <button class="toast-close" onclick="closeToast(this)">&times;</button>
                <div class="toast-icon warning">
                    🔐
                </div>
                <div class="toast-content">
                    <div class="toast-title">Giriş Yapmanız Gerekiyor</div>
                    <div class="toast-message">Bilet satın almak için lütfen giriş yapın veya yeni bir hesap oluşturun.</div>
                    <div class="toast-actions">
                        <button class="toast-btn toast-btn-primary" onclick="window.location.href='login.php'">
                            Giriş Yap
                        </button>
                        <button class="toast-btn toast-btn-secondary" onclick="window.location.href='register.php'">
                            Kayıt Ol
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Auto close after 8 seconds
            setTimeout(() => {
                closeToast(toast.querySelector('.toast-close'));
            }, 8000);
        }
        
        function closeToast(button) {
            const toast = button.closest('.toast-notification');
            toast.classList.add('hiding');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
        
        function toggleSeatSelection(tripId) {
            if (!isLoggedIn) {
                showLoginNotification();
                return;
            }
            const container = document.getElementById('seat-selection-' + tripId);
            container.classList.toggle('hidden');
        }
        
        function toggleTripDetails(tripId) {
            const content = document.getElementById('trip-details-content-' + tripId);
            const arrow = document.getElementById('trip-details-arrow-' + tripId);
            content.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        }
        
        function selectSeat(seatNumber, tripId) {
            if (!isLoggedIn) {
                showLoginNotification();
                return;
            }
            
            const seatBtn = document.querySelector('.seat-btn[data-seat="'+seatNumber+'"][data-trip="'+tripId+'"]');
            if (seatBtn.classList.contains('cursor-not-allowed')) {
                return;
            }
            
            // Deselect all seats for this trip
            document.querySelectorAll('.seat-btn[data-trip="'+tripId+'"]').forEach(btn => {
                btn.classList.remove('bg-primary', 'text-white');
                btn.classList.add('bg-gray-200', 'text-primary');
            });
            
            // Select this seat
            seatBtn.classList.remove('bg-gray-200', 'text-primary');
            seatBtn.classList.add('bg-primary', 'text-white');
        }
        
        function proceedToBooking(tripId) {
            if (!isLoggedIn) {
                showLoginNotification();
                return;
            }
            
            const selectedSeat = document.querySelector('.seat-btn[data-trip="'+tripId+'"].bg-primary');
            if (!selectedSeat) {
                // Show toast for seat selection
                const toast = document.createElement('div');
                toast.className = 'toast-notification warning';
                toast.innerHTML = `
                    <button class="toast-close" onclick="closeToast(this)">&times;</button>
                    <div class="toast-icon warning">⚠️</div>
                    <div class="toast-content">
                        <div class="toast-title">Koltuk Seçimi Gerekli</div>
                        <div class="toast-message">Lütfen devam etmek için bir koltuk seçin.</div>
                    </div>
                `;
                document.body.appendChild(toast);
                setTimeout(() => closeToast(toast.querySelector('.toast-close')), 3000);
                return;
            }
            
            const seatNumber = selectedSeat.getAttribute('data-seat');
            window.location.href = 'buy_ticket.php?trip_id=' + tripId + '&seat=' + seatNumber;
        }
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>