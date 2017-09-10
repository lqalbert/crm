<?php 
namespace Upgrade\Controller;

use Think\Controller;

class SpreadFixRightController extends Controller{

    private $rights = array(


        

        array(
            'name' => 'SpreadDetailForSp',
            'pid'  => '1',
            'remark' => '',
            'sort'  => '0',
            'status' => 1,
            'title' => '分配明细-推广',
            'level' => 2,
            'children' => array( 
                ),
            'roles' => array(1,20,21,22),
        ),


        array(
            'name' => 'SpreadDetailForSpreadCaptain',
            'pid'  => '1',
            'remark' => '',
            'sort'  => '0',
            'status' => 1,
            'title' => '分配明细-推广组',
            'level' => 2,
            'children' => array( 
                ),
            'roles' => array(21),
        ),


        array(
            'name' => 'SpreadDetailForSpreadMaster',
            'pid'  => '1',
            'remark' => '',
            'sort'  => '0',
            'status' => 1,
            'title' => '分配明细-推广部',
            'level' => 2,
            'children' => array(
                    array('name'=>'getCUsers', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'获取用户', 'roles'=>array()),
                ),
            'roles' => array(1,20),
        ),

        array(
            'name' => 'SpreadDetailForSpreadStaff',
            'pid'  => '1',
            'remark' => '',
            'sort'  => '0',
            'status' => 1,
            'title' => '分配明细-推广员工',
            'level' => 2,
            'children' => array(
                ),
            'roles' => array(22),
        ),

        

        
        
    );



    public function index(){
        $this->deal($this->rights);
    }


    private function deal($rights, $pid=1){
        foreach ($rights as $value) {

            $nodeM = M("rbac_node");
            $value['pid'] = $pid;
            $node = $nodeM->create($value);
            if (!$node) {
                // var_dump($value);
                echo "fail";
                echo "\n";
                return;
            }
            $node_id = $nodeM->add();
            if (!$node_id) {
                echo "insert into fail";
                var_dump($node);
                echo "\n";
                return ;
            }
            if ($value['children']) {
                $this->deal($value['children'], $node_id);
            }

            $this->dealRole($node_id, $value['roles'], $value['name'], $value['level']);
        }

    }

    private function dealRole($nodeId, $roles, $module, $level){
        $accessM = M("rbac_access");
        foreach ($roles as $roleId) {
            $data = array(
                'role_id'=>$roleId,
                'node_id'=>$nodeId,
                'level'  => $level,
                'module' => $module
            );
            $accessM->add($data);
        }
        

    }
}