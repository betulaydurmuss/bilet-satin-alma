<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = Database::getInstance();
$results = [];
$message = '';

$isLoggedIn = isset($_SESSION['user_id']);

$from = '';
$to = '';
$date = '';
$passengers = 1;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['from']) && isset($_GET['to']) && isset($_GET['date'])) {
    $from = $_GET['from'];
    $to = $_GET['to'];
    $date = $_GET['date'];
    $passengers = $_GET['passengers'] ?? 1;
    
    echo "<pre>";
    echo "FROM: '$from' (length: " . strlen($from) . ")\n";
    echo "TO: '$to' (length: " . strlen($to) . ")\n";
    echo "DATE: '$date'\n";
    echo "Passengers: $passengers\n";
    echo "</pre>";
    
    if (empty($from) || empty($to) || empty($date)) {
        $message = 'Lütfen tüm alanları doldurun!';
    } else {
        echo "<pre>";
        echo "Query: SELECT t.*, c.name as company_name FROM trips t LEFT JOIN companies c ON t.company_id = c.id WHERE t.departure_city = ? AND t.arrival_city = ? AND t.departure_date = ? AND t.status = 'active' AND t.available_seats > 0 ORDER BY t.departure_time\n";
        echo "Parameters: [$from, $to, $date]\n";
        echo "</pre>";
        
        $results = $db->query(
            "SELECT t.*, c.name as company_name FROM trips t LEFT JOIN companies c ON t.company_id = c.id
            WHERE t.departure_city = ? AND t.arrival_city = ? AND t.departure_date = ?
            AND t.status = 'active' AND t.available_seats > 0
            ORDER BY t.departure_time",
            [$from, $to, $date]
        );
        
        echo "<pre>";
        echo "Results count: " . count($results) . "\n";
        if (!empty($results)) {
            echo "First result: " . print_r($results[0], true) . "\n";
        }
        echo "</pre>";
        
        if (empty($results)) {
            $message = 'Seçtiğiniz kriterlere uygun sefer bulunamadı.';
        }
    }
} else {
    echo "<pre>";
    echo "No search parameters received\n";
    echo "GET data: " . print_r($_GET, true) . "\n";
    echo "</pre>";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Sefer Ara (Debug)</title>
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
                    <a href="index.php" class="text-dark hover:text-primary font-medium">Seferler</a>
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
                    <div class="flex items-center space-x-4">
                        <button onclick="window.location='login.php'" class="bg-primary hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-full transition duration-300">
                            Giriş
                        </button>
                        <button onclick="window.location='register.php'" class="bg-secondary hover:bg-purple-600 text-white font-semibold py-2 px-4 rounded-full transition duration-300">
                            Kayıt Ol
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Search Section -->
        <section class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-2xl font-bold text-dark mb-8 text-center">Otobüs Bileti Ara (Debug)</h2>
            <form method="GET" action="debug_search.php">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kalkış Noktası:</label>
                        <select name="from" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Kalkış noktası seçin</option>
                            <option value="Adana" <?php echo ($from == 'Adana') ? 'selected' : ''; ?>>Adana</option>
                            <option value="Adıyaman" <?php echo ($from == 'Adıyaman') ? 'selected' : ''; ?>>Adıyaman</option>
                            <option value="Afyonkarahisar" <?php echo ($from == 'Afyonkarahisar') ? 'selected' : ''; ?>>Afyonkarahisar</option>
                            <option value="Ağrı" <?php echo ($from == 'Ağrı') ? 'selected' : ''; ?>>Ağrı</option>
                            <option value="Aksaray" <?php echo ($from == 'Aksaray') ? 'selected' : ''; ?>>Aksaray</option>
                            <option value="Amasya" <?php echo ($from == 'Amasya') ? 'selected' : ''; ?>>Amasya</option>
                            <option value="Ankara" <?php echo ($from == 'Ankara') ? 'selected' : ''; ?>>Ankara</option>
                            <option value="Antalya" <?php echo ($from == 'Antalya') ? 'selected' : ''; ?>>Antalya</option>
                            <option value="Ardahan" <?php echo ($from == 'Ardahan') ? 'selected' : ''; ?>>Ardahan</option>
                            <option value="Artvin" <?php echo ($from == 'Artvin') ? 'selected' : ''; ?>>Artvin</option>
                            <option value="Aydın" <?php echo ($from == 'Aydın') ? 'selected' : ''; ?>>Aydın</option>
                            <option value="Balıkesir" <?php echo ($from == 'Balıkesir') ? 'selected' : ''; ?>>Balıkesir</option>
                            <option value="Bartın" <?php echo ($from == 'Bartın') ? 'selected' : ''; ?>>Bartın</option>
                            <option value="Batman" <?php echo ($from == 'Batman') ? 'selected' : ''; ?>>Batman</option>
                            <option value="Bayburt" <?php echo ($from == 'Bayburt') ? 'selected' : ''; ?>>Bayburt</option>
                            <option value="Bilecik" <?php echo ($from == 'Bilecik') ? 'selected' : ''; ?>>Bilecik</option>
                            <option value="Bingöl" <?php echo ($from == 'Bingöl') ? 'selected' : ''; ?>>Bingöl</option>
                            <option value="Bitlis" <?php echo ($from == 'Bitlis') ? 'selected' : ''; ?>>Bitlis</option>
                            <option value="Bolu" <?php echo ($from == 'Bolu') ? 'selected' : ''; ?>>Bolu</option>
                            <option value="Burdur" <?php echo ($from == 'Burdur') ? 'selected' : ''; ?>>Burdur</option>
                            <option value="Bursa" <?php echo ($from == 'Bursa') ? 'selected' : ''; ?>>Bursa</option>
                            <option value="Çanakkale" <?php echo ($from == 'Çanakkale') ? 'selected' : ''; ?>>Çanakkale</option>
                            <option value="Çankırı" <?php echo ($from == 'Çankırı') ? 'selected' : ''; ?>>Çankırı</option>
                            <option value="Çorum" <?php echo ($from == 'Çorum') ? 'selected' : ''; ?>>Çorum</option>
                            <option value="Denizli" <?php echo ($from == 'Denizli') ? 'selected' : ''; ?>>Denizli</option>
                            <option value="Diyarbakır" <?php echo ($from == 'Diyarbakır') ? 'selected' : ''; ?>>Diyarbakır</option>
                            <option value="Düzce" <?php echo ($from == 'Düzce') ? 'selected' : ''; ?>>Düzce</option>
                            <option value="Edirne" <?php echo ($from == 'Edirne') ? 'selected' : ''; ?>>Edirne</option>
                            <option value="Elazığ" <?php echo ($from == 'Elazığ') ? 'selected' : ''; ?>>Elazığ</option>
                            <option value="Erzincan" <?php echo ($from == 'Erzincan') ? 'selected' : ''; ?>>Erzincan</option>
                            <option value="Erzurum" <?php echo ($from == 'Erzurum') ? 'selected' : ''; ?>>Erzurum</option>
                            <option value="Eskişehir" <?php echo ($from == 'Eskişehir') ? 'selected' : ''; ?>>Eskişehir</option>
                            <option value="Gaziantep" <?php echo ($from == 'Gaziantep') ? 'selected' : ''; ?>>Gaziantep</option>
                            <option value="Giresun" <?php echo ($from == 'Giresun') ? 'selected' : ''; ?>>Giresun</option>
                            <option value="Gümüşhane" <?php echo ($from == 'Gümüşhane') ? 'selected' : ''; ?>>Gümüşhane</option>
                            <option value="Hakkâri" <?php echo ($from == 'Hakkâri') ? 'selected' : ''; ?>>Hakkâri</option>
                            <option value="Hatay" <?php echo ($from == 'Hatay') ? 'selected' : ''; ?>>Hatay</option>
                            <option value="Iğdır" <?php echo ($from == 'Iğdır') ? 'selected' : ''; ?>>Iğdır</option>
                            <option value="Isparta" <?php echo ($from == 'Isparta') ? 'selected' : ''; ?>>Isparta</option>
                            <option value="İstanbul" <?php echo ($from == 'İstanbul') ? 'selected' : ''; ?>>İstanbul</option>
                            <option value="İzmir" <?php echo ($from == 'İzmir') ? 'selected' : ''; ?>>İzmir</option>
                            <option value="Kahramanmaraş" <?php echo ($from == 'Kahramanmaraş') ? 'selected' : ''; ?>>Kahramanmaraş</option>
                            <option value="Karabük" <?php echo ($from == 'Karabük') ? 'selected' : ''; ?>>Karabük</option>
                            <option value="Karaman" <?php echo ($from == 'Karaman') ? 'selected' : ''; ?>>Karaman</option>
                            <option value="Kars" <?php echo ($from == 'Kars') ? 'selected' : ''; ?>>Kars</option>
                            <option value="Kastamonu" <?php echo ($from == 'Kastamonu') ? 'selected' : ''; ?>>Kastamonu</option>
                            <option value="Kayseri" <?php echo ($from == 'Kayseri') ? 'selected' : ''; ?>>Kayseri</option>
                            <option value="Kilis" <?php echo ($from == 'Kilis') ? 'selected' : ''; ?>>Kilis</option>
                            <option value="Kırıkkale" <?php echo ($from == 'Kırıkkale') ? 'selected' : ''; ?>>Kırıkkale</option>
                            <option value="Kırklareli" <?php echo ($from == 'Kırklareli') ? 'selected' : ''; ?>>Kırklareli</option>
                            <option value="Kırşehir" <?php echo ($from == 'Kırşehir') ? 'selected' : ''; ?>>Kırşehir</option>
                            <option value="Kocaeli" <?php echo ($from == 'Kocaeli') ? 'selected' : ''; ?>>Kocaeli</option>
                            <option value="Konya" <?php echo ($from == 'Konya') ? 'selected' : ''; ?>>Konya</option>
                            <option value="Kütahya" <?php echo ($from == 'Kütahya') ? 'selected' : ''; ?>>Kütahya</option>
                            <option value="Malatya" <?php echo ($from == 'Malatya') ? 'selected' : ''; ?>>Malatya</option>
                            <option value="Manisa" <?php echo ($from == 'Manisa') ? 'selected' : ''; ?>>Manisa</option>
                            <option value="Mardin" <?php echo ($from == 'Mardin') ? 'selected' : ''; ?>>Mardin</option>
                            <option value="Mersin" <?php echo ($from == 'Mersin') ? 'selected' : ''; ?>>Mersin</option>
                            <option value="Muğla" <?php echo ($from == 'Muğla') ? 'selected' : ''; ?>>Muğla</option>
                            <option value="Muş" <?php echo ($from == 'Muş') ? 'selected' : ''; ?>>Muş</option>
                            <option value="Nevşehir" <?php echo ($from == 'Nevşehir') ? 'selected' : ''; ?>>Nevşehir</option>
                            <option value="Niğde" <?php echo ($from == 'Niğde') ? 'selected' : ''; ?>>Niğde</option>
                            <option value="Ordu" <?php echo ($from == 'Ordu') ? 'selected' : ''; ?>>Ordu</option>
                            <option value="Osmaniye" <?php echo ($from == 'Osmaniye') ? 'selected' : ''; ?>>Osmaniye</option>
                            <option value="Rize" <?php echo ($from == 'Rize') ? 'selected' : ''; ?>>Rize</option>
                            <option value="Sakarya" <?php echo ($from == 'Sakarya') ? 'selected' : ''; ?>>Sakarya</option>
                            <option value="Samsun" <?php echo ($from == 'Samsun') ? 'selected' : ''; ?>>Samsun</option>
                            <option value="Şanlıurfa" <?php echo ($from == 'Şanlıurfa') ? 'selected' : ''; ?>>Şanlıurfa</option>
                            <option value="Siirt" <?php echo ($from == 'Siirt') ? 'selected' : ''; ?>>Siirt</option>
                            <option value="Sinop" <?php echo ($from == 'Sinop') ? 'selected' : ''; ?>>Sinop</option>
                            <option value="Sivas" <?php echo ($from == 'Sivas') ? 'selected' : ''; ?>>Sivas</option>
                            <option value="Şırnak" <?php echo ($from == 'Şırnak') ? 'selected' : ''; ?>>Şırnak</option>
                            <option value="Tekirdağ" <?php echo ($from == 'Tekirdağ') ? 'selected' : ''; ?>>Tekirdağ</option>
                            <option value="Tokat" <?php echo ($from == 'Tokat') ? 'selected' : ''; ?>>Tokat</option>
                            <option value="Trabzon" <?php echo ($from == 'Trabzon') ? 'selected' : ''; ?>>Trabzon</option>
                            <option value="Tunceli" <?php echo ($from == 'Tunceli') ? 'selected' : ''; ?>>Tunceli</option>
                            <option value="Uşak" <?php echo ($from == 'Uşak') ? 'selected' : ''; ?>>Uşak</option>
                            <option value="Van" <?php echo ($from == 'Van') ? 'selected' : ''; ?>>Van</option>
                            <option value="Yalova" <?php echo ($from == 'Yalova') ? 'selected' : ''; ?>>Yalova</option>
                            <option value="Yozgat" <?php echo ($from == 'Yozgat') ? 'selected' : ''; ?>>Yozgat</option>
                            <option value="Zonguldak" <?php echo ($from == 'Zonguldak') ? 'selected' : ''; ?>>Zonguldak</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Varış Noktası:</label>
                        <select name="to" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Varış noktası seçin</option>
                            <option value="Adana" <?php echo ($to == 'Adana') ? 'selected' : ''; ?>>Adana</option>
                            <option value="Adıyaman" <?php echo ($to == 'Adıyaman') ? 'selected' : ''; ?>>Adıyaman</option>
                            <option value="Afyonkarahisar" <?php echo ($to == 'Afyonkarahisar') ? 'selected' : ''; ?>>Afyonkarahisar</option>
                            <option value="Ağrı" <?php echo ($to == 'Ağrı') ? 'selected' : ''; ?>>Ağrı</option>
                            <option value="Aksaray" <?php echo ($to == 'Aksaray') ? 'selected' : ''; ?>>Aksaray</option>
                            <option value="Amasya" <?php echo ($to == 'Amasya') ? 'selected' : ''; ?>>Amasya</option>
                            <option value="Ankara" <?php echo ($to == 'Ankara') ? 'selected' : ''; ?>>Ankara</option>
                            <option value="Antalya" <?php echo ($to == 'Antalya') ? 'selected' : ''; ?>>Antalya</option>
                            <option value="Ardahan" <?php echo ($to == 'Ardahan') ? 'selected' : ''; ?>>Ardahan</option>
                            <option value="Artvin" <?php echo ($to == 'Artvin') ? 'selected' : ''; ?>>Artvin</option>
                            <option value="Aydın" <?php echo ($to == 'Aydın') ? 'selected' : ''; ?>>Aydın</option>
                            <option value="Balıkesir" <?php echo ($to == 'Balıkesir') ? 'selected' : ''; ?>>Balıkesir</option>
                            <option value="Bartın" <?php echo ($to == 'Bartın') ? 'selected' : ''; ?>>Bartın</option>
                            <option value="Batman" <?php echo ($to == 'Batman') ? 'selected' : ''; ?>>Batman</option>
                            <option value="Bayburt" <?php echo ($to == 'Bayburt') ? 'selected' : ''; ?>>Bayburt</option>
                            <option value="Bilecik" <?php echo ($to == 'Bilecik') ? 'selected' : ''; ?>>Bilecik</option>
                            <option value="Bingöl" <?php echo ($to == 'Bingöl') ? 'selected' : ''; ?>>Bingöl</option>
                            <option value="Bitlis" <?php echo ($to == 'Bitlis') ? 'selected' : ''; ?>>Bitlis</option>
                            <option value="Bolu" <?php echo ($to == 'Bolu') ? 'selected' : ''; ?>>Bolu</option>
                            <option value="Burdur" <?php echo ($to == 'Burdur') ? 'selected' : ''; ?>>Burdur</option>
                            <option value="Bursa" <?php echo ($to == 'Bursa') ? 'selected' : ''; ?>>Bursa</option>
                            <option value="Çanakkale" <?php echo ($to == 'Çanakkale') ? 'selected' : ''; ?>>Çanakkale</option>
                            <option value="Çankırı" <?php echo ($to == 'Çankırı') ? 'selected' : ''; ?>>Çankırı</option>
                            <option value="Çorum" <?php echo ($to == 'Çorum') ? 'selected' : ''; ?>>Çorum</option>
                            <option value="Denizli" <?php echo ($to == 'Denizli') ? 'selected' : ''; ?>>Denizli</option>
                            <option value="Diyarbakır" <?php echo ($to == 'Diyarbakır') ? 'selected' : ''; ?>>Diyarbakır</option>
                            <option value="Düzce" <?php echo ($to == 'Düzce') ? 'selected' : ''; ?>>Düzce</option>
                            <option value="Edirne" <?php echo ($to == 'Edirne') ? 'selected' : ''; ?>>Edirne</option>
                            <option value="Elazığ" <?php echo ($to == 'Elazığ') ? 'selected' : ''; ?>>Elazığ</option>
                            <option value="Erzincan" <?php echo ($to == 'Erzincan') ? 'selected' : ''; ?>>Erzincan</option>
                            <option value="Erzurum" <?php echo ($to == 'Erzurum') ? 'selected' : ''; ?>>Erzurum</option>
                            <option value="Eskişehir" <?php echo ($to == 'Eskişehir') ? 'selected' : ''; ?>>Eskişehir</option>
                            <option value="Gaziantep" <?php echo ($to == 'Gaziantep') ? 'selected' : ''; ?>>Gaziantep</option>
                            <option value="Giresun" <?php echo ($to == 'Giresun') ? 'selected' : ''; ?>>Giresun</option>
                            <option value="Gümüşhane" <?php echo ($to == 'Gümüşhane') ? 'selected' : ''; ?>>Gümüşhane</option>
                            <option value="Hakkâri" <?php echo ($to == 'Hakkâri') ? 'selected' : ''; ?>>Hakkâri</option>
                            <option value="Hatay" <?php echo ($to == 'Hatay') ? 'selected' : ''; ?>>Hatay</option>
                            <option value="Iğdır" <?php echo ($to == 'Iğdır') ? 'selected' : ''; ?>>Iğdır</option>
                            <option value="Isparta" <?php echo ($to == 'Isparta') ? 'selected' : ''; ?>>Isparta</option>
                            <option value="İstanbul" <?php echo ($to == 'İstanbul') ? 'selected' : ''; ?>>İstanbul</option>
                            <option value="İzmir" <?php echo ($to == 'İzmir') ? 'selected' : ''; ?>>İzmir</option>
                            <option value="Kahramanmaraş" <?php echo ($to == 'Kahramanmaraş') ? 'selected' : ''; ?>>Kahramanmaraş</option>
                            <option value="Karabük" <?php echo ($to == 'Karabük') ? 'selected' : ''; ?>>Karabük</option>
                            <option value="Karaman" <?php echo ($to == 'Karaman') ? 'selected' : ''; ?>>Karaman</option>
                            <option value="Kars" <?php echo ($to == 'Kars') ? 'selected' : ''; ?>>Kars</option>
                            <option value="Kastamonu" <?php echo ($to == 'Kastamonu') ? 'selected' : ''; ?>>Kastamonu</option>
                            <option value="Kayseri" <?php echo ($to == 'Kayseri') ? 'selected' : ''; ?>>Kayseri</option>
                            <option value="Kilis" <?php echo ($to == 'Kilis') ? 'selected' : ''; ?>>Kilis</option>
                            <option value="Kırıkkale" <?php echo ($to == 'Kırıkkale') ? 'selected' : ''; ?>>Kırıkkale</option>
                            <option value="Kırklareli" <?php echo ($to == 'Kırklareli') ? 'selected' : ''; ?>>Kırklareli</option>
                            <option value="Kırşehir" <?php echo ($to == 'Kırşehir') ? 'selected' : ''; ?>>Kırşehir</option>
                            <option value="Kocaeli" <?php echo ($to == 'Kocaeli') ? 'selected' : ''; ?>>Kocaeli</option>
                            <option value="Konya" <?php echo ($to == 'Konya') ? 'selected' : ''; ?>>Konya</option>
                            <option value="Kütahya" <?php echo ($to == 'Kütahya') ? 'selected' : ''; ?>>Kütahya</option>
                            <option value="Malatya" <?php echo ($to == 'Malatya') ? 'selected' : ''; ?>>Malatya</option>
                            <option value="Manisa" <?php echo ($to == 'Manisa') ? 'selected' : ''; ?>>Manisa</option>
                            <option value="Mardin" <?php echo ($to == 'Mardin') ? 'selected' : ''; ?>>Mardin</option>
                            <option value="Mersin" <?php echo ($to == 'Mersin') ? 'selected' : ''; ?>>Mersin</option>
                            <option value="Muğla" <?php echo ($to == 'Muğla') ? 'selected' : ''; ?>>Muğla</option>
                            <option value="Muş" <?php echo ($to == 'Muş') ? 'selected' : ''; ?>>Muş</option>
                            <option value="Nevşehir" <?php echo ($to == 'Nevşehir') ? 'selected' : ''; ?>>Nevşehir</option>
                            <option value="Niğde" <?php echo ($to == 'Niğde') ? 'selected' : ''; ?>>Niğde</option>
                            <option value="Ordu" <?php echo ($to == 'Ordu') ? 'selected' : ''; ?>>Ordu</option>
                            <option value="Osmaniye" <?php echo ($to == 'Osmaniye') ? 'selected' : ''; ?>>Osmaniye</option>
                            <option value="Rize" <?php echo ($to == 'Rize') ? 'selected' : ''; ?>>Rize</option>
                            <option value="Sakarya" <?php echo ($to == 'Sakarya') ? 'selected' : ''; ?>>Sakarya</option>
                            <option value="Samsun" <?php echo ($to == 'Samsun') ? 'selected' : ''; ?>>Samsun</option>
                            <option value="Şanlıurfa" <?php echo ($to == 'Şanlıurfa') ? 'selected' : ''; ?>>Şanlıurfa</option>
                            <option value="Siirt" <?php echo ($to == 'Siirt') ? 'selected' : ''; ?>>Siirt</option>
                            <option value="Sinop" <?php echo ($to == 'Sinop') ? 'selected' : ''; ?>>Sinop</option>
                            <option value="Sivas" <?php echo ($to == 'Sivas') ? 'selected' : ''; ?>>Sivas</option>
                            <option value="Şırnak" <?php echo ($to == 'Şırnak') ? 'selected' : ''; ?>>Şırnak</option>
                            <option value="Tekirdağ" <?php echo ($to == 'Tekirdağ') ? 'selected' : ''; ?>>Tekirdağ</option>
                            <option value="Tokat" <?php echo ($to == 'Tokat') ? 'selected' : ''; ?>>Tokat</option>
                            <option value="Trabzon" <?php echo ($to == 'Trabzon') ? 'selected' : ''; ?>>Trabzon</option>
                            <option value="Tunceli" <?php echo ($to == 'Tunceli') ? 'selected' : ''; ?>>Tunceli</option>
                            <option value="Uşak" <?php echo ($to == 'Uşak') ? 'selected' : ''; ?>>Uşak</option>
                            <option value="Van" <?php echo ($to == 'Van') ? 'selected' : ''; ?>>Van</option>
                            <option value="Yalova" <?php echo ($to == 'Yalova') ? 'selected' : ''; ?>>Yalova</option>
                            <option value="Yozgat" <?php echo ($to == 'Yozgat') ? 'selected' : ''; ?>>Yozgat</option>
                            <option value="Zonguldak" <?php echo ($to == 'Zonguldak') ? 'selected' : ''; ?>>Zonguldak</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tarih:</label>
                        <div class="relative">
                            <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Yolcu Sayısı:</label>
                        <select name="passengers" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="1" <?php echo ($passengers == 1) ? 'selected' : ''; ?>>1 Yolcu</option>
                            <option value="2" <?php echo ($passengers == 2) ? 'selected' : ''; ?>>2 Yolcu</option>
                            <option value="3" <?php echo ($passengers == 3) ? 'selected' : ''; ?>>3 Yolcu</option>
                            <option value="4" <?php echo ($passengers == 4) ? 'selected' : ''; ?>>4 Yolcu</option>
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
        </section>

        <!-- Results Section -->
        <section class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-2xl font-bold text-dark mb-6">Sefer Sonuçları</h2>
            
            <?php if ($message): ?>
                <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg mb-6">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($results)): ?>
                <div class="space-y-4">
                    <?php foreach ($results as $trip): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-300">
                            <div class="flex flex-col md:flex-row md:items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <div class="text-2xl font-bold text-dark mr-4"><?php echo $trip['departure_time']; ?></div>
                                        <div class="text-sm text-gray-500">→</div>
                                        <div class="text-lg text-gray-500 ml-4"><?php echo $trip['arrival_time']; ?></div>
                                    </div>
                                    
                                    <div class="font-bold text-dark mb-1"><?php echo $trip['departure_city']; ?> – <?php echo $trip['arrival_city']; ?></div>
                                    <div class="text-sm text-gray-600">
                                        <?php echo $trip['company_name']; ?> • 
                                        <?php 
                                            $departure = strtotime($trip['departure_time']);
                                            $arrival = strtotime($trip['arrival_time']);
                                            $duration = ($arrival - $departure) / 3600;
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
                                        
                                        <div class="flex gap-4 mt-4">
                                            <div class="flex items-center gap-1">
                                                <div class="w-4 h-4 rounded-sm bg-gray-200 border-2 border-primary"></div>
                                                <span class="text-sm">Müsait</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <div class="w-4 h-4 rounded-sm bg-primary border-2 border-primary"></div>
                                                <span class="text-sm">Seçili</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <div class="w-4 h-4 rounded-sm bg-gray-300 border-2 border-gray-500"></div>
                                                <span class="text-sm">Dolu</span>
                                            </div>
                                        </div>
                                        
                                        <input type="hidden" id="selected-seat-<?php echo $trip['id']; ?>" value="">
                                        
                                        <div class="mt-4 flex gap-2">
                                            <button type="button" class="bg-primary hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-300" onclick="proceedToBooking(<?php echo $trip['id']; ?>)" id="proceed-btn-<?php echo $trip['id']; ?>" disabled>
                                                Onayla ve Devam Et
                                            </button>
                                            <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-300" onclick="toggleSeatSelection(<?php echo $trip['id']; ?>)">
                                                İptal
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif (isset($_GET['from']) && isset($_GET['to']) && isset($_GET['date'])): ?>
                <div class="text-center py-8 text-gray-500">
                    <p>Seçtiğiniz kriterlere uygun sefer bulunamadı.</p>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <p>Sefer aramak için yukarıdaki formu doldurun.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="mt-12 py-6 text-center text-gray-500 text-sm">
        © 2025 Biletly. Tüm hakları saklıdır.
    </footer>
    
    <script>
        function toggleSeatSelection(tripId) {
            const seatSelection = document.getElementById('seat-selection-' + tripId);
            seatSelection.classList.toggle('hidden');
            
            if (seatSelection.classList.contains('hidden')) {
                resetSeatSelection(tripId);
            }
        }
        
        function resetSeatSelection(tripId) {
            document.getElementById('selected-seat-' + tripId).value = '';
            
            const proceedBtn = document.getElementById('proceed-btn-' + tripId);
            if (proceedBtn) {
                proceedBtn.disabled = true;
            }
            
            document.querySelectorAll('.seat-btn[data-trip="' + tripId + '"]').forEach(seat => {
                if (!seat.classList.contains('cursor-not-allowed')) {
                    seat.classList.remove('bg-primary', 'text-white', 'border-primary');
                    seat.classList.add('bg-gray-200', 'text-primary');
                }
            });
        }
        
        function selectSeat(seatNumber, tripId) {
            const seatElement = document.querySelector('.seat-btn[data-seat="' + seatNumber + '"][data-trip="' + tripId + '"]');
            if (seatElement.classList.contains('cursor-not-allowed')) {
                return;
            }
            
            document.querySelectorAll('.seat-btn[data-trip="' + tripId + '"]').forEach(seat => {
                if (!seat.classList.contains('cursor-not-allowed')) {
                    seat.classList.remove('bg-primary', 'text-white', 'border-primary');
                    seat.classList.add('bg-gray-200', 'text-primary');
                }
            });
            
            seatElement.classList.remove('bg-gray-200', 'text-primary');
            seatElement.classList.add('bg-primary', 'text-white', 'border-primary');
            
            document.getElementById('selected-seat-' + tripId).value = seatNumber;
            
            const proceedBtn = document.getElementById('proceed-btn-' + tripId);
            if (proceedBtn) {
                proceedBtn.disabled = false;
            }
        }
        
        function proceedToBooking(tripId) {
            const selectedSeat = document.getElementById('selected-seat-' + tripId).value;
            if (selectedSeat) {
                <?php if ($isLoggedIn): ?>
                    window.location.href = 'buy_ticket.php?trip_id=' + tripId + '&seat=' + selectedSeat;
                <?php else: ?>
                    if (confirm('Bilet almak için giriş yapmanız gerekmektedir. Giriş yapmak ister misiniz?')) {
                        window.location.href = 'login.php?redirect=buy_ticket.php&trip_id=' + tripId + '&seat=' + selectedSeat;
                    }
                <?php endif; ?>
            }
        }
    </script>
</body>
</html>