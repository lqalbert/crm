<?php
namespace Home\Controller;

use Home\Model\GroupModel;

class GodGroupController  extends GroupController{

    

    public function index(){
        $this->assign("departments", D('Department')->getAllDepartments('id,name'));
        $this->display();
    }

    public function setQeuryCondition(){

        $this->M->join('department_basic on group_basic.department_id = department_basic.id');
        parent::setQeuryCondition();

    }

    public function setField(){
        // $this->M->field("group_basic.*,ui.realname,ui.mphone");
        $this->M->field("group_basic.*,ui.realname,ui.mphone,department_basic.name as dbname");
    }

    protected function _getList(){

        $this->setQeuryCondition();

        $count = (int)$this->M->count();
        $this->setQeuryCondition();
        $this->setField();
        $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->order('group_basic.id desc')->select();
        // var_dump($this->M->getlastsql());die();
        $result = array('list'=>$list, 'count'=>$count);
        
        return $result;
    }

    /***
    *获取所选部门所属的团队小组
     */
    public function getGroups(){
        if(isset($_GET['department_id'])){
            $arr=D('Group')->getAllGoups(I('get.department_id'),'id,name');
            $this->ajaxReturn($arr);
        }
    } 
}