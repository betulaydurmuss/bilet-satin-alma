<?php












class Database {
    
    
    private static $instance = null;
    private $connection = null;
    
    


    private function __construct() {
        try {
            
            $this->connection = new PDO('sqlite:' . DB_PATH);
            
            
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            
            $this->connection->exec('PRAGMA foreign_keys = ON');
            
            
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            die("Veritabanı bağlantı hatası: " . $e->getMessage());
        }
    }
    
    



    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    


    public function getConnection() {
        return $this->connection;
    }
    
    






    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Query hatası: " . $e->getMessage());
            return [];
        }
    }
    
    






    public function queryOne($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("QueryOne hatası: " . $e->getMessage());
            return false;
        }
    }
    
    






    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Execute hatası: " . $e->getMessage());
            return false;
        }
    }
    
    



    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    



    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    


    public function commit() {
        if ($this->connection->inTransaction()) {
            return $this->connection->commit();
        }
        return false;
    }
    
    


    public function rollback() {
        if ($this->connection->inTransaction()) {
            return $this->connection->rollBack();
        }
        return false;
    }
    
    


    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
?>