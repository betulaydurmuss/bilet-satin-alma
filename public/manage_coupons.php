<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/CouponService.php';


Auth::requireAnyRole(['admin', 'firma_admin']);

$db = Database::getInstance();
$couponService = new CouponService();
$role = Auth::getRole();
$companyId = Auth::getCompanyId();

$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';


if ($role === 'admin') {
    $coupons = $couponService->getAll(null); 
} else {
    $coupons = $couponService->getAll($companyId); 
}


$companies = [];
if ($role === 'admin') {
    $companies = $db->query("SELECT id, name FROM companies ORDER BY name");
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kupon Yönetimi - Biletly</title>
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
                <a href="<?php echo $role === 'admin' ? 'admin_panel.php' : 'company_panel.php'; ?>" class="navbar-link">Panel</a>
            </div>
            
            <div class="navbar-actions">
                <div class="user-info">
                    <div class="user-avatar" style="background: linear-gradient(135deg, <?php echo $role === 'admin' ? '#F56565 0%, #E53E3E' : '#8E44AD 0%, #6C63FF'; ?> 100%);">
                        <?php echo $role === 'admin' ? '👑' : '🏢'; ?>
                    </div>
                    <span style="font-weight: 600; color: var(--gray-700);">
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
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
                <h2 style="font-size: 2rem; font-weight: 800; color: #FFFFFF; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    🎟️ Kupon Yönetimi
                </h2>
                <div style="display: flex; gap: 1rem;">
                    <button onclick="openAddModal()" class="btn btn-primary">
                        ➕ Yeni Kupon Ekle
                    </button>
                    <a href="<?php echo $role === 'admin' ? 'admin_panel.php' : 'company_panel.php'; ?>" class="btn btn-ghost btn-sm">
                        ← Geri
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="card" style="background: rgba(72, 187, 120, 0.2); border-color: rgba(72, 187, 120, 0.4); margin-bottom: 2rem;">
                    <p style="color: #E2E8F0; text-align: center; margin: 0; font-weight: 600;">
                        ✅ <?php echo htmlspecialchars($message); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="card" style="background: rgba(245, 101, 101, 0.2); border-color: rgba(245, 101, 101, 0.4); margin-bottom: 2rem;">
                    <p style="color: #E2E8F0; text-align: center; margin: 0; font-weight: 600;">
                        ❌ <?php echo htmlspecialchars($error); ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($coupons as $coupon): ?>
                    <div class="border rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-lg font-bold text-blue-600"><?php echo htmlspecialchars($coupon['code']); ?></h3>
                            <span class="px-2 py-1 text-xs rounded <?php echo $coupon['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo $coupon['status'] === 'active' ? 'Aktif' : 'Pasif'; ?>
                            </span>
                        </div>
                        
                        <div class="space-y-1 text-sm mb-3">
                            <p><strong>İndirim:</strong> 
                                <?php 
                                if ($coupon['discount_type'] === 'percentage') {
                                    echo '%' . $coupon['discount_value'];
                                } else {
                                    echo number_format($coupon['discount_value'], 2) . ' TL';
                                }
                                ?>
                            </p>
                            <p><strong>Firma:</strong> <?php echo $coupon['company_name'] ?? 'Genel'; ?></p>
                            <p><strong>Geçerlilik:</strong> <?php echo date('d.m.Y', strtotime($coupon['valid_from'])); ?> - <?php echo date('d.m.Y', strtotime($coupon['valid_until'])); ?></p>
                            <p><strong>Kullanım:</strong> <?php echo $coupon['current_uses']; ?>/<?php echo $coupon['max_uses'] ?? '∞'; ?></p>
                        </div>
                        
                        <div class="flex space-x-2">
                            <button onclick='openEditModal(<?php echo json_encode($coupon); ?>)' class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                Düzenle
                            </button>
                            <button onclick="deleteCoupon(<?php echo $coupon['id']; ?>)" class="flex-1 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                Sil
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($coupons)): ?>
                <div class="text-center py-8 text-gray-500">
                    Henüz kupon bulunmuyor. Yeni kupon ekleyebilirsiniz.
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Add/Edit Modal -->
    <div id="couponModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 id="modalTitle" class="text-lg font-bold mb-4">Yeni Kupon Ekle</h3>
            <form id="couponForm" action="coupon_actions.php" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="coupon_id" id="couponId">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Kupon Kodu</label>
                    <input type="text" name="code" id="code" required class="w-full p-2 border rounded uppercase" placeholder="HOSGELDIN">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">İndirim Tipi</label>
                    <select name="discount_type" id="discountType" required class="w-full p-2 border rounded">
                        <option value="percentage">Yüzde (%)</option>
                        <option value="fixed">Sabit Tutar (TL)</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">İndirim Değeri</label>
                    <input type="number" name="discount_value" id="discountValue" step="0.01" required class="w-full p-2 border rounded">
                </div>

                <?php if ($role === 'admin'): ?>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Firma (Opsiyonel)</label>
                    <select name="company_id" id="companyId" class="w-full p-2 border rounded">
                        <option value="">Genel Kupon</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?php echo $company['id']; ?>"><?php echo htmlspecialchars($company['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Başlangıç Tarihi</label>
                    <input type="date" name="valid_from" id="validFrom" required class="w-full p-2 border rounded">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Bitiş Tarihi</label>
                    <input type="date" name="valid_until" id="validUntil" required class="w-full p-2 border rounded">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Maksimum Kullanım (Opsiyonel)</label>
                    <input type="number" name="max_uses" id="maxUses" class="w-full p-2 border rounded" placeholder="Sınırsız">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Durum</label>
                    <select name="status" id="status" required class="w-full p-2 border rounded">
                        <option value="active">Aktif</option>
                        <option value="inactive">Pasif</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded">İptal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Yeni Kupon Ekle';
            document.getElementById('formAction').value = 'add';
            document.getElementById('couponForm').reset();
            document.getElementById('couponModal').classList.remove('hidden');
        }

        function openEditModal(coupon) {
            document.getElementById('modalTitle').textContent = 'Kupon Düzenle';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('couponId').value = coupon.id;
            document.getElementById('code').value = coupon.code;
            document.getElementById('discountType').value = coupon.discount_type;
            document.getElementById('discountValue').value = coupon.discount_value;
            <?php if ($role === 'admin'): ?>
            document.getElementById('companyId').value = coupon.company_id || '';
            <?php endif; ?>
            document.getElementById('validFrom').value = coupon.valid_from;
            document.getElementById('validUntil').value = coupon.valid_until;
            document.getElementById('maxUses').value = coupon.max_uses || '';
            document.getElementById('status').value = coupon.status;
            document.getElementById('couponModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('couponModal').classList.add('hidden');
        }

        function deleteCoupon(id) {
            if (confirm('Bu kuponu silmek istediğinizden emin misiniz?')) {
                window.location.href = 'coupon_actions.php?action=delete&coupon_id=' + id;
            }
        }
    </script>
</body>
</html>
