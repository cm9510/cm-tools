<?php
namespace Cm\Open\Wechat;

use Cm\Tool\HttpRequest;

/**
 * 小程序服务端
 */
class Applet
{
    private $appId = '';

    private $secret = '';

    public $method = 'POST';

    public $gateway = 'https://api.weixin.qq.com';

    public $url = '';

    public function __construct(string $appId, string $secret)
    {
        $this->appId = $appId;
        $this->secret = $secret;
    }

    /**
     * 登录
     * @param string $code
     * @param string $grantType
     * @return mixed|string
     */
    public function code2Session(string $code, string $grantType = 'authorization_code')
    {
        $this->url = $this->url ?: '/sns/jscode2session';
        $result = HttpRequest::instance()->httpGet($this->gateway . $this->url, [
            'appid'=>$this->appId,
            'secret'=>$this->secret,
            'js_code'=>$code,
            'grant_type'=>$grantType
        ]);

        $result = json_decode($result, true);
        if(isset($result['session_key'])) return $result;
        return 'session_key获取失败！';
    }

    /**
     * 接口调用凭证
     * @param string $grantType
     * @return mixed|string
     */
    public function getAccessToken(string $grantType = 'grant_type')
    {
        $this->url = $this->url ?: '/cgi-bin/token';
        $result = HttpRequest::instance()->httpGet($this->gateway . $this->url, [
            'appid'=>$this->appId,
            'secret'=>$this->secret,
            'grant_type'=>$grantType
        ]);
        $result = json_decode($result, true);
        if(isset($result['access_token'])) return $result;
        return 'access_token！';
    }
}