<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = Database::getInstance();


$isLoggedIn = isset($_SESSION['user_id']);

$trip_id = $_GET['trip_id'] ?? null;
if (!$trip_id) {
  echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-center">Sefer bulunamadı.</div>';
  exit;
}

$trip = $db->queryOne("SELECT t.*, c.name as company_name FROM trips t LEFT JOIN companies c ON t.company_id = c.id WHERE t.id = ?", [$trip_id]);
if (!$trip) {
  echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-center">Sefer bulunamadı.</div>';
  exit;
}

$tickets = $db->query("SELECT seat_number FROM tickets WHERE trip_id = ? AND status = 'active'", [$trip_id]);
$occupied = array_column($tickets, 'seat_number');
$total_seats = $trip['total_seats'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Sefer Detayı</title>
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
        .seat-btn {
            transition: all 0.2s ease;
        }
        .seat-btn:hover:not(.occupied):not(.selected) {
            transform: scale(1.05);
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
                    <a href="index.php#popular-trips" class="text-dark hover:text-primary font-medium">Seferler</a>
                    <a href="#" class="text-dark hover:text-primary font-medium">Kampanyalar</a>
                </div>
                
                <?php if ($isLoggedIn): ?>
                    <div class="flex items-center space-x-4">
                        <span class="text-dark font-medium">Merhaba, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Kullanıcı'); ?></span>
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
        <section class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-dark mb-6 text-center">Sefer Detayı</h2>
            
            <div class="text-center text-dark font-medium bg-gray-100 p-6 rounded-lg mb-6">
                <span class="text-lg">Firma: <strong><?php echo $trip['company_name']; ?></strong></span><br>
                <span><?php echo $trip['departure_city']; ?> → <?php echo $trip['arrival_city']; ?> | <?php echo $trip['departure_date']; ?> - <?php echo $trip['departure_time']; ?></span><br>
                <span>Koltuk: <?php echo $trip['available_seats']; ?>/<?php echo $trip['total_seats']; ?> | Fiyat: <strong><?php echo number_format($trip['price'],2); ?> TL</strong></span>
            </div>
            
            <h3 class="text-lg font-bold text-dark my-6 text-center">Koltuk Seçimi (Bilet almak için giriş yapmanız gerekecektir)</h3>
            
            <div class="flex flex-col gap-4 items-center my-8">
                <?php
                    
                    
                    echo '<div class="flex gap-2 items-center justify-center">';
                    for ($i = 1; $i <= 13; $i++) {
                        $isOccupied = in_array($i, $occupied);
                        if ($isOccupied) {
                            echo '<div class="seat-btn w-8 h-8 rounded-lg bg-gray-300 cursor-not-allowed flex items-center justify-center font-semibold text-gray-500 border-2 border-gray-500" data-seat="'.$i.'">'.$i.'</div>';
                        } else {
                            echo '<div class="seat-btn w-8 h-8 rounded-lg bg-gray-200 cursor-pointer flex items-center justify-center font-semibold text-primary border-2 border-primary" data-seat="'.$i.'">'.$i.'</div>';
                        }
                    }
                    echo '</div>';
                    
                    
                    echo '<div class="h-5 flex items-center justify-center text-gray-500 italic">KORİDOR</div>';
                    
                    
                    echo '<div class="flex flex-col gap-2 items-center">';
                    
                    
                    echo '<div class="flex gap-2 items-center">';
                    for ($i = 14; $i <= 26; $i++) {
                        $isOccupied = in_array($i, $occupied);
                        if ($isOccupied) {
                            echo '<div class="seat-btn w-8 h-8 rounded-lg bg-gray-300 cursor-not-allowed flex items-center justify-center font-semibold text-gray-500 border-2 border-gray-500" data-seat="'.$i.'">'.$i.'</div>';
                        } else {
                            echo '<div class="seat-btn w-8 h-8 rounded-lg bg-gray-200 cursor-pointer flex items-center justify-center font-semibold text-primary border-2 border-primary" data-seat="'.$i.'">'.$i.'</div>';
                        }
                    }
                    echo '</div>';
                    
                    
                    echo '<div class="flex gap-2 items-center">';
                    for ($i = 27; $i <= 39; $i++) {
                        $isOccupied = in_array($i, $occupied);
                        if ($isOccupied) {
                            echo '<div class="seat-btn w-8 h-8 rounded-lg bg-gray-300 cursor-not-allowed flex items-center justify-center font-semibold text-gray-500 border-2 border-gray-500" data-seat="'.$i.'">'.$i.'</div>';
                        } else {
                            echo '<div class="seat-btn w-8 h-8 rounded-lg bg-gray-200 cursor-pointer flex items-center justify-center font-semibold text-primary border-2 border-primary" data-seat="'.$i.'">'.$i.'</div>';
                        }
                    }
                    echo '</div>';
                    
                    echo '</div>';
                ?>
                
                <div class="flex gap-6 mt-6 justify-center">
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded-sm bg-gray-200 border-2 border-primary"></div>
                        <span>Müsait Koltuk</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded-sm bg-gray-300 border-2 border-gray-500"></div>
                        <span>Dolu Koltuk</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded-sm bg-primary border-2 border-primary"></div>
                        <span>Seçili Koltuk</span>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-6">
                <button id="continueBtn" class="bg-primary hover:bg-orange-600 text-white font-semibold py-2 px-6 rounded-lg transition duration-300 inline-block opacity-50 cursor-not-allowed" disabled>
                    Bilet Al
                </button>
                <a href="search.php" class="ml-4 text-primary hover:text-orange-600 font-medium underline">
                    Sefer Aramaya Dön
                </a>
            </div>
        </section>
    </main>

    <footer class="mt-12 py-6 text-center text-gray-500 text-sm">
        © 2025 Biletly. Tüm hakları saklıdır.
    </footer>
    
    <script>
        let selectedSeat = null;
        
        // Check if a seat was passed in the URL
        const urlParams = new URLSearchParams(window.location.search);
        const seatParam = urlParams.get('seat');
        if (seatParam) {
            selectSeat(parseInt(seatParam));
        }
        
        function selectSeat(seatNumber) {
            console.log('Selecting seat:', seatNumber);
            // Remove selection from all seats
            document.querySelectorAll('.seat-btn').forEach(seat => {
                if (!seat.classList.contains('bg-gray-300')) { // If not occupied
                    seat.classList.remove('bg-primary', 'text-white', 'border-primary');
                    seat.classList.add('bg-gray-200', 'text-primary');
                }
            });
            
            // Select the clicked seat
            const seatElement = document.querySelector(`.seat-btn[data-seat="${seatNumber}"]`);
            if (seatElement && !seatElement.classList.contains('bg-gray-300')) { // If not occupied
                seatElement.classList.remove('bg-gray-200', 'text-primary');
                seatElement.classList.add('bg-primary', 'text-white', 'border-primary');
                selectedSeat = seatNumber;
                
                // Enable the continue button
                const continueBtn = document.getElementById('continueBtn');
                continueBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                continueBtn.classList.add('opacity-100', 'cursor-pointer');
                continueBtn.disabled = false;
                
                // Add click event to continue button
                continueBtn.onclick = function() {
                    <?php if ($isLoggedIn): ?>
                        window.location.href = 'buy_ticket.php?trip_id=<?php echo $trip_id; ?>&seat=' + selectedSeat;
                    <?php else: ?>
                        // Show login prompt but keep seat selection
                        if (confirm('Koltuk seçiminiz yapıldı. Bilet almak için giriş yapmanız gerekmektedir. Giriş yapmak ister misiniz?')) {
                            // Pass the selected seat to the login page so it can be preserved after login
                            window.location.href = 'login.php?redirect=buy_ticket.php&trip_id=<?php echo $trip_id; ?>&seat=' + selectedSeat;
                        }
                    <?php endif; ?>
                };
            }
        }
        
        // Add click event listeners to all seat buttons
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.seat-btn').forEach(seat => {
                if (!seat.classList.contains('cursor-not-allowed')) {
                    seat.addEventListener('click', function() {
                        const seatNumber = parseInt(this.getAttribute('data-seat'));
                        selectSeat(seatNumber);
                    });
                }
            });
        });
    </script>
</body>
</html>