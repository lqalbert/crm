<?php
namespace Cli\Controller;

class ItTController extends \Think\Controller{



    private $sourceM = null;
    private $ccM = null;
    private $cbM = null;
    private $uiM = null;

    private $no_record = array();
    private $is_wrong = array();
    

    public function index(){


        $this->sourceM = M('import_table_d7', null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8');
        // $this->sourceM = M('import_table_d7');
        $this->ccM     = M('customers_contacts');
        $this->cbM     = M('customers_basic');
        $this->uiM     = M('user_info');


        $this->sort_way = I("get.order",'asc');

        $this->deal();
    }

    public function getUsers($offset, $size){
        // return "select * from import_table4 where is_wrong =1";

        return  $this->sourceM->order('id', $this->sort_way)
                              // ->where(array('is_deal'=>0, 'user_id'=>array('neq',0)))
                              ->limit($offset, $size)
                              ->field('id,phone,ywy')
                              ->select();

    }


    private function deal(){
        $offset = 0;
        $size   = 500;
        $user = $this->getUsers($offset, $size);


        while ($user) {

            foreach ($user as $key => $value) {
                $this->findRecord($value);
            }
            
            // $ids = array_column($user, 'id');
            
            // $this->sourceM->data(array('is_deal'=>1))->where(array('id'=>array('in', array_column($user, 'id') )))->save();

            // $this->sourceM->execute("update import_table_d7 set is_deal=1 where id in(". implode(",", array_column($user, 'id')).")");

            if (!empty($this->is_wrong)) {
                // $this->sourceM->where(array('id'=>array('in', $this->wrongRecord )))->data(array('is_wrong'=>1))->save();
                $this->sourceM->execute("update import_table_d7 set is_wrong=1 where id in(". implode(",", $this->is_wrong).")");
                $this->is_wrong = array();
            }
            if (!empty($this->noRecord)) {
                // $this->sourceM->data(array('no_record'=>1))->where(array('id'=>array('in', $this->noRecord)))->save();
                $this->sourceM->execute("update import_table_d7 set no_record=1 where id in(". implode(",", $this->noRecord).")");
                $this->noRecord = array();
            }

            $offset += $size;
           
            $user = $this->getUsers($offset, $size);
        }

        
    }

    private function findRecord($record){
        //mysql:host=139.224.40.238;dbname=run_gaocrm;charset=utf8
        $ccRow = $this->ccM->where(array('phone'=>$record['phone']))
                           ->field('cus_id,weixin,id')
                           ->find();
        if ($ccRow) {
            // if (empty($ccRow['weixin']) && !empty($record['weixin'])) {
            //     $this->updateWeixin($record, $ccRow['id']);
            // }
            // $this->updateCreated($record, $ccRow['cus_id']); 
            $this->contrast($record, $ccRow['cus_id']);
        } else {
            //没找到 记一下 这个没有导入
            $this->noRecord[] = $record['id'];
        }

        echo "id:", $record['id'];
        echo "\n";
    }

    //对照所属员工是不是正确的
    private function contrast($record, $cus_id){

        $saleRow = $this->uiM->where(" realname='".$record['ywy']."'")
                  ->field('user_id')->find();
        
        if (!$saleRow) {
            
            $this->is_wrong[] = $record['id'];
        } else {
            $cbRow = $this->cbM->where(array('id'=>$cus_id))->field('user_id,salesman_id')->find();
            
            if ($cbRow['salesman_id']!=$saleRow['user_id']) {
                $this->is_wrong[] = $record['id'];
            }
        }

        

    }
}