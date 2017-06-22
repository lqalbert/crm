<?php 

//select distinct import_table4.department from import_table4 left join import_table3 on import_table4.encode = import_table3.encode where import_table3.id is null 

namespace Cli\Controller;


    

class DimissionController extends \Think\Controller {

    private function strtoTransform($dirName){
        return iconv('UTF-8', 'EUC-CN', $dirName);
    }

    public function index(){
        import('Common.Vender.PhpExcel.PHPExcel',APP_PATH,'.php');
        $root = getcwd();
        $done = $root ."\\"."data3";


        $sql="select distinct import_table4.department from import_table4 left join import_table3 on import_table4.encode = import_table3.encode where import_table3.id is null ";
        $groups = M()->query($sql);
        foreach ($groups as $key => $value) {
            $sql ="select  import_table4.* from import_table4 left join import_table3 on import_table4.encode = import_table3.encode where import_table3.id is null and import_table4.department='".$value['department']."' order by import_table4.ywy asc";
            $all = M()->query($sql);

            $objPHPExcel = new \PHPExcel();

            $objPHPExcel->getProperties()->setCreator("yczx")
                                 ->setLastModifiedBy("yczx")
                                 ->setTitle($value['department']);
            $objPHPExcel->removeSheetByIndex(0);
            $myWorkSheet = $objPHPExcel->createSheet();
            foreach ($all as $index=> $cus) {
                $myWorkSheet->setCellValue('A'.($index+1), $cus['name']);
                $myWorkSheet->setCellValue('B'.($index+1), $cus['ctype']);
                $myWorkSheet->setCellValue('C'.($index+1), $cus['qq']);
                $myWorkSheet->setCellValue('D'.($index+1), $cus['phone']);
                $myWorkSheet->setCellValue('E'.($index+1), $cus['ywy']);
                $myWorkSheet->setCellValue('F'.($index+1), $cus['glr']);
                $myWorkSheet->setCellValue('G'.($index+1), $cus['cjr']);
                $myWorkSheet->setCellValue('H'.($index+1), $cus['create_at']);
                $myWorkSheet->setCellValue('I'.($index+1), $cus['department']);
                $myWorkSheet->setCellValue('J'.($index+1), $cus['city']);
                $myWorkSheet->setCellValue('K'.($index+1), $cus['encode']);
                $myWorkSheet->setCellValue('L'.($index+1), $cus['edit_at']);
                $myWorkSheet->setCellValue('M'.($index+1), $cus['weixin']);
                $myWorkSheet->setCellValue('N'.($index+1), $cus['weixin_n']);
            }
            
            $objPHPExcel->setActiveSheetIndex(0);

            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

            $objWriter->save($done."\\". $this->strtoTransform($value['department']).".xls");


            $objPHPExcel = null;
            $myWorkSheet = null;
            $objWriter = null;
            /*
            $myWorkSheet->setCellValue('A1', '账号');
            $myWorkSheet->setCellValue('B1', '密码');
            $row = 1;
            */

        }
    }
}