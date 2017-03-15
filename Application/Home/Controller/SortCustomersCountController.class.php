<?php
namespace Home\Controller;

use Home\Model\CustomerLogModel;
use Home\Model\DepartmentModel;
use Home\Model\RoleModel;

class SortCustomersCountController extends CommonController{
    protected $pageSize = 15;

    private function getDayBetween(){


        $start = I('get.start', null);
        $end   = I('get.end',   null); 

        if (empty($start)) {
            $start = Date("Y-m-d")." 00:00:00";
        } else {
            $start = $start." 00:00:00";
        }

        if (empty($end)) {
            $end = Date("Y-m-d H:i:s", strtotime("+1 day", strtotime($start)));
        } else {
            $end = Date("Y-m-d H:i:s", strtotime("+1 day", strtotime($end." 00:00:00"))) ;
        }

        return "cb.created_at > '$start' and cb.created_at < '$end' ";
    }

    public function index(){
        $this->display();
    }



    /**
     * 公用 获取列表
     *
     * @return array() || null
     * 
     **/
    public function getList(){
        switch (I('get.object')) {
            case 'user':
                 $result = $this->getUserCount();
                break;
            case 'group':
                 $result = $this->getGroupCount();
                break;
            case 'department':
                 $result = $this->getDepartmentCount();
                 break;
            default:
                 $result = $this->getUserCount();
                break;
        }
        $this->ajaxReturn($result);

    }
    private function getBetween(){
        $date = I('get.dist', Date("Y-m-d")." 00:00:00");
        return $this->getDayBetween($date);
    }

    private function setUserCondition($query){
        $roleM = D('Role');
        $query->where(array('role_id'=>array('in', array($roleM->getIdByEname(RoleModel::CAPTAIN), $roleM->getIdByEname(RoleModel::STAFF)))));
        return $query;
    }

    private function getOffset(){
        return (I('get.p',1)-1) * $this->pageSize;
    }


    /**
    * 基于人的录入统计
    */
    private function getUserCount(){
        $between_today = $this->getBetween();
        $count =  $this->setUserCondition(M('user_info'))->count();
        // $list  =  $this->setUserCondition(M('user_info'))->field('user_id ,realname as name')->page(I('get.p',0). ','. $this->pageSize)->select();
        
        // 这个有小问题 如果 A员工 对应的时间段内 没有录入 就不会 显示这个A员工
        /*$list  =  M()->query('select count(id) as count ,ui.realname as name  from customers_basic as cb right join user_info as ui on cb.user_id = ui.user_id  where '. $this->getDayBetween().' group by cb.user_id  order by count desc limit '. $this->getOffset().','.$this->pageSize);*/
        $roleM = D('Role');
        // 修改版 没有录入客户的A员工 会显示 0
        $list = M()->query('select realname as name , IFNULL(cbc.c, 0) as `count`, ui.user_id from user_info as ui left join (select count(id) as `c`, user_id from customers_basic as cb where '. $this->getDayBetween().' group by user_id) as cbc on ui.user_id = cbc.user_id where ui.role_id in('.$roleM->getIdByEname(RoleModel::CAPTAIN).', '.$roleM->getIdByEname(RoleModel::STAFF).') order by `count` desc limit '. $this->getOffset().','.$this->pageSize);

        return  array('list'=>$list, 'count'=>$count);
    }

    /**
    * 基于小组的录入统计
    */
    private function getGroupCount(){

        $between_today = $this->getBetween();
        $count = M('group_basic')->where(array('status'=>1))->count();
        // $list = M('group_basic')->field('id, name')->where(array('status'=>1))->page(I('get.p',0). ','. $this->pageSize)->select();
        
        // 修改原因同上
        /*$list = M()->query('select gb.name, count(cb.id) as count , gb.id  from group_basic as gb left join user_info as ui on gb.id = ui.group_id  inner join customers_basic as cb  on cb.user_id = ui.user_id where '. $this->getDayBetween().' group by ui.group_id order by count desc limit '. $this->getOffset().','.$this->pageSize);*/

       /* $list = M()->query('select id,name,  IFNULL(sum(uc.count), 0)  as `count` from group_basic as gb left join 
(select count(id) as `count`,ui.group_id  from customers_basic as cb inner join user_info as ui on cb.user_id = ui.user_id where '. $this->getDayBetween().'  group by cb.user_id ) as uc on gb.id=uc.group_id where gb.status=1 group by gb.id order by `count` desc limit '. $this->getOffset().','.$this->pageSize);*/
        //跟进一步修改
        $list = M()->query('select id,name,  IFNULL(uc.count, 0)  as `count` from group_basic as gb left join 
(select count(id) as `count`,ui.group_id  from customers_basic as cb inner join user_info as ui on cb.user_id = ui.user_id where '. $this->getDayBetween().'  group by  ui.group_id ) as uc on gb.id=uc.group_id where gb.status=1 group by gb.id order by `count` desc limit '. $this->getOffset().','.$this->pageSize);


        return array('list'=>$list, 'count'=>$count);
    }


    /**
    * 基于部门的录入统计
    */
    private function getDepartmentCount(){
        $between_today = $this->getBetween();
        $count = M('department_basic')->where(array('type'=>array('in', array(DepartmentModel::CAREER, DepartmentModel::GENERALIZE)),'status'=>1))
                                      ->count();
        /*$list  = M('department_basic')->where(array('type'=>array('in', array(DepartmentModel::CAREER, DepartmentModel::GENERALIZE)),'status'=>1))
                                      ->field('id, name')
                                      ->page(I('get.p',0). ','. $this->pageSize)
                                      ->select();*/
        /*$list = M()->query('select db.id,db.name, gb.id, gb.name, sum(gc.c) as count from  department_basic as db inner join group_basic as gb on db.id = gb.department_id  inner join (select  count(cb.id) as c , gb.id  from group_basic as gb left join user_info as ui on gb.id = ui.group_id  inner join customers_basic as cb  on cb.user_id = ui.user_id where '. $this->getDayBetween().' group by ui.group_id order by c desc) as gc on gb.id=gc.id where db.`type` in (2,3) group by gb.id order by count desc limit '. $this->getOffset().','.$this->pageSize);*/

        $list = M()->query('select id,name, IFNULL(dc.`count`, 0) as `count`  from department_basic as db left join (
    select sum(cc.c) as `count`,gb.department_id  from group_basic as gb  inner join (
        select count(cb.id) as c , ui.group_id from customers_basic as cb inner join user_info as ui on cb.user_id = ui.user_id where '. $this->getDayBetween().'  group by ui.group_id
    ) as cc on gb.id=cc.group_id group by gb.department_id
) as dc on db.id=dc.department_id where db.`type` in('.DepartmentModel::CAREER.', '.DepartmentModel::GENERALIZE.') and db.status=1 order by `count` desc limit '. $this->getOffset().','.$this->pageSize);


        
        return array('list'=>$list, 'count'=>$count);
    }




























}


