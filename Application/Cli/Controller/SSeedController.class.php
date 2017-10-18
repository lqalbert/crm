<?php
namespace Cli\Controller;

use Think\Controller; 

class SSeedController extends Controller {
    private $days = 0;
    private $dateTpl = '';
    private $users = array();

    public function index(){
        die();
        $this->init();
        $this->seedRecord();
    }

    private function init(){
        $tmp = strtotime('2017-10-01');
        $this->days = 15;
        $this->dateTpl = '2017-10-';
    }

    private function seedRecord(){
        // for ($i=1; $i <= $this->days ; $i++) { 
        //      $dateTpl = $this->dateTpl. sprintf("%02d", $i);
             
        //      $this->setRecord($dateTpl);
             
        //      $this->updateRecord();
             
        // }
        $this->updateRecord();
    }

    private function getUsers(){
        if (!$this->users) {
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
                            'sale_amount' => '0.00',
                        );

                        $this->users[] = $tmp;
                    }
                }
            }
        } 
        return $this->users;
    }


    private function setRecord($date){
        $users = $this->getUsers();
        foreach ($users as &$value) {
            $value['date'] = $date;
        }

        echo "record insert: ", M('statistics_spread_achievement')->addAll($users);
        echo "\n";
    }

    private function updateRecord(){
        $orders = $this->getOrders();
        echo 'all orders';
        echo "\n";
        // var_dump($orders);
        foreach ($orders as $order) {
            $record = $this->getAchievementRecord($order);
            var_dump($record);
            if (is_array($record)) {
                $amount = intval($order['paid_in']);
                $sql = "update statistics_spread_achievement set order_num = order_num +1 ,sale_amount=sale_amount+".$amount." where id = ".$record['id'];
                M()->execute($sql);
            }
        }
    }

    private function getOrders(){
        return M('customers_order')->where(array(
            'source_type'=>2,
            'created_at'=> array(array('EGT', '2017-10-01'), array('ELT', '2017-10-15 23:59:59'))
            ))->field('paid_in,user_id,salesman_id,created_at,cus_id')
                                 ->select();
    }

    //检查是不是推广部分配给销售的
    private function check($order){
        $row = M("customers_basic")->where(array('id'=>$order['cus_id'], 'spread_id'=>array('NEQ', '0')))->find();
        if ($row) {
            return true;
        }
        return false;
    }

    private function getAchievementRecord($order){
        $date   = $order['created_at'];
        $userId = $order['user_id'];

        $date = explode(" ", $date);
        $date = $date[0];

        $row =  M('statistics_spread_achievement')->where(array('user_id'=>$userId, 'date'=>$date))->find();
        if ($row) {
            return $row;
        }  else {
            $useRow = M("user_info")->where(array("user_id"=>$userId))->field("group_id, department_id")->find();
            $group = M("group_basic")->where(array('id'=>$userRow['group_id']))->field("name")->find();
            $department = M("department_basic")->where(array('id'=>$userRow['department_id']))->field("name")->find();
            $data = array(
                'user_id'    => $userId,
                'group_id'   =>  $useRow['group_id'],
                'group_name' =>  $group['name'],
                'department_id'   =>  $useRow['department_id'],
                'department_name' =>  $department['name'],
                'date' => $date,
                'order_num' => 1,
                'sale_amount' => $order['paid_in'],
            );
            return  M('statistics_spread_achievement')->add($data);
        }
    }
}