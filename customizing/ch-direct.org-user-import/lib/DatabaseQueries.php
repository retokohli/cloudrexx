<?php
class DatabaseQueries
{
    public static function getInstance()
    {
        static $dbConnection = null;
        if (!$dbConnection) {
            try {
                $dbConnection = new PDO(
                    "mysql:host=" . Config::DATABASE_HOST
                    . ";dbname=" . Config::DATABASE_NAME,
                    \Config::DATABASE_USER, Config::DATABASE_PW,
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
                );
                $dbConnection->setAttribute(PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return $dbConnection;
    }

    /**
     * Run any database query
     *
     * Caches queries to optimize performance.
     * @param   string  $query
     * @return  mixed
     */
    public static function query($query)
    {
        static $result_cache = array();
        $uniqueQueryHash = hash('ripemd160', $query);
        if (!isset($result_cache[$uniqueQueryHash])) {
            $result = static::getInstance()->query($query);
            $result_cache[$uniqueQueryHash] = $result->fetchAll();
        }
        return $result_cache[$uniqueQueryHash];
    }

}
