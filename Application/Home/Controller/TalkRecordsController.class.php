<?php
namespace Home\Controller;

class TalkRecordsController extends CommonController{

    protected $table= "customers_tracks";


    public function index(){

    }

    public function setQeuryCondition(){
        $id = I('get.id');
        if ($id) {
            $this->M->where(array('cus_id'=>$id));
        }

        //,all:1
        if (I("get.all")) {
            # code...
            // $this->M->where(array('status'=>1));
        } else {
            $this->M->where(array('status'=>1));
        }

       
    }



    public function uploadRecord(){

        $path = $this->upload();
        $data = array(
            'cus_id' => I('post.cus_id'),
            'creator_id' => session('uid'),
            'path' => $path,
        );
        $data['id'] = M('customers_tracks')->add($data);
        $this->ajaxReturn($data);
    }

    public function delete(){
        $data = array('status'=>-1);
        $re = $this->M->data($data)->where(array("id"=>I("post.id")))->save();
        if ($re !== false) {
            $this->success(L('DELETE_SUCCESS'));
        } else {
            $this->error(L('DELETE_ERROR').$this->M->getError());
        }
    }

    public function uploadBigFile(){


        $path = $this->upload();
        if (I("post.index") == 0) {
            // rename("/tmp/tmp_file.txt", "/home/user/login/docs/my_file.txt");
            $extension = pathinfo(I("post.filename"), PATHINFO_EXTENSION );
            //realpath 在 win 与 linux 下结果有点不一至
            //win 下
            //  /tmp/tmp_file. 返回 /tmp/tmp_file
            // linux下
            // /tmp/tmp_file. 返回 /tmp/tmp_file.
            $fullPath = realpath(DIRROOT. $path);
            rename($fullPath, trim($fullPath,".").".".$extension);

            $data = array(
                'cus_id' => I('post.cus_id'),
                'creator_id' => session('uid'),
                'path' => trim($path,".").".".$extension,
                // 'path' => $path 
            );
            $data['id'] = M('customers_tracks')->add($data);
            $this->ajaxReturn($data);
        } else {
            // var_dump(DIRROOT);
            $filePath = I("post.path");
            $partContent = file_get_contents(realpath(DIRROOT. $path));
            // var_dump(realpath(DIRROOT. $path));
            if ($partContent) {

                $size = file_put_contents(realpath(DIRROOT.$filePath), $partContent, FILE_APPEND);
                
                if ($size) {
                    unlink(realpath(DIRROOT. $path));
                    $re = array(
                        'path' => $filePath
                    );
                    
                    $this->ajaxReturn($re);
                } else {
                    $this->error('出错了');
                }
            } else {
                $this->error('不能读');
            }

            
        }


        
    }


    private function upload(){
        $folder = "records";

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     2097152 ;// 设置附件上传大小3M
        // $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     './Upload/'.$folder.'/'; // 设置附件上传根目录
        $upload->autoSub   =     true;
        $info   = $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
            exit();
            // return $upload->getError();
        }else{// 上传成功
            $path = substr($upload->rootPath, 1 ).$info['file']['savepath'].$info['file']['savename'];
            return $path;
        }
    }
}