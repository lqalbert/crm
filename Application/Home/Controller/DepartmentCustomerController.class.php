<?php
/**
* 部门经理 管理 客户
*/
namespace Home\Controller;
use Common\Lib\User;
use Home\Model\CustomerModel;
use Home\Logic\CustomerLogic;
use Home\Model\CustomerLogModel;

class DepartmentCustomerController extends CommonController {
    private $depart_id = 0;
    protected $table="Customer";
    protected $pageSize = 11;

    public function _initialize(){
        parent::_initialize();
        // $this->depart_id = $this->getDepartMentID();
    }

    private function getDepartMentID(){
        $re =  M('department_basic')->where(array('user_id'=>session('uid')))->getField('id');
        if (is_numeric($re)) {
            return $depart_id;
        } else {
            return 0;
        }
    }



    private function getGoups(){
        // 又重复了 赶进度 暂时先这样。后面再优化
        $depart_id = $this->depart_id;
        if ($depart_id) {
            return D("Group")->getAllGoups($depart_id, 'id,name');
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
         $this->assign('Departments',  D('Department')->getAllDepartments('id,name'));
         $this->assign('GoodsType',    D('CustomerLog')->getGoodsType());
         $this->assign('ServiceCycle', D('CustomerLog')->getServiceCycle());
         $this->assign('searchGroup', $searchGroup);
         $this->display();
    }


    private function checkLikeField(){
        $arrList = array('name', 'cc.phone', 'cc.qq', 'cc.weixin', );
        foreach ($arrList as $value) {
            if (strpos($value, 'cc')===false) {
                // $value = substr($value, 3);
                
                $val = I('get.'.$value);
            } else {
               $val = I('get.'.substr($value, 3));
            }
            if ($val) {
                $this->M->where(array($value=>array('like', $val."%")));
            }
        }
    }


    private function setCreatedField(){
        if(I('get.start')){
            $this->M->setStart('created_at', I('get.start'));
        } 


        if(I('get.end')){
            $this->M->setEnd('created_at', I('get.end'));
        } 
    }


    private function setTrackField(){
        if(I('get.track_start')){
            $this->M->setStart('last_track', I('get.track_start'));
        } 


        if(I('get.track_end')){
            $this->M->setEnd('last_track', I('get.track_end'));
        } 

        
    }

    private function setGroupField(){
        $group_id = I('get.group',0);
        switch ($group_id) {
            case 0:
                $userIds = M("user_info")->where(array('department_id'=>$this->depart_id, 'user_id'=>array('NEQ', session('uid'))))->getField('user_id', true);
                break;
            default:
                $userIds = M("user_info")->where(array('group_id'=>$group_id))->getField('user_id', true);
                break;
        }
        if ($userIds) {
            $this->M->where(array('salesman_id'=>array('IN', $userIds)));
        } else {
            //没有队员 没有组长  
            $this->M->where(array('salesman_id'=>'-1'));
        }
        
    }

    //单个按钮那个
    private function setField(){
        D('Customer','Logic')->getSingleButton('DepCus',$this->M);
    }


    /**
    * 设置查询参数
    * 
    * @return null
    */
    public function setQeuryCondition() {
        $this->M->setShowCondition();
        //手机 QQ WEIXIN name
        $this->checkLikeField();

        //start 锁定起始时间
        //end 锁定截止时间
        $this->setCreatedField();

        //track_start 最后跟踪起始时间
        //track_end 最后跟踪截止时间
        $this->setTrackField();

        //类型 
        if (I('get.type')) {
            $this->M->where(array('type'=>I('get.type') ));
        }
        // $this->M->join('customers_contacts as cc on customers_basic.id = cc.cus_id');

        
        //个人
        if (I('get.user_id')) {
            $this->M->setSalesman(I('get.user_id'));
        } else {
            //部门还是小组
            if (strpos(I('get.field'),'transf') === false) {
                $this->setGroupField();
            }   
        }

        $this->setField();


        // 如果一个时间都没传
        // 近3个月之内的
        if ( empty(I('get.start')) 
             && empty(I('get.end')) 
             && empty(I('get.track_start')) 
             && empty(I('get.track_end')) 
             && strpos(I('get.fiedl'),'transf') === false
             ) {
            $this->M->setStart('last_track', D('Customer','Logic')->ThreeMonthsAge());
        }

        $this->M->join('customers_contacts as cc on customers_basic.id = cc.cus_id');
    }

    public function delete() {

        if ($this->M->data(array('status'=>-1))->where(array('id'=>array('in', I("post.ids") )))->save()) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败'.$this->M->getError());
        }
    }

    public function _getList(){
        $this->setQeuryCondition();
        //没有 is_main
        $count = (int)$this->M->count();
        $this->setQeuryCondition();
        D('Customer','Logic')->getJoinCondition($this->M);
        if (I('get.sort_field', null)) {
            $this->M->order(I('get.sort_field')." ". I('get.sort_order'));
        }
        $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->select();
        $result = array('list'=>$list, 'count'=>$count);
        return $result;
    }


    /**
    * 获取小组队员
    */
    public function getGroupMemberList(){
        $id = I('get.id',0);
        // 重复了 ，赶进度啊
        if ($id == 0) {
            $members = M('user_info')->where(array('department_id'=>$this->depart_id, 'user_id'=>array('NEQ', session('uid'))))
                                     ->field('user_id, realname')
                                     ->select();
        } else {
            $members = M('user_info')->where(array('group_id'=>$id))->field('user_id, realname')->select();
        }

        $this->ajaxReturn($members);
    }

    private function getEmploye($id){
        $sql = "select user_id,group_id from user_info where user_id in (select salesman_id from customers_basic where id=$id)";
        return M()->query($sql);
    }

    public function trasnfCustomers(){
        //比例也应该插入记录表 
        //但是比例的要求还没提出来 就先不加
        $d = D('CustomerTransflog');
        $cus_ids = I('post.cus_id');
        $rec_dep = I('post.rec_dep');
        $proportion = I('post.proportion');
        

        $preDate = array(
            'from_department_id' => $this->depart_id,
            'to_department_id'   => $rec_dep,
            'content'            => I('post.content'),
        );
        $d->startTrans();
        /*$d->rollback();
        $d->commit();*/
        foreach ($cus_ids as  $value) {
            if ( $this->M->setTransf($value) === false) {
                $d->rollback();
                $this->error('转让失败');
            }

            $data = $preDate;
            $data['cus_id'] = $value;
            $userInfo = $this->getEmploye( intval($value) );
            if ($userInfo) {
                $data['from_id'] = $userInfo[0]['user_id'];
                $data['from_group_id'] = $userInfo[0]['group_id'];
            } else {
                $data['from_id']       = 0;
                $data['from_group_id'] = 0;
            }

            $re = $d->data($data)->add();
            if (!$re) {
                $d->rollback();
                $this->error('转让失败');
            }
        }

        $d->commit();
        $this->success('转让成功');

    }


    /**
    * 添加跟踪纪录
    *
    */
	public function addTrackLogs(){
        D('Customer','Logic')->addTrackLogs();
    }

    /**
    *   获取跟踪信息
    *
    */
	public function trackInfo(){
        $arr=D('Customer','Logic')->trackInfo($this->M);
		if (IS_AJAX) {
			$this->ajaxReturn($arr);
		}  else {
			return $arr;
		}
	}

}

