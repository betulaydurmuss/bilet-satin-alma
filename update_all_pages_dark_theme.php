<?php


echo "=== TÜM SAYFALAR KOYU TEMA İLE GÜNCELLENİYOR ===\n\n";

$register_html = <<<'HTML'
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

$db = Database::getInstance();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Lütfen tüm alanları doldurun!';
    } elseif ($password !== $password_confirm) {
        $error = 'Şifreler eşleşmiyor!';
    } elseif (strlen($password) < 6) {
        $error = 'Şifre en az 6 karakter olmalıdır!';
    } else {
        $exists = $db->queryOne('SELECT id FROM users WHERE username = ? OR email = ?', [$username, $email]);
        if ($exists) {
            $error = 'Kullanıcı adı veya e-posta zaten kayıtlı!';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $result = $db->execute(
                'INSERT INTO users (username, email, password, full_name, role, credit) VALUES (?, ?, ?, ?, ?, ?)',
                [$username, $email, $hash, $full_name, 'user', DEFAULT_CREDIT]
            );
            
            if ($result) {
                $_SESSION['success_message'] = 'Kayıt başarılı! Giriş yapabilirsiniz.';
                header('Location: login.php');
                exit;
            } else {
                $error = 'Kayıt sırasında bir hata oluştu!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Biletly</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern-style.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .auth-card {
            background: rgba(45, 55, 72, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3rem;
            max-width: 520px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(108, 99, 255, 0.3);
            animation: fadeIn 0.5s ease-out;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .auth-logo {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #6C63FF 0%, #5548E6 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 20px rgba(108, 99, 255, 0.4);
        }
        
        .auth-logo-text {
            color: white;
            font-size: 2rem;
            font-weight: 900;
        }
        
        .auth-title {
            font-size: 2rem;
            font-weight: 800;
            color: #F7FAFC;
            margin-bottom: 0.5rem;
        }
        
        .auth-subtitle {
            color: #A0AEC0;
            font-size: 1rem;
        }
        
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .alert-error {
            background: rgba(245, 101, 101, 0.1);
            border: 1px solid rgba(245, 101, 101, 0.3);
            color: #FC8181;
        }
        
        .form-label {
            color: #CBD5E0;
        }
        
        .auth-link {
            color: #6C63FF;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        
        .auth-link:hover {
            color: #8B84FF;
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 2rem 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(108, 99, 255, 0.2);
        }
        
        .divider span {
            padding: 0 1rem;
            color: #A0AEC0;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <span class="auth-logo-text">B</span>
                </div>
                <h1 class="auth-title">Hesap Oluştur</h1>
                <p class="auth-subtitle">Biletly'ye katılın</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">👤 Ad Soyad</label>
                    <input 
                        type="text" 
                        name="full_name" 
                        class="form-control" 
                        placeholder="Ahmet Yılmaz"
                        required
                        autofocus
                    >
                </div>
                
                <div class="form-group">
                    <label class="form-label">🏷️ Kullanıcı Adı</label>
                    <input 
                        type="text" 
                        name="username" 
                        class="form-control" 
                        placeholder="kullaniciadi"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label class="form-label">📧 E-posta</label>
                    <input 
                        type="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="email@example.com"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label class="form-label">🔒 Şifre</label>
                    <input 
                        type="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="••••••••"
                        required
                        minlength="6"
                    >
                </div>
                
                <div class="form-group">
                    <label class="form-label">🔒 Şifre Tekrar</label>
                    <input 
                        type="password" 
                        name="password_confirm" 
                        class="form-control" 
                        placeholder="••••••••"
                        required
                        minlength="6"
                    >
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.05rem;">
                    ✨ Hesap Oluştur
                </button>
            </form>
            
            <div class="divider">
                <span>veya</span>
            </div>
            
            <div style="text-align: center;">
                <p style="color: #A0AEC0; margin-bottom: 1rem;">
                    Zaten hesabınız var mı?
                </p>
                <a href="login.php" class="btn btn-outline" style="width: 100%; padding: 1rem;">
                    🚀 Giriş Yap
                </a>
            </div>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="index.php" class="auth-link">
                    ← Ana Sayfaya Dön
                </a>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

file_put_contents('public/register.php', $register_html);
echo "✅ register.php güncellendi\n\n";

echo "=== TAMAMLANDI ===\n";
echo "Güncellenen dosyalar:\n";
echo "- public/register.php\n";
echo "- public/login.php (önceden güncellendi)\n";
echo "- public/index.php (önceden güncellendi)\n";
echo "- public/css/modern-style.css (koyu tema)\n";
?>
