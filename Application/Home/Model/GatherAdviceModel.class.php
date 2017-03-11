<?php
namespace Home\Model;
use Think\Model;

class GatherAdviceModel extends Model {
	protected $tableName = 'advices_basic';
    
    protected $AdviceType = array(
        '1'=>"技术建议",
        '2'=>"OA问题反馈",
        '3'=> "系统制度",
        '4'=> "其它建议",
        '5'=> "软件功能"
    );

    /**
    * 返回类型 或 所有的类型
    * 
    * @return string|array
    */
    public function getAdviceType($index=NULL){
    	if (!empty($index)) {
    		return $this->AdviceType[$index];
    	} else {
    		return $this->AdviceType;
    	}
    }
}