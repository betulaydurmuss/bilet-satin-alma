<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$message = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            $full_name = trim($_POST['full_name']);
            $company_id = (int)$_POST['company_id'];
            
            if (!empty($username) && !empty($password) && !empty($full_name) && $company_id > 0) {
                
                $existing = $db->queryOne("SELECT id FROM users WHERE username = ?", [$username]);
                
                if ($existing) {
                    $message = 'Bu kullanıcı adı zaten kullanılıyor!';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $result = $db->execute(
                        "INSERT INTO users (username, password, full_name, role, company_id, credit) VALUES (?, ?, ?, 'firma_admin', ?, 0)",
                        [$username, $hashed_password, $full_name, $company_id]
                    );
                    $message = $result ? 'Firma Admin başarıyla eklendi!' : 'Firma Admin eklenirken hata oluştu.';
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            $result = $db->execute("DELETE FROM users WHERE id = ? AND role = 'firma_admin'", [$id]);
            $message = $result ? 'Firma Admin başarıyla silindi!' : 'Firma Admin silinirken hata oluştu.';
        }
    }
}


$firma_admins = $db->query("SELECT u.*, c.name as company_name FROM users u LEFT JOIN companies c ON u.company_id = c.id WHERE u.role = 'firma_admin' ORDER BY u.username");


$companies = $db->query("SELECT * FROM companies ORDER BY name");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Firma Admin Yönetimi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern-style.css">
    <script src="https://cdn.tailwindcss.com"></script>
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
                <a href="admin_panel.php" class="navbar-link">Admin Panel</a>
            </div>
            
            <div class="navbar-actions">
                <div class="user-info">
                    <div class="user-avatar" style="background: linear-gradient(135deg, #F56565 0%, #E53E3E 100%);">
                        👑
                    </div>
                    <span style="font-weight: 600; color: var(--gray-700);">
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                    </span>
                </div>
                <a href="logout.php" class="btn btn-ghost btn-sm">Çıkış</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container section">
        <div class="card fade-in">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 style="font-size: 2rem; font-weight: 800; color: #FFFFFF; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    👤 Firma Admin Yönetimi
                </h2>
                <a href="admin_panel.php" class="btn btn-ghost btn-sm">
                    ← Geri
                </a>
            </div>

            <?php if ($message): ?>
                <div class="card" style="background: <?php echo strpos($message, 'başarıyla') !== false ? 'rgba(72, 187, 120, 0.2)' : 'rgba(245, 101, 101, 0.2)'; ?>; border-color: <?php echo strpos($message, 'başarıyla') !== false ? 'rgba(72, 187, 120, 0.4)' : 'rgba(245, 101, 101, 0.4)'; ?>; margin-bottom: 2rem;">
                    <p style="color: #E2E8F0; text-align: center; margin: 0; font-weight: 600;">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Add Firma Admin Form -->
            <div style="background: rgba(45, 55, 72, 0.4); border-radius: var(--radius-xl); padding: 2rem; margin-bottom: 3rem;">
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #FFFFFF; margin-bottom: 1.5rem;">
                    ➕ Yeni Firma Admin Ekle
                </h3>
                <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label class="form-label" style="color: #E2E8F0;">Kullanıcı Adı:</label>
                        <input type="text" name="username" required class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color: #E2E8F0;">Şifre:</label>
                        <input type="password" name="password" required class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color: #E2E8F0;">Ad Soyad:</label>
                        <input type="text" name="full_name" required class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color: #E2E8F0;">Firma:</label>
                        <select name="company_id" required class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF;">
                            <option value="">Firma Seçin</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?php echo $company['id']; ?>"><?php echo htmlspecialchars($company['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            ➕ Ekle
                        </button>
                    </div>
                </form>
            </div>

            <!-- Firma Admins List -->
            <h3 style="font-size: 1.5rem; font-weight: 700; color: #FFFFFF; margin-bottom: 1.5rem;">
                📋 Firma Adminleri (<?php echo count($firma_admins); ?>)
            </h3>
            
            <?php if (empty($firma_admins)): ?>
                <div class="card" style="background: rgba(79, 172, 254, 0.2); border-color: rgba(79, 172, 254, 0.4); text-align: center; padding: 3rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">👤</div>
                    <p style="color: #E2E8F0; font-size: 1.125rem;">Henüz firma admin eklenmemiş</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto; background: rgba(45, 55, 72, 0.4); border-radius: var(--radius-xl); padding: 1.5rem;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid rgba(108, 99, 255, 0.3);">
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">ID</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Kullanıcı Adı</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Ad Soyad</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Firma</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 700; color: #E2E8F0;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($firma_admins as $admin): ?>
                                <tr style="border-bottom: 1px solid rgba(108, 99, 255, 0.1); transition: all 0.2s;" onmouseover="this.style.background='rgba(108, 99, 255, 0.1)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 1rem; color: #CBD5E0;"><?php echo $admin['id']; ?></td>
                                    <td style="padding: 1rem; color: #FFFFFF; font-weight: 600;"><?php echo htmlspecialchars($admin['username']); ?></td>
                                    <td style="padding: 1rem; color: #A0AEC0;"><?php echo htmlspecialchars($admin['full_name']); ?></td>
                                    <td style="padding: 1rem; color: #A0AEC0;"><?php echo htmlspecialchars($admin['company_name'] ?? '-'); ?></td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Bu firma admini silmek istediğinizden emin misiniz?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $admin['id']; ?>">
                                            <button type="submit" class="btn btn-sm" style="background: linear-gradient(135deg, #F56565 0%, #E53E3E 100%); color: white;">
                                                🗑️ Sil
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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
</body>
</html>
