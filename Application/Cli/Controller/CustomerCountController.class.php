<?php
namespace Cli\Controller;
use Think\Controller;
use Cli\Service\CustomerCountServiceModel ;

class CustomerCountController extends Controller {
    const DAY_SECONDS = 86400;

    private $date = '';

    private $m = '';

    private function setDate(){
        if (I('get.date')) {
            $this->date = I('get.date');
        } else {
            $this->date =  Date('Y-m-d', time() - self::DAY_SECONDS);
        }
        
    }


    public function index(){
        
        if (defined('MODE_NAME') && MODE_NAME =="cli") {
            // 初始化
            $this->setDate();
            $this->m = new CustomerCountServiceModel;


            
            echo $this->date;
            echo "\n";
            var_dump($this->m->index($this->date));
            echo "\n";
        }

        
    }
}