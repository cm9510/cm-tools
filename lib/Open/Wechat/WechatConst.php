<?php
namespace Cm\Open\Wechat;

final class WechatConst
{
    # wxpay
	const PAY_TYPE_JSAPI = 'jsapi';
	const PAY_TYPE_APP = 'app';
	const PAY_TYPE_H5 = 'h5';
	const PAY_TYPE_NATIVE = 'native';
	const PAY_TYPE_APPLET = 'applet';
	const PAY_TYPE_COMBINE = 'combine';
	const TRADE_QUERY_WAY_TRA = 'transaction';
	const TRADE_QUERY_WAY_OTN = 'out_trade_no';

	# applet
    const OK = 0;
    const IllegalAesKey = -41001;
    const IllegalIv = -41002;
    const IllegalBuffer = -41003;
    const DecodeBase64Error = -41004;
}