<?php
namespace Cm\CmOpen\Wechat;

use Cm\CmBase\Traits\Singleton;
use Cm\CmTool\HttpRequest;
use Cm\CmTool\Tools;

final class WechatUtil
{
	use Singleton;

	const AUTH_TAG_LENGTH_BYTE = 16;


	/**
	 * 组装微信支付 Authorization
	 * @param string $method http请求
	 * @param string $url 请求url
	 * @param array $mchInfo 商户信息
	 * @param string $body 请求参数
	 * @return string
	 */
	public function buildPayRequestSign(string $method, string $url, array $mchInfo, string $body = ''):string
	{
		$time = $_SERVER['REQUEST_TIME'] ?? time();
		$nonceStr = Tools::getRandString(24);
		$sign = self::getSign($mchInfo['private_key_path'],[$method, $url, $time, $nonceStr, $body]);
		$token = sprintf(
		  'mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
		  $mchInfo['mch_id'],
		  $nonceStr,
		  $time,
		  $mchInfo['serial_no'],
		  $sign
		);
		unset($time,$nonceStr,$authorize,$privateKey,$sign);
		return 'Authorization: WECHATPAY2-SHA256-RSA2048 '.$token;
	}

	/**
	 * 生成签名
	 * @param string $priKeyPath 私钥路径
	 * @param array $data 待签名数据
	 * @return string
	 */
	public static function getSign(string $priKeyPath, array $data):string
	{
		$message = '';
		foreach ($data as $v) {
			$message .= $v."\n";
		}
		$privateKey = file_get_contents($priKeyPath);
		openssl_sign($message, $sign, $privateKey, 'sha256WithRSAEncryption');
		return base64_encode($sign);
	}

	/**
	 * 解密报文
	 * @param $aesKey
	 * @param $associatedData
	 * @param $nonceStr
	 * @param $cipherText
	 * @return false|string
	 */
	public function decryptCert($aesKey, $associatedData, $nonceStr, $cipherText)
	{
		if(strlen($aesKey) == 32){
			if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', openssl_get_cipher_methods())) {
				$cipherText = base64_decode($cipherText);
				$ctx = substr($cipherText, 0, -self::AUTH_TAG_LENGTH_BYTE);
				$authTag = substr($cipherText, -self::AUTH_TAG_LENGTH_BYTE);
				return openssl_decrypt($ctx, 'aes-256-gcm', $aesKey, OPENSSL_RAW_DATA, $nonceStr,
				  $authTag, $associatedData);
			}
			return '';
		}
		return '';
	}


	/**
	 * 单例出口
	 * @return static
	 */
	public static function instance():self
	{
		if(self::$instance && self::$instance instanceof self){
			return self::$instance;
		}
		self::$instance = new self;
		return  self::$instance;
	}
}
