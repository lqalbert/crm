<?php
namespace Home\Controller;

use Home\Model\RoleModel;
class DistributeCustomerController extends CommonController{

    protected $table = "DistributeCustomer";

    public function index(){

        $customerM = D("Customer");

        $this->assign('customerType', $customerM->getType(null));
        $this->assign('sexType',      $customerM->getSexType(null));
        $this->assign('Quality',      $customerM->getQuality(null));
        $this->assign('Year',         $customerM->getYear(null));
        $this->assign('Income',       $customerM->getIncome(null));
        $this->assign('Sty',          $customerM->getStyle(null));
        $this->assign('Money',        $customerM->getMoney(null));
        $this->assign('Energy',       $customerM->getEnergy(null));
        $this->assign('Problem',      $customerM->getProblem(null));
        $this->assign('Mode',         $customerM->getMode(null));
        $this->assign('Attitude',     $customerM->getAttitude(null));
        $this->assign('Profession',   $customerM->getProfession(null));
        $this->assign('Intention',    $customerM->getIntention(null));

        $this->assign('Source',       $customerM->getSource(null));
        $this->assign("searchGroup",  $this->getSearchGroup());

        $this->assign("isCaptain",  $this->getRoleEname()==RoleModel::SP_CAPTAIN);
        $this->assign("uid", session("uid"));


        $this->display();
    }

    private function getSearchGroup(){
        $group_id = $this->getUserGroupId();
        if ($group_id!=0) {
            return D("User")->getGroupEmployee($group_id, 'user_id, realname as name');
        } else {
            return  array();
        }
        
    }

    /**
    * 在添加之前 加入一个参数
    * @return true
    */
    public function _before_add(){
        $_POST['user_id'] = session('uid');
        return $this->uniquCheck();
    }

    /**
    * 三参 要必填一个
    */
    private function uniquCheck(){
        if(I('post.qq')=='' && I('post.weixin')==''){
            $this->error('QQ1/微信1二者必填一');
        }else if(I('post.qq')==''){
            $_POST['qq']= null;
            return true;
        }else if(I('post.weixin')==''){
            $_POST['weixin']= null;
            return true;
        }else{
            return true;

        }
    }


    /**
    * 设置查询参数
    * 
    * @return null
    */
    public function setQeuryCondition() {
        
        if (I('get.name')) {
            $this->M->where(array("name"=> array('like', I('get.name')."%")));
        }

        if (I('get.contact')) {
            $complexWhere = array('_logic'=>'OR');
            $arrList = array('phone', 'qq', 'weixin' );
            $val = I('get.contact');
            foreach ($arrList as $value) {
                $complexWhere['cc.'.$value] = array('like', $val."%");
                $complexWhere['cc2.'.$value] = array('like', $val."%");
            }

            if (count($complexWhere)>1) {
                $this->M->where(array('_complex'=>$complexWhere));
            }
        }

        if (I("get.group")) {
            $this->M->where(array("customers_basic.user_id"=> I("get.group") ));
        } else {
            $this->M->where(array("customers_basic.user_id"=> session("uid") ));
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

        $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->order('customers_basic.id desc')->select();
        // var_dump($this->M->getlastsql());
        $result = array('list'=>$list, 'count'=>$count);
        
        return $result;
    }

     public function edit() {
        $this->M->startTrans();
        if ($this->M->create($_POST, \Think\Model::MODEL_UPDATE) && ($this->M->save() !== false) )  {
            $D_cc  = D('CustomerContact');
            $D_cc->where(array('is_main'=>1, 'cus_id'=>$_POST['id']))->find();
            $mainData = $D_cc->getMainPost();
            $mainData['id'] = $D_cc->id;
            $reData = $D_cc->create($mainData);
            if (!$reData) {
                $this->M->rollback();
                $this->error($D_cc->getError());
            }
            $re = $D_cc->edit($reData);
            if ($re === false) {
                $this->M->rollback();
                $this->error($D_cc->getError());
            }

            $data2 = $D_cc->getSecondPost();
            $row2  = $D_cc->where(array('is_main'=>0, 'cus_id'=>$_POST['id']))->find();
            if ($row2) {

                $mainData = $D_cc->getMainPost();
                $data2['id'] = $row2['id'];
                $reData = $D_cc->create($data2);
                if (!$reData) {
                    $this->M->rollback();
                    $this->error($D_cc->getError());
                }

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