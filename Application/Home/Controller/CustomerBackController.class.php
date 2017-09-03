<?php
namespace Home\Controller;

use Think\Model;
use Common\Lib\User;
use Home\Model\RoleModel;
use Home\Model\CustomerModel;
use Home\Model\RealInfoModel;
use Home\Logic\CustomerLogic;
use Home\Model\CustomerLogModel;
use Home\Model\ProductModel;

class CustomerBackController extends CommonController {

    protected $table = "Customer";
    protected $pageSize = 11;

    private function getSearchGroup(){
        $roleEname = $this->getRoleEname();
        $funcName = $roleEname."SearchGroup";
        if (method_exists($this, $funcName)) {
            return call_user_func(array($this, $funcName));
        }
        return array();
    }

    private function captainSearchGroup(){
        $gorup_id = $this->getUserGroupId();
        $re =  D("Group")->getGroupEmployee($gorup_id, 'user_id, realname as name');
        return array_merge($re, array(array('user_id'=>0, 'name'=>"团组")));
    }

    private function staffSearchGroup(){
        $gorup_id = $this->getUserGroupId();
        return array(array("user_id"=>session('uid'), "name"=>"本人"));
    }


    private function getAggregation(){
        //统计
        //条件的数组
        $field = array(
            'plan',
            'log',
            'unlog',
            'transfto',
            'transfin',
            'type',
            'important',
            'conflict'
        );

        $aggregation = array();
        foreach ($field as $value) {
            $_GET['field'] = $value;
            $this->setQeuryCondition();
            // $this->M->field("customers_basic.id");
            $aggregation[$value] = $this->M->count();
            
            
        }

        return $aggregation;
    }


    public function index(){


        $this->assign('customerType', $this->M->getType(null));
        $this->assign('sexType',      $this->M->getSexType(null));
        $this->assign('Quality',      $this->M->getQuality(null));
        $this->assign('Year',         $this->M->getYear(null));
        $this->assign('Income',       $this->M->getIncome(null));
        $this->assign('Sty',          $this->M->getStyle(null));
        $this->assign('Money',        $this->M->getMoney(null));
        $this->assign('Energy',       $this->M->getEnergy(null));
        $this->assign('Problem',      $this->M->getProblem(null));
        $this->assign('Mode',         $this->M->getMode(null));
        $this->assign('Attitude',     $this->M->getAttitude(null));
        $this->assign('Profession',   $this->M->getProfession(null));
        $this->assign('Intention',    $this->M->getIntention(null));

        $this->assign('Source',       $this->M->getSource(null));

        

        $this->assign('searchGroup',  $this->getSearchGroup());
        $this->assign('aggregation',  $this->getAggregation());
        $this->display();
    }

    private function getDayBetween(){
        $today = Date("Y-m-d")." 00:00:00" ;
        return   array(
                    array('GT', $today), 
                    array('LT', Date("Y-m-d H:i:s", strtotime("+1 day", strtotime($today))))
                 );
    }

    private function setFieldCondition($field){
        if ($field) {
            switch ($field) {
                case 'plan':
                    $between_today = $this->getDayBetween();
                    $this->M->where(array('plan'=> $between_today));
                    break;
                case 'log':
                    $this->M->where(array('log_count'=> array('NEQ',0)));
                    break;
                case 'unlog':
                    $this->M->where(array('log_count'=> 0));
                    break;
                case 'transfto':
                    $this->M->where(array("user_id"=>$this->uid, 'salesman_id'=>array("NEQ", $this->uid)));                
                    break;
                case 'transfin':
                    $this->M->where(array("user_id"=>array('NEQ', $this->uid), 'salesman_id'=>$this->uid));   
                    break;
                case 'type':
                    $this->M->where(array('type'=>array( array('EQ',CustomerModel::TYPE_V),array('EQ',CustomerModel::TYPE_VX),array('EQ',CustomerModel::TYPE_VT) , 'or' )));
                    break;
                case 'important':
                    $this->M->where(array('important'=>1));
                    break;
                case 'conflict':
                    $between_today = $this->getDayBetween();
                    $this->M->where(array('conflict'=> $between_today));
                    break;
                case 'check':
                    $this->M->where(array('buy_check'=> -1));
                    break;
                default :
                    $this->M->where(array('salesman_id'=>$this->uid));
                    break;
            }
            
        }
    }


    public function setQeuryCondition(){
        $this->uid = I("get.user_id", session("uid"));
        $field = I("get.field");
        $this->setFieldCondition($field);
        //联系方式查询
        $this->contactsCondition(I("get.contact"));
        //客户姓名
        $this->nameCondition(I("get.name"));
    }


    private function nameCondition($name){
        if ($name) {
            $this->M->where(array("name"=>array("like", $name."%")));
        }
    }

    private function contactsCondition($contact){
        if ($contact) {

            $cus_ids = D("CustomerContact")->where(array(
                'phone|qq|weixin'=>array('like', $contact."%") 
            ))->getField("cus_id", true);

            if ($cus_ids) {
                $this->M->where(array("id"=>array("in", $cus_ids)));   
            } else {
                $this->M->where(array('id'=>-1));
            }

        }
    }


    private function setFields(){
        $this->M->field("customers_basic.*");
                // ->join("left join ")

    }

    //有 "副代码" 想一想怎么处理
    protected function _getList(){
        $this->setQeuryCondition();
        $count = $this->M->count();
        $this->setQeuryCondition();
        $this->setFields();

        $this->setOrder(I("get.sort_field", 'customers_basic.id'), I("get.sort_order", 'desc'));

        $list = $this->M->order($this->order, $this->way)
                        ->page(I("get.p",0), $this->pageSize)
                        ->select();
        //联系方式
        $this->setContacts($list);
        $this->fixFields($list);
        return array('list'=>$list, 'count'=>$count);
    }

    //把一些字段进行转换
    private function fixFields(&$list){
        foreach ($list as $key => &$value) {
            $value['sex_text'] = $this->M->getSexType($value['sex']);
        }
    }

    private function setOrder($field, $way){
        $this->order = $field;
        $this->way   = $way;
    }


    private function setContacts(&$list){
        foreach ($list as $key => &$value) {
            $value['contact'] = D("CustomerContact")->where(array('cus_id'=>$value['id']))
                                                    ->order("is_main desc")
                                                    ->select();
        }
    }


}