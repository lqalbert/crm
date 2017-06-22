<?php
namespace Cli\Controller;

class ErrorRemarkController extends \Think\Controller{



    private $sourceM = null;
    private $ccM = null;
    private $cbM = null;
    private $uiM = null;

    private $is_ywymiss = array();
    private $is_cjrmiss = array();
    private $is_ywy     = array();
    private $is_cjr     = array();
    private $is_switch  = array();

    public function index(){


        // $this->sourceM = M('import_table4', null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8');
        $this->sourceM = M('import_table4');
        // , null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8'
        $this->ccM     = M('customers_contacts');
        $this->cbM     = M('customers_basic');
        $this->uiM     = M('user_info');


        $this->sort_way = I("get.order",'asc');

        $this->deal();
    }

    public function getUsers($offset, $size){
        // return "select * from import_table4 where is_wrong =1";

        return $this->sourceM->order('id', $this->sort_way)
                              ->where(array('is_wrong'=>1, 'is_deal'=>0))
                              // ->limit($offset, $size)
                              ->field('id,phone,ywy,cjr,group_id')
                              ->select();
    }


    private function deal(){
        $offset = 0;
        $size   = 100;
        $user = $this->getUsers($offset, $size);


        // var_dump($user);
        while ($user) {

            foreach ($user as $key => $value) {
                $this->findRecord($value);
            }
            
            // $ids = array_column($user, 'id');
            
            // $this->sourceM->data(array('is_deal'=>1))->where(array('id'=>array('in', array_column($user, 'id') )))->save();

            $this->sourceM->execute("update import_table4 set is_deal=1 where id in(". implode(",", array_column($user, 'id')).")");
            var_dump("update import_table4 set is_deal=1 where id in(". implode(",", array_column($user, 'id')).")");
            if (!empty($this->is_ywymiss)) {
                // $this->sourceM->where(array('id'=>array('in', $this->wrongRecord )))->data(array('is_wrong'=>1))->save();
                $this->sourceM->execute("update import_table4 set is_ywymiss=1 where id in(". implode(",", $this->is_ywymiss).")");
                $this->is_ywymiss = array();
            }

            if (!empty($this->is_cjrmiss)) {
                // $this->sourceM->where(array('id'=>array('in', $this->wrongRecord )))->data(array('is_wrong'=>1))->save();
                $this->sourceM->execute("update import_table4 set is_cjrmiss=1 where id in(". implode(",", $this->is_cjrmiss).")");
                $this->is_cjrmiss = array();
            }

            if (!empty($this->is_ywy)) {
                // $this->sourceM->where(array('id'=>array('in', $this->wrongRecord )))->data(array('is_wrong'=>1))->save();
                $this->sourceM->execute("update import_table4 set is_ywy=1 where id in(". implode(",", $this->is_ywy).")");
                $this->is_ywy = array();
            }

            if (!empty($this->is_cjr)) {
                // $this->sourceM->where(array('id'=>array('in', $this->wrongRecord )))->data(array('is_wrong'=>1))->save();
                $this->sourceM->execute("update import_table4 set is_cjr=1 where id in(". implode(",", $this->is_cjr).")");
                $this->is_cjr = array();
            }

            if (!empty($this->is_switch)) {
                // $this->sourceM->where(array('id'=>array('in', $this->wrongRecord )))->data(array('is_wrong'=>1))->save();
                $this->sourceM->execute("update import_table4 set is_switch=1 where id in(". implode(",", $this->is_switch).")");
                $this->is_switch = array();
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
            // $this->noRecord[] = $record['id'];
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
                $this->is_ywy[] = $record['id'];
            }

            if ($record['ywy'] == $record['cjr']) {
                $userRow = $saleRow;

                //创建人不对
                if ($cbRow['user_id']!=$userRow['user_id']) {
                    $this->is_ywy[] = $record['id'];
                }
            } else {
                $userRow = $this->uiM->where("  realname like '". $record['cjr'] ."%'")
                      ->field('user_id')->find();
                //创建人不存存
                if (!$userRow) {
                    $this->is_cjrmiss[]=$record['id'];
                } else {

                    //创建人不对
                    if ($cbRow['user_id']!=$userRow['user_id']) {
                        $this->is_cjr[] = $record['id'];
                    }

                    if ($cbRow['user_id']==$saleRow['user_id'] && $cbRow['salesman_id']==$userRow['user_id']) {
                        $this->is_switch[] = $record['id'];
                    }


                }
            }

        }

    }
}