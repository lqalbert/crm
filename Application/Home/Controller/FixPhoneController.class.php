<?php
namespace Home\Controller;

class FixPhoneController extends \Think\Controller {


    public function index() {

        $users = M('user_info')->select();
        foreach ($users as $key => $value) {
            $data = array();
             
            if (strpos($value['mphone'], chr(32)) !== false ) {
                $data['mphone']  = str_replace(chr(32), '', $value['mphone']);
            }
            if (strpos($value['id_card'], 39) !== false ) {
                $data['id_card'] = str_replace(chr(39),"", $value['id_card']);
            }

            if (count($data)) {
                var_dump($data);  
                M('user_info')->where(array('user_id'=>$value['user_id']))->save($data);
            }

           
            
        }



        /*echo str_replace(' ', '', '187 0387 6910');
        echo str_replace("\'","", "\'411024198911204851");*/
    }
}