<?php 
namespace Cli\Controller;


class Import7Controller extends \Think\Controller {


    private $insert_data = array();

    private function strtoTransform($dirName){
        return iconv('EUC-CN', 'UTF-8',$dirName);
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

        $updar = scandir($done);

        foreach ($updar as $val) {
            $dir = $done."\\".$val;
            
            if ($val !="." && $val!=".." ) {
                $this->user($dir, $val);
            }
        }

        if (count($this->insert_data)>0) {
            $this->save();
        }
    }



    private function user($path, $filename){
        
        $data = getExcelArrayData($path);
        foreach ($data as $key => $value) {
             if (!empty($value['D']) &&  !empty($value['A']) &&  mb_strpos($value['A'], '简称')=== false) {
                $this->setFail($value, 0);
             }
        }


    }


    private function setFail($data, $user_id){
        $importData = array(
            'name' => $data['A'],
            'ctype' => $data['B'],
            'qq' => $data['C'],
            'phone' => $this->fixPhone($data['D']),
            'ywy' => $data['E'],
            'glr' => $data['F'],
            'cjr' => $data['G'],
            'create_at' => $data['H'],
            'department' => $data['I'],
            'city' => $data['J'],
            'encode' => $data['K'],
            'edit_at' => $data['L'],
            'weixin' => $data['M'],
            'weixin_n' => $data['N'],
            'user_id' => $user_id,
        );

        $this->insert_data[] = $importData;

        if (count($this->insert_data)==10) {
            $this->save();
        }
    }


    private function save(){
        $re =  M('import_table_fixtime3')->addAll($this->insert_data);
        echo $re;
        echo "\n";
        $this->insert_data = array();
    }
}