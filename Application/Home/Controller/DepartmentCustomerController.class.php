<?php
/**
* 部门经理 管理 客户
*/
namespace Home\Controller;
use Common\Lib\User;
use Home\Model\CustomerModel;
use Home\Logic\CustomerLogic;
use Home\Model\CustomerLogModel;
use Think\Model;

class DepartmentCustomerController extends CommonController {
    private $depart_id = 0;
    protected $table="Customer";
    protected $pageSize = 11;

    public function _initialize(){
        parent::_initialize();
        $this->depart_id = $this->getDepartMentID();
    }

    private function getDepartMentID(){
        $re =  M('department_basic')->where(array('user_id'=>session('uid')))->getField('id');
        if (is_numeric($re)) {
            return $re;
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
         $id = I('get.id', '');

         $this->assign('user_id', $id);
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
        //改造成复合查询
        if (I('get.name')) {
            $this->M->where(array('name'=>array('like', I('get.name')."%")));
        }

        $complexWhere = array('_logic'=>'OR');
        $arrList = array('phone', 'qq', 'weixin', );
        foreach ($arrList as $value) {
            if (I('get.'.$value)) {
                $complexWhere['cc.'.$value] = array('like', I('get.'.$value)."%");
                $complexWhere['cc2.'.$value] = array('like', I('get.'.$value)."%");
            }
        }

        if (count($complexWhere)>1) {
            $this->M->where(array('_complex'=>$complexWhere));
        }
    }


    private function setCreatedField(){
        $start = I('get.start');
        $end   = I('get.end');

        $this->M->setTimeDiv('created_at', $start, $end); 
    }


    private function setTrackField(){
        $start = I('get.track_start');
        $end   = I('get.track_end');
        $this->M->setTimeDiv('last_track', $start, $end); 

    }

    private function setGroupField(){
        $group_id = I('get.group',0);
        switch ($group_id) {
            case 0:
                if (empty($this->depart_id)) {
                    $userIds = array();
                } else {
                    $userIds = M("user_info")->where(array('department_id'=>$this->depart_id, 'user_id'=>array('NEQ', session('uid'))))->getField('user_id', true);
                }
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
             && strpos(I('get.field'),'transf') === false
             ) {
            $this->M->setStart('created_at', D('Customer','Logic')->ThreeMonthsAge());
        }

        $this->M->join(' customers_contacts as cc on customers_basic.id =  cc.cus_id  and cc.is_main = 1')
                ->join('left join customers_contacts as cc2 on customers_basic.id =  cc2.cus_id and cc2.is_main = 0');


       
        
    }



    public function _getList(){
        $this->setQeuryCondition();
        //没有 is_main
        $count = (int)$this->M->count();
        
        $this->setQeuryCondition();
        D('Customer','Logic')->getJoinCondition($this->M);
        if (I('get.sort_field', null)) {
            $this->M->order(I('get.sort_field')." ". I('get.sort_order'));
        } else {
            $this->M->order('customers_basic.id desc');
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
        $rec_group = I('post.rec_group');
        $rec_user = I('post.rec_user');

        $preDate = array(
            'from_department_id' => $this->depart_id,
            'to_department_id'   => $rec_dep,
            'to_id'              => $rec_user,
            'to_group_id'        => $rec_group,
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



            $re = D('Customer')->where(array('id'=>$value))->data(array('salesman_id'=>$rec_user ))->save();
            if ($re===false) {
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

     /**
    * 编辑
    *
    * 知道 丑丑丑丑丑丑丑丑丑丑丑丑  
    * if 超过三层了 fuck customerContact 的逻辑需要重构封装 
    */
    public function edit() {
        $this->M->startTrans();
        if ($this->M->create($_POST, Model::MODEL_UPDATE) && ($this->M->save() !== false) )  {
            $D_cc  = D('CustomerContact');
            $D_cc->where(array('is_main'=>1, 'cus_id'=>$_POST['id']))->find();
            $re = $D_cc->edit($D_cc->getMainPost());
            if ($re === false) {
                $this->M->rollback();
                $this->error($D_cc->getError());
            }

            $data2 = $D_cc->getSecondPost();

            if ($D_cc->where(array('is_main'=>0, 'cus_id'=>$_POST['id']))->find()) {
                $re = $D_cc->edit($data2, true);
                if ($re !== false) {
                    $this->M->commit();
                    $this->success('编辑成功1');
                } else {
                     $this->M->rollback();
                    $this->error($D_cc->getError());
                }
            } else {
                // 
                if ( empty($data2['phone']) 
                    && empty($data2['qq']) 
                    && empty($data2['weixin'])
                    && empty($data2['qq_nickname'])
                    && empty($data2['weixin_nickname'])
                    ) {
                    $this->M->commit();
                    $this->success('编辑成功2');
                }
                $data2['cus_id'] = $_POST['id'];
                if ( $D_cc->create($data2) && $D_cc->add()  ) {
                    $this->M->commit();
                    $this->success('编辑成功3_');
                } else {
                    $this->M->rollback();
                    $this->error($D_cc->getError());
                }
            }
            $this->M->commit();
            $this->success('编辑成功4');
            //$this->success(L('EDIT_SUCCESS'));
        } else {
            $this->M->rollback();
            $this->error($this->M->getError());
        }
    }

    /**
    * 跟据depart_id 获取小组
    * 直接用 1 这个数字不好啊 ，暂进这样了
    */
    public function getDepartGroups(){
        $id = I('get.id');
        $re = D('Group')->field('id,name')->where(array('department_id'=>$id, 'status'=>1))->select();
        $this->ajaxReturn($re);
    }

    public function getRecUser(){
        $id = I('get.id');
        // $re = D('User')->field('user_id,realname as name')->where(array('group_id'=>$id, 'status'=>array))->select();
        $re = D('User')->getGroupEmployee($id);
        $this->ajaxReturn($re);
    }

    /**
    *  添加客户真实资料
    *  
    */
    public function realInfo(){ 
        $ob=D('RealInfo');
        $cus_id = I('post.cus_id');
        $_POST['user_id']= D('Customer')->where(array('id'=>$cus_id))->getField('salesman_id');//session('uid'); 
        if($ob->where(array('cus_id'=>I('post.cus_id'),'identity'=>I('post.identity')))->find()){
            $ob->where(array('cus_id'=>I('post.cus_id')))->save(I('post.'));
            M('customers_service')->where(array('cus_id'=>I('post.cus_id'),'user_id'=>I('post.user_id')))->setField('call_back','1');
        }else{
            if($ob->create($_POST) && $ob->add()){
                $data=array(
                    'cus_id'=>I('post.cus_id'),
                    'user_id'=>I('post.user_id'),
                    'risk_one'=>'1'     
                );
                M('customers_service')->add($data);
                $this->success(L('真实资料添加成功'));
            }else{
                $this->error($ob->getError());    
                
            }
        }
    }

}

