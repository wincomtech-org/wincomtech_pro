<?php
ini_set('date.timezone','Asia/Shanghai');
//error_reporting(E_ERROR);

require_once "../lib/WxPayApi.php";
require_once "WxPayNativePay.php";
require_once 'log.php';

class native{
    public function GetU()
    {
        $notify=new NativePay();
        $input = new WxPayUnifiedOrder();
        $input->SetBody("test-localhost");
        $input->SetAttach("test");
        $input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
        $input->SetTotal_fee("2");  //此处以人民币分为最小单位，1为0.01元
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url("http://127.0.0.1/test/weixinpay3/example/notify.php");
        //$input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id("123456789");
        $result = $notify->GetPayUrl($input);
        //var_dump($result);
        //$url2 = $result["code_url"];
        return $result;
    }
}

//模式二
/**
 * 流程：
 * 1、调用统一下单，取得code_url，生成二维码
 * 2、用户扫描二维码，进行支付
 * 3、支付完成之后，微信服务器会通知支付成功
 * 4、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
 */



