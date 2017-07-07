<?php
namespace Cli\Controller;
use Think\Controller;
class IndexController extends Controller {

    const DAY_SECONDS = 86400;
    private $runList = array(
        // 'CustomerCount/index',
        // 'GroupCount/index',
        // 'WorkSum/index',
        'SaleAchievement/index'
    );

    private function setDate(){
        if (I('get.date')) {
            $this->date = I('get.date');
        } else {
            $this->date =  Date('Y-m-d', time() - self::DAY_SECONDS);
        }
        
    }


    public function index(){

        $this->setDate();

        if (defined('MODE_NAME') && MODE_NAME =="cli") {
            foreach ($this->runList as $value) {
                echo R($value, array($this->date));
                echo "\n";
            }
        }
        
    }
}