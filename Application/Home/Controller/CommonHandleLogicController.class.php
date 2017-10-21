<?php
namespace Home\Controller;
use Think\Model;
use Common\Lib\User;
use Home\Model\RoleModel;
use Home\Model\CustomerModel;
use Home\Model\RealInfoModel;
use Home\Logic\CustomerLogic;
use Home\Model\CustomerLogModel;
class CommonHandleLogicController extends CommonController{
    
  /**
  *   添加跟踪纪录
  *
  */
  public function addTrackLogs(){
    $LogM = D('CustomerLog');
    $cusM = D('Customer');

    
    $to_type = I('post.to_type', '');
    $LogM->startTrans();
    if (!$LogM->create()) { 
        $LogM->rollback();
        return L('ADD_ERROR').$LogM->getError();
    }
    $cusM->find($LogM->cus_id);
    $cus_id = $LogM->cus_id;
    if ($to_type !== "" &&  $to_type != $cusM->type) {
        $LogM->contentSetChangeType($cusM->type, $to_type);
        $cusM->type = $to_type;
        $re = $cusM->save();
        if ($re === false) {
            $LogM->rollback();
            return L('ADD_ERROR').$LogM->getError()."e";
        }
    }

    $LogM->track_type = $LogM->track_type == "" ? null : $LogM->track_type ;
    $LogM->step = $LogM->step == "" ? null : $LogM->step ;
    if( $LogM->track_type == '0' || !empty($LogM->track_type)){
        $LogM->track_text = D('CustomerLog')->getType((int)$LogM->track_type);
    }
    if ($LogM->add()) {
        $LogM->commit();

        $roleName = $this->getRoleEname();
        if ( $roleName == RoleModel::SUP_SERVICE || $roleName == RoleModel::GEN_SERVICE ) {
            // 更新 kf_time
            $cusM->data(array('kf_time'=>Date("Y-m-d H:i:s")))->where(array('id'=>$cus_id))->save();
        } else {
            //列新 last_track
            $cusM->data(array('last_track'=>Date("Y-m-d H:i:s")))->where(array('id'=>$cus_id))->save();
        }


        return L('ADD_SUCCESS');
    } else {
        $LogM->rollback();
        return L('ADD_ERROR').$LogM->getError();
    }

  }

  /**
  *   添加意见投诉
  *  更改  
  *  如果客户不是 VT 则改状态 ，插入log纪录
  *  投诉纪录
  *  否则就只  投诉纪录
  */
  public function addComplain(){

    $complanM = D("CustomerComplain");
    $LogM = D('CustomerLog');
    $cusM = D('Customer');

    // $complanData = $complanM->create();
    $cusId  = I('post.cus_id');
    $cusRow = D('Customer')->where(array("id"=>$cusId))->field("type,id")->find();

    $LogM->startTrans();
    // 如果客户不是 VT 则改状态 ，插入log纪录
    if ($cusRow['type'] !== CustomerModel::TYPE_VT) {
        
        //改状态
        $cusM->find(I("post.cus_id"));
        $cusM ->type = CustomerModel::TYPE_VT;
        $re = $cusM ->save();

        if ($re === false) {
            $LogM->rollback();
            return L('ADD_ERROR').$LogM->getError()."e";
        }


        $data=array(
           'cus_id'=>I('post.cus_id'),
           'track_type'=>'11',
           'content'=>I('post.content')
        );

        if (!$LogM->create($data)) { 
            $LogM->rollback();
            return L('ADD_ERROR').$LogM->getError();
        }

        $LogM->contentSetChangeType($cusRow['type'], CustomerModel::TYPE_VT);

        if (!$LogM->add()) {
            $LogM->rollback();
            return L('ADD_ERROR').$LogM->getError();
        } 
    }

    //投诉纪录 
    if (!$complanM->create()) {
        $LogM->rollback();
        return L('ADD_ERROR').$complanM->getError();
    }

    if($complanM->add()){
        $LogM->commit();
        return L('ADD_SUCCESS');
    }

  }





























}