<?php
namespace Cli\Controller;

use Think\Controller;


//从crm.riign.com导入组
class ImportGroupController extends  Controller{

    private $sourceM = null;
    private $targetM = null;

    private $departIds = array(67,68);
    private $mapDepartIds = array("67"=>24,"68"=>23);



    public function index(){
        echo '改了id了?';
        // die();
            //mysql://root:1234@localhost:3306/thinkphp#utf8
           //mysql://root:1234@localhost/demo#utf8
        $this->sourceM = M('', null, 'mysql://run_crm_0326:run2008run@139.224.40.238:3306/run_crm_0326#utf8');
        $this->targetM = M();

        $this->deal();
    }


    private function deal(){
        foreach ($this->departIds as $key => $value) {
            $groups = $this->sourceM->query("select * from group_basic where department_id=".$value);
            // var_dump($groups);
            // var_dump($this->sourceM->getError());
            foreach ($groups as $group) {
                unset($group['id']);
                unset($group['img']);
                $group['old_userid'] = $group['user_id'];
                $group['department_id'] = $this->mapDepartIds[$value];
                $group['user_id'] = null;
                echo $group['name']," ", $group_basic['department_id'];
                $data = M("group_basic")->create($group);
                if (!$data) {
                    echo ' fail ';
                    continue;
                }
                $re = M("group_basic")->add();
                if ($re) {
                    echo ' success';
                } else {
                    echo ' fail ';
                }
                echo "\n";
            }
        }
    }
}