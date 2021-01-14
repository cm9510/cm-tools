<?php
namespace Cm\CmTool;

use Cm\CmBase\Traits\Singleton;

class Tools
{
	use Singleton;

	/**
	 * array to xml
	 * @param $data
	 * @return string
	 */
	public static function arr2xml($data):string
	{
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

	/**
	 * 笛卡尔积(存在bug)
	 * @param $arr
	 * @return array
	 */
	public static function cartesian(array $arr):array
	{
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

	/**
	 * query string to array
	 * @param string $string
	 * @return array
	 */
	public static function query2arr(string $string):array
	{
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

	/**
	 * 获取随机位数数字
	 * @param  integer $len 长度
	 * @return string
	 */
	public static function randNumber(int $len = 6):string
	{
		return substr(str_shuffle(str_repeat('0123456789', $len)), 0, $len);
	}

	/**
	 * 获取随机字符串
	 * @param int $length 长度
	 * @return false|string
	 */
	public static function getRandString(int $length = 8)
	{
		$char = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$char = str_shuffle(str_shuffle($char));
		$char = substr($char, rand(0, strlen($char)-$length-1), $length);
		return $char;
	}

	/**
	 * header头转数组
	 * @param string $header
	 * @return array
	 */
	public static function header2arr(string $header = ''):array
	{
		$header = explode("\r\n", $header);
		$arr = [];
		foreach ($header as $v) {
			$t = explode(': ', $v);
			$arr[strtolower($t[0])] = isset($t[1]) ? $t[1] : '';
		}
		return $arr;
	}

	/**
	 * 创建28位订单号
	 * @return string
	 */
	public static function createOrderNo():string
	{
		return date('YmdHis', $_SERVER['REQUEST_TIME']).rand(1000,9999).str_shuffle('1234567890');
	}

	/**
	 * 加密解密字符串
	 * @param string $string 待加解密字符串
	 * @param string $key 密钥
	 * @param bool $de
	 * @return false|string
	 */
	public static function scrypt(string $string, string $key = 'cm9510tools', bool $de = false)
	{
		if($de){
			$content = openssl_decrypt($string, 'DES-ECB', $key, 0);
		}else{
			$content = openssl_encrypt($string,'DES-ECB', $key, 0);
		}
		return $content;
	}

	/**
	 * 图片base64编码
	 * @param string $path
	 * @return false|string
	 */
	public static function imgBase64(string $path)
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