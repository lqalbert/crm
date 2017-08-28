<?php
namespace Home\Controller;

use Home\Model\DepartmentModel;
use Home\Model\RoleModel;

class DepartSpreadCustomerController extends CommonController {

    protected $table = "Customer";

    protected $pageSize = 15;

    public function index(){

        $this->assign('searchGroup', array(array("name"=>"个人", "value"=>"user_id"), array("name"=>"小组", "value"=>"to_gid"), array("name"=>"部门", "value"=>"spread_id")));
        $this->assign("departments", D("Department")->getGoodSalesDepartments());
        $this->display();
    }


    //这样算 没有分配到的人/小组/部门 就没统计到
    // 个人的 这样改
    // select user_info.user_id, user_info.realname, b.c from user_info 
    // left join (select count(cb.id) from c...b where spread_id<>0 group by salesman_id) as b

    // 小组的 这样改
    // select gb.user_id, gb.name, b.c from group_basic 
    // left join (select count(cb.id) from c...b where spread_id<>0 group by to_gid) as b

    // 部门的 这样改
    // select db.user_id, db.name, b.c from department_basic 
    // left join (select count(cb.id) from c...b where spread_id<>0 group by depart_id) as b order by c
    public function setQeuryCondition(){

        $searchGroup = I("get.searchgroup");
         // $this->M->where( array("spread_id"=>array("NEQ", 0)));
        switch ($searchGroup) {
            case 'user_id':
                $roleM = D("Role");
                $roleids = array(
                    $roleM->getIdByEname(RoleModel::CAPTAIN),
                    $roleM->getIdByEname(RoleModel::STAFF),
                    $roleM->getIdByEname(RoleModel::DEPARTMENTMASTER)
                );
                $this->sql = "select ### from user_info"
                      ." left join (select count(*) as c, salesman_id from customers_basic where spread_id<>0 group by salesman_id) as b on user_info.user_id=b.salesman_id"
                      ." inner join rbac_user as ru on user_info.user_id = ru.id "
                      ." where ru.status=1 and user_info.role_id in (".implode(',',$roleids).")";


                $this->fields = "user_info.user_id as id, user_info.realname as name, b.c ";


                if (I("get.group_id")) {
                    $this->sql.=" and user_info.group_id=".I("get.group_id")." ";
                    return ;
                }
               
                if (I("get.depart_id")) {
                    $this->sql .=" and user_info.department_id=".I("get.depart_id")." ";
                    
                }


                
                
                break;
            case 'to_gid':
                $this->sql = "select ### from group_basic  "
                            ." left join (select count(*) as c, to_gid from  customers_basic where spread_id<>0 group by to_gid) as b on group_basic.id=b.to_gid"
                            ." where group_basic.status=1 ";
                $this->fields = "group_basic.id, group_basic.name, b.c";
                

                if (I("get.group_id")) {
                    $this->sql.=" and group_basic.id=".I("get.group_id")." ";
                    return ;
                }


                if (I("get.depart_id")) {
                    $this->sql.=" and group_basic.department_id=".I("get.depart_id")." ";
                }
                break;
            case 'spread_id':
                $this->sql = "select ### from department_basic "
                            ." left join (select count(*) as c, depart_id from customers_basic where spread_id<>0 group by depart_id) as b on department_basic.id=b.depart_id"
                            ." where department_basic.status=1 and department_basic.type=" .DepartmentModel::SALES_DEPARTMENT;
                $this->fields="department_basic.id, department_basic.name, b.c";
                /*$this->M->join("department_basic as db on customers_basic.depart_id=db.id")
                        ->group('depart_id')
                        ->field("count(customers_basic.id) as c, db.name");*/
                if (I("get.depart_id")) {
                    $this->sql.=" and department_basic.id=".I("get.depart_id")." ";
                }
                break;
            
            default:
                $this->sql = "select ### from department_basic "
                            ." left join (select count(*) as c, depart_id from customers_basic where spread_id<>0 group by depart_id) as b on department_basic.id=b.depart_id"
                            ." where department_basic.status=1 and department_basic.type=" .DepartmentModel::SALES_DEPARTMENT;
                $this->fields="department_basic.id, department_basic.name, b.c";
                break;
        }
    }

    private function getCount(){
        $sql = str_replace('###', "count(*) as x", $this->sql);
        $re = M()->query($sql);

        return $re[0]['x'];
    }

    private function getReList(){
        $sql = str_replace('###', $this->fields, $this->sql);
        $re = M()->query($sql." order by c desc limit ". $this->getOffset() .",".$this->pageSize); //分页
        return $re;
    }

    private function getOffset(){
        return (I('get.p',1)-1) * $this->pageSize;
    }

    private function fixName(&$list){
        $searchGroup = I("get.searchgroup");
        switch ($searchGroup) {
            case 'user_id':
                foreach ($list as &$value) {
                    $group_id = M("user_info")->where(array('user_id'=>$value['id']))->getField('group_id');
                    $value['name'] = D("Group")->where(array("id"=>$group_id))->getField("name")."-".$value['name'];
                    $department_id = M("user_info")->where(array('user_id'=>$value['id']))->getField('department_id');
                    $value['name'] = D("Department")->where(array("id"=>$department_id))->getField("name")."-".$value['name'];
                }
                break;
            case 'to_gid':
                foreach ($list as &$value) {
                    
                    $department_id = D("Group")->where(array('id'=>$value['id']))->getField('department_id');
                    $value['name'] = D("Department")->where(array("id"=>$department_id))->getField("name")."-".$value['name'];
                }
                
            default:
                
                break;
        }
    }

    public function getGroups(){
        $this->ajaxReturn(D("Group")->getAllGoups(I('get.id'),"id,name"));
    }


    protected function _getList(){

        $this->setQeuryCondition();

        $count = $this->getCount();
        $list = $this->getReList();

        
        $this->fixName($list);
        // $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->order('c desc')->select();
        // var_dump($this->M->getlastsql());
        $result = array('list'=>$list, 'count'=>$count);
        
        return $result;
    }
}