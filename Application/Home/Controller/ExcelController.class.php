<?php
namespace Home\Controller;

/**
* 
*/
class ExcelController extends CommonController {
    protected $table = "customer";
    protected $pageSize = 20;



    private function conformQQ($qq){
        if (!empty($qq)) {

            if (preg_match('/^\d+$/', $qq)==0) {
                return false;
            }



            $row = M('customers_basic')->field('id')->where(array('qq'=>$qq))->find();
            if ($row) {
                return false;
            } 
        }
        return true;
        
    }

    private function conformPhone($phone){
        $row = M('customers_basic')->field('id')->where(array('phone'=>$phone))->find();
        if ($row) {
            return false;
        } else {
            return true;
        }
    }

    private function conformCheck($data, $qq){
        foreach ($data as $value) {
            if (trim($value['qq']) == trim($qq)) {
                return false;
            }
        }
        return true;
    }


    public function index(){

        $this->display();
    }


    public function upload(){
        $folder = 'xls';
        $this->upload = new \Think\Upload();// 实例化上传类
        $this->upload->maxSize   =     3145728 ;// 设置附件上传大小3M
        $this->upload->exts      =     array('xls');// 设置附件上传类型
        $this->upload->rootPath  =     './Upload/'.$folder.'/'; // 设置附件上传根目录
        $this->upload->autoSub   =     true;


        $info =  $this->upload->upload();
        $this->ajaxReturn(array('path'=>substr($this->upload->rootPath, 1 ).$info['file']['savepath'].$info['file']['savename']));
        // return substr($this->upload->rootPath, 1 ).$info['file']['savepath'].$info['file']['savename'];
    }


    public function customerImport(){

        if (IS_GET) {
            $this->assign('pageSize', $this->pageSize);
            $this->assign('groups',   M('group_basic')->field('id,name')->select());
            $this->display('customerImport');
        } else {
            $filename = I('post.file');
            $group_id = I('post.group_id',0);
            
            if (file_exists(".".__ROOT__.$filename)) {
                $data = getExcelArrayData(".".__ROOT__.$filename);
                // var_dump(count($data));
                $insert_data = array();
                $fault_data = array();
                foreach ($data as $value) {

                    if (empty($value['E'])  || $value['C']=="QQ") {
                        continue;
                    }

                    $row = array();
                    $row['name'] = $value['A'];
                    if (empty($value['B'])) {
                        $row['type'] = 'C';
                    } else {
                        $row['type'] = strtoupper(mb_substr($value['B'], 0,1));
                    }
                    // 重复测栓

                    $row['qq'] = empty($value['C']) ? null: $value['C'];
                    $row['phone'] = $value['D'];

                    $row['help_salesman'] = $value['E'];
                    $row['help_transfer'] = $value['F'];
                    $row['help_user']     = $value['G'];
                    $row['help_group_id'] = $group_id;
                    $row['created_at']    = null;//str_replace("/","-", $value['H']);

                    $row['weixin']        = empty($value['M']) ? null: $value['M'];
                    $row['weixin_nickname']    = empty($value['N']) ? '': $value['N'];

                    // 避免 触发器报错
                    $row['area_province'] = null; 
                    $row['area_city'] = null; 
                    
                    // self重复测
                    if ($this->conformPhone($row['phone']) && $this->conformQQ($row['qq']) && $this->conformCheck($insert_data, $row['qq'])) {
                        $insert_data[] = $row;
                    } else {
                        $fault_data[] = $row;
                    }
                }
                //去重复
                $conf = array();
                // $insert_data = array_values(arr_to_map($insert_data, 'qq'));
                $insert_data = array_values(arr_to_map($insert_data, 'phone'));
               
                $re  = M('customers_basic')->addAll($insert_data);
                $det = count($data) - count($insert_data);
                if ($re) {
                    // echo '导入成功';
                    if ($det==0) {
                        $this->success("导入成功");
                    } else {
                        $this->success("导入成功,有".$det."条数据未导入");
                    }
                    
                    /*if (!empty($fault_data)) {
                        echo '有'.count($fault_data)."条数据导入失败";
                        print_r($fault_data);
                    }*/
                } else {
                    /*var_dump($insert_data);
                    echo '导入失败'.M('customers_basic')->getLastsql();*/
                    $this->error("导入失败");
                }
            } else {
                // echo '文件不存在';
                $this->error("文件不存在:".".".__ROOT__.$filename);
            }
            
        } 
    }

    /**
    * 设置查询参数
    * 
    * @return null
    */
    public function setQeuryCondition() {
        $map = array(); //查询的参数
        $map['help_salesman'] = array('EXP', 'is not null');
        if (!empty(I('get.name'))) {
            $map['name|phone|qq|qq_nickname|weixin'] = array('like', I('get.name')."%");
        }
        $this->M->where($map);
    }

    /**
    * 查看导入的数据
    */
    public function customers(){
        $this->assign('pageSize', 20);
        $this->assign('groups', M('group_basic')->getField('id,name'));
        $this->display();
    }

    public function employeeImport(){
         if (IS_GET) {
            $this->assign('pageSize', $this->pageSize);
            $this->assign('departments',   M('department_basic')->field('id,name')->select());
            $this->display('employeeImport');
        } else {
            $filename = I('post.file');
            $department_id = I('post.department_id',0);
            
            if (file_exists(".".__ROOT__.$filename)) {
                $data = getExcelArrayData(".".__ROOT__.$filename);
                $groups = M('group_basic')->where(array('department_id'=>$department_id))
                                          ->getField('name,id');
                $gorups_name = array_keys($groups);
                // var_dump(count($data));
                $fault_data = array();

                foreach ($data as $value) {

                    if (empty($value['C'])  || $value['C']=="简称") {
                        continue;
                    }

                    
                    /*var_dump($value);
                    die();*/

                    //rbac_user
                    $user = array();
                    $user['account'] = trim($value['C']);
                    $user['password'] = md5('111111');
                    M('rbac_user')->create($user);
                    $user_id = M('rbac_user')->add();
                    if (!$user_id) {
                        // $this->error("操作出错")
                        $fault_data[] = $value['C'];
                        continue;
                    }
                    //user_info
                    $info = array();
                    $info['user_id']   = $user_id;
                    $info['realname']  = trim($value['D']);
                    $info['qq']        = $value['I'];
                    $info['mphone']    = $value['H'];
                    $info['id_card']   = $value['J'];
                    $info['join_time'] = $value['G'];

                    $info['department_id'] = $department_id ;

                    $group = trim($value['F']);
                    if (in_array($group, $gorups_name)) {
                        $info['group_id'] = $groups[trim($value['F'])];
                    } else {
                        $info['gorup_id'] = 0;
                    }

                    

                    $info['role_id'] = 8;

                    M('user_info')->create($info);
                    $re = M('user_info')->add();


                    // rbac_role_user
                    $role_user = array();
                    $role_user['user_id'] = $user_id;
                    $role_user['role_id'] = 8;
                    M('rbac_role_user')->create($role_user);
                    M('rbac_role_user')->add();

                    
                    
                    // 重复测栓
                    // if ($this->conformPhone($row['phone']) && $this->conformQQ($row['qq']) ) {
                    //     $insert_data[] = $row;
                    // } else {
                    //     $fault_data[] = $row;
                    // }
 
                }
                
                if (!empty($fault_data)) {
                    $this->success("导入成功，有".count($fault_data)."条导入失败");
                }else{
                    $this->success("导入成功");
                }
               
                
            } else {
                
                $this->error("文件不存在:".".".__ROOT__.$filename);
            }
            
        } 
    }
}