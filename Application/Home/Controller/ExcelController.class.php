<?php
namespace Home\Controller;

use Home\Model\RoleModel;
/**
* 
*/
class ExcelController extends CommonController {
    protected $table = "customer";
    protected $pageSize = 20;

    private function fixPhone($phone){
        if (strpos($phone, chr(32)) !== false ) {
            return str_replace(chr(32), '', $phone);
        } else {
            return $phone;
        }
    }

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
        // die('暂不可用');
        if (IS_GET) {
            $this->assign('pageSize', $this->pageSize);
            $this->assign('departments',   M('department_basic')->field('id,name')->select());
            $this->assign('groups',        arr_group(M('group_basic')->field('id,name,department_id')->select(), 'department_id'));
            $this->display('customerImport');
        } else {
            set_time_limit (120);
            $filename = I('post.file');
            $group_id = I('post.group_id',0);
            
            if (file_exists(".".__ROOT__.$filename)) {
                $data = getExcelArrayData(".".__ROOT__.$filename);
                
                $m = M();
                $m->startTrans();
                
                
                foreach ($data as $value) {
                    /*
                    * A 简称
                    * B QQ
                    * C 手机号
                    * D 身份证号
                    * E 客户分类
                    * F 业务员
                    * G 管理人
                    * H 创建人
                    * I 编号
                    */

                    /**
                    * customers_contacts
                    * cus_id
                    * phone 
                    * qq
                    * weixin
                    * is_main
                    */

                    /**
                    * customers_basic
                    * name
                    * id_card D
                    * type E
                    * help_salesman  F
                    * help_transfer G ==> salesman_id
                    * help_user     H ==> user_id
                    * old_encode    I
                    *
                    */
                    //手机号不为空

                    if (!empty($value['C']) &&  !empty($value['A']) &&  mb_strpos($value['A'], '简称')=== false) {
                        
                        $basicData = array(
                            'name'=> $value['A'],
                            'type'=> strtoupper(mb_substr($valuee['E'], 0,1)) ,
                            'area_province'=>null,
                            'area_city'=>null,
                            'help_group_id'=>$group_id,
                            'help_salesman'=> $value['F'],
                            'help_transfer'=> $value['G'],
                            'help_user'    => $value['H'],
                            'old_encode'   => $value['I'],
                            'created_at'   => null,
                        );

                        

                        $contactData = array(
                            'cus_id' =>  0,
                            'phone'  =>  $this->fixPhone($value['C']),
                            'qq'     =>  $value['B'],
                            'is_main' => 1
                        );

                        $re = D('CustomerContact')->create($contactData);
                        
                        if($re){
                            M('customers_basic')->create($basicData);

                            $cus_id = M('customers_basic')->add();
                            if (!$cus_id) {
                                $m->rollback();
                                $this->error("Customer".  M('customers_basic')->getError());
                            } else {
                                $re['cus_id'] = $cus_id;
                                $id = D('CustomerContact')->data($re)->add();
                                if (!$id) {
                                    $m->rollback();
                                    $this->error(D('CustomerContact')->getError());
                                } 
                            }
                        }else {
                            /*$m->rollback();
                            $this->error("phone:".$contactData['phone']." QQ:" .$contactData['qq']. D('CustomerContact')->getError());*/
                        }
                    }
                } 
                $m->commit();
                $this->success("导入成功");
            } else {
                // echo '文件不存在';
                $this->error("文件不存在:".".".__ROOT__.$filename);
            }
            
        } 
    }


    public function importToTable(){
        set_time_limit (120);
            $filename = I('post.file');
            $group_id = I('post.group_id',0);
            
            if (file_exists(".".__ROOT__.$filename)) {
                $data = getExcelArrayData(".".__ROOT__.$filename);
               
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
                        'group_id'=>$group_id
                    );
                    $insert_data[] = $tmp_data;
                }

                $re = M('import_table')->addAll($insert_data);
                if ($re) {
                    $this->success('成功');
                } else {
                    $this->error(M('import_table')->getError());
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

    /*public function employeeImport(){
         if (IS_GET) {
            $this->assign('pageSize', $this->pageSize);
            $this->assign('departments',   M('department_basic')->field('id,name')->select());
            $this->display('employeeImport');
        } else {
            $filename = I('post.file');
            $department_id = I('post.department_id',0);
            
            if (file_exists(".".__ROOT__.$filename)) {
                $data = getExcelArrayData(".".__ROOT__.$filename);
                $groups = M('group_basic')->getField('name,id');
                $gorups_name = array_keys($groups);
                // var_dump(count($data));
                $fault_data = array();

                foreach ($data as $value) {

                    if (empty($value['C'])  || $value['C']=="简称") {
                        continue;
                    }

                    
                    

                    //rbac_user
                    $user = array();
                    $user['account'] = $value['C'];
                    $user['password'] = md5('111111');
                    $re = M('rbac_user')->create($user);

                    if (!$re) {
                        echo $user['account']."失败";
                        echo "\n";
                        continue;
                    }


                    $user_id = M('rbac_user')->add();
                    if (!$user_id) {
                        // $this->error("操作出错")
                        $fault_data[] = $value['C'];
                        continue;
                    }
                    //user_info
                    $info = array();
                    $info['user_id']   = $user_id;
                    $info['realname']  = $value['D'];
                    $info['qq']        = $value['I'];
                    $info['mphone']    = $value['H'];
                    $info['id_card']   = $value['J'];
                    $info['join_time'] = $value['G'];

                    $info['department_id'] = $department_id ;

                    $group = mb_substr(trim($value['F']), -1, 3);
                    if (in_array($group, $gorups_name)) {
                        $info['group_id'] = $groups[trim($value['F'])];
                    } else {
                        $info['gorup_id'] = 0;
                    }

                    
                    //
                    $info['role_id'] = D('Role')->getIdByEname(RoleModel::STAFF);

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
    }*/

     public function employeeImport(){
         if (IS_GET) {
            $this->assign('pageSize', $this->pageSize);
            $this->assign('departments',   M('department_basic')->field('id,name')->select());
            $this->assign('groups',        arr_group(M('group_basic')->field('id,name,department_id')->select(), 'department_id'));
            $this->display('employeeImport');
        } else {
            $filename = I('post.file');
            $department_id = I('post.department_id',0);
            
            if (file_exists(".".__ROOT__.$filename)) {
                $data = getExcelArrayData(".".__ROOT__.$filename);
                /*$groups = M('group_basic')->where(array('department_id'=>$department_id))->getField('name,id');
                $gorups_name = array_keys($groups);*/
                
                $fault_data = array();

                $role_id = D('Role')->getIdByEname(RoleModel::STAFF);
                $m = M();
                $m->startTrans();
                
                foreach ($data as $value) {

                    
                    if (empty($value['B'])  ||  0 === mb_stripos($value['B'], "简称") ) {
                        continue;
                    }

                    
                    

                    //rbac_role_user
                    $user = array();
                    $user['account'] = $value['B'];
                    $user['password'] = md5('111111');
                    $re = D('RbacUser')->create($user);
                    if (!$re) {
                        $m->rollback();
                        $this->error(D('RbacUser')->getError().$value['B']);
                    }
                    


                    $user_id = D('RbacUser')->add();
                    // $user_id  =-1;
                    if (!$user_id) {
                        // $this->error("操作出错")
                        $m->rollback();
                        $this->error(D('RbacUser')->getError());
                        
                        /*$fault_data[] = $value['B'];
                        continue;*/
                    }
                    //user_info
                    $info = array();
                    $info['user_id']   = $user_id;
                    $info['realname']  = $value['C'];
                    $info['qq']        = $value['H'];
                    $info['mphone']    = str_replace(' ', '', $value['G']);
                    $info['id_card']   = $value['I'];
                    // $info['join_time'] = $value['G'];

                    $info['department_id'] = $department_id ;
                    $info['group_id']      = I('post.group_id') ;

                    /*$group = mb_substr(trim($value['E']), -3);
                   

                    if (in_array($group, $gorups_name)) {
                        $info['group_id'] = $groups[$group];
                    } else {
                        $info['gorup_id'] = 0;
                    }*/
                    
                    
                    //

                    $info['role_id'] = $role_id ;

                    $infoDate = M('user_info')->create($info);
                    if (!$infoDate) {
                        $m->rollback();
                        $this->error(M('user_info')->getError());
                    }
                    $re = M('user_info')->add();
                    if (!$re) {
                        $m->rollback();
                        $this->error(M('user_info')->getError());
                    } else {
                        $m->commit();
                    }


                    // rbac_role_user
                    $role_user = array();
                    $role_user['user_id'] = $user_id;
                    $role_user['role_id'] = $role_id ;
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
                    $this->success("导入成功，有".count($fault_data)."条导入失败". json_encode($fault_data));
                }else{
                    $this->success("导入成功");
                }
               
                
            } else {
                
                $this->error("文件不存在:".".".__ROOT__.$filename);
            }
            
        } 
    }



    public function fixGroup(){
        $filename = I('post.file');
        $department_id = I('post.department_id',0);
        $group_id      = I('post.group_id',0);
            
        if (file_exists(".".__ROOT__.$filename)) {
            $data = getExcelArrayData(".".__ROOT__.$filename);
            $where = array('department_id'=> $department_id , 'group_id'=>0);
            foreach ($data as $value) {
                $where['realname'] = $value['C'];
                $userRow = M('user_info')->where($where)->field('user_id')->find();
                if ($userRow) {
                    M('user_info')->data(array('group_id'=>$group_id))->where(array('user_id'=>$userRow['user_id']))->save();
                }
            }

        }
        $this->success("导入成功");
    }
}