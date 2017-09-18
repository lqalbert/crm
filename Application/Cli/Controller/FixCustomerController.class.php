<?php
namespace Cli\Controller;


class FixCustomerController extends \Think\Controller {

    private $depart_ids = array(
        // 1, //二部
        // 3,//六部
        // 4,// 7 bu
        // 5,// 3
        // 7,// 10
        // 8, // 4
        // 10,// 5
        // 12,//成都 
        2,
        18,//沈阳
        20,// 1
    );


    // public function index(){
    //     $sourM = M('', null, 'mysql://beta_crmd:beta2008beta@139.224.40.238:3306/beta_crmd#utf8');
    //     foreach ($this->depart_ids as $key => $value) {
    //         $Users = M()->query("select id from customers_basic where user_id in (select user_id from user_info where department_id=$value) and salesman_id=0");
    //         foreach ($Users as  $user) {
    //             $oldUser = $sourM->query("select salesman_id from customers_basic where id=".$user['id']." limit 1");
    //             echo "$user[id]", M()->execute("update customers_basic set salesman_id=".$oldUser[0]['salesman_id'].", olde_mark=null where id=".$user['id']);
    //             echo "\n";
    //         }
    //     }
    // }


    public function fix2(){
        $sql ="select id FROM `customers_basic`a,user_info b where salesman_id=0 and a.user_id=b.user_id and b.department_id=23";

        $user = M()->query($sql);
        foreach ($user as $key => $value) {
            M()->execute("update  customers_basic set salesman_id=1292 where id=".$value['id']);
        }
    }
}