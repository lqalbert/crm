<?php
namespace Cli\Controller;
use Think\Controller;

use Home\Model\CustomerContactModel;

class CustoUController extends Controller {
   

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
        $offset=I('get.or','asc'); // desc
        $iTM =  M('import_table');
         
        $cus =  $iTM->where(array('is_fault'=>1))->limit("0,20000")->order("id $offset")->select();
        
        $m = M();
        foreach ($cus as $key => $value) {

            if (!empty($value['phone']) &&  !empty($value['name']) &&  mb_strpos($value['name'], '简称')=== false) {
                $user_id = M('user_info')->where(array('realname'=>$value['ywy']))->getField('user_id');
                $sales_id = M('user_info')->where(array('realname'=>$value['sales']))->getField('user_id');

                if (empty($user_id) && empty($sales_id)) {
                    echo $value['name'];
                    echo "\n";
                    continue;
                }
                if (!$user_id) {
                    $user_id = $sales_id;
                }

                if (!$sales_id) {
                    $sales_id = $user_id;
                }

                
                $basicData = array(
                    'name'=> $value['name'],
                    'type'=> strtoupper(mb_substr($value['ctype'], 0,1)) ,
                    'area_province'=>null,
                    'area_city'=>null,
                    'user_id'=>$user_id,
                    'salesman_id'=>$sales_id,
                    'service_time'=>time(),
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
                         echo $value['phone'],  "失败";
                         echo "\n";
                    }
                    $m->startTrans();
                    $cus_id = M('customers_basic')->add();
                    
                    if (!$cus_id) {
                        $m->rollback();
                        // $this->error("Customer".  M('customers_basic')->getError());
                        echo $value['phone'], "失败.customers_basic";
                        echo "\n";
                    } else {
                        $re['cus_id'] = $cus_id;
                        $id = $cc->data($re)->add();
                        if (!$id) {
                            $m->rollback();
                            echo $value['phone'], "失败.cc";
                            echo "\n";
                            // $this->error(D('CustomerContact')->getError());
                        }  else {
                            $m->commit();

                            echo $value['phone'], "导入成功";
                            echo "\n";
                            $iTM->where(array('id'=>$value['id']))->data(array('is_fault'=>0))->save(); //->delete();
                        }
                    }
                } else {
                    
                    echo $value['name'];
                    echo $contactData['phone'];
                    echo $cc->getError();
                    echo "\n";
                    $iTM->where(array('id'=>$value['id']))->delete();
                }
                $cc = null;
            }


            

            
        }
    }
}