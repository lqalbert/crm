<?php
namespace Home\Logic;
use Think\Model;
use Common\Lib\User;
use Home\Model\CustomerLogModel;
use Home\Model\CustomerModel;
class CustomerLogic extends Model{
    const THREE_MONTH_AGE = 7776000;
    protected $tableName = 'customers_basic';

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
        if( $LogM->track_type == 0 || !empty($LogM->track_type)){
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
        $group_id=M('user_info')->where(array('user_id'=>I('post.user_id')))->field('group_id')->find();
        $groupInfo=M('group_basic')->where(array('id'=>$group_id['group_id']))->field('name')->find();
        $userName=M('user_info')->where(array('user_id'=>I('user_id')))->field('realname')->find();
        $user=$groupInfo['name']."-".$userName['realname'];
        $arr=M('customers_log')->where(array('cus_id'=>I('post.id')))->order('id desc')->select();
        foreach ($arr as $key => $value){
        	$arr[$key]['type']=$type;
        	$arr[$key]['user']=M('user_info')->where(array('user_id'=>$arr[$key]['user_id'],'id'=>I('post.id')))->getField('realname');
        	$arr[$key]['name']=I('post.name');
        	$arr[$key]['track_type']=D('CustomerLog')->getType((int)$arr[$key]['track_type']);
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
       $D->join(' customers_contacts as cc on customers_basic.id =  cc.cus_id and cc.is_main=1')
            ->join('left join customers_contacts as cc2 on customers_basic.id =  cc2.cus_id and cc2.is_main!=1')
            ->join('left join user_info as ui on customers_basic.salesman_id = ui.user_id')
            ->field('customers_basic.*,cc.qq,cc.phone,cc.weixin,cc.qq_nickname,cc.weixin_nickname,cc2.qq as qq2,cc2.phone as phone2,cc2.weixin as weixin2,cc2.qq_nickname as qq_nickname2,cc2.weixin_nickname as weixin_nickname2, ui.realname');
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
                    $D->where(array(
                        'transfer_status'=>1, 
                        'from_department_id'=> $this->depart_id,
                        'ct.created_at'=>array('EGT', D('Customer','Logic')->ThreeMonthsAge())))
                        ->join('customer_transflog as ct on customers_basic.id=ct.cus_id');
                }else{
                   $D->where(array('transfer_status'=>1, 'transfer_to'=>array('NEQ', 0)));
                }
                
                break;
            case 'transfin':
                if($Contro == 'DepCus'){
                    $D->where(array(
                        'transfer_status'=>1, 
                        'to_department_id'=> $this->depart_id,
                        'ct.created_at'=>array('EGT', D('Customer','Logic')->ThreeMonthsAge())))
                        ->join('customer_transflog as ct on customers_basic.id=ct.cus_id');
                }else{
                   $D->where(array('transfer_status'=>array( array('EQ', 1), array('EQ', 2), 'or'), 'transfer_to'=>session('uid')));
                }
                
                break;
            case 'type':
                $D->where(array('type'=>CustomerModel::TYPE_V));
                break;
            case 'important':
                $D->where(array('important'=>1));
                break;
            case 'conflict':
                $D->where(array('conflict'=> $between_today));
                break;
            default:
                
                break;
        }
    }


}