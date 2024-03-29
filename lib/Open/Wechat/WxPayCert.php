<?php
namespace Cm\Open\Wechat;

use Cm\Tool\Tools;

class WxPayCert
{
	public $apiV3 = '';
	public $privateKeyPath = '';
	public $mchId = '';
	public $serialNo = '';

	/**
	 * 下载微信平台证书
	 * @param string $storePath
	 * @return false|string
	 */
	public function downloadCert(string $storePath)
	{
		$header = [
		  'Content-Type: application/json;charset=UTF-8',
		  'Accept: application/json',
		  'User-Agent: '.$_SERVER['HTTP_USER_AGENT']
		];
		$header[] = WxPayUtil::instance()->buildPayRequestSign('GET','/v3/certificates', [
			'private_key_path'=> $this->privateKeyPath,
			'mch_id'=>$this->mchId,
			'serial_no'=>$this->serialNo
		]);
		$result = Tools::httpGet('https://api.mch.weixin.qq.com/v3/certificates', [], ['header'=>$header]);
		$result = json_decode($result, true);
		if(isset($result['data'])){
			$certData = end($result['data']);
			$certCont = WxPayUtil::instance()->decryptCert(
			  $this->apiV3,
			  $certData['encrypt_certificate']['associated_data'],
			  $certData['encrypt_certificate']['nonce'],
			  $certData['encrypt_certificate']['ciphertext']
			);
			file_put_contents($storePath.$certData['serial_no'].'.txt',$certCont,LOCK_EX);
			unset($certCont);
			return $certData['serial_no'].'.txt';
		}else{
			return false;
		}
	}


	public function __construct(array $config)
	{
		$this->apiV3 = $config['api_v3'] ?? '';
		$this->privateKeyPath = $config['private_key_path'] ?? '';
		$this->mchId = $config['mch_id'] ?? '';
		$this->serialNo = $config['serial_no'] ?? '';
	}

	public function __destruct()
	{
		$this->apiV3 = '';
		$this->privateKeyPath = '';
		$this->mchId = '';
		$this->serialNo = '';
	}
}