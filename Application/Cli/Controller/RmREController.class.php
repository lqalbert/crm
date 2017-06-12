<?php
namespace  Cli\Controller;


class RmREController  extends \Think\Controller {

    private $phones = array();

    public function index(){
        $re = M()->query("select id,phone from import_table4 where `phone` in (select `phone` from import_table4  group by  `phone` having count(id) > 1)");
        foreach ($re as $key => $value) {
            $this->phones[$value['id']] = $value['phone'];
        }

        $ids = array_values(array_flip($this->phones));
        M('import_table4')->where(array('id'=>array('in', $ids )))->delete();

    }
}