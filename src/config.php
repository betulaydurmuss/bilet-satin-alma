<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);


ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');


if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);  
    ini_set('session.use_strict_mode', 1);   
}


define('DB_PATH', __DIR__ . '/../data/bilet_satin_alma.db');


define('SITE_NAME', 'Bilet Satın Alma Platformu');
define('SITE_URL', 'http://localhost/Bilet-satin-alma/public');


define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');


define('ROLE_ADMIN', 'admin');
define('ROLE_FIRMA_ADMIN', 'firma_admin');
define('ROLE_USER', 'user');


define('CANCELLATION_LIMIT_HOURS', 1);


define('DEFAULT_CREDIT', 1000.00);


define('DATE_FORMAT', 'd.m.Y');
define('TIME_FORMAT', 'H:i');
define('DATETIME_FORMAT', 'd.m.Y H:i');


define('ITEMS_PER_PAGE', 10);





spl_autoload_register(function ($class) {
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});





if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>