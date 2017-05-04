<?php
namespace Common\Lib;
/**
* 传入一个日期 2017-01-01
* 得到一个月的起止日期
*
*/

class GetMonth {
    // private static $instance=null;

    private $currentTimStamp = "";
    private $re = array();
    private $monthDay = 0;


    public function __construct($date){
        
        $this->currentTimeStamp = strtotime($date);
        $this->monthDay = date("t", $this->currentTimeStamp);
        $delta    =  date("j", $this->currentTimeStamp);
        $firstDayTimeStamp  = $this->currentTimeStamp - (($delta-1) * 86400);
        // $this->re = array();
        for ($i=0; $i < $this->monthDay ; $i++) { 
            $this->re[] = Date("Y-m-d", $firstDayTimeStamp+ ($i*86400) );
        }
    }
    /**
    * @param int $index 0-6
    */
    public function getDay($index=null){
        if (is_numeric($index) && $index < $this->monthDay) {
            return $this->re[$index];
        } else {
            return $this->re;
        }
    }

    public function getMonthDay(){
        return $this->monthDay ;
    }

    /*public static function getInstance($date=null){
        if (self::$instance == null && $date != null) {
            self::$instance = new self($date);
        } else {
            return self::$instance;
        }
    }*/


}