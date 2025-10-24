<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/TripService.php';


Auth::requireRole('firma_admin');

$tripService = new TripService();
$companyId = Auth::getCompanyId();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = [
        'departure_city' => $_POST['departure_city'],
        'arrival_city' => $_POST['arrival_city'],
        'departure_date' => $_POST['departure_date'],
        'departure_time' => $_POST['departure_time'],
        'arrival_time' => $_POST['arrival_time'],
        'price' => $_POST['price'],
        'bus_plate' => $_POST['bus_plate'],
        'total_seats' => 39,
        'available_seats' => 39
    ];
    
    $tripId = $tripService->create($data, $companyId);
    
    if ($tripId) {
        header('Location: manage_trips.php?message=Sefer başarıyla eklendi');
    } else {
        header('Location: manage_trips.php?error=Sefer eklenemedi');
    }
    exit;
}

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $tripId = $_POST['trip_id'];
    $data = [
        'departure_city' => $_POST['departure_city'],
        'arrival_city' => $_POST['arrival_city'],
        'departure_date' => $_POST['departure_date'],
        'departure_time' => $_POST['departure_time'],
        'arrival_time' => $_POST['arrival_time'],
        'price' => $_POST['price'],
        'bus_plate' => $_POST['bus_plate']
    ];
    
    $success = $tripService->update($tripId, $data, $companyId);
    
    if ($success) {
        header('Location: manage_trips.php?message=Sefer başarıyla güncellendi');
    } else {
        header('Location: manage_trips.php?error=Sefer güncellenemedi');
    }
    exit;
}

if ($action === 'delete') {
    
    $tripId = $_GET['trip_id'];
    
    
    if ($tripService->hasTickets($tripId)) {
        header('Location: manage_trips.php?error=Bu seferde satılmış biletler var, silinemez');
        exit;
    }
    
    $success = $tripService->delete($tripId, $companyId);
    
    if ($success) {
        header('Location: manage_trips.php?message=Sefer başarıyla silindi');
    } else {
        header('Location: manage_trips.php?error=Sefer silinemedi');
    }
    exit;
}


header('Location: manage_trips.php?error=Geçersiz işlem');
exit;
?>
