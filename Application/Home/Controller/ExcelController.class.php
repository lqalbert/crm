<?php
namespace Home\Controller;

/**
* 
*/
class ExcelController extends CommonController {
    protected $table = "customer";
    protected $pageSize = 20;



    private function conformQQ($qq){
        
        $row = M('customers_basic')->field('id')->where(array('qq'=>$qq))->find();
        if ($row) {
            return false;
        } else {
            return true;
        }
    }

    private function conformPhone($phone){
        $row = M('customers_basic')->field('id')->where(array('phone'=>$phone))->find();
        if ($row) {
            return false;
        } else {
            return true;
        }
    }

    /*private function conformCheck($data, $qq, $phone){
        
        
        foreach ($data as $value) {
            if (trim($value['qq']) == trim($qq) or trim($value['phone'])==trim($phone)) {
                return false;
            } else {
                return true;
            }
        }
        return true;
    }*/


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
            $this->display();
        } else {
            $filename = I('post.file');
            $group_id = I('post.group_id',0);
            
            if (file_exists(".".__ROOT__.$filename)) {
                $data = getExcelArrayData($filename);
                // var_dump(count($data));
                $insert_data = array();
                $fault_data = array();
                foreach ($data as $value) {

                    if (empty($value['C'])  || $value['C']=="QQ") {
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
                    $row['qq'] = $value['C'];
                    $row['phone'] = $value['D'];

                    $row['help_salesman'] = $value['E'];
                    $row['help_transfer'] = $value['F'];
                    $row['help_user']     = $value['G'];
                    $row['help_group_id'] = $group_id;
                    $row['created_at']    = str_replace("/","-", $value['H']);

                    $row['weixin']        = empty($value['M']) ? null: $value['M'];
                    $row['weixin_nickname']    = empty($value['N']) ? '': $value['N'];

                    // 避免 触发器报错
                    $row['area_province'] = null; 
                    $row['area_city'] = null; 
                    
                    // 重复测栓
                    if ($this->conformPhone($row['phone']) && $this->conformQQ($row['qq']) ) {
                        $insert_data[] = $row;
                    } else {
                        $fault_data[] = $row;
                    }
 
                }
                //去重复
                $conf = array();
                $insert_data = array_values(arr_to_map($insert_data, 'qq'));
                $insert_data = array_values(arr_to_map($insert_data, 'phone'));

                $re  = M('customers_basic')->addAll($insert_data);
                $det = count($data) - count($insert_data);
                if ($re) {
                    // echo '导入成功';
                    if ($det==0) {
                        $this->success("导入成功");
                    } else {
                        $this->success("导入成功,有".$det."条重复数据未导入");
                    }
                    
                    /*if (!empty($fault_data)) {
                        echo '有'.count($fault_data)."条数据导入失败";
                        print_r($fault_data);
                    }*/
                } else {
                    /*var_dump($insert_data);
                    echo '导入失败'.M('customers_basic')->getLastsql();*/
                    $this->error("导入失败".M('customers_basic')->getLastSql());
                }
            } else {
                // echo '文件不存在';
                $this->error("文件不存在");
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
            $map['name'] = array('like', I('get.name')."%");
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

    }
}