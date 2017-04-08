<?php
namespace Cli\Controller;
use Think\Controller;
use Cli\Service\CustomerCountServiceModel ;

class CustomerCountController extends Controller {
    const DAY_SECONDS = 86400;

    private $date = '';

    private $m = '';

    private function setDate(){
        $this->date =  Date('Y-m-d', time() - self::DAY_SECONDS);
    }


    public function index(){

        // 初始化
        $this->setDate();
        $this->m = new CustomerCountServiceModel;


        

        $this->m->index($this->date);
    }
}