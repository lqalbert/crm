<?php
namespace Cli\Controller;

use Think\Controller;

class CheckController extends Controller {



    private function fixPhone($phone){
        if (strpos($phone, chr(32)) !== false ) {
            return str_replace(chr(32), '', $phone);
        } else {
            return $phone;
        }
    }


    public function index(){
       $offset = 319849;
       $size = 10000;
       $count = M('import_table')->count();
       while ( $offset < $count ) {
          $records =  M('import_table')->order("id desc")->limit($offset, $size)->select();
          foreach ($records as $key => $value) {
              $phone = $this->fixPhone($value['phone']);
              echo $value['id']." ".$value['phone']."\n";
              $row = M('customers_contacts')->where(array('phone'=>$phone ))->find();
              if (!$row) {
                  $data = array('is_fault'=>1);
                  $re = M('import_table')->data($data)->where(array("id"=>$value['id']))->save();
              }
          }
          var_dump($offset);
          $offset += $size; 
       }  
    }
}