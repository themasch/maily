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
        $default = json_decode(file_get_contents(__DIR__.'/config.default.json'), true);
        $local   = json_decode(file_get_contents($path), true);
        $mix = $default;
        foreach($local as $k => $v) {
            $mix[$k] = $v;
        }
        self::$content = $mix;
        return self::$content;
    }

    public static function get()
    {
        return self::$content;
    }
}
