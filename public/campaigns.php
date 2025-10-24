<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = Database::getInstance();


$isLoggedIn = isset($_SESSION['user_id']);


$activeCoupons = $db->query("SELECT code, discount_type, discount_value, valid_from, valid_until, max_uses, current_uses FROM coupons WHERE status = 'active' AND (valid_until IS NULL OR valid_until >= date('now')) ORDER BY discount_value DESC");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Kampanyalar</title>
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
                <a href="index.php#seferler" class="navbar-link">Seferler</a>
                <a href="campaigns.php" class="navbar-link" style="color: var(--primary);">Kampanyalar</a>
            </div>
            
            <div class="navbar-actions">
                <?php if ($isLoggedIn): ?>
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                        </div>
                        <span style="font-weight: 600; color: var(--gray-700);">
                            <?php echo htmlspecialchars($_SESSION['username'] ?? 'Kullanıcı'); ?>
                        </span>
                    </div>
                    <a href="my_account.php" class="btn btn-primary btn-sm">💼 Hesabım</a>
                    <a href="logout.php" class="btn btn-ghost btn-sm">Çıkış</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline btn-sm">Giriş Yap</a>
                    <a href="register.php" class="btn btn-primary btn-sm">Kayıt Ol</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container section">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 3rem;" class="fade-in">
            <div style="font-size: 4rem; margin-bottom: 1rem;">🎉</div>
            <h1 style="font-size: 2.5rem; font-weight: 800; color: #F7FAFC; margin-bottom: 0.5rem;">
                Aktif Kampanyalar
            </h1>
            <p style="font-size: 1.125rem; color: #A0AEC0;">
                Bilet alırken bu kupon kodlarını kullanarak indirim kazanın!
            </p>
        </div>

        <?php if (!empty($activeCoupons)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
                <?php foreach ($activeCoupons as $coupon): ?>
                    <div class="card fade-in" style="position: relative; overflow: hidden;">
                        <!-- Decorative corner -->
                        <div style="position: absolute; top: 0; right: 0; width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); transform: rotate(45deg) translate(40px, -40px);"></div>
                        
                        <div style="position: relative; z-index: 1;">
                            <!-- Discount Badge -->
                            <div style="display: inline-block; background: linear-gradient(135deg, #48BB78 0%, #38A169 100%); color: white; padding: 0.75rem 1.5rem; border-radius: var(--radius-lg); font-weight: 800; font-size: 1.5rem; margin-bottom: 1rem; box-shadow: 0 4px 15px rgba(72, 187, 120, 0.4);">
                                <?php 
                                    if ($coupon['discount_type'] === 'percentage') {
                                        echo '%' . $coupon['discount_value'] . ' İndirim';
                                    } else {
                                        echo number_format($coupon['discount_value'], 0) . ' ₺ İndirim';
                                    }
                                ?>
                            </div>
                            
                            <!-- Coupon Code -->
                            <div style="background: rgba(108, 99, 255, 0.2); border: 2px dashed var(--primary); border-radius: var(--radius-lg); padding: 1rem; margin-bottom: 1rem; text-align: center;">
                                <div style="font-size: 0.75rem; color: #A0AEC0; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 1px;">
                                    Kupon Kodu
                                </div>
                                <div style="font-size: 1.75rem; font-weight: 800; color: #FFFFFF; font-family: 'Courier New', monospace; letter-spacing: 2px;">
                                    <?php echo htmlspecialchars($coupon['code']); ?>
                                </div>
                            </div>
                            
                            <!-- Details -->
                            <div style="display: flex; flex-direction: column; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                                <?php if ($coupon['valid_from']): ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #A0AEC0; font-size: 0.875rem;">
                                        <span>🗓️</span>
                                        <span>Başlangıç: <?php echo date('d.m.Y', strtotime($coupon['valid_from'])); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($coupon['valid_until']): ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #A0AEC0; font-size: 0.875rem;">
                                        <span>📅</span>
                                        <span>Son Kullanma: <?php echo date('d.m.Y', strtotime($coupon['valid_until'])); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($coupon['max_uses']): ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #A0AEC0; font-size: 0.875rem;">
                                        <span>👥</span>
                                        <span>Kalan Kullanım: <?php echo ($coupon['max_uses'] - $coupon['current_uses']); ?> / <?php echo $coupon['max_uses']; ?></span>
                                    </div>
                                <?php else: ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #48BB78; font-size: 0.875rem;">
                                        <span>♾️</span>
                                        <span>Sınırsız Kullanım</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Copy Button -->
                            <button onclick="copyCouponCode('<?php echo htmlspecialchars($coupon['code']); ?>')" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                                📋 Kodu Kopyala
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card fade-in" style="text-align: center; padding: 4rem 2rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">😔</div>
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #F7FAFC; margin-bottom: 0.5rem;">
                    Aktif Kampanya Bulunamadı
                </h3>
                <p style="color: #A0AEC0; margin-bottom: 2rem;">
                    Şu anda aktif bir kampanya bulunmamaktadır. Yakında yeni kampanyalar için takipte kalın!
                </p>
                <a href="index.php" class="btn btn-primary">
                    Ana Sayfaya Dön
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Info Box -->
        <div class="card fade-in" style="margin-top: 3rem; background: rgba(108, 99, 255, 0.1); border-color: rgba(108, 99, 255, 0.3);">
            <div style="display: flex; align-items: start; gap: 1rem;">
                <div style="font-size: 2rem;">💡</div>
                <div>
                    <h3 style="font-size: 1.125rem; font-weight: 700; color: #F7FAFC; margin-bottom: 0.5rem;">
                        Kupon Nasıl Kullanılır?
                    </h3>
                    <ol style="color: #E2E8F0; line-height: 1.8; padding-left: 1.5rem;">
                        <li>Sefer arayın ve koltuk seçin</li>
                        <li>Bilet alma sayfasında kupon kodu bölümüne kampanya kodunu girin</li>
                        <li>"Uygula" butonuna tıklayın</li>
                        <li>İndiriminiz otomatik olarak uygulanacaktır</li>
                    </ol>
                </div>
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
        function copyCouponCode(code) {
            // Copy to clipboard
            navigator.clipboard.writeText(code).then(() => {
                // Show success toast
                const toast = document.createElement('div');
                toast.className = 'toast-notification toast-success';
                toast.innerHTML = `
                    <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
                    <div class="toast-icon success">✅</div>
                    <div class="toast-content">
                        <div class="toast-title">Kupon Kodu Kopyalandı!</div>
                        <div class="toast-message">${code} kodu panoya kopyalandı. Bilet alırken kullanabilirsiniz.</div>
                    </div>
                `;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            }).catch(err => {
                console.error('Kopyalama hatası:', err);
            });
        }
    </script>
</body>
</html>
