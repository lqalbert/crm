<?php
namespace Cli\Controller;

use Think\Controller;
use Cli\Service\DisExeService;

class DisExcController extends Controller {

    private $departs = array(
        // array('id'=>6, 'name'=>'新乡部')
    );
    private $service = null;

    public function index(){
        $this->init();
        $this->deal();
    }

    private function init(){
        $this->departs = M("department_basic")->where(array('type'=>4))->select();

    }

    private function deal(){
        $root = getcwd();
        $done = $root ."\\"."data3";
        foreach ($this->departs as  $value) {
            $path = $root .DIRECTORY_SEPARATOR."data3".DIRECTORY_SEPARATOR. $value['name']; //iconv('UTF-8', 'GBK', $value['name']);
            // var_dump($path);
            // var_dump(mkdir($path,0777));
            mkdir($path,0777);
           
            $groups = D('Home/Group')->where(array('department_id'=>$value['id'], 'status'=>1))
                                     ->select();
            foreach ($groups as $group) {
                $obj = new DisExeService();
                $obj->setGroupId($group['id']);
                $obj->setTitle($group['name']);
                var_dump($path);
                var_dump($group['name']);
                outPutExcel2($obj,$path);
                var_dump($path);
                $obj = null;
            }

            
        }
    }

    /*

        $outExcel = new EmployeeOutput();
        $outExcel->setDepartmentId($id);
        $outExcel->setTitle(D('Department')->where(array('id'=>$id))->getField('name'));
        outPutExcel($outExcel);
    */
}