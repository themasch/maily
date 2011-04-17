<?php
/**
 * Description of Config
 *
 * @author masch
 */
class Config {

    public static $content;
    
    public static function readConfig($path)
    {
        self::$content = json_decode(file_get_contents($path), true);
        return self::$content;
    }

    public static function get()
    {
        return self::$content;
    }
}
