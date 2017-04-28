<?php
namespace Home\Behaviors;


class checkContactBehavior extends \Think\Behavior {
    private $list = array();
    private $uid = 0;
    private $type ="";

    private $map = array(
        'phone'=>'手机号冲突',
        'qq'=>'qq号冲突',
        'weixin'=>'微信号冲突',
    );


    //行为执行入口
    public function run(&$param){

        $this->list = $param['list'];
        $this->uid  = $param['uid'];
        $this->type = $param['type'];
        $this->value = $param['value'];

        $this->D = D('CustomerConflict');

        
        $this->deal();


    }




    public function deal(){
        foreach ($this->list as $value) {
            
            $funcname = "add". ucfirst($this->type);
            
            if (method_exists($this->D, $funcname)) {
                $re = call_user_func(array($this->D, $funcname), $value, $this->uid, $this->value);
                if ($re) {
                    // 生成预查
                    $content = $this->map[$this->type].":".$this->value;
                    //冲突的跟踪纪录
                    D('CustomerLog')->add(array(
                        'cus_id'=>$value,
                        'user_id'=> $this->uid,
                        'track_text'=> '添加冲突',
                        'content'=> $content
                    ));

                    //生成消息盒子的纪录
                    D("MsgBox")->add(array(
                        'title'=>'添加冲突',
                        'content'=>$content ,
                        'from_id' => $this->uid,
                        'to_id' => M('customers_basic')->where(array('id'=>$value))->getField('salesman_id')
                    ));
                }
            }
        }
    }
}