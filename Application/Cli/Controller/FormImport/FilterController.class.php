<?php 
namespace Cli\Controller;


class FilterController extends \Think\Controller{


    private $insert_data = array();
    private $dep2="";

    private function strtoTransform($dirName){
        return iconv('EUC-CN', 'UTF-8',$dirName);
    }

    private function strtoBack($dirName){
        return iconv('UTF-8','EUC-CN', $dirName);
    }

    private function fixPhone($phone){
        if (strpos($phone, chr(32)) !== false ) {
            return str_replace(chr(32), '', $phone);
        } else {
            return $phone;
        }
    }

    public function index(){
        $root = getcwd();
        $done = $root ."\\"."data1";
        $this->dealDir($done);

        if (count($this->insert_data)>0) {
            $this->save();
        }
    }


    private function dealDir($dir){
        $updar = scandir($dir);
        $pathTrans = $this->strtoTransform($dir);
        foreach ($updar as $name) {
            if ($name!="." && $name!="..") {
                $subdir = $dir."\\".$name;
               
                if (is_dir($subdir)) {

                    $iconvName = $this->strtoTransform($name);
                    
                    if (mb_substr($this->getName($iconvName), -1)=="部") {
                        
                        $this->dep2 = $this->getName($iconvName);
                        /*$this->outPut($name);
                        $this->clear();*/
                    }

                    $this->dealDir($subdir);
                    
                } else if(is_file($subdir)) {
                    /*echo "detect:";
                    var_dump($subdir);
                    var_dump($dir);*/
                    $tmp  = explode("\\", $dir);
                    $dirName = $this->getName($this->strtoTransform($tmp[count($tmp)-1]));
                    /*echo "dir:";
                    var_dump($this->strtoBack($dirName));*/
                    // die();
                    $lastword = mb_substr($dirName, -1);
                    /*echo "lastword:";
                    var_dump($this->strtoBack($lastword));
                    var_dump($lastword == "队");*/
                    if ($lastword == "队") {
                       $filename = basename($subdir, ".xls");
                       if ($this->strtoTransform(mb_substr($filename, -2))!="队") {
                           continue;
                       }
                    }
                    $this->dealFile($subdir, $dirName);
                }   
            }
        }
    }


    private function dealFile($file, $filename){
        
        $filInfo = pathinfo($this->strtoTransform($file), PATHINFO_EXTENSION );
        
        if ($filInfo != 'xls') {
           return ;
        }

        // $iconvName = $this->strtoTransform($filInfo['filename']);
        /*$lastword =  mb_substr($filInfo['filename'], -1); 
        if ($lastword !="队") {
            return ;
        }*/

        echo "read:", $file;
        echo "\n";
        $data = getExcelArrayData($file);

        foreach ($data as $key => $value) {
         if (!empty($value['D']) &&  !empty($value['A']) &&  mb_strpos($value['A'], '简称')=== false) {
            $this->setFail($value, $filename);
         }
        }

        

    }


    private function setFail($data, $group){
        // var_dump($this->strtoBack($group));
        $importData = array(
            'name' => $data['A'],
            'ctype' => $data['B'],
            'qq' => $data['C'],
            'phone' => $this->fixPhone($data['D']),
            'ywy' => $data['E'],
            'glr' => $data['F'],
            'cjr' => $data['G'],
            'create_at' => $data['H'],
            'department' => $group,// $data['I'],
            'department2' => $this->dep2,
            'city' => $data['J'],
            'encode' => $data['K'],
            'edit_at' => $data['L'],
            'weixin' => $data['M'],
            'weixin_n' => $data['N'],
            'group_id' => 0,
        );

        $this->insert_data[] = $importData;

        if (count($this->insert_data)==500) {
            $this->save();
        }
    }


    private function save(){
        $re =  M('import_table_fixtime3')->addAll($this->insert_data);
        echo $re;
        echo "\n";
        $this->insert_data = array();
    }

    private function clear(){
        M('import_table_fixtime3')->where("1")->delete();
    }

    private function outPut($name){
        R('Double/index', array($name));
    }

    private function getName($name){
        $match = array();
        $re = preg_match('/([\x{4e00}-\x{9fa5}]+)(\(\d+\-\d+\))?/u', $name, $match);
        if ($re !== false) {
           
            return $match[1];
        } else {
            return $name;
        }

    }
}