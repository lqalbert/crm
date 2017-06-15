<?php
namespace Cli\Controller;


use Home\Model\CustomerContactModel;

class ReImportController extends \Think\Controller{

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
        $done = $root ."\\"."data1";

        $updar = scandir($done);
        
        // $this->delete();

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
        $m = M();
        foreach ($data as $key => $value) {
             if (!empty($value['D']) &&  !empty($value['A']) &&  mb_strpos($value['A'], '简称')=== false) {
                // $phone = $this->fixPhone($value['D']);
                
                $phone = $this->fixPhone($value['D']);
                $createTime = $value['H'];
                $servTime   = $value['L'];
                $weixin     = empty($value['M']) ? null:$value['M'];
                $weixin_n   = $value['N'];

                echo 'deal phone:', $phone;
                echo "\n";

                
                $basicData = array(
                    'name'=> $value['A'],
                    'type'=> strtoupper(mb_substr($value['B'], 0,1)) ,
                    'area_province'=>null,
                    'area_city'=>null,
                    'user_id'=>$user_id,
                    'salesman_id'=>$user_id,
                    'service_time'=>time(),
                    // 'old_encode'   => $value['oldcode'],
                    'created_at'   => $createTime,
                );

                $contactData = array(
                    'cus_id'  =>  0,
                    'phone'   =>  $phone,
                    'qq'      =>  empty($value['C']) ? null : $value['C'],
                    'weixin'  =>  $weixin,
                    'weixin_nickname'      => $weixin_n,
                    'is_main' => 1
                );

                $cc = new CustomerContactModel();
                $re = $cc->create($contactData);
                
                 if($re){
                    $basicData =  M('customers_basic')->create($basicData);
                    if ($basicData) {
                        $m->startTrans();
                        $cus_id = M('customers_basic')->add();
                        
                        if (!$cus_id) {
                            $m->rollback();
                            $this->setFail($value, $path);
                            // $this->error("Customer".  M('customers_basic')->getError());
                            echo $phone, "fail.customers_basic";
                            echo "\n";
                        } else {
                            $re['cus_id'] = $cus_id;
                            $id = $cc->data($re)->add();
                            if (!$id) {
                                $m->rollback();
                                $this->setFail($value, $path);
                                echo $phone, "fail.cc";
                                echo "\n";
                                // $this->error(D('CustomerContact')->getError());
                            }  else {
                                $m->commit();

                                echo $phone, "success";
                                echo "\n";
                                // $iTM->where(array('id'=>$value['id']))->data(array('is_fault'=>0))->save(); //->delete();
                            }
                        }
                    }                  
                } else {
                    
                    // echo $value['name'];
                    echo $contactData['phone'];
                    echo $cc->getError();
                    echo "\n";
                    $this->setFail($value, $path);
                    // $iTM->where(array('id'=>$value['id']))->delete();
                }

                $cc = null;
             }
        }
    }


    private function setFail($data, $path){
        /*echo 'weixin fail';
        echo "\n";
        $m = M('update_weixinfail');
        if (!$m->where(array('phone'=>$fail_data['phone']))->find()) {
            $m->data($fail_data)->add();
        }*/

         /*A 简称
         B 客户分类
         C qq
         D 手机
         E 业务员
         F 管理人
         G 创建人
         H 创建日期
         I 部门
         J 城市
         K 编号
         L 修改日期
         M 微信号
         N 微信昵称*/

         $importData = array(
            'name' => $data['A'],
            'ctype' => $data['B'],
            'qq' => $data['C'],
            'phone' => $data['D'],
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
            'file_path' => $path,
         );

         M('reimport')->data($importData)->add();


    }
}