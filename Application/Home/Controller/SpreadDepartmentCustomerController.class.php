<?php
namespace Home\Controller;


//给推广部经理看的
class SpreadDepartmentCustomerController extends CommonController{
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

        $D = $this->M;

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
        $this->assign("gorups",       $this->getGoups());

        

        $this->display();
    }



    public function setQeuryCondition(){
        if (I('get.name')) {
            $this->M->where(array("customers_basic.name"=> array('like', I('get.name')."%")));
        }

        if (I("get.user_id")) {
            $this->M->where(array('customers_basic.user_id'=>I("get.user_id")));
        } else {
            if (I('get.group')) {
            // $this->M->where(array("name"=> array('like', I('get.name')."%")));
                $user_ids = M('user_info')->where(array('group_id'=>I('get.group') ))->getfield("user_id", true);
                if ($user_ids) {
                    $this->M->where(array('customers_basic.user_id'=>array('IN', $user_ids)));
                } else {
                    $this->M->where(array('customers_basic.user_id'=>-1));
                }
            } else {
                $user_ids = M('user_info')->where(array('department_id'=>$this->getDepartMentID() ))->getfield("user_id",true);

                if ($user_ids) {
                    $this->M->where(array('customers_basic.user_id'=>array('IN', $user_ids)));
                } else {
                    $this->M->where(array('customers_basic.user_id'=>-1));
                }
            }
        }

        


        

        $this->M->join(' customers_contacts as cc on customers_basic.id =  cc.cus_id  and cc.is_main = 1')
                ->join('left join customers_contacts as cc2 on customers_basic.id =  cc2.cus_id and cc2.is_main = 0')
                ->where(array('customers_basic.status'=>array('NEQ', -1)))
                ->join('left join user_info as ui on customers_basic.salesman_id = ui.user_id')
                ->join('left join user_info as usi on customers_basic.user_id = usi.user_id')
                ->join("left join department_basic as db2 on customers_basic.depart_id=db2.id")
                ->field('customers_basic.*,cc.qq,cc.phone,cc.weixin,cc.qq_nickname,cc.weixin_nickname, cc.is_main as cc_main,
            cc2.qq as qq2,cc2.phone as phone2,cc2.weixin as weixin2,cc2.qq_nickname as qq_nickname2,
            cc2.weixin_nickname as weixin_nickname2, ui.realname,usi.realname as lock_name,db2.name as depart_name');


                

   
        
        
    }

    protected function _getList(){
        $this->setQeuryCondition();
        $count = (int)$this->M->count();
        $this->setQeuryCondition();

        
        $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->select();
        
        //echo $this->M->getLastSql();
        foreach($list as $k=>$v){
            $list[$k]['qq_nickname']=mb_substr($v['qq_nickname'],0,12);
            $list[$k]['weixin_nickname']=mb_substr($v['weixin_nickname'],0,12);
        }

        $result = array('list'=>$list, 'count'=>$count);
        
        return $result;
    }

    public function getGroupMemberList(){
        $group_id = I("get.id");

        // 重复了 ，赶进度啊
        if ($group_id == 0) {
            $members = M('user_info')->where(array('spread_id'=>$this->depart_id))
                                     ->field('user_id, realname')
                                     ->select();
        } else {
            $members = D('User')->getGroupEmployee($group_id, 'user_id, realname');
            //M('user_info')->where(array('group_id'=>))->field('user_id, realname')->select();
        }

        $this->ajaxReturn($members);

    }

    /**
    * 编辑
    *
    * 知道 丑丑丑丑丑丑丑丑丑丑丑丑  
    * if 超过三层了 fuck customerContact 的逻辑需要重构封装 
    */
    public function edit() {
        $this->M->startTrans();
        if ($this->M->create($_POST, \Think\Model::MODEL_UPDATE) && ($this->M->save() !== false) )  {
            $D_cc  = D('CustomerContact');
            $olddate = $D_cc->where(array('is_main'=>1, 'cus_id'=>$_POST['id']))->find();
            // $D_cc->find($olddate['id']);
            $re = $D_cc->edit($D_cc->getMainPost(), true);
            
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




}