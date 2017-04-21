<?php
namespace Cli\Controller;
use Think\Controller;
use Cli\Service\CustomerCountServiceModel ;

class CustomerCountController extends Controller {
   

    private $date = '';

    private $m = '';

    


    public function index($date){
        var_dump($date);
        // 初始化
        $this->date = $date;
        $this->m = new CustomerCountServiceModel;
        return $this->m->index($this->date);  
    }
}