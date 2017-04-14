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
        foreach ($this->groups as $key =>  $value) {
            // var_dump($value);
            $myWorkSheet = $this->PHPexcel->createSheet();
            $myWorkSheet->setTitle($value['name']);

            $myWorkSheet->setCellValue('A1', '账号');
            $myWorkSheet->setCellValue('B1', '密码');
            $this->setExcelEmployees($myWorkSheet, $value['id']);
            
        }
    }

    private function setExcelEmployees($sheet, $id){
        $users = D('User')->getGroupEmployee($id);
        foreach ($users as $key => $value) {
            $sheet->setCellValue('A'.($key+2), $value['account']);
            $sheet->setCellValue('B'.($key+2), '111111');
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