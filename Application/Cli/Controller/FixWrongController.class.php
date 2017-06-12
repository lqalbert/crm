<?php
namespace Cli\Controller;
// is_wrong
class FixWrongController extends \Think\Controller {
    //注意有重名的情况
    private $users = array(
        "刘晓飞",
        "王玉师",
        "张阿丹",
        "张照喜",
        "李阿敏",
        "胡尚伟",
        "轩明宇",

    );

    private $sourceM = null;
    private $ccM = null;
    private $cbM = null;
    private $uiM = null;

    private $is_ywymiss = array();
    private $is_cjrmiss = array();
    private $is_ywy     = array();
    private $is_cjr     = array();
    private $is_switch  = array();
    private $is_wrong   = array();


    public function init(){
        $this->sourceM = M('import_table_d7', null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8');
        // 
        $this->ccM     = M('customers_contacts');
        $this->cbM     = M('customers_basic');
        $this->uiM     = M('user_info');

    }

    /*public function getUsers($offset, $size){
        // return "select * from import_table4 where is_wrong =1";

        return $this->sourceM->order('id', $this->sort_way)
                              ->where(array( 'is_deal'=>0)) //'is_wrong'=>1,
                              // ->limit($offset, $size)
                              ->field('id,phone,ywy,cjr,group_id')
                              ->select();
    }*/

    public function fix(){
        $this->init();
        foreach ($this->users as $key => $value) {
            $userRow = $this->uiM->where(array('realname'=>$value))->field('user_id')->find();
            $records = $this->sourceM->where(array('ywy'=>$value, 'is_wrong'=>1))->select();
            foreach ($records as $record) {
                $this->FixRecord($record, $userRow['user_id']);
            }

            $this->sourceM->execute("update import_table4 set is_deal=1 where id in(". implode(",", array_column($records, 'id')).")");
            
            

            $this->sourceM->execute("update import_table4 set is_wrong=0 where id in(". implode(",", array_column($records, 'id')).")");
            $this->is_wrong = array();
        }
    }

    public function FixRecord($record, $user_id){
        $ccRow = $this->ccM->where(array('phone'=>$record['phone']))
                           ->field('cus_id')
                           ->find();
        if ($ccRow) {
            $this->cbM->data(array('salesman_id'=>$user_id))->where("id=".$ccRow['cus_id'])->save();
        }
    }



    public function remark(){
        $this->init();
        foreach ($this->users as $key => $value) {
            $records = $this->sourceM->where(array('ywy'=>$value))->select();
            foreach ($records as $record) {
                $this->findRecord($record);
            }

            $this->sourceM->execute("update import_table4 set is_deal=1 where id in(". implode(",", array_column($records, 'id')).")");
            
            if (!empty($this->is_ywymiss)) {
                // $this->sourceM->where(array('id'=>array('in', $this->wrongRecord )))->data(array('is_wrong'=>1))->save();
                $this->sourceM->execute("update import_table4 set is_ywymiss=1 where id in(". implode(",", $this->is_ywymiss).")");
                $this->is_ywymiss = array();
            }

        
            /*if (!empty($this->is_ywy)) {
                // $this->sourceM->where(array('id'=>array('in', $this->wrongRecord )))->data(array('is_wrong'=>1))->save();
                $this->sourceM->execute("update import_table4 set is_ywy=1 where id in(". implode(",", $this->is_ywy).")");
                $this->is_ywy = array();
            }*/

            if (!empty($this->is_wrong)) {
                // $this->sourceM->where(array('id'=>array('in', $this->wrongRecord )))->data(array('is_wrong'=>1))->save();
                $this->sourceM->execute("update import_table4 set is_wrong=1 where id in(". implode(",", $this->is_wrong).")");
                $this->is_wrong = array();
            }
        }
    }



    private function findRecord($record){
        //mysql:host=139.224.40.238;dbname=run_gaocrm;charset=utf8
        $ccRow = $this->ccM->where(array('phone'=>$record['phone']))
                           ->field('cus_id,weixin,id')
                           ->find();
        if ($ccRow) {
            $this->contrast($record, $ccRow['cus_id']);
        } 

        echo "id:", $record['id'];
        echo "\n";
    }


    //对照所属员工是不是正确的
    private function contrast($record, $cus_id){

        $saleRow = $this->uiM->where("group_id=".$record['group_id']." and realname like '". $record['ywy'] ."%'")
                  ->field('user_id')->find();

        $cbRow = $this->cbM->where(array('id'=>$cus_id))->field('user_id,salesman_id')->find();

        //业务员不存存
        if ( !$saleRow ) {
            $this->is_ywymiss[]=$record['id'];
        } else {
            //业务员不对
            if ($cbRow['salesman_id']!=$saleRow['user_id']) {
                $this->is_wrong[] = $record['id'];
                // $this->is_ywy[] = $record['id'];
            }
        }
    }
}