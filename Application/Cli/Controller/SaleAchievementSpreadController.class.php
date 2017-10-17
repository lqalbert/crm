<?php
namespace Cli\Controller;

use Think\Controller;


class SaleAchievementSpreadController extends Controller {

    private $date = '';
    private $days = 0;
    private $dateTpl = '';
    private $users = array();

    public function index($date='2017-10-13'){
        $this->init($date);
        $this->seedRecord();
    }

    private function init($date){
        $this->date = $date;
    }

    private function seedRecord(){
        
        $users = $this->getUsers();
        foreach ($users as &$value) {
            $value['date'] = $this->date;

            $orders = M('customers_order')->where(array(
                                                'created_at'=> array(array('EGT', $this->date), array('ELT', $this->date.' 23:59:59')), 
                                                'user_id'   => $value['user_id']))
                                             ->field('paid_in,user_id')
                                             ->select();
            
            foreach ($orders as $order) {
                $value['order_num'] +=1;
                $value['sale_amount'] += intval($order['paid_in']);
            }

        }
        
        M('statistics_spread_achievement')->addAll($users);
        
    }

    private function getUsers(){
        if (empty($this->users)) {
            $departs = D('Home/Department')->getSpreadDepartments('id,name');
           
            foreach ($departs as  $depart) {
                $groups = D('Home/Group')->getAllGoups($depart['id'], 'id,name');
                foreach ($groups as $group) {
                    $users = D("Home/User")->getGroupEmployee($group['id'], "id");
                    
                    foreach ($users as $user) {
                        $tmp = array(
                            'user_id'    => $user['id'],
                            'group_id'   => $group['id'],
                            'group_name' => $group['name'],
                            'department_id'   => $depart['id'],
                            'department_name' => $depart['name'],
                            'date' => '',
                            'order_num' => 0,
                            'sale_amount' => '0',
                        );

                        $this->users[] = $tmp;
                    }
                }
            }
        } 

        return $this->users;
    }

}