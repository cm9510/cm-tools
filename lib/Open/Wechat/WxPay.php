<?php
namespace Cm\Open\Wechat;

use Cm\Tool\Tools;

/**
 * 微信支付
 */
final class WxPay
{
	# 商户私钥路径 如：/public/pay/apiclient_key.pem
	public $privateKeyPath = '';

	# 商户证书路径 如：/public/pay/apiclient_cert.pem
	public $certPath = '';

	# 商户密钥(API_V3_key)
	public $secretV3 = '';

	# 平台证书存放目录
	public $platformCertDir = '';

	# 商户API证书序列号
	public $serialNo = '';

	# 网关
	public $gateway = 'https://api.mch.weixin.qq.com';

	# 路径
	public $url = '';

	# 密钥
	public $secret = '';

	# HTTP请求的方法
	public $method = 'POST';

	# 应用ID 如：wxd678efh567hg6787
	private $appId = '';

	# 直连商户号 如：1230000109
	private $mchId = '';

	# 商品描述 如：Image形象店-深圳腾大-QQ公仔
	public $description = '';

	# 交易结束时间
	public $timeExpire = '';

	# 自定义数据
	public $attach = '';

	# 通知地址 如：https://www.aaa.com/pay.php
	public $notifyUrl = '';

	# 货币类型 如：CNY
	public $currency = 'CNY';

	# 用户标识 如：oUpF8uMuAJO_M2pxb1Q9zNjWeS6o
	private $openId = '';

	# 订单优惠标记 如：WXG
	public $goodsTag = '';

	# 优惠功能
	public $detail = [];

	# 场景信息
	public $sceneInfo = [];
	
	public function __construct(string $appId, string $mchId)
    {
        $this->appId = $appId;
        $this->mchId = $mchId;
    }

    /**
     * jsapi trade(公众号、小程序支付)
     * @param string $outTradeNo 商户订单号
     * @param int $total 金额，单位/分
     * @param string $openId 用户openid
     * @return array|string
     */
	public function jsapiV3(string $outTradeNo, int $total, string $openId)
	{
	    $this->url = $this->url ?: '/v3/pay/transactions/jsapi';
	    $this->openId = $openId;
		$result = $this->transaction($outTradeNo, $total,WechatConst::PAY_TYPE_JSAPI);
		if (isset($result['prepay_id'])){
			$time = time();
			$nonceStr = Tools::getRandString(24);

			return [
				'appId'=> $this->appId,
				'timeStamp'=> $time,
				'nonceStr'=> $nonceStr,
				'package'=> 'prepay_id='.$result['prepay_id'],
				'signType'=> 'RSA',
				'paySign'=> WxPayUtil::getSign($this->privateKeyPath, [
                    $this->appId,
                    $time,
                    $nonceStr,
                    'prepay_id='.$result['prepay_id']
				]),
			];
		}
		return isset($result['message']) ? '发生错误：'.$result['message'] : '支付失败';
	}

	/**
	 * app trade
	 * @param string $outTradeNo 商户订单号
	 * @param int $total 金额，单位/分
	 * @return array|string
	 */
	public function appV3(string $outTradeNo, int $total)
	{
        $this->url = $this->url ?: '/v3/pay/transactions/app';
        $result = $this->transaction($outTradeNo, $total);
		if (isset($result['prepay_id'])){
			$time = time();
			$nonceStr = Tools::getRandString(24);

			return [
                'appid'=> $this->appId,
                'partnerid'=> $this->mchId,
                'prepayid'=> $result['prepay_id'],
                'timestamp'=> $time,
                'package'=> 'Sign=WXPay',
                'noncestr'=> $nonceStr,
                'sign'=> WxPayUtil::getSign($this->privateKeyPath, [
                    $this->appId,
                    $time,
                    $nonceStr,
                    $result['prepay_id']
			    ])
			];
		}
		return isset($result['message']) ? '发生错误：'.$result['message'] : '支付失败';
	}

    /**
     * H5支付
     * @param string $outTradeNo 商户订单号
     * @param int $total 金额，单位/分：100=1元
     * @return array|false|mixed|string
     */
    public function h5V3(string $outTradeNo, int $total)
    {
        $this->url = $this->url ?: '/v3/pay/transactions/h5';
        $result = $this->transaction($outTradeNo, $total, WechatConst::PAY_TYPE_H5);
        if(isset($result['h5_url'])){
            return $result;
        }
        return isset($result['message']) ? '发生错误：'.$result['message'] : '支付失败';
	}

	/**
	 * native trade(自行使用QR code将code_url生成二维码)
	 * @param string $outTradeNo 订单号
	 * @param int $total 金额，单位/分：100=1元
	 * @return mixed|string
	 */
	public function nativeV3(string $outTradeNo, int $total)
	{
        $this->url = $this->url ?: '/v3/pay/transactions/native';
        $result = $this->transaction($outTradeNo, $total);
		if (isset($result['code_url'])){
			return $result['code_url'];
		}
		return isset($result['message']) ? '发生错误：'.$result['message'] : '支付失败';
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
			$url = '/v3/pay/transactions/id/'.$number.'?mchid='.$this->mchId;
		}elseif ($type == WechatConst::TRADE_QUERY_WAY_OTN){
			$url = '/v3/pay/transactions/out-trade-no/'.$number.'?mchid='.$this->mchId;
		}
		$header = [
            'Content-Type: application/json;charset=UTF-8',
            'Accept: application/json',
            'User-Agent: '.$_SERVER['HTTP_USER_AGENT']
		];
		$header[] = WxPayUtil::instance()->buildPayRequestSign('GET',$url, [
            'private_key_path'=> $this->privateKeyPath,
            'mch_id'=>$this->mchId,
            'serial_no'=>$this->serialNo
		]);

		$result = Tools::httpGet($this->gateway.$url, [],['header'=>$header]);
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
			'appid'=> $this->appId,
			'mchid'=> $this->mchId,
            'description'=> $this->description,
			'out_trade_no'=> $outTradeNo,
			'notify_url'=> $this->notifyUrl,
			'amount'=> ['currency'=>$this->currency, 'total'=>$total],
		];

		if($payChannel == WechatConst::PAY_TYPE_JSAPI) $body['payer'] = ['openid'=>$this->openId];
		if($this->timeExpire) $body['time_expire'] = $this->timeExpire;
		if($this->attach) $body['attach'] = $this->attach;
		if($this->goodsTag) $body['goods_tag'] = $this->goodsTag;
		if($this->detail) $body['detail'] = $this->detail;
		if($this->sceneInfo) $body['scene_info'] = $this->sceneInfo;

		$header = [
            'Content-Type: application/json;charset=UTF-8',
            'Accept: application/json',
            'User-Agent: '.$_SERVER['HTTP_USER_AGENT']
		];
		try {
			$header[] = WxPayUtil::instance()->buildPayRequestSign($this->method, $this->url, [
				'private_key_path'=>$this->privateKeyPath,
				'mch_id'=>$this->mchId,
				'serial_no'=>$this->serialNo
            ], json_encode($body)); // 这里的json_encode切勿使用JSON_UNESCAPED_UNICODE

			$result = Tools::httpPost($this->gateway.$this->url, $body,['header'=> $header, 'format'=>'json']);
			list($h,$b) = explode("\r\n\r\n", $result);
            $verifySign = $this->verifySign($h, $b);
            if(!$verifySign){
                throw new \Exception('应答验签失败');
            }
            $this->url = '';
			return json_decode($b, true);
		}catch (\Exception $e){
			return ['message'=>$e->getMessage()];
		}
	}

    /**
     * 退款V3版本
     * @param string $tradeNo
     * @param string $refundNo
     * @param int $refund
     * @param int $total
     * @param string $type
     * @return array|mixed
     */
    public function refundV3(string $tradeNo, string $refundNo, int $refund, int $total, string $type = 'out_trade_no')
    {
        $this->url = $this->url ?: '/v3/refund/domestic/refunds';

        $body = [
            'out_refund_no'=> $refundNo,
            'amount'=> ['currency'=>'CNY', 'total'=>$total, 'refund'=>$refund]
        ];
        if($type == 'out_trade_no') $body['out_trade_no'] = $tradeNo;
        if($type == 'transaction_id') $body['transaction_id'] = $tradeNo;

        $header = [
            'Content-Type: application/json;charset=UTF-8',
            'Accept: application/json',
            'User-Agent: '.$_SERVER['HTTP_USER_AGENT']
        ];

        try {
            $header[] = WxPayUtil::instance()->buildPayRequestSign($this->method, $this->url, [
                'private_key_path'=>$this->privateKeyPath,
                'mch_id'=>$this->mchId,
                'serial_no'=>$this->serialNo
            ], json_encode($body)); // 这里的json_encode切勿使用JSON_UNESCAPED_UNICODE
            $result = Tools::httpPost($this->gateway.$this->url, $body,['header'=> $header, 'format'=>'json']);
            return json_decode($result, true);
        }catch (\Exception $e){
            return ['message'=>$e->getMessage()];
        }
	}

	/**
	 * 退款V2版本
	 * @param string $transactionId 交易单号
	 * @param int $totalFee 总金额
	 * @param int $refundFee 退款金额
	 * @param string $outRefundNo 退款单号
	 * @return array|string
	 */
	public function refundV2(string $transactionId, int $totalFee, int $refundFee, string $outRefundNo)
	{
		$params = [
		  'appid'=> $this->appId,
		  'mch_id'=> $this->mchId,
		  'nonce_str'=> Tools::getRandString(24),
		  'transaction_id'=> $transactionId,
		  'out_refund_no'=> $outRefundNo,
		  'total_fee'=> $totalFee,
		  'refund_fee'=> $refundFee
		];
		ksort($params);
		$str = http_build_query($params);
		$str .= '&key='.$this->secret;
		$str = strtoupper(md5($str));
		$params['sign'] = $str;

		try {
			$result = Tools::httpPost($this->gateway.'/secapi/pay/refund', $params, [
			  'cert'=> ['cert'=> $this->privateKeyPath, 'key'=> $this->certPath],
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
				throw new \Exception('微信支付V2退款请求失败');
			}
		}catch (\Exception $e){
			return '发生错误：'.$e->getMessage();
		}
	}

    /**
     * 应答验签
     * @param $header
     * @param $body
     * @return bool
     * @throws \Exception
     */
    public function verifySign($header, $body): bool
    {
        if(is_string($header)){
            $header = Tools::header2arr($header);
        }
        $body = trim($body);
        $cert = $header['wechatpay-serial'] ?? 'no-cert';
        if(file_exists($this->platformCertDir . $cert . '.txt')){
            $publicKey = file_get_contents($this->platformCertDir . $cert . '.txt');
            $message = $header['wechatpay-timestamp']."\n".$header['wechatpay-nonce']."\n".$body."\n";
            $remoteSign = base64_decode($header['wechatpay-signature']);
            $result = openssl_verify($message, $remoteSign, $publicKey,OPENSSL_ALGO_SHA256);
            return ($result == 1);
        }else{
            throw new \Exception('平台证书不存在');
        }
    }
}