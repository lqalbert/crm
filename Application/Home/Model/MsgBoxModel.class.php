<?php
namespace Home\Model;
use Think\Model;

class MsgBoxModel extends Model {

	protected $tableName = 'msgbox_basic';
   
    protected $_auto = array(
        array('from_id', 'getUserId', 1, 'callback'),
    );
    

    //获取user_id
    public function getUserId(){
    	if(session('uid')){
    		$user_id=session('uid');
    		return $user_id;
    	}
    }

}