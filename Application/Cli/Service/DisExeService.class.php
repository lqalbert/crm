<?php
namespace Cli\Service;

class DisExeService {

    private $PHPexcel = null;

    private $id = 0;

    private $groups = array();

    private $fileTitle = "Office";

    private $rowKey = 2;



    private function setUsers(){
        $this->users =  M('user_info')->where(array('group_id'=>$this->id))->field("user_id as id, realname")->select();//D('Home/User')->getGroupEmployee($this->id, 'id, realname');
    }


    private function setExcelFile(){
        $this->PHPexcel->getProperties()->setCreator("yczx")
                                 ->setLastModifiedBy("yczx")
                                 ->setTitle($this->fileTitle);
    }

    private function setExcelGroupsEmployee(){
        $this->PHPexcel->removeSheetByIndex(0);
        $myWorkSheet = $this->PHPexcel->createSheet();
        $myWorkSheet->setCellValue('A1', '员工');
        $myWorkSheet->setCellValue('B1', '客户');
        $myWorkSheet->setCellValue('C1', '手机');
        $myWorkSheet->setCellValue('D1', 'QQ');
        $myWorkSheet->setCellValue('E1', '微信');
        $myWorkSheet->setCellValue('F1', '销售部员工');
        // $row = 1;
        foreach ($this->users as $user) {
            var_dump($user);
            var_dump('A'.$this->rowKey);
            var_dump("员工:".$user['realname']);
            $myWorkSheet->setCellValue('A'.$this->rowKey++, $this->setValue("员工:".$user['realname']));
            var_dump('error !!');
            $this->setCustomers($user, $myWorkSheet);
        }
  
    }

    private function setCustomers($user, $sheet){
        $re  = M('customers_basic')->field("id,name,salesman_id, depart_id, to_gid")
                            ->where(array('dis_time'=>array('GT', '2017-10-10 17:30:00'), 'created_at'=>array('LT', '2017-10-08'), 'user_id'=>$user['id']))
                            ->select();
        if (count($re)==0) {
            return;
        }
        foreach ($re as  $customer) {
            var_dump('after');
            var_dump($customer['name']);
            var_dump($this->rowKey);
            $sheet->setCellValue('B'.$this->rowKey, $this->setValue($customer['name']));
            
            $contacts = M('customers_contacts')->where(array('cus_id'=>$customer['id'], 'is_main'=>1))->find();
            $sheet->setCellValue('C'.$this->rowKey, $contacts['phone']);
            
            $sheet->setCellValue('D'.$this->rowKey, $contacts['qq']);
            
            $sheet->setCellValue('E'.$this->rowKey, $contacts['weixin']);
            

            //销售部
            $sale  = M("user_info")->where(array('user_id'=>$customer['salesman_id']))->getField('realname');
            $group = M("group_basic")->where(array('id'=>$customer['to_gid']))->getField('name');
            $depart  = M("department_basic")->where(array('id'=>$customer['depart_id']))->getField('name');
            $sheet->setCellValue('F'.$this->rowKey, $depart."-".$group."-".$sale);
            
            $this->rowKey++;
        }
        $this->rowKey++;
        
    }

    

    public function setGroupId($id){
        $this->id = $id;
    }

    public function setPHPExcel($obj){
        $this->PHPexcel = $obj;
    }

    public function setTitle($title){
        $this->fileTitle = $title;
    }

    


    public function run(){
        $this->setUsers();
        $this->setExcelFile();
        $this->setExcelGroupsEmployee();
    }


    private function setValue($value){
        if(strpos($value,'=') === 0){ $value = "'".$value; } 
        return $value;
    }
}