<?php
namespace Cli\Controller ;


class OldCodeController extends \Think\Controller {



    public function index(){
        $offset = I('get.of',0);
        $size = 1000;
        $user = M('import_table4')->field('phone,encode')->limit($offset, $size)->select();
        while ($user) {
            foreach ($user as $key => $value) {
                $this->update($value);
            }
            $offset += $size;
            echo $offset;
            echo "\n";
            $user = M('import_table4')->field('phone,encode')->limit($offset, $size)->select();
        }
    }

    public function update($data){
        //oldcode
        $ccRow = M('customers_contacts')->where(array('phone'=>$data['phone']))->field('cus_id')->find();
        if ($ccRow) {
            $updateData = array('oldcode'=>$data['encode']);
            M('customers_basic')->data($updateData)->where(array('id'=>$ccRow['cus_id']))->save();
        } else {

        }

    }
}