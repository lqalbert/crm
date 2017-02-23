<?php
namespace Home\Controller;

class PreCheckController extends CommonController {
    protected $table = "customer";

    public function index(){
        $this->assign('customerType', $this->M->getType());
        $this->assign('sexType',      $this->M->getSexType());
        $this->display();
    }



    public function serach(){
        $result = $this->_getList();
        $this->setUserName($result['list']);
        if (IS_AJAX) {
            $this->ajaxReturn($result); 
        }  else {
            return $result;
        }
    }


    public function setUserName(&$list){
        foreach ($list as $key => $value) {
            if ($value['user_id']) {
                $list[$key]['user_name'] = M('user_info')->where(array('user_id'=>$value['user_id']))->getField('realname');
            } else {
                $list[$key]['user_name'] = "";
            }
        }
    }

    public function setQeuryCondition(){
        if (I('get.name', null)) {
            $this->M->where(array("name|phone|qq|phone2|weixin"=> array('like', I('get.name')."%")));
            //var_dump($this->M->getLastSql());
        } else {
            $this->M->where(array("phone"=> '00000000000000000000000'));
        }
    }


}