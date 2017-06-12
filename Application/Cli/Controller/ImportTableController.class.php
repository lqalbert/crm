<?php
namespace Cli\Controller;


use Home\Model\CustomerContactModel;

class ImportTableController extends \Think\Controller{

    //当前小组
    private $groups = array();

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

    public function delete(){
        $where = array('created_at'=>array( array('gt', '2017-05-27 00:00:00'), array('lt', '2017-05-28 00:00:00')  ));
        $count = M('customers_basic')->where($where)->count();
        $size = 5000;
        $offset = 0;
        while ($count>0) {
            $ids = M('customers_basic')->where($where)->limit($offset, $size)->getField('id', true);

            M('customers_basic')->where(array('id'=>array('in', $ids)))->delete();
            M('customers_contacts')->where(array('cus_id'=>array('in', $ids)))->delete();


            $count = M('customers_basic')->where($where)->count();

        }
        echo 'done';
        exit(0);

    }



    public function index(){
        $root = getcwd();
        $done = $root ."\\"."data2";

        $updar = scandir($done);
        
        // $this->delete();

        foreach ($updar as $val) {
            $dir = $done."\\".$val;
            if ($val !="." && $val!=".." && is_dir($done."\\".$val)) {
                $this->department($dir, $val);
            }
        }

        if (count($this->insert_data)>0) {
            $this->save();
        }
    }


    private function department($dir, $dirName){
        $iconvName = $this->strtoTransform($dirName);
        $match = array();
        preg_match('/([\x{4e00}-\x{9fa5}]+)(\(\d+\-\d+\))?/u', $iconvName, $match);
        $name = $match[1];
        $departments = M('department_basic')->where(array('name'=>$name))->find();
        if (!$departments) {
            return ;
        }
        $this->gorups  = M('group_basic')->where(array('department_id'=> $departments['id']))->getField('name,id', true);
        
        $updar = scandir($dir);
        foreach ($updar as $key => $value) {
            $subdir = $dir."\\".$value;
            if ($value !="." && $value!=".." && is_dir($subdir)) {
                $this->group($subdir, $value);
            }
        }

    }

    private function group($subdir, $dirName){
        $iconvName = $this->strtoTransform($dirName);
        $match = array();
        preg_match('/([\x{4e00}-\x{9fa5}]+)(\(\d+\-\d+\))?/u', $iconvName, $match);
        $name = $match[1];

        if (!in_array($name, array_keys($this->gorups))) {
            return ;
        }
        $group_id = $this->gorups[$name];
        $this->group_name = $name;
        $this->users  = M('user_info')->where(array('group_id'=>$group_id))->getField('realname,user_id', true);

        $updar = scandir($subdir);
        foreach ($updar as $key => $value) {
            $filename = $subdir."\\".$value;
            if ($value !="." && $value!=".." ) {
                $this->user($filename, $value);
            }
        }
    }

    private function user($path, $filename){
        $filename = pathinfo($path, PATHINFO_FILENAME);
        
        $iconvName = $this->strtoTransform($filename);

        $lastword =  mb_substr($iconvName, -3); //iconv('UTF-8', 'EUC-CN', mb_substr($iconvName, -3)); ;
        if ($lastword==$this->group_name) {
            return;
        }


        if (!in_array($iconvName, array_keys($this->users))) {
            // return ;
            $user_id = 0;
        } else {
            $user_id   = $this->users[$iconvName];
        }
        

        $tmp = pathinfo($path, PATHINFO_EXTENSION);
        if ($tmp != 'xls') {
           return ;
        }
        var_dump($path);
        $data = getExcelArrayData($path);
        var_dump(count($data));
        
        foreach ($data as $key => $value) {
             if (!empty($value['D']) &&  !empty($value['A']) &&  mb_strpos($value['A'], '简称')=== false) {
                $this->setFail($value, $user_id);
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

        if (count($this->insert_data)==500) {
            $this->save();
        }
    }


    private function save(){
        $re =  M('import_table3')->addAll($this->insert_data);
        echo $re;
        echo "\n";
        $this->insert_data = array();
    }
}