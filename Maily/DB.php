<?php

namespace Maily;

require_once __DIR__.'/Config.php';

class DB {

    private static $instance = null;
    
    /**
     *
     * @return PDO
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
