<?php
namespace Cli\Controller;

class DisConfigController extends \Think\Controller {


    public function index(){
        $re = M("distribute_basic")->where(array("type"=>2))->select();
        foreach ($re as  $value) {
            $this->deal($value);
        }
    }


    private function deal($row){
        $config = json_decode($row['config'], true);
        if (empty($config['limina'])) {
            var_dump($config);
            $this->setConfig($row);
        }
    }

    private function setConfig($row){
        $gid = $row['obj_id'];
        $users = D("Home/User")->getGroupEmployee($gid,'id');
        $map = array(
                1=>array(100),
                2=>array(50,50),
                3=>array(30,30,40),
                4=>array(25,25,25,25),
                5=>array(20,20,20,20,20),
                6=>array(20,20,20,20,10,10),
                7=>array(20,20,20,10,10,10,10),
                8=>array(20,20,10,10,10,10,10,10),
                9=>array(20,10,10,10,10,10,10,10,10),
                10=>array(10,10,10,10,10,10,10,10,10,10),
            );
        $cout = count($users);
        if (isset($map[$cout])) {
            $config = array("limina"=>"11", "type"=>2, "list"=>array() );
            $list = array();
            foreach ($users as $key => $value) {
                $list[] = array("id"=>$value['id'], 'value'=>$map[$cout][$key]);
               // $list[$value['id']] = $map[$cout][$key];
            }
            $config['list'] = $list;
            // var_dump($config);
            M("distribute_basic")->data(array('config'=>json_encode($config)))->where(array("id"=>$row['id']))->save();
        }

    }

    public function fixEleven(){
        $re = M("distribute_basic")->where(array("type"=>2))->select();
        foreach ($re as  $value) {
            $config = json_decode($value['config'], true);
            if ($config['limina']==11) {
                $config['limina']=10;
                M("distribute_basic")->data(array('config'=>json_encode($config)))->where(array("id"=>$value['id']))->save();
            }
        }
    }
}