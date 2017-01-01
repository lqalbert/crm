<?php
/**
* 通用的重排数组
*
* 特定的使用方式 
*
* 把子级排在父级的后面 
* 只支持二级
*/

/**
* 
*/
namespace Common\LIb;

class Arr 
{	
	/**
	* @param array
	* @param string 判断是不是第一级的元素索引
	* @param string|int|mix 判断是不是第一级的值
	* @param string 指向父级ID 的索引
	* @param string id 的索旨
	*/
	public static function reSort($list, $judgKey, $judgV, $parentKey='pid', $idKey='id'){
		$arr = array(); //新的数组
		
		foreach ($list as $value) {
			if ($value[$judgKey] == $judgV) {
				$arr[] = $value;
				$children = self::findValue($list, $value[$idKey], $parentKey);
				foreach ($children as $child) {
					$arr[] = $child;
				}
			}
		}

		return $arr;
	}

	//找到指定的数组
	public static function findValue($list, $pid, $judgKey){
		$arr = array();
		foreach ($list as $value) {
			if ($value[$judgKey] == $pid ) {
				$arr[]= $value;
			}
		}
		return $arr;
	} 
	
}
