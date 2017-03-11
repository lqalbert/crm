<?php
namespace Home\Model;
use Think\Model;
class RealInfoModel extends Model{
	protected $tableName = 'deal_info';
    protected $_validate = array(
    	array('identity','/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/','身份证号格式错误',1,'regex',1), 
    	array('identity','unique','身份证号已经存在',1,'regex',2), 
        array('expense','number','金额为纯数字',1,'regex',1),
        array('type','require','产品类型必填',1,'regex',1),
        array('cycle','require','服务周期必须',1,'regex',1),
        array('address','require','通讯地址必须',1,'regex',1),
    );
}