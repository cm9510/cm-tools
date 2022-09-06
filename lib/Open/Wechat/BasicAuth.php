<?php
namespace Cm\Open\Wechat;

use Cm\Tool\Tools;

class BasicAuth
{
    private $type = '';

    private $appId = '';

    private $secret = '';

    public $gateway = 'https://api.weixin.qq.com';

    public $url = '';

    public function __construct(string $appId, string $secret, string $type = 'general')
    {
        $this->type = $type;
        $this->appId = $appId;
        $this->secret = $secret;
    }

    /**
     * 小程序-登录
     * @param string $code
     * @param string $grantType
     * @return mixed|string
     */
    public function code2Session(string $code, string $grantType = 'authorization_code')
    {
        if($this->type != 'applet') return '方法不可调用，请检查实例类型';
        $this->url = $this->url ?: '/sns/jscode2session';
        $result = Tools::httpGet($this->gateway . $this->url, [
            'appid'=>$this->appId,
            'secret'=>$this->secret,
            'js_code'=>$code,
            'grant_type'=>$grantType
        ]);
        $result = json_decode($result, true);
        if(isset($result['session_key'])) return $result;
        return 'session_key获取失败';
    }

    /**
     * 小程序-接口调用凭证
     * @param string $grantType
     * @return mixed|string
     */
    public function getAccessToken(string $grantType = 'client_credential')
    {
        if($this->type != 'applet') return '方法不可调用，请检查实例类型';
        $this->url = $this->url ?: '/cgi-bin/token';
        $result = Tools::httpGet($this->gateway . $this->url, [
            'appid'=>$this->appId,
            'secret'=>$this->secret,
            'grant_type'=>$grantType
        ]);
        $result = json_decode($result, true);
        if(isset($result['access_token'])) return $result;
        return 'access_token获取失败';
    }

    /**
     * oauth2-接口调用凭证
     * @param string $code
     * @param string $grantType
     * @return mixed|string
     */
    public function oauth2AccessToken(string $code, string $grantType = 'authorization_code')
    {
        if($this->type != 'general') return '方法不可调用，请检查实例类型';
        $this->url = $this->url ?: '/sns/oauth2/access_token';
        $result = Tools::httpGet($this->gateway . $this->url, [
            'appid'=>$this->appId,
            'secret'=>$this->secret,
            'code'=>$code,
            'grant_type'=>$grantType
        ]);
        $result = json_decode($result, true);
        if(isset($result['access_token'])) return $result;
        return 'access_token获取失败';
    }

    /**
     * oauth2-获取用户个人信息(UnionID机制)
     * @param string $accessToken
     * @param string $openid
     * @return mixed|string
     */
    public function oauth2Userinfo(string $accessToken, string $openid)
    {
        if($this->type != 'general') return '方法不可调用，请检查实例类型';
        $this->url = $this->url ?: '/sns/userinfo';
        $result = Tools::httpGet($this->gateway.$this->url, ['access_token'=> $accessToken, 'openid'=> $openid]);
        $result = json_decode($result, true);
        if(isset($result['unionid'])) return $result;
        return '用户个人信息获取失败';
    }
}