<?php
namespace Home\Model;
use Think\Model;
class WorkSummaryModel extends Model{
    protected $tableName ="work_sum";
    protected $SummaryType = array(
       '1'=>'每日总结',
       '2'=>'每周总结',
       '3'=>'每月总结',
       '4'=>'季度总结',
       '5'=>'年度总结',
       '6'=>'月度计划',
       '7'=>'主管建议',
    );

    protected $_auto = array(
        array('su_user', 'getUserId', 1, 'callback'),
    );

    //获取user_id
    public function getUserId(){
    	if(session('uid')){
    		$user_id=session('uid');
    		return $user_id;
    	}
    }

    /**
    * 返回类型 或 所有的类型
    * 
    * @return string|array
    */
    public function getType($index=null){
        if(!empty($index)){
        	return $this->SummaryType[$index];
        }else{
        	return $this->SummaryType;
        }
    }

}