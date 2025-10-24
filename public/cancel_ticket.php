<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];
$ticket_id = $_GET['ticket_id'] ?? null;
$message = '';

if (!$ticket_id) { 
    $message = 'Bilet ID eksik!'; 
} else {
    $ticket = $db->queryOne('SELECT * FROM tickets WHERE id = ? AND user_id = ?', [$ticket_id, $user_id]);
    if (!$ticket || $ticket['status'] !== 'active') { 
        $message = 'Bilet bulunamadı veya zaten iptal edilmiş!'; 
    } else {
        
        $trip = $db->queryOne('SELECT departure_date, departure_time FROM trips WHERE id = ?', [$ticket['trip_id']]);
        $departure = strtotime($trip['departure_date'] . ' ' . $trip['departure_time']);
        if (($departure - time())/3600 < CANCELLATION_LIMIT_HOURS) { 
            $message = 'Bilet iptal süresi geçti! Kalkışa 1 saatten az kaldığı için iptal edilemez.'; 
        } else {
            
            require_once __DIR__ . '/../src/RefundService.php';
            $refundService = new RefundService();
            
            $db->beginTransaction();
            try {
                
                $db->execute('UPDATE tickets SET status = ?, cancellation_date = CURRENT_TIMESTAMP WHERE id = ?', ['cancelled', $ticket_id]);
                
                
                $db->execute('UPDATE trips SET available_seats = available_seats + 1 WHERE id = ?', [$ticket['trip_id']]);
                
                
                $refundSuccess = $refundService->process($ticket_id, $user_id, $ticket['price']);
                
                if ($refundSuccess) {
                    $db->commit();
                    $newBalance = $refundService->getUserBalance($user_id);
                    $message = 'Bilet başarıyla iptal edildi! ' . number_format($ticket['price'], 2) . ' TL hesabınıza iade edildi. Yeni bakiyeniz: ' . number_format($newBalance, 2) . ' TL';
                } else {
                    $db->rollback();
                    $message = 'Bilet iptal edildi ancak iade işlemi başarısız oldu. Lütfen müşteri hizmetleri ile iletişime geçin.';
                }
            } catch (Exception $e) {
                $db->rollback();
                $message = 'İptal işlemi sırasında bir hata oluştu: ' . $e->getMessage();
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
    <title>Biletly - Bilet İptal</title>
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
                <a href="campaigns.php" class="navbar-link">Kampanyalar</a>
            </div>
            
            <div class="navbar-actions">
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
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container section">
        <div class="card fade-in" style="max-width: 600px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">
                    <?php echo (strpos($message, 'başarıyla') !== false) ? '✅' : '❌'; ?>
                </div>
                <h2 style="font-size: 2rem; font-weight: 800; color: #F7FAFC; margin-bottom: 0.5rem;">
                    Bilet İptal İşlemi
                </h2>
            </div>
            
            <?php if ($message): ?>
                <div class="card" style="background: <?php echo (strpos($message, 'başarıyla') !== false) ? 'rgba(34, 197, 94, 0.2)' : 'rgba(239, 68, 68, 0.2)'; ?>; border-color: <?php echo (strpos($message, 'başarıyla') !== false) ? 'rgba(34, 197, 94, 0.4)' : 'rgba(239, 68, 68, 0.4)'; ?>; margin-bottom: 2rem;">
                    <p style="color: #E2E8F0; text-align: center; margin: 0; font-size: 1.125rem; line-height: 1.75;">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="my_account.php" class="btn btn-primary btn-lg">
                    ← Hesabıma Dön
                </a>
                <?php if (strpos($message, 'başarıyla') !== false): ?>
                    <a href="search.php" class="btn btn-outline btn-lg">
                        🔍 Yeni Sefer Ara
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if (strpos($message, 'başarıyla') !== false): ?>
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                    <div style="text-align: center; color: #A0AEC0; font-size: 0.875rem;">
                        <p style="margin-bottom: 0.5rem;">✨ İptal işleminiz başarıyla tamamlandı</p>
                        <p style="margin: 0;">İade tutarı hesabınıza yansıtılmıştır</p>
                    </div>
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