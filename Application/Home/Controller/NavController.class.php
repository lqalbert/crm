<?php
namespace Home\Controller;

class NavController extends CommonController {

	public function index() {
		$nav = $this->setMenu(); //C('MENU');
		$this->transNavUrl($nav);
		//$nav[0]['children'][5]['href']='http://up.riign.cn';

		$ename = $this->getRoleEname();
		$funcName = $ename."Menu";
		if (method_exists($this, $funcName)) {
			$addMenu = call_user_func(array($this, $funcName));
			$nav = array_merge($nav, $addMenu);
		}

		$this->assign("nav", $nav);
		$this->display();
		/*echo 'var navs = '.json_encode($nav).';';
		exit();*/
		
	}

	private function getUsers($group_id){
		return D('User')->getGroupEmployee($group_id, 'id,realname');
	}

	private function setUserMenu($group_id){
		$users = $this->getUsers($group_id);
		$re = array();
		foreach ($users as $key => $value) {
			$re[] = array(
						"href"=> U('DepartmentCustomer/index', array('id'=>$value['id'])),
						"icon"=>"",
						'spread'=>false,
						"title" => $value['realname'],
					);
		}
		return $re;
	}

	/**
	* 返回结果数组
	* href
	* icon
	* spread
	* title
	* children
	*/

	private function departmentMasterMenu(){
		$depart_id = session('account')['userInfo']['department_id'];
		$groupMenu = array();
		if ($depart_id!=0) {
			$groups = D('Group')->getAllGoups($depart_id, 'id,name');
			foreach ($groups as $key => $value) {
				$groupMenu[] = array(
							"href"=>"javascript:;",
							"icon"=>"",
							'spread'=>false,
							"title" => "小组:".$value['name'],
							"children" => $this->setUserMenu($value['id'])
						);

			}
		} 
		return $groupMenu;
	}


	/**
	* 返回结果数组
	* href
	* icon
	* spread
	* title
	* children
	*/
	private function captainMenu(){
		$group_id = session('account')['userInfo']['group_id'];
		if ($group_id!=0) {
			$group_name = D('Group')->where(array('id'=>$group_id))->getField("name");
			return array(
				"href"=>"javascript:;",
				"icon"=>"",
				'spread'=>false,
				"title" => $group_name,
				"children" => $this->setUserMenu($group_id)
			);
		} else {
			return [];
		}
	}



	/**
	* 设置菜单
	*
	*/
	public function setMenu(){
		
		$m = M('menu_basic');
		$menu = $m->where(array('pid'=>0))->select();
		if (!$_SESSION[C('ADMIN_AUTH_KEY')]) {
			//跟据权限加载菜单
			$sql = "select role_id from rbac_role_user where user_id = ". session('uid');
			$sql = "select node_id from rbac_access where role_id in ($sql)";
			$sql = "select * from menu_basic where node_id in ($sql)";
			$result = $m->query($sql);
			$menu = arr_to_map($menu, 'id');
			foreach ($result as $key => $value) {
				if (isset($menu[$value['pid']])) {
					$menu[$value['pid']]['children'][] = $value;
				}
			}
			$new_menu = array();
			foreach ($menu as $value) {
				if (count($value['children'])>0) {
					$new_menu[] = $value;
				}
			}

			$menu = $new_menu;
		} else {
			foreach ($menu as $key => $me) {
				$menu[$key]['spread'] = false;
				$menu[$key]['children'] = $m->where(array('pid'=>$me['id']))->select();
			}
		}
		
		return $menu;
	}



	private function transNavUrl(&$nav) {
		foreach ($nav as $key => $value) {
			
			if (isset($value['href'])) {
				$nav[$key]['href'] = $this->makeUrl($value['href']);
			}

			if (isset($value['children'])) {
				$this->transNavUrl($nav[$key]['children']);
			} 
		}
	}

	private function makeUrl($url) {
        
		return  strpos($url, "javascript:;") === false ? U($url) : $url ;
	}

	public function test() {
		// $nav = C('MENU');
		$nav = [[
          "date"=> '2016-05-01',
          "name"=> '王小虎1',
          "address"=> '上1海市普陀区金沙江路 1518 弄'
        ]];
        $this->ajaxReturn( $nav );
	}
	
}