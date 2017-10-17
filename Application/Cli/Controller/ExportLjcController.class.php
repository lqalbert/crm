<?php 
namespace Cli\Controller;

use Think\Controller;
use Cli\Service\ExportCusSerive;

class ExportLjcController extends Controller {

    private $departId = 67;
    private $users = null;
    private $start = '2017-08-01 00:00:00';
    private $end   = '2017-08-31 23:59:59';
    private $customers = array();


    public function index(){
        $this->init();
        $this->deal();
    }

    private function init(){
        $this->users = M('user_info')->where(array('department_id'=> $this->departId))
                                     ->getField("user_id", true);
        $this->customers = M('customers_basic')->where(
            array(
                'is_main'=>1,
                'user_id'=>array('IN', $this->users), 
                'created_at'=>array(array('EGT', $this->start), array('ELT', $this->end))))
                                        ->join('customers_contacts on customers_basic.id = customers_contacts.cus_id')
                                        ->select();
        foreach ($this->customers as &$cus) {
            $cus['realname'] = M('user_info')->where(array('user_id'=>$cus['user_id']))->getField('realname');
        }

        $root = getcwd();
        $this->path = $root .DIRECTORY_SEPARATOR."data3";
        
    }


    private function deal(){
        $obj = new ExportCusSerive();
        $obj->setCustomers($this->customers);
        outPutExcel2($obj,$this->path);

        die();
    }


}