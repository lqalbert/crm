<?php
namespace Cli\Controller;
use Think\Controller;
use Cli\Service\CustomerCountServiceModel ;

class CustomerCountController extends Controller {
   

    private $date = '';

    private $m = '';

    


    public function index($date){
        // 初始化
        $this->date = $date;
        $this->m = new CustomerCountServiceModel;
        var_dump($this->m->index($this->date));  
    }
}