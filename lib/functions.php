<?php
/**
 * function helper
 */

if(!function_exists('arr2xml')){
	/**
	 * array to xml
	 * @param $data
	 * @return string
	 */
	function arr2xml($data):string {
		if($data){
			$xml = '<xml>';
			foreach ($data as $k => $v) {
				$xml .= '<'.$k.'>'.$v.'</'.$k.'>';
			}
			$xml .= '</xml>';
			return $xml;
		}
		return '';
	}
}
if(!function_exists('cartesian')){
	/**
	 * 笛卡尔积(存在bug)
	 * @param $arr
	 * @return array
	 */
	function cartesian(array $arr):array {
		$first = array_filter($arr[0]);
		for ($i=1; $i < count($arr); $i++) {
			$temp = [];
			foreach($first as $v1){
				foreach ($arr[$i] as  $v2) {
					$temp[] = str_replace('/','_', $v1).'/'.str_replace('/','_', $v2);
				}
			}
			$first = $temp;
		}
		return $first;
	}
}
if(!function_exists('query2arr')){
	/**
	 * query string to array
	 * @param string $string
	 * @return array
	 */
	function query2arr(string $string):array {
		if(trim($string)){
			$arr = explode('&',$string);
			array_map(function ($v) use (&$res) {
				$tmp = explode('=',$v);
				$res[$tmp[0]] = $tmp[1];
			},$arr);
			return $res;
		}
		return [];
	}
}
if(!function_exists('rand_number')){
	/**
	 * 获取随机位数数字
	 * @param  integer $len 长度
	 * @return string
	 */
	function rand_number(int $len = 6):string {
		return substr(str_shuffle(str_repeat('0123456789', $len)), 0, $len);
	}
}
if(!function_exists('get_rand_string')){
	/**
	 * 获取随机字符串
	 * @param int $length
	 * @return false|string
	 */
	function get_rand_string(int $length = 8){
		$char = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$char = str_shuffle(str_shuffle($char));
		$char = substr($char, rand(0, strlen($char)-$length-1), $length);
		return $char;
	}
}
if(!function_exists('header2arr')){
	/**
	 * header头转数组
	 * @param string $header
	 * @return array
	 */
	function header2arr(string $header = ''):array {
		$header = explode("\r\n", $header);
		$arr = [];
		foreach ($header as $v) {
			$t = explode(': ', $v);
			$arr[strtolower($t[0])] = isset($t[1]) ? $t[1] : '';
		}
		return $arr;
	}
}
if(!function_exists('create_order_no')) {
	/**
	 * 创建28位订单号
	 * @return string
	 */
	function create_order_no():string {
		return date('YmdHis', $_SERVER['REQUEST_TIME']).rand(1000,9999).str_shuffle('1234567890');
	}
}
if(!function_exists('scrypt')){
	/**
	 * 加密解密字符串
	 * @param string $string
	 * @param string $key
	 * @param bool $de
	 * @return false|string
	 */
	function scrypt(string $string, string $key = 'cm9510tools', bool $de = false){
		if($de){
			$content = openssl_decrypt($string, 'DES-ECB', $key, 0);
		}else{
			$content = openssl_encrypt($string,'DES-ECB', $key, 0);
		}
		return $content;
	}
}
if(!function_exists('img_base64')){
	/**
	 * base64编码
	 * @param string $path
	 * @return false|string
	 */
	function img_base64(string $path)
	{
		# 对path进行判断，如果是本地文件就二进制读取并base64编码，如果是url,则返回
		if (substr($path,0,strlen("http")) === "http"){
			return $path;
		}
		if($fp = fopen($path, "rb", 0)) {
			$binary = fread($fp, filesize($path)); // 文件读取
			fclose($fp);
			return base64_encode($binary); // 转码
		}
		return false;
	}

}
