<?php
// require_once "../lib/WxPay.Api.php";
/**
 * 
 * App支付实现类（移植于WxPay.JsApiPay.php）
 * 该类实现了从微信公众平台获取code、通过code获取openid和access_token、
 * 生成jsapi支付js接口所需的参数、生成获取共享收货地址所需的参数
 * 
 * 该类是微信支付提供的样例程序，商户可根据自己的需求修改，或者使用lib中的api自行开发
 * 
 * @author widy
 *
 */
class AppPay
{	
	/**
	 * 
	 * 获取jsapi支付的参数
	 * @param array $UnifiedOrderResult 统一支付接口返回的数据
	 * @throws WxPayException
	 * 
	 * @return json数据，可直接填入js函数作为参数
	 */
	public function GetAppParameters($UnifiedOrderResult)
	{
		if(!array_key_exists("appid", $UnifiedOrderResult)
		|| !array_key_exists("prepay_id", $UnifiedOrderResult)
		|| $UnifiedOrderResult['prepay_id'] == "")
		{
			throw new WxPayException("参数错误");
		}

		$app = new WxPayAppPay();
		$app->SetAppid($UnifiedOrderResult["appid"]);
		$app->SetPartnerid($UnifiedOrderResult['mch_id']);
		$app->SetPrepayid($UnifiedOrderResult['prepay_id']);
		$app->SetPackage('Sign=WXPay');
		$app->SetNoncestr(WxPayApi::getNonceStr());
		$app->SetTimestamp(time());
		$app->SetSignValue($app->MakeSign());
		// $parameters = json_encode($app->GetValues());
		// return $parameters;
		return $app->GetValues();
	}
}