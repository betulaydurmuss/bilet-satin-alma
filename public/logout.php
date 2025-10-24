<?php
require_once __DIR__ . '/../src/config.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$_SESSION = array();


if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}


session_destroy();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Çıkış</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern-style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes checkmark {
            0% {
                transform: scale(0) rotate(0deg);
                opacity: 0;
            }
            50% {
                transform: scale(1.2) rotate(180deg);
                opacity: 1;
            }
            100% {
                transform: scale(1) rotate(360deg);
                opacity: 1;
            }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
        .checkmark {
            animation: checkmark 0.6s ease-out;
        }
    </style>
</head>
<body>
    <!-- Modern Navbar -->
    <nav class="modern-navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand">
                <div class="logo-icon">B</div>
                <span class="logo-text">Biletly</span>
            </a>
            
            <div class="navbar-menu">
                <a href="index.php" class="navbar-link">Seferler</a>
                <a href="#" class="navbar-link">Kampanyalar</a>
            </div>
            
            <div class="navbar-actions">
                <a href="login.php" class="btn btn-outline btn-sm">Giriş Yap</a>
                <a href="register.php" class="btn btn-primary btn-sm">Kayıt Ol</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container section" style="min-height: 70vh; display: flex; align-items: center; justify-content: center;">
        <div class="card fade-in" style="max-width: 500px; width: 100%; text-align: center;">
            <!-- Success Icon -->
            <div style="margin-bottom: 2rem;">
                <div class="checkmark" style="width: 100px; height: 100px; margin: 0 auto; background: linear-gradient(135deg, #48BB78 0%, #38A169 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 40px rgba(72, 187, 120, 0.3);">
                    <svg style="width: 60px; height: 60px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Title -->
            <h2 style="font-size: 2rem; font-weight: 800; color: #FFFFFF; margin-bottom: 1rem;">
                👋 Güle Güle!
            </h2>
            
            <!-- Message -->
            <div style="background: rgba(72, 187, 120, 0.2); border: 2px solid rgba(72, 187, 120, 0.3); border-radius: var(--radius-xl); padding: 2rem; margin-bottom: 2rem;">
                <p style="font-size: 1.125rem; color: #E2E8F0; margin-bottom: 0.5rem; font-weight: 600;">
                    ✅ Başarıyla çıkış yapıldı
                </p>
                <p style="font-size: 0.95rem; color: #A0AEC0;">
                    Oturumunuz güvenli bir şekilde kapatıldı.
                </p>
            </div>
            
            <!-- Redirect Info -->
            <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; color: #A0AEC0; font-size: 0.875rem; margin-bottom: 2rem;">
                <div class="pulse" style="width: 8px; height: 8px; background: var(--primary); border-radius: 50%;"></div>
                <span>Ana sayfaya yönlendiriliyorsunuz...</span>
            </div>
            
            <!-- Action Buttons -->
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="index.php" class="btn btn-primary btn-lg">
                    🏠 Ana Sayfaya Git
                </a>
                <a href="login.php" class="btn btn-secondary btn-lg">
                    🔐 Tekrar Giriş Yap
                </a>
            </div>
        </div>
    </div>

    <!-- Modern Footer -->
    <footer class="modern-footer">
        <div class="footer-content">
            <p class="footer-text">
                © 2025 Biletly. Tüm hakları saklıdır. | Modern ve güvenli otobüs bileti sistemi
            </p>
        </div>
    </footer>
    
    <script>
        // Redirect after 3 seconds
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 3000);
    </script>
</body>
</html>