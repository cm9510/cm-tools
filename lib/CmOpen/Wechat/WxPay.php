<?php
namespace Cm\CmOpen\Wechat;

use Cm\CmBase\Traits\CallStatic;
use Cm\CmTool\{HttpRequest, Tools};

final class WxPay
{
	use CallStatic;

	# 商户私钥路径 如：/public/pay/apiclient_key.pem
	public static $privateKeyPath = '';

	# 商户证书路径 如：/public/pay/apiclient_cert.pem
	public static $certPath = '';

	# 商户密钥
	public static $secret = '';

	# 商户API证书序列号
	public static $serialNo = '';

	# 网关
	public static $gateway = 'https://api.mch.weixin.qq.com';

	# 路径
	public static $url = '';

	# HTTP请求的方法
	public static $method = 'POST';

	# 应用ID 如：wxd678efh567hg6787
	public static $appId = '';

	# 直连商户号 如：1230000109
	public static $mchid = '';

	# 商品描述 如：Image形象店-深圳腾大-QQ公仔
	public static $description = '';

	# 交易结束时间
	public static $timeExpire = '';

	# 自定义数据
	public static $attach = '';

	# 通知地址 如：https://www.aaa.com/pay.php
	public static $notifyUrl = '';

	# 货币类型 如：CNY
	public static $currency = 'CNY';

	# 用户标识 如：oUpF8uMuAJO_M2pxb1Q9zNjWeS6o
	public static $openId = '';

	# 订单优惠标记 如：WXG
	public static $goodsTag = '';

	# 优惠功能
	public static $detail = [];

	# 场景信息
	public static $sceneInfo = [];

	/**
	 * jsapi trade(包括小程序)
	 * @param string $outTradeNo 商户订单号
	 * @param int $total 金额，单位/分
	 * @return array|false|mixed
	 */
	public function jsapiV3(string $outTradeNo, int $total)
	{
		$result = $this->transaction($outTradeNo, $total, WechatConst::PAY_TYPE_JSAPI);
		if (isset($result['prepay_id'])){
			$time = time();
			$nonceStr = Tools::getRandString(24);

			return [
				'appId'=> self::$appId,
				'timeStamp'=> $time,
				'nonceStr'=> $nonceStr,
				'package'=> 'prepay_id='.$result['prepay_id'],
				'signType'=>'RSA',
				'paySign'=> WechatUtil::getSign(self::$privateKeyPath, [
				  self::$appId,
				  $time,
				  $nonceStr,
				  'prepay_id='.$result['prepay_id']
				]),
			];
		}
		return isset($result['message']) ? '发生错误：'.$result['message'] : '支付失败！';
	}

	/**
	 * app trade
	 * @param string $outTradeNo 商户订单号
	 * @param int $total 金额，单位/分
	 * @return array|string
	 */
	public function appV3(string $outTradeNo, int $total)
	{
		$result = $this->transaction($outTradeNo, $total, WechatConst::PAY_TYPE_APP);
		if (isset($result['prepay_id'])){
			$time = time();
			$nonceStr = Tools::getRandString(24);

			return [
			  'appid'=> self::$appId,
			  'partnerid'=> self::$mchid,
			  'prepayid'=> $result['prepay_id'],
			  'timestamp'=> $time,
			  'noncestr'=> $nonceStr,
			  'sign'=> WechatUtil::getSign(self::$privateKeyPath, [
				self::$appId,
				$time,
				$nonceStr,
				$result['prepay_id']
			  ]),
			];
		}
		return isset($result['message']) ? '发生错误：'.$result['message'] : '支付失败！';
	}

	/**
	 * h5 trade
	 * @param string $outTradeNo 商户订单号
	 * @param int $total 金额，单位/分
	 * @return array|string
	 */
	public function h5V3(string $outTradeNo, int $total)
	{
		$result = $this->transaction($outTradeNo, $total, WechatConst::PAY_TYPE_H5);
		if (isset($result['h5_url'])){
			return $result['h5_url'];
		}
		return isset($result['message']) ? '发生错误：'.$result['message'] : '支付失败！';
	}

	/**
	 * native trade(自行使用QR code将code_url生成二维码)
	 * @param string $outTradeNo
	 * @param int $total
	 * @param bool $createImg
	 * @return mixed|string
	 */
	public function nativeV3(string $outTradeNo, int $total, bool $createImg = false)
	{
		$result = $this->transaction($outTradeNo, $total, WechatConst::PAY_TYPE_NATIVE);
		if (isset($result['code_url'])){
			return $result['code_url'];
		}
		return isset($result['message']) ? '发生错误：'.$result['message'] : '支付失败！';
	}

	/**
	 * 订单查询
	 * @param string $number 查询单号
	 * @param string $type 查询方式
	 * @return array
	 */
	public function tradeQuery(string $number, string $type = 'transaction'):array
	{
		$url = '';
		if($type == WechatConst::TRADE_QUERY_WAY_TRA){
			$url = '/v3/pay/transactions/id/'.$number;
		}elseif ($type == WechatConst::TRADE_QUERY_WAY_OTN){
			$url = '/v3/pay/transactions/out-trade-no/'.$number;
		}
		$result = HttpRequest::instance()->httpGet(self::$gateway.$url, ['mchid'=>self::$mchid]);
		$result = json_decode($result,true);
		if(isset($result['appid'])){
			unset($result['appid']);
			unset($result['mchid']);
			return $result;
		}
		return [];
	}

	/**
	 * 发起支付
	 * @param string $outTradeNo 商户订单号
	 * @param int $total 金额，单位/分
	 * @param string $payChannel 支付渠道
	 * @return false|mixed
	 */
	public function transaction(string $outTradeNo, int $total, string $payChannel = '')
	{
		$body = [
			'appid'=> self::$appId,
			'mchid'=> self::$mchid,
			'out_trade_no'=> $outTradeNo,
			'notify_url'=> self::$notifyUrl,
			'amount'=> ['currency'=>self::$currency, 'total'=>$total],
		];

		switch ($payChannel){
			case WechatConst::PAY_TYPE_JSAPI:
			case WechatConst::PAY_TYPE_APPLET:
				$body['payer'] = ['openid'=>self::$openId];
				break;
			case WechatConst::PAY_TYPE_NATIVE:
				$body['description'] = self::$description;
				break;
			case WechatConst::PAY_TYPE_H5:
				$body['scene_info'] = self::$sceneInfo;
				break;
//			case WechatConst::PAY_TYPE_APP:
//				break;
		}

		if(self::$description && !isset($body['description'])){
			$body['description'] = self::$description;
		}
		if(self::$timeExpire && !isset($body['time_expire'])){
			$body['time_expire'] = self::$timeExpire;
		}
		if(self::$attach && !isset($body['attach'])){
			$body['attach'] = self::$attach;
		}
		if(self::$goodsTag && !isset($body['goods_tag'])){
			$body['goods_tag'] = self::$goodsTag;
		}
		if(self::$detail && !isset($body['detail'])){
			$body['detail'] = self::$detail;
		}

		$header = [
		  'Content-Type: application/json;charset=UTF-8',
		  'Accept: application/json',
		  'User-Agent: '.$_SERVER['HTTP_USER_AGENT']
		];
		try {
			$header[] = WechatUtil::instance()->BuildPayRequestSign(
			  self::$method,
			  self::$url,
			  [
				'private_key_path'=>self::$privateKeyPath,
				'mch_id'=>self::$mchid,
				'serial_no'=>self::$serialNo
			  ],
			  json_encode($body,JSON_UNESCAPED_UNICODE)
			);

			$result = HttpRequest::instance()->httpPost(self::$gateway.self::$url, $body,[
			  'header'=> $header,
			  'format'=>'json'
			]);
			return json_decode($result, true);
		}catch (\Exception $e){
			return ['message'=>$e->getMessage()];
		}
	}

	/**
	 * 退款
	 * @param string $transactionId 交易单号
	 * @param int $totalFee 总金额
	 * @param int $refundFee 退款金额
	 * @param string $outRefundNo 退款单号
	 * @return array|string
	 */
	public function refundV2(string $transactionId, int $totalFee, int $refundFee, string $outRefundNo)
	{
		$params = [
		  'appid'=> self::$appId,
		  'mch_id'=> self::$mchid,
		  'nonce_str'=> Tools::getRandString(24),
		  'transaction_id'=> $transactionId,
		  'out_refund_no'=> $outRefundNo,
		  'total_fee'=> $totalFee,
		  'refund_fee'=> $refundFee
		];
		ksort($params);
		$str = http_build_query($params);
		$str .= '&key='.self::$secret;
		$str = strtoupper(md5($str));
		$params['sign'] = $str;

		try {
			$result = HttpRequest::instance()->httpPost(self::$gateway.'/secapi/pay/refund', $params, [
			  'cert'=> ['cert'=> self::$privateKeyPath, 'key'=> self::$certPath],
			  'format'=>'xml'
			]);
			if($result){
				$result = simplexml_load_string($result,'SimpleXMLElement', LIBXML_NOCDATA);
				if(isset($result->return_code) && $result->return_code == 'SUCCESS' && $result->return_msg == 'OK'
				  && isset($result->result_code) && $result->result_code == 'SUCCESS'){
					return [
					  'refund_no'=> $result->out_refund_no,
					  'refund_fee'=> bcdiv($result->refund_fee, 100,2)
					];
				}else{
					throw new \Exception('微信支付V2退款失败：'.$result->return_msg);
				}
			}else{
				throw new \Exception('微信支付V2退款请求失败！');
			}
		}catch (\Exception $e){
			return '发生错误：'.$e->getMessage();
		}
	}

}