<?php

namespace Maily;

require_once BASE.'/DB.php';

class ListModel 
{

    protected $data;

    /**
     *
     * @param string $mailaddr
     * @return mList 
     */
    public static function lookUp($mailaddr) 
    {
        $con = DB::getConnection();
        $stmt = $con->prepare('SELECT * FROM `list` WHERE `address` = :lst LIMIT 1');
        $stmt->execute(array(':lst' => $mailaddr));
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result_cnt = count($result);
        if($result_cnt == 0) {
            return false;
        }
        if($result_cnt > 1) {
            \Log::write('found more then one list with the following address: "'.$mailaddr.'"', \Log::WARN);
            \Log::write('maily will use the first, but please make your checks', \Log::WARN);
        }
        if($result_cnt >= 1) {
            return new mList($result[0]);
        }
    }

    public static function getAll() 
    {
        $con = DB::getConnection();
        $stmt = $con->prepare('SELECT `address`  FROM `list`');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function __construct($data) 
    {
        $this->data = $data;
    }
    
    public function getConfig($key, $default=null)
    {
        $query = 'SELECT `value` FROM `list_config` WHERE `list` = :id AND `name` = :key';
        $con = DB::getConnection();
        $stmt = $con->prepare($query);
        $stmt->execute(array(':id' => $this->getID(), 
                             ':key' => $key));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row !== false) {
            return $row['value'];
        } else {
            return $default;
        }
        
    }
    
    /**
     *
     * @return int
     */
    public function getID()
    {
        return (int)$this->data['id'];
    }
    
    public function getAddress() 
    {
        return $this->data['address'];
    }
    
    public function getTargets()
    {
        $qry = 'SELECT `address` FROM `target` as `t` INNER JOIN `recipient` as `r` ON `t`.`recipient` = `r`.`id` WHERE `t`.`active` = "y" AND `t`.`list` = :id';
        $con = DB::getConnection();
        $stmt = $con->prepare($qry);
        $stmt->execute(array(':id' => $this->getID()));
        $targets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $list = array();
        foreach($targets as $t) {
            $list[] = $t['address'];
        }
	return $list;
    }   

    /**
     *
     * @param String $recipient
     * @return bool
     */
    public function canSend($recipient)
    {
        $qry = 'SELECT "y", *  FROM `target` as `t` INNER JOIN `recipient` as `r` WHERE `r`.`id` = `t`.`recipient` AND `t`.`list` = :lid  AND (`user_level` >= 2 OR (`allow_send` = "y" AND `active` = "y")) AND `r`.`address` = :rec';    
        $con = DB::getConnection();
        $stmt = $con->prepare($qry);
        $stmt->execute(array(':lid' => $this->getID(), ':rec' => $recipient));
        return ($stmt->fetch() != false); 
    }

    public function __toString() 
    {
        return $this->data['name'] . ' <' . $this->data['address'] . '>';
    }
}

