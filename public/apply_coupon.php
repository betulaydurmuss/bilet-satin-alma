<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/CouponService.php';

header('Content-Type: application/json');


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Lütfen giriş yapın']);
    exit;
}


$code = $_POST['code'] ?? '';
$price = (float)($_POST['price'] ?? 0);
$companyId = (int)($_POST['company_id'] ?? 0);

if (empty($code) || $price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
    exit;
}


$couponService = new CouponService();
$coupon = $couponService->validate($code, $companyId);

if (!$coupon) {
    echo json_encode([
        'success' => false,
        'message' => 'Kupon geçersiz, süresi dolmuş veya kullanım limiti aşılmış'
    ]);
    exit;
}


$discount = $couponService->calculateDiscount($price, $coupon);
$finalPrice = $price - $discount;


$_SESSION['applied_coupon'] = [
    'id' => $coupon['id'],
    'code' => $coupon['code'],
    'discount' => $discount
];

echo json_encode([
    'success' => true,
    'message' => 'Kupon başarıyla uygulandı!',
    'discount' => number_format($discount, 2, '.', ''),
    'final_price' => number_format($finalPrice, 2, '.', ''),
    'discount_formatted' => number_format($discount, 2, ',', '.') . ' TL',
    'final_price_formatted' => number_format($finalPrice, 2, ',', '.') . ' TL'
]);
?>
