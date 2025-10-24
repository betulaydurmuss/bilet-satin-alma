<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/CouponService.php';


Auth::requireAnyRole(['admin', 'firma_admin']);

$couponService = new CouponService();
$role = Auth::getRole();
$companyId = Auth::getCompanyId();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'code' => strtoupper($_POST['code']),
        'discount_type' => $_POST['discount_type'],
        'discount_value' => $_POST['discount_value'],
        'company_id' => null,
        'valid_from' => $_POST['valid_from'],
        'valid_until' => $_POST['valid_until'],
        'max_uses' => $_POST['max_uses'] ?: null,
        'status' => $_POST['status']
    ];
    
    
    if ($role === 'admin') {
        $data['company_id'] = $_POST['company_id'] ?: null;
    } else {
        $data['company_id'] = $companyId;
    }
    
    try {
        $couponService->create($data);
        header('Location: manage_coupons.php?message=Kupon başarıyla eklendi');
    } catch (Exception $e) {
        header('Location: manage_coupons.php?error=Kupon eklenemedi: ' . $e->getMessage());
    }
    exit;
}

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $couponId = $_POST['coupon_id'];
    
    
    $existingCoupon = $couponService->getById($couponId);
    if ($role === 'firma_admin' && $existingCoupon['company_id'] != $companyId) {
        header('Location: manage_coupons.php?error=Bu kuponu düzenleme yetkiniz yok');
        exit;
    }
    
    $data = [
        'code' => strtoupper($_POST['code']),
        'discount_type' => $_POST['discount_type'],
        'discount_value' => $_POST['discount_value'],
        'valid_from' => $_POST['valid_from'],
        'valid_until' => $_POST['valid_until'],
        'max_uses' => $_POST['max_uses'] ?: null,
        'status' => $_POST['status']
    ];
    
    $success = $couponService->update($couponId, $data);
    
    if ($success) {
        header('Location: manage_coupons.php?message=Kupon başarıyla güncellendi');
    } else {
        header('Location: manage_coupons.php?error=Kupon güncellenemedi');
    }
    exit;
}

if ($action === 'delete') {
    $couponId = $_GET['coupon_id'];
    
    
    $existingCoupon = $couponService->getById($couponId);
    if ($role === 'firma_admin' && $existingCoupon['company_id'] != $companyId) {
        header('Location: manage_coupons.php?error=Bu kuponu silme yetkiniz yok');
        exit;
    }
    
    $success = $couponService->delete($couponId);
    
    if ($success) {
        header('Location: manage_coupons.php?message=Kupon başarıyla silindi');
    } else {
        header('Location: manage_coupons.php?error=Kupon silinemedi');
    }
    exit;
}

header('Location: manage_coupons.php?error=Geçersiz işlem');
exit;
?>
