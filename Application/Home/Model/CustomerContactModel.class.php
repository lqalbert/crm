<?php
namespace Home\Model;
use Think\Model;

class CustomerContactModel extends Model {

     protected $tableName = 'customers_contacts';


     protected $_validate = array(
        array('phone','/^1[34578]\d{9}$/','手机号格式错误',   self::MUST_VALIDATE ,'regex'),
        array('phone',''                 ,'手机号已经存在',self::MUST_VALIDATE ,'unique'), // 是否唯一

        array('qq',   'number','QQ号格式错误',   self::VALUE_VALIDATE),
        array('qq',   ''      ,'QQ号已经存在',   self::VALUE_VALIDATE,    'unique' ),

        array('weixin',''      ,'微信号已经存在',self::VALUE_VALIDATE,  'unique' ),
    );
}