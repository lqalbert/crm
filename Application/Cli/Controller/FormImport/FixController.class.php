<?php
namespace Cli\Controller;


use Home\Model\CustomerContactModel;

class FixController extends \Think\Controller{

    //当前小组
    private $groups = array();

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
            if ($val !="." && $val!=".." && is_dir($done."\\".$val)) {
                $this->department($dir, $val);
            }
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


        if (!in_array($iconvName, array_keys($this->users))) {
            return ;
        }
        $user_id   = $this->users[$iconvName];

        $tmp = pathinfo($path, PATHINFO_EXTENSION);
        if ($tmp != 'xls') {
           return ;
        }
        var_dump($path);
        $data = getExcelArrayData($path);
        var_dump(count($data));
        foreach ($data as $key => $value) {
             if (!empty($value['D']) &&  !empty($value['A']) &&  mb_strpos($value['A'], '简称')=== false) {
                // $phone = $this->fixPhone($value['D']);
                
                $phone = $this->fixPhone($value['D']);
                $createTime = $value['H'];
                $servTime   = $value['L'];
                $weixin     = $value['M'];
                $weixin_n   = $value['N'];

                echo 'deal phone:', $phone;
                echo "\n";

                //微信号
                $row = M('customers_contacts')->where(array('phone'=>$phone,'is_main'=>1))->find();
                if ($row) {
                    echo 'find cc row';
                    echo "\n";
                    if (!empty($weixin) && strlen($weixin) >0  ) {
                        $row['weixin']          = $weixin;
                        $row['weixin_nickname'] = $weixin_n;
                        $cc = new CustomerContactModel();
                        $createRe= $cc->create($row);
                        if (!$createRe) {
                            echo $cc->getError();
                            echo "\n";
                            $fail_data = array(
                                'phone'=>$phone, 
                                'weixin'=>$weixin, 
                                'weixin_n'=>$weixin_n,
                                'file_path'=>$this->strtoTransform($path)
                                );
                            // M('update_weixinfail')->data($fail_data)->add();
                            $this->setFail($fail_data);
                            
                        } else {
                            $re = $cc->save();
                            if ($re === false) {
                                echo $cc->getError();
                                echo "\n";
                                $fail_data = array(
                                    'phone'=>$phone, 
                                    'weixin'=>$weixin, 
                                    'weixin_n'=>$weixin_n,
                                    'file_path'=>$this->strtoTransform($path)
                                    );
                                // M('update_weixinfail')->data($fail_data)->add();
                                $this->setFail($fail_data);
                            } else {
                                echo $phone. " success";
                                echo "\n";
                            }
                        }
                        $cc = null;
                    }
                    
                    $basic_row = M('customers_basic')->field('user_id, salesman_id')->find($row['cus_id']);
                
                    //
                    $basic_data = array('created_at'=> $createTime);
                    if ($basic_row['salesman_id']!=$user_id) {
                        $basic_data['salesman_id'] = $user_id;
                    }
                    M('customers_basic')->data($basic_data)->where('id='.$row['cus_id'])->save();
                } else {
                    echo  $phone. " not found";
                    echo "\n";
                }
                

                
             }
        }
    }


    private function setFail($fail_data){
        echo 'weixin fail';
        echo "\n";
        $m = M('update_weixinfail');
        if (!$m->where(array('phone'=>$fail_data['phone']))->find()) {
            $m->data($fail_data)->add();
        }
    }
}