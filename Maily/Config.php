<?php

namespace Maily;

/**
 * Description of Config
 *
 * @author Mark Schmale <masch@masch.it>
 */
class Config {

    public static $content;
    
    public static function readConfig($path)
    {
        $default = json_decode(file_get_contents(__DIR__.'/../config.default.json'), true);
        $local   = json_decode(file_get_contents($path), true);
        $mix = $default;
	if(!is_array($local)) {
	    Log::write('invalid config file', Log::ERROR);
	} else {
            foreach($local as $k => $v) {
                $mix[$k] = $v;
            }
        }
        self::$content = $mix;
        return self::$content;
    }

    public static function get()
    {
        return self::$content;
    }
}
