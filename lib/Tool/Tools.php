<?php
namespace Cm\Tool;

use Cm\Base\Traits\Singleton;

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
     * @param int $length
     * @param bool $sc
     * @return false|string
     */
	public static function getRandString(int $length = 8, bool $sc = false)
	{
	    $char = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    if ($sc) $char .= '*&/=+\\-@!$)~';
		$char = str_shuffle($char);
        return substr($char, rand(0, strlen($char)-$length-1), $length);
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
	 * 创建24位订单号
	 * @return string
	 */
	public static function createOrderNo():string
	{
		return date('YmdHis').rand(1000,9999).substr(str_shuffle('1234567890'),0,6);
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
	    $fun = $de ? 'openssl_decrypt' : 'openssl_encrypt';
	    return $fun($string, 'DES-ECB', $key, 0);
	}

	/**
	 * 图片base64编码
	 * @param string $path
	 * @return false|string
	 */
	public static function imgBase64(string $path)
	{
		# 对path进行判断，如果是本地文件就二进制读取并base64编码，如果是url,则返回
		if (substr($path,0,strlen("http")) === "http") return $path;
		if($fp = fopen($path, "rb", 0)) {
			$binary = fread($fp, filesize($path)); // 文件读取
			fclose($fp);
			return base64_encode($binary); // 转码
		}
		return false;
	}

	/**
	 * 写日志
	 * @param string $storePath 保存路径
	 * @param string $msg 内容
	 * @param bool $oneFile 是否写成一个文件
	 * @return bool
	 */
	public static function log(string $storePath, string $msg, bool $oneFile = false):bool
	{
		if (empty(trim($msg))) return false;

		$logFile = $oneFile ? 'cm_logs_all.txt' : 'cm_logs_'.date('Y-m-d').'.txt';
		$logTime = $oneFile ? date('Y-m-d/H:i:s') : date('H:i:s');
		if($storePath && is_dir($storePath)){
			$msg = '['.date_default_timezone_get().' > '.$logTime.'] '.$msg."\n";
			file_put_contents($storePath.$logFile, $msg, FILE_APPEND);
			unset($logFile,$logTime,$msg);
			return true;
		}
		unset($logFile,$logTime);
		return false;
	}

	/**
	 * xml转array
	 * @param string $xml
	 * @param int $xmlType 1xml字符串，2xml文件
	 * @return false|mixed
	 */
	public static function xmlToArray(string $xml, int $xmlType = 1)
	{
		if (empty($xml)) return false;
		switch ($xmlType){
			case 1:
				$result = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
				break;
			case 2:
				$result = simplexml_load_file($xml);
				break;
			default:
				return  false;
		}
		return json_decode(json_encode($result), true);
	}

}