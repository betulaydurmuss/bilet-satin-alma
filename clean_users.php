<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Cleaning user accounts...\n\n";

echo "Current users in database:\n";
$allUsers = $db->query("SELECT id, username, email, role FROM users ORDER BY role, id");
foreach ($allUsers as $user) {
    echo "  - ID: {$user['id']} | Username: {$user['username']} | Email: {$user['email']} | Role: {$user['role']}\n";
}
echo "\nTotal users: " . count($allUsers) . "\n\n";

$userCount = $db->queryOne("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$adminCount = $db->queryOne("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
$firmaCount = $db->queryOne("SELECT COUNT(*) as count FROM users WHERE role = 'firma_admin'");

echo "Users by role:\n";
echo "  - Regular users: {$userCount['count']}\n";
echo "  - Admins: {$adminCount['count']}\n";
echo "  - Firma admins: {$firmaCount['count']}\n\n";

echo "Deleting regular users (role = 'user')...\n";

$db->beginTransaction();

try {
    $ticketsDeleted = $db->execute("DELETE FROM tickets WHERE user_id IN (SELECT id FROM users WHERE role = 'user')");
    echo "  - Deleted tickets: " . ($ticketsDeleted ? "✓" : "✗") . "\n";
    
    $refundsDeleted = $db->execute("DELETE FROM refunds WHERE user_id IN (SELECT id FROM users WHERE role = 'user')");
    echo "  - Deleted refunds: " . ($refundsDeleted ? "✓" : "✗") . "\n";
    
    $usersDeleted = $db->execute("DELETE FROM users WHERE role = 'user'");
    echo "  - Deleted users: " . ($usersDeleted ? "✓" : "✗") . "\n";
    
    $db->commit();
    
    echo "\n✅ Regular users cleaned successfully!\n\n";
    
    echo "Remaining users:\n";
    $remainingUsers = $db->query("SELECT id, username, email, role FROM users ORDER BY role, id");
    if (empty($remainingUsers)) {
        echo "  No users remaining.\n";
    } else {
        foreach ($remainingUsers as $user) {
            echo "  - ID: {$user['id']} | Username: {$user['username']} | Email: {$user['email']} | Role: {$user['role']}\n";
        }
    }
    
} catch (Exception $e) {
    $db->rollback();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
}
?>
