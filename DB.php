<?php

require_once BASE.'/Config.php';

class DB {

    private static $instance = null;
    
    /**
     *
     * @return PDOConnection
     */
    public static function getConnection()
    {
        if(self::$instance == null) {
            $cfg = Config::get();
            $path = isset($cfg['db_path']) ? $cfg['db_path'] : './.db';
            self::$instance = new PDO('sqlite:'.$path);
        }
        return self::$instance;
    }
}
