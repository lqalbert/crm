<?php
namespace Common\Job;
//有两个任务 一个是 分配给某个人 一个是 发消息
            //两个任务合在一个job里 
class DisDataStaffJob extends CommonJob {

    private $roll = 0;

    protected function deal(){
        // var_dump($this);
        $args = $this->args;
        // $id = $this->id;
        fwrite(STDOUT, json_encode($args). PHP_EOL);
        $dataStaff_id = $this->disDataStaff($args['id']);
        $this->setAlert($dataStaff_id, $args['cus_name'], $args['product_name']);
    }


    private function initRoll(){
        $roll = S('dataStaffRoll');
        if ($roll) {
            $this->roll = $roll;
        }
    }

    private function setRoll($riskRoll){
        S('dataStaffRoll', $riskRoll);
    }

    private function disDataStaff($id){
        $dataStaffs = $this->getDataStatff();
        
        $this->initRoll();
        $dataStaff_i = $this->roll % count($dataStaffs);
        $dataStaff_id = $dataStaffs[$dataStaff_i]['id'];
        

        $data = array('datastaff_id'=>$dataStaff_id,
                      'disstaff_time'=> Date('Y-m-d H:i:s'));
        M("customers_buy")->data($data)->where(array('id'=>$id))->save();
        $this->setRoll(++$dataStaff_i);
        return $dataStaff_id;
    }

    private function getDataStatff(){
        return D("Home/User")->getDataStaff();
    }



    /**
    *  to_id
    *  客户：huangdi59 商品：点金手高端版(季度)
    */
    private function setAlert($id, $cus_name, $product_name){
        M("msg_alert")->add(array(
            'to_id'=>$id,
            'title'=>'您有一个新客户',
            'content' => '客户：'.$cus_name." 商品：".$product_name
        ));
    }

    
}