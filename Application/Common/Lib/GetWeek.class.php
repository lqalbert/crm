<?php
namespace Common\Lib;
/**
* 传入一个日期 2017-01-01
* 得到一周的起止日期
*
*/

class GetWeek {
    // private static $instance=null;

    private $currentTimStamp = "";
    private $re = array();


    public function __construct($date){
        
        $this->currentTimeStamp = strtotime($date);
        $delta = date("N", $this->currentTimeStamp);
        $firstDayTimeStamp  = $this->currentTimeStamp - (($delta-1) * 86400);
        // $this->re = array();
        for ($i=0; $i < 7 ; $i++) { 
            $this->re[] = Date("Y-m-d", $firstDayTimeStamp + ($i*86400) );
        }
    }

    
    /**
    * @param int $index 0-6
    */
    public function getDay($index=null){
        if (is_numeric($index) && $index <= 6) {
            return $this->re[$index];
        } else {
            return $this->re;
        }
    }

    /*public static function getInstance($date=null){
        if (self::$instance == null && $date != null) {
            self::$instance = new self($date);
        } else {
            return self::$instance;
        }
    }*/


}