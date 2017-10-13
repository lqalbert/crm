<?php
namespace Home\Controller;

use Home\Model\GroupModel;
use Home\Model\RoleModel;

class GroupController  extends CommonController{

    protected $table="Group";
    protected $departmentSelect=NULL;

    public function index(){
        
        if ($this->getRoleEname() == RoleModel::GOLD) {
            
            redirect(U('GodGroup/index'));
            exit();
        }


        $this->assign("departmentId", $this->getUserDepartmentId());
        $this->display();
    }

    public function setQeuryCondition(){

        $this->M->join('left join user_info as ui on group_basic.user_id = ui.user_id');
        
        if (!empty(I('get.group_id'))) {
            $map['group_basic.id']=I('get.group_id');
        }

        if(isset($_GET['department_id'])){
            $map['group_basic.department_id']=$_GET['department_id'];
        }

        if(!empty(I('get.realname')))
        {
            $map['realname']=array('like',I('get.realname').'%');
        }

        if(!empty(I('get.phone')))
        {
            $map['ui.mphone']=array('like',I('get.phone').'%');
        }

        $map['group_basic.status'] = array('NEQ', GroupModel::DELETE_STATUS);
        
        $this->M->where($map);

    }

    public function setField(){
        $this->M->field("group_basic.*,ui.realname,ui.mphone");
    }

    public function getusers(){
        $id = I("get.id");
        $result = D("User")->getGrouper($this->getUserDepartmentId(), 'user_id,realname,group_id');
        //已经有小组的要删除
        foreach ($result as $key => $value) {
            if ($value['group_id'] !=0 && $value['user_id']!=$id ) {
                unset($result[$key]);
            }
        }

        $this->ajaxReturn($result);
    }

    //包含这个 部门 的的所有成员
    public function getMemberList(){

        $departmentId = $this->getUserDepartmentId();
        if ($departmentId) {
            $result = D("User")->getDepartmentEmployee($departmentId, 'user_id,realname,group_id');
        } else {
            $result = array();
        }
        //排除组长?
        $groupers = array_column(D('User')->getGrouper($departmentId, 'id'), 'id');

        foreach ($result as $key=>&$user) {
            if ( in_array($user['user_id'], $groupers) ) {
                unset($result[$key]);
            } else {
                $user['group_name'] = D("Group")->where(array('id'=>$user['group_id']))->getField('name');
            }
        }

        $this->ajaxReturn($result);
    }

    public function setEmployees(){
        $id = I("post.id");
        $user_ids = I("post.user_ids");
        if (empty($user_ids)) {
            $this->error("请选择成员");
        }
        $this->setGroupEmployee($user_ids, $id);
        
    }

    public function removeMember(){
        $user_ids = I("post.user_ids");
        $this->setGroupEmployee($user_ids, 0);
    }

    private function setGroupEmployee($user_ids , $group_id){
        
        $re = M('user_info')->where(array('user_id'=>array('in', $user_ids)))->data(array('group_id'=>$group_id))->save();
        if ($re) {
            $this->success("操作成功");
        } else {
            $this->error('操作失败');
        }
    }

    public function getEmployeesByGroupId(){
        $re = M('user_info')->where(array('group_id'=>I('get.id')))
                            ->field('user_id,qq,realname,mphone as phone')
                            ->select();
        $this->ajaxReturn($re);
    }
}