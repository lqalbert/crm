<?php
/**
* 部门经理 管理 客户
*/
namespace Home\Controller;

use Common\Lib\User;
use Home\Model\CustomerModel;

class DepartmentCustomerController extends CommonController {


    private $depart_id = 0;


    protected $table="Customer";


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

        $this->assign('searchGroup', $searchGroup);
        $this->display();
    }


    private function checkLikeField(){
        $arrList = array('name', 'phone', 'qq', 'weixin', );
        foreach ($arrList as $value) {
            if (I('get.'.$value)) {
                $this->M->where(array($field=>array('like', $value."%")));
            }
        }
    }


    private function setCreatedField(){
        /*if(I('get.start')){
            $this->M->setStart('created_at', I('get.start'));
        } 


        if(I('get.end')){
            $this->M->setEnd('created_at', I('get.end'));
        } */

        $this->M->setTimeDiv('created_at', I('get.start'), I('get.end'));
    }


    private function setTrackField(){
        /*if(I('get.track_start')){
            $this->M->setStart('last_track', I('get.track_start'));
        } 


        if(I('get.track_end')){
            $this->M->setEnd('last_track', I('get.track_end'));
        } */
        $this->M->setTimeDiv('last_track', I('get.track_start'), I('get.track_end'));

        
    }


    private function setTimeDiv($field, $start, $end){
        if( $start || $end ){
            if ( $start && $end ) {
                $this->M->where(array($field=> array(array('GT', $start), array('LT', $end)) ));
            } else if( $start ){
                 $this->M->where(array($field=> array('GT', $start)));
            } else if($end) {
                $this->M->where(array($field=> array('LT', $end)));
            }
            
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
        // var_dump($group_id);
        if ($userIds) {
            $this->M->where(array('salesman_id'=>array('IN', $userIds)));
        }
        
    }

    //单个按钮那个
    private function setField(){
         $between_today =  R('Customer/getDayBetween');
         switch (I('get.field')) {
                case 'plan':
                    $this->M->where(array(
                        'plan'=> $between_today
                        ));
                    break;
                case 'log':
                    $this->M->where(array('log_count'=> array('NEQ',0)));
                    break;
                case 'unlog':
                    $this->M->where(array('log_count'=> 0));
                    break;
                    //转出 连 customer_transflog 表 from_department_id = this->depart_id
                    // 近三个月的
                case 'transfto':
                    $this->M->where(array(
                        'transfer_status'=>1, 
                        'from_department_id'=> $this->depart_id,
                        'ct.created_at'=>array('EGT', $this->getThreeMonthsAgoDateTime())))
                            ->join('customer_transflog as ct on customers_basic.id=ct.cus_id');
                    break;
                    //转入 连 customer_transflog 表 to_department_id = this->depart_id
                case 'transfin':
                    $this->M->where(array(
                        'transfer_status'=>1, 
                        'to_department_id'=> $this->depart_id,
                        'ct.created_at'=>array('EGT', $this->getThreeMonthsAgoDateTime())))
                            ->join('customer_transflog as ct on customers_basic.id=ct.cus_id');
                    break;
                case 'type':
                    $this->M->where(array('type'=>CustomerModel::TYPE_V));
                    break;
                case 'important':
                    $this->M->where(array('important'=>1));
                    break;
                case 'conflict':
                    $this->M->where(array(
                        'conflict'=> $between_today
                        ));
                    break;
                default:
                    
                    break;
            }
    }

    private function getThreeMonthsAgoDateTime(){
        return Date("Y-m-d 00:00:00", time()-CustomerController::THREE_MONTH_AGE);
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
        // Date("Y-m-d", time()-CustomerController::THREE_MONTH_AGE)
        if ( empty(I('get.start')) 
             && empty(I('get.end')) 
             && empty(I('get.track_start')) 
             && empty(I('get.track_end')) 
             && strpos(I('get.fiedl'),'transf') === false
             ) {
            $this->M->setStart('last_track', Date("Y-m-d", time()-CustomerController::THREE_MONTH_AGE));
        }
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
        $this->M->join('customers_contacts as cc on customers_basic.id = cc.cus_id');
        $count = (int)$this->M->count();
        $this->setQeuryCondition();
        $this->M->join(' customers_contacts as cc on customers_basic.id =  cc.cus_id and cc.is_main=1')
                ->join('left join customers_contacts as cc2 on customers_basic.id =  cc2.cus_id and cc2.is_main!=1')
                ->join('left join user_info as ui on customers_basic.salesman_id = ui.user_id')
                ->field('customers_basic.*,cc.qq,cc.phone,cc.weixin,cc.qq_nickname,cc.weixin_nickname,cc2.qq as qq2,cc2.phone as phone2,cc2.weixin as weixin2,cc2.qq_nickname as qq_nickname2,cc2.weixin_nickname as weixin_nickname2, ui.realname');


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
        // $id = I('post.id');
        
        $LogM = D('CustomerLog');
        $to_type = I('post.to_type', '');
        $LogM->startTrans();
        if (!$LogM->create()) {
            
            $LogM->rollback();
            $this->error(L('ADD_ERROR').$LogM->getError());
        }
        $this->M->find($LogM->cus_id);
        if ($to_type !== "" &&  $to_type != $this->M->type) {
            $LogM->contentSetChangeType($this->M->type, $to_type);
            $this->M->type = $to_type;
            
            $re = $this->M->save();
            if ($re === false) {
                $LogM->rollback();
                $this->error(L('ADD_ERROR').$this->M->getError()."e");
            }
        }

        if( $LogM->track_type == 0 || !empty($LogM->track_type)){
            $LogM->track_text = D('CustomerLog')->getType((int)$LogM->track_type);
        }

        if ($LogM->add()) {
            $LogM->commit();
            $this->success(L('ADD_SUCCESS'));
        } else {
            $LogM->rollback();
            $this->error(L('ADD_ERROR').$LogM->getError());
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
                if ((empty($data2['phone']) && empty($data2['qq']) && empty($data2['weixin']))) {
                    $this->M->commit();
                    $this->success('编辑成功2');
                }
                $data2['cus_id'] = $_POST['id'];
                if ( $D_cc->create($data2) && $D_cc->add()  ) {
                    $this->M->commit();
                    $this->success('编辑成功3_'.$D_cc->getLastSql());
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
    *   获取跟踪信息
    *
    */
    public function trackInfo(){
        $type=$this->M->getType(I('post.type'));
        $group_id=M('user_info')->where(array('user_id'=>I('post.user_id')))->field('group_id')->find();
        $groupInfo=M('group_basic')->where(array('id'=>$group_id['group_id']))->field('name')->find();
        $userName=M('user_info')->where(array('user_id'=>I('user_id')))->field('realname')->find();
        $user=$groupInfo['name']."-".$userName['realname'];
        $arr=M('customers_log')->where(array('cus_id'=>I('post.id')))->order('id desc')->select();
        foreach ($arr as $key => $value){
            $arr[$key]['type']=$type;
            $arr[$key]['user']=M('user_info')->where(array('user_id'=>$arr[$key]['user_id'],'id'=>I('post.id')))->getField('realname');
            $arr[$key]['name']=I('post.name');
            $arr[$key]['track_type']=D('CustomerLog')->getType((int)$arr[$key]['track_type']);
        }
        if (IS_AJAX) {
            $this->ajaxReturn($arr);

        }  else {
            
            return $arr;
        }
    }

}