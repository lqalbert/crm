<?php
namespace Cli\Service;

class ExportCusSerive {
    private $PHPexcel = null;
    private $fileTitle = "Office";
    private $customers = null;

    private $rowKey = 2;



    private function setExcelFile(){
        $this->PHPexcel->getProperties()->setCreator("yczx")
                                 ->setLastModifiedBy("yczx")
                                 ->setTitle($this->fileTitle);
    }

    public function setPHPExcel($obj){
        $this->PHPexcel = $obj;
    }



    public function run(){
        // $this->setUsers();
        $this->setExcelFile();
        $this->setExcel();
    }

    public function setCustomers($customers){
        $this->customers = $customers;
    }


    private function setValue($value){
        if(strpos($value,'=') === 0){ $value = "'".$value; } 
        return $value;
    }

    private function setExcel(){
        $this->PHPexcel->removeSheetByIndex(0);
        $myWorkSheet = $this->PHPexcel->createSheet();
        $myWorkSheet->setCellValue('A1', '员工');
        $myWorkSheet->setCellValue('B1', '客户');
        $myWorkSheet->setCellValue('C1', '类型');
        $myWorkSheet->setCellValue('D1', '手机');
        $myWorkSheet->setCellValue('E1', 'QQ');
        $myWorkSheet->setCellValue('F1', '微信');
        $myWorkSheet->setCellValue('G1', '创建时间');

        foreach ($this->customers as $value) {
            $myWorkSheet->setCellValue('A'.$this->rowKey, $this->setValue($value['realname']));
            $myWorkSheet->setCellValue('B'.$this->rowKey, $this->setValue($value['name']));
            $myWorkSheet->setCellValue('C'.$this->rowKey, $this->setValue($value['type']));
            $myWorkSheet->setCellValue('D'.$this->rowKey, $this->setValue($value['phone']));
            $myWorkSheet->setCellValue('E'.$this->rowKey, $this->setValue($value['qq']));
            $myWorkSheet->setCellValue('F'.$this->rowKey, $this->setValue($value['weixin']));
            $myWorkSheet->setCellValue('G'.$this->rowKey, $this->setValue($value['created_at']));

            $this->rowKey++;
        }
    }
}