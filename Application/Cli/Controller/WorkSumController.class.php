<?php
namespace Cli\Controller;


class WorkSumController extends \Think\Controlelr {

    private $startDate = '';
    private $endDate = '';
    private $date ='';
    private $allData = array();

    public function index($date){
        $this->types = D('Home/CustomerLog')->getType();

        $this->date = $date;
        $this->startDate = $this->date. " 00:00:00";
        $this->endDate   = Date('Y-m-d', strtotime($date) + 86400);
    }

    public function setDateRe(){
        $sql="SELECT count(id) as c, user_id, track_type FROM `beta_testcrm`.`customers_log` where created_at >= '".$this->startDate."' and created_at <'".$this->endDate."'  group by user_id,track_type ";
        $this->allData = M()->query($sql);

    }


}