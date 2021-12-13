<?php
namespace Cm\CmTool;

use Cm\CmBase\Traits\Singleton;

final class HttpRequest
{
	use Singleton;

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


	/**
	 * get请求
	 * @param string $url
	 * @param array $data
	 * @param array $params
	 * @return bool|string
	 */
	public function httpGet(string $url, array $data = [], array $params = [])
	{
		$url .= empty($data) ? '' : '?' . http_build_query($data);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER , false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if (1 == strpos('$'.$url, 'https://')) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}
		if(isset($params['cert']) && $params['cert']){
			curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
			curl_setopt($ch, CURLOPT_SSLCERT, $params['cert']['cert']);
			curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
			curl_setopt($ch, CURLOPT_SSLKEY, $params['cert']['key']);

		}
		if(isset($params['header']) && $params['header']){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $params['header']);
			curl_setopt($ch, CURLOPT_HEADER, false);//返回response头部信息
		}
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	/**
	 * post请求
	 * @param string $url
	 * @param array $data
	 * @param array|string[] $params
	 * @return bool|string
	 * @throws \Exception
	 */
	public function httpPost(string $url, array $data, array $params = ['format'=>'form_field'])
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		if (1 == strpos('$'.$url, 'https://')) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		if(isset($params['cert']) && $params['cert']){
			curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
			curl_setopt($ch, CURLOPT_SSLCERT, $params['cert']['cert']);
			curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
			curl_setopt($ch, CURLOPT_SSLKEY, $params['cert']['key']);

		}
		if(isset($params['header']) && $params['header']){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $params['header']);
			curl_setopt($ch, CURLOPT_HEADER, true);//返回response头部信息
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);//返回response头部信息
		}
		if($data){
			switch ($params['format']){
				case 'form_field': $data = http_build_query($data); break;
				case 'json': $data = json_encode($data); break;
				case 'xml': $data = Tools::arr2xml($data); break;
				default:
					throw new \Exception('data format is not valid.');
			}
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
}