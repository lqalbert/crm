<?php
/**
* 部门经理 管理 客户
*/
namespace Home\Controller;

use Common\Lib\User;

class DepartmentCustomerController extends CommonController {
    protected $table="Customer";

    private function getGoups(){
        // 又重复了 赶进度 暂时先这样。后面再优化
        $depart_id = M('department_basic')->where(array('user_id'=>session('uid')))->getField('id');;
        if ($depart_id) {
            return D('Group')->where(array('department_id'=>$depart_id, 'status'=> 1))->field('id,name')->select();
        } else {
            return array();
        }
        
    }

    public function index(){
        
        $searchGroup = $this->getGoups();
        array_unshift($searchGroup, array('id'=>0, 'name'=>'本部门'));
        
        $D = D('Customer');

        $this->assign('customerType', $D->getType());
        $this->assign('sexType',      $D->getSexType());
        $this->assign('Quality',      $D->getQuality());
        $this->assign('Year',         $D->getYear());
        $this->assign('Income',       $D->getIncome());
        $this->assign('Sty',          $D->getStyle());
        $this->assign('Money',        $D->getMoney());
        $this->assign('Energy',       $D->getEnergy());
        $this->assign('Problem',      $D->getProblem());
        $this->assign('Mode',         $D->getMode());
        $this->assign('Attitude',     $D->getAttitude());
        $this->assign('Profession',   $D->getProfession());
        $this->assign('Intention',    $D->getIntention());
        $this->assign('Source',       $D->getSource());
        $this->assign('logType',      D('CustomerLog')->getType());
        $this->assign('steps',        D('CustomerLog')->getSteps());
        $this->assign('Proportion',   D('CustomerLog')->getProportion());
        $this->assign('Remind',       D('CustomerLog')->getRemind());

        $this->assign('searchGroup', $searchGroup);
        $this->display();
    }

    /**
    * 设置查询参数
    * 
    * @return null
    */
    public function setQeuryCondition() {
        
    }

    public function delete() {

        if ($this->M->data(array('status'=>-1))->where(array('id'=>array('in', I("post.ids") )))->save()) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败'.$this->M->getError());
        }
    }



}