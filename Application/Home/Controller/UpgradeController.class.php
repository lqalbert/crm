<?php
namespace Home\Controller;

class UpgradeController extends CommonController {


    public function index(){

    }



    /**
    * 升级客户管理的数据表
    */
    public function customers(){
        // die('close');
        $all = M('customers_basic')->field('id,phone_del,qq_del,qq_nickname_del,weixin_del,weixin_nickname_del')->select();
        foreach ($all as $key => $value) {
            $data = array();
            $data['cus_id']   = $value['id'];
            $data['phone']    = $value['phone_del'];
            $data['qq']       = $value['qq_del'];
            $data['weixin']   = $value['weixin_del'];
            $data['qq_nickname']     = $value['qq_nickname_del'];
            $data['weixin_nickname'] = $value['weixin_nickname_del'];
            $data['is_main'] = 1;

            M('customers_contacts')->data($data)->add();
        }

        echo 'done';


        // 初始化 service_time 
        $all2 = M('customers_basic')->field('id,created_at,user_id')->where('service_time=0')->select();
        foreach ($all2 as $key => $value) {
            M('customers_basic')->where(array('id'=>$value['id']))->save(array('service_time'=> strtotime($value['created_at']), 'salesman_id'=>$value['user_id']));     
        }
        echo "\r\n";
        echo 'done2';
    }
}
