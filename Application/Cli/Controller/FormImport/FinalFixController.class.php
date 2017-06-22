<?php
namespace Cli\Controller;


class FinalFixController extends \Think\Controller{


    private $sourceM = null;
    private $ccM = null;
    private $cbM = null;
    private $uiM = null;

    private $wrongRecord = array();
    private $noRecord = array();


    public function index(){
        /*$this->sourceM = M('import_table4');
        $this->ccM     = M('customers_contacts', null, 'mysql://run_gaocrm:run2008run@139.224.40.238/run_gaocrm#utf8');
        $this->cbM     = M('customers_basic', null, 'mysql://run_gaocrm:run2008run@139.224.40.238/run_gaocrm#utf8');
        $this->uiM     = M('user_info', null, 'mysql://run_gaocrm:run2008run@139.224.40.238/run_gaocrm#utf8');*/


        $this->sourceM = M('import_table4', null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8');
        $this->ccM     = M('customers_contacts');
        $this->cbM     = M('customers_basic');
        $this->uiM     = M('user_info');

        $this->sort_way = I("get.order",'asc');

        $this->deal();
    }

    private function getUsers($offset, $size){
        return $this->sourceM->order('id', $this->sort_way)
                              ->where(array('is_deal'=>0))
                              ->limit($offset, $size)
                              ->field('id,phone,ywy,cjr,create_at,group_id,weixin,weixin_n')
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

            $ids = array_column($user, 'id');
            
            // $this->sourceM->data(array('is_deal'=>1))->where(array('id'=>array('in', array_column($user, 'id') )))->save();

            $this->sourceM->execute("update import_table4 set is_deal=1 where id in(". implode(",", array_column($user, 'id')).")");

            if (!empty($this->wrongRecord)) {
                // $this->sourceM->where(array('id'=>array('in', $this->wrongRecord )))->data(array('is_wrong'=>1))->save();
                $this->sourceM->execute("update import_table4 set is_wrong=1 where id in(". implode(",", $this->wrongRecord).")");
                $this->wrongRecord = array();
            }
            if (!empty($this->noRecord)) {
                // $this->sourceM->data(array('no_record'=>1))->where(array('id'=>array('in', $this->noRecord)))->save();
                $this->sourceM->execute("update import_table4 set no_record=1 where id in(". implode(",", $this->noRecord).")");
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

    private function updateWeixin($record, $id){
        $upData = array('weixin'=>$record['weixin'] );
        if (!empty($record['weixin_n'])) {
            $upData['weixin_nickname']=$record['weixin_n'];
        }
        if (!$this->ccM->where(array('weixin'=>$record['weixin']))->find()) {
            $re = $this->ccM->data($upData)->where(array('id'=>$id))->save();
            echo "weixin:";
            var_dump($re);
        }

        
    }

    private function updateCreated($record, $id){
        $re = $this->cbM->data(array('created_at'=>$record['create_at']))->where(array('id'=>$id))->save();
        echo 'created:';
        var_dump($re);
        // var_dump($this->cbM->getlastsql());
    }

    //对照所属员工是不是正确的
    private function contrast($record, $cus_id){

        $saleRow = $this->uiM->where("group_id=".$record['group_id']." and realname like '". $record['ywy'] ."%'")
                  ->field('user_id')->find();
        /*if ($record['ywy'] == $record['cjr']) {
            $userRow = $saleRow
        } else {
            $userRow = $this->uiM->where("group_id=".$record['group_id']." and realname='". $record['cjr'] ."'")
                  ->field('user_id')->find();
        }*/
        $cbRow = $this->cbM->where(array('id'=>$cus_id))->field('user_id,salesman_id')->find();
        if ($cbRow['salesman_id']!=$saleRow['user_id']) {
            
            // $this->sourceM->where(array('id'=>$record['id']))->data(array('is_wrong'=>1))
            $this->wrongRecord[] = $record['id'];
        }


    }




}