<?php
namespace Cli\Controller;
use Think\Controller;

use Home\Model\CustomerContactModel;

class ImportCusController extends Controller {
   

    private $date = '';

    private $m = '';


    private function fixPhone($phone){
        if (strpos($phone, chr(32)) !== false ) {
            return str_replace(chr(32), '', $phone);
        } else {
            return $phone;
        }
    }
    


    public function index(){
        
        $users = M('user_info')->where(array('role_id'=>array(array('eq',5),array('eq',6),array('eq',8), 'or')))->select();

        
        foreach ($users as $user) {
            $realname = $user['realname'];
            $group_id = $user['group_id'];

            
            if ($user['role_id']!=5) {
                $data = M('import_table2')->where(array('group_id'=>$group_id, 'sales'=>$realname))->select();
            } else {
                $data = M('import_table2')->where(array('group_id'=>0, 'sales'=>$realname))->select();
            }
            $m = M();
            
            foreach ($data as $value) {
                //手机号不为空
                if (!empty($value['phone']) &&  !empty($value['name']) &&  mb_strpos($value['name'], '简称')=== false) {
                    
                    if ($value['user']==$value['sales']) {
                        $user_id = $user['user_id'];
                    } else {
                        $user_id = M('user_info')->where(array('realname'=>$value['user']))->getField('user_id');
                        if (!$user_id) {
                            $user_id = $user['user_id'];
                        }
                    }

                    $basicData = array(
                        'name'=> $value['name'],
                        'type'=> strtoupper(mb_substr($value['ctype'], 0,1)) ,
                        'area_province'=>null,
                        'area_city'=>null,
                        'user_id'=>$user_id,
                        'salesman_id'=>$user['user_id'],
                        /*'help_group_id'=>$group_id,
                        'help_salesman'=> $value['F'],
                        'help_transfer'=> $value['G'],
                        'help_user'    => $value['H'],*/
                        'old_encode'   => $value['oldcode'],
                        'created_at'   => null,
                    );

                    
                    

                    $contactData = array(
                        'cus_id'  =>  0,
                        'phone'   =>  $this->fixPhone($value['phone']),
                        'qq'      =>  empty($value['qq']) ? null : $value['qq'],
                        'is_main' => 1
                    );
                    $cc = new CustomerContactModel();
                    $re = $cc->create($contactData);
                   
                    if($re){
                        $basicData =  M('customers_basic')->create($basicData);
                        if (!$basicData) {
                             echo $value['name'], $realname, "失败";
                             echo "\n";
                        }
                        $m->startTrans();
                        $cus_id = M('customers_basic')->add();
                        
                        if (!$cus_id) {
                            $m->rollback();
                            // $this->error("Customer".  M('customers_basic')->getError());
                            echo $value['name'], $realname, "失败.customers_basic";
                            echo "\n";
                        } else {
                            $re['cus_id'] = $cus_id;
                            $id = $cc->data($re)->add();
                            if (!$id) {
                                $m->rollback();
                                echo $value['name'], $realname, "失败.cc";
                                echo "\n";
                                // $this->error(D('CustomerContact')->getError());
                            }  else {
                                $m->commit();
                                echo $value['name'], $realname, "导入成功";
                                echo "\n";
                                M('import_table2')->where(array('id'=>$value['id']))->delete();
                            }
                        }
                    } else {
                        
                        echo $value['name'];
                        echo $contactData['phone'];
                        echo $cc->getError();
                        echo "\n";
                    }
                    $cc = null;
                }
            }
        }
    }
}