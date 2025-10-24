<?php

require_once __DIR__ . '/src/config.php';

require_once __DIR__ . '/src/Database.php';

echo "Örnek biletler oluşturuluyor...\n";

try {
    $db = Database::getInstance();
    
    $sampleTickets = [
        [
            'user_id' => 1,
            'trip_id' => 1,
            'seat_number' => '15A',
            'price' => 350.00
        ],
        [
            'user_id' => 1,
            'trip_id' => 2,
            'seat_number' => '22B',
            'price' => 350.00
        ],
        [
            'user_id' => 2,
            'trip_id' => 3,
            'seat_number' => '10C',
            'price' => 320.00
        ]
    ];
    
    foreach ($sampleTickets as $ticket) {
        $exists = $db->queryOne(
            "SELECT id FROM tickets WHERE user_id = ? AND trip_id = ? AND seat_number = ?", 
            [$ticket['user_id'], $ticket['trip_id'], $ticket['seat_number']]
        );
        
        if (!$exists) {
            $db->execute(
                "INSERT INTO tickets (user_id, trip_id, seat_number, price) VALUES (?, ?, ?, ?)",
                [$ticket['user_id'], $ticket['trip_id'], $ticket['seat_number'], $ticket['price']]
            );
            echo "✓ Bilet " . $ticket['seat_number'] . " oluşturuldu (User: " . $ticket['user_id'] . ", Trip: " . $ticket['trip_id'] . ")\n";
        } else {
            echo "• Bilet " . $ticket['seat_number'] . " zaten var\n";
        }
    }
    
    echo "\n✅ Örnek biletler başarıyla oluşturuldu!\n";
    
} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>