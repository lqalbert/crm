<?php
namespace Cli\Controller;

class VCustomerController extends \Think\Controller{
    /*
    create table `cus_bak`(
        `id` int unsigned not null primary key auto_increment,
        `name` varchar(20) ,
        `phone` varchar(20),
        `qq`    varchar(20),
        `account_date` char(20),
        `dead_time` char(20)
    )
    
    */

    public function index(){
        $root = getcwd();
        $done = $root ."\\"."data\\data2.xls";
        // $path = "xxx.xls";
        $data = getExcelArrayData($done);
        foreach ($data as $key => $value) {
              var_dump($value);
              $row = $this->checkPhone($value['C']) ? $this->checkPhone($value['C']) : $this->checkqq($value['D']);
              if ($row) {
                  $this->setV($row['cus_id']);
                  //生成
                  $this->setBuy($row['cus_id'], 13, $value['F'], $value['G']);
              }  else {
                //放入另一个表
                $this->saveBack($value);
              }


             /*if (!empty($value['D']) &&  !empty($value['A']) &&  mb_strpos($value['A'], '简称')=== false) {
                $this->setFail($value, 0);
             }*/
        }
    }

    public function saveBack($row){
        M('cus_bak')->data(
            array(
                'name' => $row['B'],
                'phone' => $row['C'],
                'qq'    => $row['D'],
                'account_date' => $row['F'],
                'dead_time'  => $row['G']
            )
        )->add();
    }



    public function checkPhone($phone){
        return M("customers_contacts")->where(array('phone'=>$phone))->find();
    }

    public function checkqq($qq){
        return M("customers_contacts")->where(array('qq'=>$qq))->find();
    }


    public function setV($id){
        M("customers_basic")->data(array("type"=>"V"))->where(array("id"=>$id))->save();
        
    }

    //生成购买的 customers_buy 记录 并设置到期时间
    //生成 customers_order
    public function setBuy($id, $pro_id, $account_date, $dead_time){
        $buy_id = M("customers_buy")->data(
            array('user_id'=>1,
                  'cus_id' =>$id,
                  'product_id'=>$pro_id,
                  'product_name'=>'虚拟商品以导入成交客户',
                  'product_money'=>'0.00',
                  'product_t'=>'0',
                  'risk_state'=>1,
                  'callback_state'=>1,
                  'status'=>1,
                  'todo_list'=>'[]',
                  'buy_time'=>$account_date,
                  'type'=>0,
                  'dead_time'=>$dead_time )
        )->add();

        M('customers_order')->data(
            array('buy_id'=>$buy_id,
                  'product_id'=>$pro_id,
                  'sale_money' => '0.00',
                  'receivable' => '0.00',
                  'paid_in'    => '0.00',
                  'cus_id'     => $id,
                  'creater_id' => '1')
        )->add();
    }
}