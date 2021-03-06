<?php
namespace Home\Logic;
use Think\Model;
use Common\Lib\User;
use Home\Model\CustomerLogModel;
use Home\Model\CustomerModel;
class CustomerLogic extends Model{
    const THREE_MONTH_AGE = 7776000;
    protected $tableName = 'customers_basic';
    protected $where = null;

    /**
    * 计算3个月前的时间日期
    *
    */
    public function ThreeMonthsAge(){
        return Date("Y-m-d H:i:s", time()-self::THREE_MONTH_AGE);
    }

    /**
    * 添加跟踪纪录
    *
    */
    public function addTrackLogs(){
        // $id = I('post.id');
        $LogM = D('CustomerLog');
        $to_type = I('post.to_type', '');
        $LogM->startTrans();
        if (!$LogM->create()) { 
            $LogM->rollback();
            return L('ADD_ERROR').$LogM->getError();
        }
        $this->find($LogM->cus_id);
        if ($to_type !== "" &&  $to_type != $this->type) {
            $LogM->contentSetChangeType($this->type, $to_type);
            $this->type = $to_type;
            $re = $this->save();
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
            return L('ADD_SUCCESS');
        } else {
            $LogM->rollback();
            return L('ADD_ERROR').$LogM->getError();
        }
        
    }

    /**
    *   获取跟踪信息
    *
    */
	public function trackInfo($D){
        $type=$D->getType(I('post.type'));
        $arr=M('customers_log')->where(" cus_id = ".I('post.id')." AND (`track_type` <> 11 or `track_type` is null) ")->order('id desc')->select();
        foreach ($arr as $key => $value){
        	$arr[$key]['type']=$type;
            $dep_user=M('user_info as ui')->join('left join department_basic as db on ui.department_id=db.id')
                     ->where(array('ui.user_id'=>$value['user_id']))->getField("IFNULL(concat(db.name,'-',ui.realname),ui.realname) as user");
            foreach ($dep_user as $k => $v) {
                $arr[$key]['user']=$v['user'];
            }
        	$arr[$key]['name']=I('post.name');
            $arr[$key]['track_type'] = $arr[$key]['track_type'] == null ?:D('CustomerLog')->getType((int)$arr[$key]['track_type']);
            $arr[$key]['step'] = $arr[$key]['step'] == null ?:D('CustomerLog')->getType((int)$arr[$key]['step']);
        }
       
        return $arr;
	}

    /**
    *   获取中间日期时间
    *
    */
	public function getDayBetween(){
		$today = Date("Y-m-d")." 00:00:00" ;
		return   array(
					array('GT', $today), 
					array('LT', Date("Y-m-d H:i:s", strtotime("+1 day", strtotime($today))))
		         );
	}

    /**
    *   获取中间日期时间
    *
    */
    public function getJoinCondition($D){
      $D->join('left join user_info as ui on customers_basic.salesman_id = ui.user_id')
        ->join('left join user_info as usi on customers_basic.user_id = usi.user_id')
        ->join('left join department_basic as db on ui.department_id=db.id')
        ->join('left join department_basic as db2 on usi.department_id=db2.id')
        ->field('customers_basic.*,cc.qq,cc.phone,cc.weixin,cc.qq_nickname,cc.weixin_nickname, cc.is_main as cc_main,
            cc2.qq as qq2,cc2.phone as phone2,cc2.weixin as weixin2,cc2.qq_nickname as qq_nickname2,
            cc2.weixin_nickname as weixin_nickname2, ui.realname,usi.realname as lock_name, db.name as sales_depart,db2.name as creat_depart ');
    }



    /**
    *   获取表格上面的按钮条件
    */
    public function getSingleButton($Contro,$D){
        $between_today = $this->getDayBetween();
        switch (I('get.field')) {
            case 'plan':
                $D->where(array('plan'=> $between_today));
                break;
            case 'log':
                $D->where(array('log_count'=> array('NEQ',0)));
                break;
            case 'unlog':
                $D->where(array('log_count'=> 0));
                break;
            case 'transfto':
                if($Contro == 'DepCus'){
                    // $D->where(array(
                    //     'transfer_status'=>1, 
                    //     'from_department_id'=> $this->depart_id,
                    //     ))
                    //     ->join('customer_transflog as ct on customers_basic.id=ct.cus_id');
                        //'ct.created_at'=>array('EGT', $this->ThreeMonthsAge())
                    $departUser = M("user_info")->where(array('department_id'=> $this->depart_id))->getField("user_id", true);
                    if ($departUser) {
                        $D->where(array('customers_basic.user_id'=>array(array("NEQ", 'salesman_id'), array("IN", $departUser), 'AND')));
                    } else {
                        $D->where(array("customers_basic.user_id"=>-1));
                    }
                    
                }else{
                   // $D->where(array('transfer_status'=>1, 'transfer_to'=>array('NEQ', 0)));
                    // $D->where(array(
                    //     'transfer_status'=>1, 
                    //     'ct.from_id'=> session('uid'),
                    //     ))
                    //     ->join('customer_transflog as ct on customers_basic.id=ct.cus_id');
                       // 'ct.created_at'=>array('EGT', D('Customer','Logic')->ThreeMonthsAge())
                    $D->where(array('customers_basic.user_id'=>session('uid'), 'salesman_id'=>array("NEQ", session('uid'))));
                }
                
                break;
            case 'transfin':
                if($Contro == 'DepCus'){
                    /*$D->where(array(
                        'transfer_status'=>1, 
                        'to_department_id'=> $this->depart_id,
                        ))
                        ->join('customer_transflog as ct on customers_basic.id=ct.cus_id');*/
                        //'ct.created_at'=>array('EGT', $this->ThreeMonthsAge())
                    $departUser = M("user_info")->where(array('department_id'=> $this->depart_id))->getField("user_id", true);
                    if ($departUser) {
                        $D->where(array('salesman_id'=>array(array("NEQ", 'customers_basic.user_id'), array("IN", $departUser), 'AND')));
                    } else {
                        $D->where(array("salesman_id"=>-1));
                    }
                }else{
                    // 多条相同的客户转让记录 会出现多条数据
                    // 用子查询 join (select from  这里选出 一条纪录 ) on customers_basic.id=ct.cus_id
                    // 这种思路 
                   // $D->where(array(
                   //      'transfer_status'=>1, 
                   //      'ct.to_id'=> session('uid'),
                   //      ))
                   //      ->join('customer_transflog as ct on customers_basic.id=ct.cus_id');
                        //'ct.created_at'=>array('EGT', $this->ThreeMonthsAge())
                    $D->where(array('salesman_id'=>session('uid'), 'customers_basic.user_id'=>array("NEQ", session('uid'))));
                }
                
                break;
            case 'recommend':
                $D->where(array('spread_id'=>array("neq",0), 'customers_basic.user_id'=>array("NEQ", session('uid')), 'salesman_id'=>session('uid')));
                break;
            case 'type':
                $D->where(array('customers_basic.type'=>CustomerModel::TYPE_V));
                break;
            case 'important':
                $D->where(array('important'=>1));
                break;
            case 'conflict':
                $D->where(array('conflict'=> $between_today));
                break;
            case 'check':
                
                $D->where(array('buy_check'=> -1));
                
            
                break;
            default:
                
                break;
        }
    }


}