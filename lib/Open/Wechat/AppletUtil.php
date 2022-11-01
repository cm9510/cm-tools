<?php
namespace Cm\Open\Wechat;

class AppletUtil
{
    private $appId;
    
    public function __construct(string $appId = '')
    {
        $this->appId = $appId;
    }

    /**
     * 数据签名校验
     * @param array $bizData
     * @param string $sessionKey
     * @return bool
     */
    public function checkSignature(array $bizData, string $sessionKey): bool
    {
        return ($bizData['signature'] != sha1($bizData['raw_data'] . $sessionKey));
    }
    
    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData(string $sessionKey, string $encryptedData, string $iv, string &$data): int
    {
        $aesKey=base64_decode($sessionKey);
        
        if (strlen($iv) != 24) return WechatConst::IllegalIv;
        
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj = json_decode($result, true);
        
        if($dataObj == NULL) return WechatConst::IllegalBuffer;
        if($dataObj['watermark']['appid'] != $this->appId) return WechatConst::IllegalBuffer;
        
        $data = $dataObj;
        return WechatConst::OK;
    }
}