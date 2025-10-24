<?php

require_once __DIR__ . '/src/config.php';

require_once __DIR__ . '/src/Database.php';

echo "Örnek kullanıcılar oluşturuluyor...\n";

try {
    $db = Database::getInstance();
    
    $users = [
        [
            'username' => 'admin',
            'email' => 'admin@biletly.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'full_name' => 'Admin Kullanıcı',
            'role' => 'admin',
            'credit' => 10000.00
        ],
        [
            'username' => 'company',
            'email' => 'company@biletly.com',
            'password' => password_hash('company123', PASSWORD_DEFAULT),
            'full_name' => 'Firma Kullanıcı',
            'role' => 'company',
            'credit' => 5000.00
        ],
        [
            'username' => 'user',
            'email' => 'user@biletly.com',
            'password' => password_hash('user123', PASSWORD_DEFAULT),
            'full_name' => 'Normal Kullanıcı',
            'role' => 'user',
            'credit' => 1000.00
        ]
    ];
    
    foreach ($users as $user) {
        $exists = $db->queryOne("SELECT id FROM users WHERE username = ? OR email = ?", [$user['username'], $user['email']]);
        
        if (!$exists) {
            $db->execute(
                "INSERT INTO users (username, email, password, full_name, role, credit) VALUES (?, ?, ?, ?, ?, ?)",
                [$user['username'], $user['email'], $user['password'], $user['full_name'], $user['role'], $user['credit']]
            );
            echo "✓ " . $user['username'] . " kullanıcısı oluşturuldu\n";
        } else {
            echo "• " . $user['username'] . " kullanıcısı zaten var\n";
        }
    }
    
    echo "\n✅ Örnek kullanıcılar başarıyla oluşturuldu!\n";
    echo "Giriş bilgileri:\n";
    echo "  Admin: admin / admin123\n";
    echo "  Firma: company / company123\n";
    echo "  Kullanıcı: user / user123\n";
    
} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>