<?php
namespace Home\Model;
use Think\Model;
class SysNoticeModel extends Model{
	  protected $tableName = 'msgbox_basic';
   
    protected $_auto = array(
        array('start', 'transfer', 1, 'callback'),
        array('end', 'transfer', 1, 'callback'),
        array('user_id', 'getUserId', 1, 'callback'),
    );
    //UTC时间转换
    public function transfer($v){
      if (empty($v)) {
        return null;
      } else {
        return UTC_to_locale_time($v);
      }
    }

    //获取user_id
    public function getUserId(){
    	if(session('uid')){
    		$user_id=session('uid');
    		return $user_id;
    	}
    }










}