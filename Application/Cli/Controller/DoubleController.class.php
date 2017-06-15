<?php 

//select distinct import_table4.department from import_table4 left join import_table3 on import_table4.encode = import_table3.encode where import_table3.id is null 

namespace Cli\Controller;


class DoubleController extends \Think\Controller {

    private $departments = array();


    private function strtoTransform($dirName){
        return iconv('UTF-8', 'EUC-CN', $dirName);
    }

    public function index($name="成都"){

        echo $name, "=======================";
        echo "\n";
        import('Common.Vender.PhpExcel.PHPExcel',APP_PATH,'.php');
        $root = getcwd();
        $done = $root ."\\"."data3";

        $sql = "select * from import_table_fixtime2 where phone in (select phone  from import_table_fixtime2 group by `phone` having count(id) > 1) ";

        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()->setCreator("yczx")
                                 ->setLastModifiedBy("yczx")
                                 ->setTitle($name);
        $objPHPExcel->removeSheetByIndex(0);
        $myWorkSheet = $objPHPExcel->createSheet();
        
        $data = M()->query($sql);
        foreach ($data as $key => $value) {
            
            $myWorkSheet->setCellValue('A'.($key+1), $value['name']);
            $myWorkSheet->setCellValue('B'.($key+1), $value['ctype']);
            $myWorkSheet->setCellValue('C'.($key+1), $value['qq']);
            $myWorkSheet->setCellValue('D'.($key+1), $value['phone']);
            $myWorkSheet->setCellValue('E'.($key+1), $value['ywy']);
            $myWorkSheet->setCellValue('F'.($key+1), $value['glr']);
            $myWorkSheet->setCellValue('G'.($key+1), $value['cjr']);
            $myWorkSheet->setCellValue('H'.($key+1), $value['create_at']);
            $myWorkSheet->setCellValue('I'.($key+1), $value['department']);
            $myWorkSheet->setCellValue('J'.($key+1), $value['city']);
            $myWorkSheet->setCellValue('K'.($key+1), $value['encode']);
            $myWorkSheet->setCellValue('L'.($key+1), $value['edit_at']);
            $myWorkSheet->setCellValue('M'.($key+1), $value['weixin']);
            $myWorkSheet->setCellValue('N'.($key+1), $value['weixin_n']);
            
        }

        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $objWriter->save($done."\\". $name.".xls");

        $objPHPExcel = null;
        $myWorkSheet = null;
        $objWriter   = null;
        /*
        $myWorkSheet->setCellValue('A1', '账号');
        $myWorkSheet->setCellValue('B1', '密码');
        $row = 1;
        */
    }
}