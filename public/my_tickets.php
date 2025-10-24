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
$tickets = $db->query('SELECT t.*, tr.departure_city, tr.arrival_city, tr.departure_date, tr.departure_time FROM tickets t LEFT JOIN trips tr ON t.trip_id = tr.id WHERE t.user_id = ?', [$user_id]);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Biletlerim</title>
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
                <a href="index.php" class="navbar-link">Seferler</a>
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
        <div class="card fade-in">
            <h2 style="font-size: 2rem; font-weight: 800; color: #F7FAFC; margin-bottom: 2rem; text-align: center;">
                🎫 Biletlerim
            </h2>
            
            <?php if (count($tickets) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">ID</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Kalkış</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Varış</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Tarih</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Saat</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Koltuk</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Durum</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="py-3 px-4 text-sm text-gray-700"><?php echo $ticket['id']; ?></td>
                                    <td class="py-3 px-4 text-sm text-gray-700"><?php echo $ticket['departure_city']; ?></td>
                                    <td class="py-3 px-4 text-sm text-gray-700"><?php echo $ticket['arrival_city']; ?></td>
                                    <td class="py-3 px-4 text-sm text-gray-700"><?php echo $ticket['departure_date']; ?></td>
                                    <td class="py-3 px-4 text-sm text-gray-700"><?php echo $ticket['departure_time']; ?></td>
                                    <td class="py-3 px-4 text-sm text-gray-700"><?php echo $ticket['seat_number']; ?></td>
                                    <td class="py-3 px-4 text-sm">
                                        <span class="<?php echo $ticket['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> px-2 py-1 rounded-full text-xs font-semibold">
                                            <?php echo $ticket['status']; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-700">
                                      <?php if ($ticket['status'] === 'active'): ?>
                                        <a href="ticket_detail.php?id=<?php echo $ticket['id']; ?>" class="text-primary hover:text-orange-600 font-medium underline mr-4">Detaylar</a>
                                        <a href="download_ticket_pdf.php?id=<?php echo $ticket['id']; ?>&download=1" class="text-primary hover:text-orange-600 font-medium underline mr-4">PDF</a>
                                        <a href="cancel_ticket.php?ticket_id=<?php echo $ticket['id']; ?>" class="text-primary hover:text-orange-600 font-medium">İptal Et</a>
                                      <?php else: ?>
                                        <a href="ticket_detail.php?id=<?php echo $ticket['id']; ?>" class="text-primary hover:text-orange-600 font-medium underline mr-4">Detaylar</a>
                                        <a href="download_ticket_pdf.php?id=<?php echo $ticket['id']; ?>&download=1" class="text-primary hover:text-orange-600 font-medium underline">PDF</a>
                                      <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="card" style="background: rgba(79, 172, 254, 0.2); border-color: rgba(79, 172, 254, 0.4); text-align: center; padding: 3rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🎫</div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #F7FAFC; margin-bottom: 0.5rem;">
                        Biletiniz bulunmuyor
                    </h3>
                    <p style="color: #A0AEC0; margin-bottom: 1.5rem;">
                        Henüz hiç bilet almadınız.
                    </p>
                    <a href="index.php" class="btn btn-primary">
                        🎫 Bilet Al
                    </a>
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