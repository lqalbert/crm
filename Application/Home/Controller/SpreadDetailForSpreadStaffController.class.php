<?php
namespace Home\Controller;

class SpreadDetailForSpreadStaffController extends CommonController{

     protected $table = "Customer";


    public function index(){
        $this->assign("uid", session("uid"));
        $this->display();
    }

    


    public function setQeuryCondition(){
        
        switch (I("get.dis")) {
            case '0':
                //未分配
                $this->M->where(array('depart_id'=>0));
                break;
            case '1':
                //分配到部门
                $this->M->where(array('depart_id'=>array("NEQ", 0)));
                break;
            case '2':
                //分配到小组
                $this->M->where(array('to_gid'=>array("NEQ", 0)));
                break;
            case '3':
                //分配到员工
                $this->M->where(array('salesman_id'=>array("NEQ", 0)));
                break;
            
            default:
                //未分配
                $this->M->where(array('depart_id'=>0));
                
                break;
        }


        $this->M->where(array('customers_basic.user_id'=>I("get.spread_uid")));

        $this->M->where(array('customers_basic.created_at'=>array(array('EGT', I("get.start")), array('ELT', I("get.end")." 23:59:59"))));

    }

    private function getField(){
        $this->M->join("left join department_basic as db1 on customers_basic.spread_id=db1.id")
                ->join("left join department_basic as db2 on customers_basic.depart_id=db2.id")
                ->join("left join user_info as ui1 on customers_basic.user_id=ui1.user_id")
                ->join("left join user_info as ui2 on customers_basic.salesman_id=ui2.user_id")
                ->join("left join group_basic as gb on customers_basic.to_gid=gb.id")
                ->field("customers_basic.name,customers_basic.id,customers_basic.created_at,customers_basic.dis_time,customers_basic.type, CONCAT(db1.name,' - ',ui1.realname) as spread_name,db2.name as depart_name, gb.name as g_name ,ui2.realname as sale_name");
    }


    protected function _getList(){
        $this->setQeuryCondition();
        $count = (int)$this->M->count();

        $this->setQeuryCondition();
        $this->getField();
        $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->order('customers_basic.id desc')->select();
        
        $result = array('list'=>$list, 'count'=>$count);
        foreach ($result['list'] as &$value) {
            $value['contacts'] = M("customers_contacts")->where(array('cus_id'=>$value['id']))->select();
        }
        
        return $result;
    }
}