<?php
namespace Home\Model;

use Think\Model;

class DistributeCustomerModel extends Model {

    const BENEFIT = "benefit";

    protected $tableName = 'customers_basic';

    public static $shareBenefit = array(
        "9/1",
        "8/2",
        "7/3",
        "6/4",
        "5/5",
        "4/6",
        "3/7",
        "2/8",
        "1/9",
        "10/0"
        );


    protected $_auto = array(
        array('salesman_id', '0'),
        array('last_track', 'getDate', 1, 'callback'),
        array('service_time','time', 1, 'function'),
        array('share_benefit', 'setBenefit', 1 , 'callback')
    );

    public function getDate($v){
      return Date("Y-m-d H:i:s");
    }
    //分配的比率
    public function setBenefit($v){
       $re =  F(self::BENEFIT, I("post."));
       if ($re) {
         return $re['benefit'];
       } else {
        return "9/1";
       }
      
    }


    public function add(){
      //开启事务
      // die('asdf');
      $this->startTrans();
      $id = parent::add();
      if ($id == false) {
        $this->rollback();
        return false;
      }

      $D_contact = D('CustomerContact');
      $mainData = $D_contact->getMainPost();
      $d = $D_contact->create($mainData, self::MODEL_INSERT);
      if ($d == false) {
        $this->rollback();
        $this->error = $D_contact->getError();
        // $this->addConflict($mainData );

        return false;
      }

      $d['is_main'] = 1;
      $d['cus_id'] = $id;
      if ($D_contact->data($d)->add()) {

        //第二套手机 qq 和 微信
        $data = $D_contact->getSecondPost();
        if (!empty($data['phone']) || !empty($data['qq']) || !empty($data['weixin'])) {
          $data['cus_id'] = $id;
          if ( !($D_contact->create($data) && $D_contact->add())) {
            $this->rollback();
            $this->error = $D_contact->getError($data );
           // $this->addConflict($data);
            return false;
          }
        }

        $this->commit();
        return true;
      }else{
        $this->error = D('CustomerContact')->getError();
        $this->rollback();
        return false;
      }
    }


}

