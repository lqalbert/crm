<?php
namespace Cli\Controller;


import('Common.Vender.PhpExcel.PHPExcel',APP_PATH,'.php');

class TController extends \Think\Controller{

    private $list=array(
       
  
    );


    public function index(){
        $all = 0;
        
        $root = getcwd();
        $done = $root ."\\"."data1";
        
        $this->list[] = $done;

        foreach ($this->list as $key => $value) {
            
            //获取本文件目录的文件夹地址
           


            $updar = scandir($value);
            
            foreach ($updar as $val) {
                if ($name !="." && $name!="..") {
                    $filesnames = scandir($value."\\".$val);
                    //////////////////////////////

                    $all += $this->deal($filesnames, $value."\\".$val);
                    /////////////////////////////
                }
            }

            //获取也就是扫描文件夹内的文件及文件夹名存入数组 $filesnames
              //print_r ($filesnames);
            

            echo $all;
        }
        

    }

    private function deal($filesnames, $path){
        $all = 0;
        foreach ($filesnames as $name) {
            if ($name !="." && $name!="..") {
                $filename = $path.'\\'.$name;
                $tmp = pathinfo($filename, PATHINFO_EXTENSION  );
                if ($tmp != 'xls') {
                   continue;
                }
                var_dump($filename);
                $data = getExcelArrayData($filename);
                
                $insert_data = array();
                foreach($data as $value){
                    
                    $tmp_data = array(
                        'name'=>$value['A'],
                        'qq'  =>$value['B'],
                        'phone'=>$value['C'],
                        'ctype'=>$value['E'],
                        'id_card'=>$value['D'],
                        'ywy'=>$value['F'],
                        'sales'=>$value['G'],
                        'user'=>$value['H'],
                        'oldcode'=>$value['I'],
                        'group_id'=>0
                    );
                    $insert_data[] = $tmp_data;
                    if (count($insert_data)==500) {
                      $re =  M('import_table')->addAll($insert_data);
                      echo $re;
                      echo "\n";
                      $insert_data = array();
                    }
                   
                }
                if (count($insert_data)) {
                    $re = M('import_table')->addAll($insert_data);
                    echo $re;
                    echo "\n";
                }   
            }
        }
        return $all;
    }

      
}