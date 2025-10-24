<?php




class Auth {
    



    public static function getRole(): ?string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['role'] ?? null;
    }
    
    



    public static function getUserId(): ?int {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_id'] ?? null;
    }
    
    



    public static function getCompanyId(): ?int {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['company_id'] ?? null;
    }
    
    



    public static function isLoggedIn(): bool {
        return self::getUserId() !== null;
    }
    
    




    public static function hasRole(string $role): bool {
        return self::getRole() === $role;
    }
    
    




    public static function hasAnyRole(array $roles): bool {
        $userRole = self::getRole();
        return $userRole && in_array($userRole, $roles);
    }
    
    





    public static function requireRole(string $role, string $message = ''): void {
        if (!self::hasRole($role)) {
            self::redirectUnauthorized($message ?: "Bu sayfaya erişim için '$role' yetkisi gereklidir.");
        }
    }
    
    





    public static function requireAnyRole(array $roles, string $message = ''): void {
        if (!self::hasAnyRole($roles)) {
            $roleList = implode(', ', $roles);
            self::redirectUnauthorized($message ?: "Bu sayfaya erişim için şu yetkilerden biri gereklidir: $roleList");
        }
    }
    
    



    public static function requireLogin(string $message = ''): void {
        if (!self::isLoggedIn()) {
            self::redirectToLogin($message ?: 'Bu işlem için giriş yapmanız gerekmektedir.');
        }
    }
    
    



    public static function redirectToLogin(string $message = ''): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($message) {
            $_SESSION['error_message'] = $message;
        }
        
        
        $currentPage = $_SERVER['REQUEST_URI'];
        $_SESSION['redirect_after_login'] = $currentPage;
        
        header('Location: /Bilet-satın-alma/public/login.php');
        exit;
    }
    
    



    public static function redirectUnauthorized(string $message = 'Bu sayfaya erişim yetkiniz yok.'): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['error_message'] = $message;
        
        
        $role = self::getRole();
        
        if (!$role) {
            
            header('Location: /Bilet-satın-alma/public/login.php');
        } elseif ($role === 'admin') {
            header('Location: /Bilet-satın-alma/public/admin_panel.php');
        } elseif ($role === 'firma_admin') {
            header('Location: /Bilet-satın-alma/public/company_panel.php');
        } else {
            header('Location: /Bilet-satın-alma/public/index.php');
        }
        
        exit;
    }
    
    





    public static function canAccess(string $resource, ?int $resourceOwnerId = null): bool {
        $role = self::getRole();
        $userId = self::getUserId();
        
        
        if ($role === 'admin') {
            return true;
        }
        
        
        if ($resourceOwnerId && $userId === $resourceOwnerId) {
            return true;
        }
        
        return false;
    }
    
    




    public static function canAccessCompany(int $companyId): bool {
        $role = self::getRole();
        $userCompanyId = self::getCompanyId();
        
        
        if ($role === 'admin') {
            return true;
        }
        
        
        if ($role === 'firma_admin' && $userCompanyId === $companyId) {
            return true;
        }
        
        return false;
    }
}
?>
