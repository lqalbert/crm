<?php 
namespace Upgrade\Controller;

use Think\Controller;

class BRightController extends Controller{

    private $rights = array(
        array(
            'name' => 'Distribute',
            'pid'  => '1',
            'remark' => '',
            'sort'  => '0',
            'status' => 1,
            'title' => '客户分配',
            'level' => 2,
            'children' => array(
                    array('name'=>'manuallyDistribute', 'pid'=>0, 'sort'=>0, 'level'=>3, 'status'=>1, 'title'=>'手动分配action', 'roles'=>array()),
                    array('name'=>'manually', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'手动分配', 'roles'=>array()),
                    array('name'=>'edit', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'编辑分配参数','roles'=>array()),
                    array('name'=>'view', 'pid'=>0, 'sort'=>0, 'status'=>1,'level'=>3, 'title'=>'参数编辑','roles'=>array()),
                    array('name'=>'saveBenefit', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'分成比率','roles'=>array()),
                ),
            'roles' => array(),
        ),


        array(
            'name' => 'DistributeCustomer',
            'pid'  => '1',
            'remark' => '',
            'sort'  => '0',
            'status' => 1,
            'title' => '推广客户管理',
            'level' => 2,
            'children' => array(
                    array('name'=>'edit', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'编辑', 'roles'=>array()),
                    array('name'=>'add', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'添加', 'roles'=>array()),
                ),
            'roles' => array(),
        ),

        array(
            'name' => 'SpreadCustomerForSaleGroup',
            'pid'  => '1',
            'remark' => '',
            'sort'  => '0',
            'status' => 1,
            'title' => '分配明细-销信组',
            'level' => 2,
            'children' => array(
                    array('name'=>'index', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'列表', 'roles'=>array()),
                ),
            'roles' => array(),
        ),

        array(
            'name' => 'SpreadCustomerForSale',
            'pid'  => '1',
            'remark' => '',
            'sort'  => '0',
            'status' => 1,
            'title' => '分配明细-销售',
            'level' => 2,
            'children' => array(
                    array('name'=>'getUsers', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'获取员工', 'roles'=>array()),
                ),
            'roles' => array(),
        ),

        

        array(
            'name' => 'SpreadCustomer',
            'pid'  => '1',
            'remark' => '',
            'sort'  => '0',
            'status' => 1,
            'title' => '分配的客户', //分配明细
            'level' => 2,
            'children' => array(
                    array('name'=>'index', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'列表', 'roles'=>array()),
                    array('name'=>'getUsers', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'获取员工', 'roles'=>array()),
                    array('name'=>'getGroups', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'获取小组', 'roles'=>array()),
                ),
            'roles' => array(),
        ),

        array(
            'name' => 'DistributeRecord',
            'pid'  => '1',
            'remark' => '',
            'sort'  => '0',
            'status' => 1,
            'title' => '分配历史', //分配明细
            'level' => 2,
            'children' => array(
                    array('name'=>'index', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'列表', 'roles'=>array()),
                ),
            'roles' => array(),
        ),

        array(
            'name' => 'SpreadDepartmentCustomer',
            'pid'  => '1',
            'remark' => '',
            'sort'  => '0',
            'status' => 1,
            'title' => '推广部客户', //分配明细
            'level' => 2,
            'children' => array(
                    array('name'=>'index', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'列表', 'roles'=>array()),
                    array('name'=>'edit', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'编辑', 'roles'=>array()),
                    array('name'=>'getGroupMemberList', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'获取小组成员', 'roles'=>array()),
                ),
            'roles' => array(),
        ),

        array(
            'name' => 'SpreadCustomerSort',
            'pid'  => '1',
            'remark' => '',
            'sort'  => '0',
            'status' => 1,
            'title' => '推广客户排名', //分配明细
            'level' => 2,
            'children' => array(
                    array('name'=>'getGroups', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'获取小组', 'roles'=>array()),
                ),
            'roles' => array(),
        ),

        array(
            'name' => 'DepartSpreadCustomer',
            'pid'  => '1',
            'remark' => '查看销售部 小组 员工 被分配的客户数量',
            'sort'  => '0',
            'status' => 1,
            'title' => '分配总数', //分配明细
            'level' => 2,
            'children' => array(
                    array('name'=>'getGroups', 'pid'=>0, 'sort'=>0, 'status'=>1, 'level'=>3,'title'=>'获取小组', 'roles'=>array()),
                ),
            'roles' => array(),
        ),



        
        
        //推广部客
        
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