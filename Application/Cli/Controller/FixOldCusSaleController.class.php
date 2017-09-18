<?php
namespace Cli\Controller;

class FixOldCusSaleController extends \Think\Controller{

    private $departs = array(
        6, //新乡部
        9,//信阳部
        11,//许昌部
        14,//洛阳部
        13,//周口一部
        15,//周口二部
        16,//驻马店部
        17,//平顶山部
    );

    public function index(){
        $this->sourcM = M('', null, 'mysql://beta_crmd:beta2008beta@139.224.40.238:3306/beta_crmd#utf8');

        $this->deal();
    }

    private function deal(){
        foreach ($this->departs as $value) {
            $users = $this->getUsers($value);
            foreach ($users as $user) {
                $customers = $this->getCustomers($user['id']);
                foreach ($customers as $customer) {
                    $this->update($customer['id'], $customer['salesman_id']);
                }
            }
        }
    }

    private function getUsers($id){
        return D("Home/User")->getDepartmentEmployee($id, 'id');
    }

    private function getCustomers($userId){
        $sql= "select id , salesman_id from customers_basic where user_id=".$userId." and user_id<> salesman_id";
        return $this->sourcM->query($sql);
    }

    private function update($id, $user_id){
        $sql = "update customers_basic set user_id=".$user_id." where id=".$id;
        echo  M()->execute($sql);
        echo "\n";
    }
}