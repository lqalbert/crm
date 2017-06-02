<?php
namespace Home\Model;
use Think\Model;

class CustomerContactModel extends Model {

     protected $tableName = 'customers_contacts';


     protected $_validate = array(
        // array('phone','/^1[34578]\d{9}$/','手机号格式错误',   self::VALUE_VALIDATE ,'regex'),
        array('phone',''                 ,'手机号已经存在',self::MUST_VALIDATE ,'unique'), // 是否唯一

        array('qq',   'number','QQ号格式错误',   self::VALUE_VALIDATE),
        array('qq',   ''      ,'QQ号已经存在',   self::VALUE_VALIDATE,    'unique' ),

        array('weixin',''      ,'微信号已经存在',self::VALUE_VALIDATE,  'unique' ),
    );

     


    public function filterField($data){
      /*empty($this->qq) && $this->qq = null;
      empty($this->weixin) && $this->weixin = null;
      return $this;*/
      empty($data['qq']) && $data['qq'] = null;
      empty($data['weixin']) && $data['weixin'] = null;
      empty($data['phone']) && $data['phone'] = null;
      return $data;

    }

    public function edit($data, $isPhone=false){
        if (empty($this->qq) && !empty($data['qq'])) {
            $this->qq = $data['qq'];
        }

        if (empty($this->weixin) && !empty($data['weixin'])) {
            $this->weixin = $data['weixin'];
        }

        if ($isPhone) {
            if (empty($this->phone) && !empty($data['phone'])) {
            $this->phone = $data['phone'];
        }
        }


        $this->weixin_nickname!=$data['weixin_nickname'] && $this->weixin_nickname = $data['weixin_nickname'];

        $this->qq_nickname != $data['qq_nickname'] && $this->qq_nickname=$data['qq_nickname'];

        return $this->save();

    }

    public function getMainPost(){
        $data = array(
            'phone' => I('post.phone',null),
            'qq'    => I('post.qq',null),
            'weixin' => I('post.weixin',null),
            'qq_nickname' => I('post.qq_nickname',null),
            'weixin_nickname' => I('post.weixin_nickname',null),
        );
        return $this->filterField($data);
    }

    public function getSecondPost(){
        $data = array(
            'phone' => I('post.phone2'),
            'qq'    => I('post.qq2'),
            'weixin' => I('post.weixin2'),
            'qq_nickname' => I('post.qq_nickname2'),
            'weixin_nickname' => I('post.weixin_nickname2'),
        );
        return $this->filterField($data);
    }

    

}