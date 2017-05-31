<?php
namespace Home\Service;

/**
* 导出 部门小组 人员的 excel 
*/
class EmployeeOutput {

    private $PHPexcel = null;

    private $id = 0;

    private $groups = array();

    private $fileTitle = "Office 2007 XLSX Test Document";



    private function setGroups(){
        $this->groups = D('Group')->where(array('department_id'=>$this->id, 'status'=>1))->select();
    }


    private function setExcelFile(){
        $this->PHPexcel->getProperties()->setCreator("yczx")
                                 ->setLastModifiedBy("yczx")
                                 ->setTitle($this->fileTitle);
                                 
                                 /*->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                                 ->setKeywords("office 2007 openxml php")
                                 ->setCategory("Test result file");*/
    }

    private function setExcelGroupsEmployee(){
        $this->PHPexcel->removeSheetByIndex(0);
        $myWorkSheet = $this->PHPexcel->createSheet();
        $myWorkSheet->setCellValue('A1', '账号');
        $myWorkSheet->setCellValue('B1', '密码');
        $row = 1;

        if (count($this->groups)) {
            foreach ($this->groups as $key =>  $value) {
                // $myWorkSheet->setTitle($value['name']);
                ++$row;
                $myWorkSheet->setCellValue('A'.$row, $value['name']);
                $this->setExcelEmployees($myWorkSheet, $value['id'], $row);
            }
        } 

        $users = D('user_info')->join('rbac_user on user_info.user_id=rbac_user.id')
                              ->where(array('department_id'=>$this->id, 'group_id'=>0))
                              ->select();
            
        foreach ($users as $key => $value) {
            ++$row;
            $myWorkSheet->setCellValue('A'.$row, $value['account']);
            $myWorkSheet->setCellValue('B'.$row, '111111');
        }
        


        
        
    }

    private function setExcelEmployees($sheet, $id, &$row){
        $users = D('User')->getGroupEmployee($id);
        foreach ($users as $key => $value) {
            ++$row;
            $sheet->setCellValue('A'.$row, $value['account']);
            $sheet->setCellValue('B'.$row, '111111');
        }
    }

    public function setDepartmentId($id){
        $this->id = $id;
    }

    public function setPHPExcel($obj){
        $this->PHPexcel = $obj;
    }

    public function setTitle($title){
        $this->fileTitle = $title;
    }

    


    public function run(){
        $this->setGroups();
        $this->setExcelFile();
        $this->setExcelGroupsEmployee();
    }


}