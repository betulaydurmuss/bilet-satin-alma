<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/TripService.php';


Auth::requireRole('firma_admin');

$db = Database::getInstance();
$tripService = new TripService();
$companyId = Auth::getCompanyId();

$message = '';
$page = $_GET['page'] ?? 1;


$trips = $tripService->getCompanyTrips($companyId, $page, 20);
$totalTrips = $tripService->getCompanyTripCount($companyId);
$totalPages = ceil($totalTrips / 20);


$cities = $tripService->getCities();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sefer Yönetimi - Biletly</title>
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
                <a href="company_panel.php" class="navbar-link">Firma Panel</a>
                <a href="manage_coupons.php" class="navbar-link">Kuponlar</a>
            </div>
            
            <div class="navbar-actions">
                <div class="user-info">
                    <div class="user-avatar" style="background: linear-gradient(135deg, #8E44AD 0%, #6C63FF 100%);">
                        🏢
                    </div>
                    <span style="font-weight: 600; color: var(--gray-700);">
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'Firma'); ?>
                    </span>
                </div>
                <a href="logout.php" class="btn btn-ghost btn-sm">Çıkış</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container section">
        <div class="card fade-in">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 style="font-size: 2rem; font-weight: 800; color: #FFFFFF; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        🚌 Sefer Yönetimi
                    </h2>
                    <p style="color: #A0AEC0; margin-top: 0.5rem;">Toplam <?php echo $totalTrips; ?> sefer</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button onclick="openAddModal()" class="btn btn-primary">
                        ➕ Yeni Sefer Ekle
                    </button>
                    <a href="company_panel.php" class="btn btn-ghost btn-sm">
                        ← Geri
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="card" style="background: <?php echo strpos($message, 'başarı') !== false ? 'rgba(72, 187, 120, 0.2)' : 'rgba(245, 101, 101, 0.2)'; ?>; border-color: <?php echo strpos($message, 'başarı') !== false ? 'rgba(72, 187, 120, 0.4)' : 'rgba(245, 101, 101, 0.4)'; ?>; margin-bottom: 2rem;">
                    <p style="color: #E2E8F0; text-align: center; margin: 0; font-weight: 600;">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (empty($trips)): ?>
                <div class="card" style="background: rgba(79, 172, 254, 0.2); border-color: rgba(79, 172, 254, 0.4); text-align: center; padding: 3rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🚌</div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #F7FAFC; margin-bottom: 0.5rem;">
                        Henüz sefer eklenmemiş
                    </h3>
                    <p style="color: #A0AEC0; margin-bottom: 1.5rem;">
                        Yeni sefer eklemek için yukarıdaki butona tıklayın.
                    </p>
                    <button onclick="openAddModal()" class="btn btn-primary">
                        ➕ İlk Seferi Ekle
                    </button>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto; background: rgba(45, 55, 72, 0.4); border-radius: var(--radius-xl); padding: 1.5rem;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid rgba(108, 99, 255, 0.3);">
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Rota</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Tarih</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Kalkış</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Varış</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Fiyat</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Koltuk</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 700; color: #E2E8F0;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trips as $trip): ?>
                                <tr style="border-bottom: 1px solid rgba(108, 99, 255, 0.1); transition: all 0.2s;" onmouseover="this.style.background='rgba(108, 99, 255, 0.1)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 1rem; color: #FFFFFF; font-weight: 600;">
                                        <?php echo htmlspecialchars($trip['departure_city']); ?> → <?php echo htmlspecialchars($trip['arrival_city']); ?>
                                    </td>
                                    <td style="padding: 1rem; color: #A0AEC0;"><?php echo date('d.m.Y', strtotime($trip['departure_date'])); ?></td>
                                    <td style="padding: 1rem; color: #A0AEC0;"><?php echo substr($trip['departure_time'], 0, 5); ?></td>
                                    <td style="padding: 1rem; color: #A0AEC0;"><?php echo substr($trip['arrival_time'], 0, 5); ?></td>
                                    <td style="padding: 1rem; color: #48BB78; font-weight: 700;"><?php echo number_format($trip['price'], 0); ?> ₺</td>
                                    <td style="padding: 1rem; color: #CBD5E0;">
                                        <span style="<?php echo $trip['available_seats'] == 0 ? 'color: #F56565;' : ''; ?>">
                                            <?php echo $trip['available_seats']; ?>/<?php echo $trip['total_seats']; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <button onclick='openEditModal(<?php echo json_encode($trip); ?>)' class="btn btn-sm" style="background: linear-gradient(135deg, #4FACFE 0%, #00F2FE 100%); color: white; margin-right: 0.5rem;">
                                            ✏️ Düzenle
                                        </button>
                                        <button onclick="deleteTrip(<?php echo $trip['id']; ?>)" class="btn btn-sm" style="background: linear-gradient(135deg, #F56565 0%, #E53E3E 100%); color: white;">
                                            🗑️ Sil
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="btn btn-sm <?php echo $i == $page ? 'btn-primary' : 'btn-ghost'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
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

    <!-- Add/Edit Modal -->
    <div id="tripModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" style="backdrop-filter: blur(4px);">
        <div class="relative top-10 mx-auto p-0 border-0 shadow-2xl rounded-2xl" style="max-width: 600px; background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);">
            <div style="padding: 2rem;">
                <h3 id="modalTitle" style="font-size: 1.5rem; font-weight: 800; color: #FFFFFF; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    🚌 Yeni Sefer Ekle
                </h3>
                <form id="tripForm" action="trip_actions.php" method="POST">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="trip_id" id="tripId">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label class="form-label" style="color: #E2E8F0;">📍 Kalkış Şehri</label>
                            <select name="departure_city" id="departureCity" required class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF;">
                                <option value="">Şehir Seçin</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?php echo htmlspecialchars($city['name']); ?>"><?php echo htmlspecialchars($city['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="color: #E2E8F0;">📍 Varış Şehri</label>
                            <select name="arrival_city" id="arrivalCity" required class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF;">
                                <option value="">Şehir Seçin</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?php echo htmlspecialchars($city['name']); ?>"><?php echo htmlspecialchars($city['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="color: #E2E8F0;">📅 Tarih</label>
                        <input type="date" name="departure_date" id="departureDate" required class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF;" min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label class="form-label" style="color: #E2E8F0;">🕐 Kalkış Saati</label>
                            <input type="time" name="departure_time" id="departureTime" required class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF;">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="color: #E2E8F0;">🕐 Varış Saati</label>
                            <input type="time" name="arrival_time" id="arrivalTime" required class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label class="form-label" style="color: #E2E8F0;">💰 Fiyat (TL)</label>
                            <input type="number" name="price" id="price" step="0.01" min="0" required class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF;" placeholder="0.00">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="color: #E2E8F0;">🚌 Plaka</label>
                            <input type="text" name="bus_plate" id="busPlate" required class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF;" placeholder="34 ABC 123">
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                        <button type="button" onclick="closeModal()" class="btn btn-ghost">
                            İptal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            💾 Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Yeni Sefer Ekle';
            document.getElementById('formAction').value = 'add';
            document.getElementById('tripForm').reset();
            document.getElementById('tripModal').classList.remove('hidden');
        }

        function openEditModal(trip) {
            document.getElementById('modalTitle').textContent = 'Sefer Düzenle';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('tripId').value = trip.id;
            document.getElementById('departureCity').value = trip.departure_city;
            document.getElementById('arrivalCity').value = trip.arrival_city;
            document.getElementById('departureDate').value = trip.departure_date;
            document.getElementById('departureTime').value = trip.departure_time;
            document.getElementById('arrivalTime').value = trip.arrival_time;
            document.getElementById('price').value = trip.price;
            document.getElementById('busPlate').value = trip.bus_plate;
            document.getElementById('tripModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('tripModal').classList.add('hidden');
        }

        function deleteTrip(id) {
            if (confirm('Bu seferi silmek istediğinizden emin misiniz?')) {
                window.location.href = 'trip_actions.php?action=delete&trip_id=' + id;
            }
        }
    </script>
</body>
</html>
