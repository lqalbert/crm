<?php
namespace Home\Controller;

class PreCheckController extends CommonController {
    protected $table = "customer";

    public function index(){
        $this->assign('customerType', $this->M->getType());
        $this->assign('s_time', 6*30*86400); //6个月
        $this->display();
    }





    public function serach(){
        $result = $this->_getList();
        // var_dump($this->M->getLastSql());
        if (IS_AJAX) {
            $this->ajaxReturn($result); 
        }  else {
            return $result;
        }
    }


    public function setQeuryCondition(){
        $queryName = I('get.name', null);

        $sixMonthAgo = time()-15552000;

        $this->M->field('customers_basic.id,customers_basic.name,customers_basic.type, customers_basic.created_at,(customers_basic.service_time-'.$sixMonthAgo.') as s, ui.realname as user_name')
                ->join('inner join customers_contacts as cc1 on customers_basic.id = cc1.cus_id')
                ->join('left join user_info as ui on customers_basic.salesman_id = ui.user_id');

        if (!empty($queryName)) {
            $this->M->where(array("cc1.phone|cc1.qq|cc1.weixin|name"=> array('like', I('get.name')."%")));
            //var_dump($this->M->getLastSql());
        } else {
            $this->M->where(array("name"=> '00000000000000000000000'));
        }           
    }


    public function getUser(){
        $id = I("post.id");
        $re = $this->M->changeSalesman($id, session('uid'));
        if ($re) {
            $this->success('索取成功');
        } else {
            $this->error('索取失败'. $this->M->getError());
        }
    }


}