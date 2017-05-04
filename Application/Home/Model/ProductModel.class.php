<?php
namespace Home\Model;
use Think\Model;
class ProductModel extends Model{
	const DELETE_STATUS = -1;
  
  protected $tableName = "products";

  protected $_auto = array(
      array('operator_id', 'getUserId', 1, 'callback'),
  );

  //获取发布人的id
  public function getUserId(){
  	if(session('uid')){
  		$user_id=session('uid');
  		return $user_id;
  	}
  }

  public function delete($ids){
    $id_arr = explode(",", $ids);
    return $this->data(array('status'=>self::DELETE_STATUS))->where(array('id'=> array('in', $id_arr)))->save();
  }
























}
