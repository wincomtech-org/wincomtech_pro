<?php
header("content-type:text/html;charset=utf-8");
ini_set('date.timezone','Asia/Shanghai');
$mysqli=new mysqli('localhost', '2222', '2222', '2222', '3306');
$mysqli->set_charset('utf8');
//$index="http://zzhuachuang.com/user/order/index";
$index="http://hcpro.wincomtech.cn/user/order/index";
/******** 支付宝支付配置 ********/
$alipay_config=array(
   
    "partner"=>"111",//合作身份者ID
    "seller_id"=>"111",//收款支付宝账号sxc660226@163.com
    "key"=>"111",//MD5密钥
    "notify_url"=>"http://hcpro.wincomtech.cn/alipay/notify.php",//服务器异步通知页面路径
   
    "return_url"=>"http://hcpro.wincomtech.cn/alipay/return.php",//页面跳转同步通知页面路径
    "sign_type"=>strtoupper('MD5'),//签名方式
    "input_charset"=>strtolower('utf-8'),//字符编码格式 目前支持 gbk 或 utf-8
    "cacert"=>getcwd().'\\cacert.pem',//ca证书路径地址
    "transport"=>"http",//访问模式
    "payment_type"=>"1",//支付类型 ，无需修改
    "service"=>"create_direct_pay_by_user",//产品类型，无需修改
    "anti_phishing_key"=>"",//
    "exter_invoke_ip"=>"",//
);