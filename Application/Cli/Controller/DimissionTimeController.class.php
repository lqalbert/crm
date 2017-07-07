<?php
namespace Cli\Controller;

class DimissionTimeController extends \Think\Controller{

    public function index(){
        $this->deal();
    }

    public function deal(){
        $re = M('rbac_user')->where(array('status'=>-1))->field('id,account')->select();
        foreach ($re as $key => $value) {
            //user_info
            $row = M('user_info')->where('user_id='.$value['id']." and dimission_at is null")->find();
            if ($row) {
                $time = $this->getTime($value['account']);
                $sql = " update user_info set dimission_at='".$time." 00:00:00' where user_id=".$value['id'];
                M()->execute($sql);
            }
        }
        
    }

    private function getTime($account){
        $reg = '/[\x{4e00}-\x{9fa5}]+\_(\d{4}\-\d{2}-\d{2})/u';
        $match = array();
        $i =  preg_match($reg, $account, $match);
        if ($i !== false) {
           return $match[1];
        }
    }
}



