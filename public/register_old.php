<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $db = Database::getInstance();
    $exists = $db->queryOne('SELECT id FROM users WHERE username = ? OR email = ?', [$username, $email]);
    if ($exists) {
        $message = 'Kullanıcı adı veya e-posta zaten kayıtlı!';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $db->execute('INSERT INTO users (username, email, password, full_name, role, credit) VALUES (?, ?, ?, ?, ?, ?)', [$username, $email, $hash, $full_name, 'user', DEFAULT_CREDIT]);
        header('Location: login.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Kayıt Ol</title>
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
                
                <div class="flex items-center space-x-4">
                    <button onclick="window.location='login.php'" class="bg-primary hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-full transition duration-300">
                        Giriş
                    </button>
                    <button onclick="window.location='register.php'" class="bg-secondary hover:bg-purple-600 text-white font-semibold py-2 px-4 rounded-full transition duration-300">
                        Kayıt Ol
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <section class="bg-white rounded-xl shadow-md p-6 max-w-md mx-auto">
            <h2 class="text-2xl font-bold text-dark mb-8 text-center">Kayıt Ol</h2>
            
            <?php if ($message): ?>
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg mb-6">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" class="flex flex-col items-center space-y-5">
                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kullanıcı Adı:</label>
                    <input type="text" name="username" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                
                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-posta:</label>
                    <input type="email" name="email" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                
                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad:</label>
                    <input type="text" name="full_name" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                
                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Şifre:</label>
                    <input type="password" name="password" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                
                <button type="submit" class="bg-primary hover:bg-orange-600 text-white font-semibold py-3 px-8 rounded-lg transition duration-300 mt-4 w-full">
                    Kayıt Ol
                </button>
            </form>
            
            <div class="text-center mt-6">
                <p class="text-gray-600">Zaten üye misiniz? <a href="login.php" class="text-primary hover:text-orange-600 font-medium underline">Giriş Yap</a></p>
            </div>
        </section>
    </main>

    <footer class="mt-12 py-6 text-center text-gray-500 text-sm">
        © 2025 Biletly. Tüm hakları saklıdır.
    </footer>
</body>
</html>