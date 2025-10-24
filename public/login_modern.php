<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'user';
    if ($role === 'admin') {
        header('Location: admin_panel.php');
    } elseif ($role === 'company') {
        header('Location: company_panel.php');
    } else {
        header('Location: my_account.php');
    }
    exit;
}

$db = Database::getInstance();
$error = '';
$success = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Lütfen tüm alanları doldurun!';
    } else {
        $user = $db->queryOne('SELECT * FROM users WHERE username = ? OR email = ?', [$username, $username]);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['company_id'] = $user['company_id'];
            
            
            if ($user['role'] === 'admin') {
                header('Location: admin_panel.php');
            } elseif ($user['role'] === 'company') {
                header('Location: company_panel.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = 'Kullanıcı adı veya şifre hatalı!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Biletly</title>
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
            max-width: 480px;
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
        
        .alert-success {
            background: rgba(72, 187, 120, 0.1);
            border: 1px solid rgba(72, 187, 120, 0.3);
            color: #68D391;
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
                <h1 class="auth-title">Hoş Geldiniz</h1>
                <p class="auth-subtitle">Hesabınıza giriş yapın</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✅ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">👤 Kullanıcı Adı veya E-posta</label>
                    <input 
                        type="text" 
                        name="username" 
                        class="form-control" 
                        placeholder="kullaniciadi veya email@example.com"
                        required
                        autofocus
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
                    >
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; color: #CBD5E0; cursor: pointer;">
                        <input type="checkbox" style="width: 18px; height: 18px; cursor: pointer;">
                        <span style="font-size: 0.95rem;">Beni Hatırla</span>
                    </label>
                    <a href="#" class="auth-link" style="font-size: 0.95rem;">Şifremi Unuttum</a>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.05rem;">
                    🚀 Giriş Yap
                </button>
            </form>
            
            <div class="divider">
                <span>veya</span>
            </div>
            
            <div style="text-align: center;">
                <p style="color: #A0AEC0; margin-bottom: 1rem;">
                    Hesabınız yok mu?
                </p>
                <a href="register.php" class="btn btn-outline" style="width: 100%; padding: 1rem;">
                    ✨ Yeni Hesap Oluştur
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
