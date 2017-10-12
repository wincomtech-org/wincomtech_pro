<?php

require_once 'AlipaySubmit.class.php';
require_once 'config.php';

$out_trade_no = $_GET['oid'];
$sql="select * from hc_order where oid='{$out_trade_no}' limit 1";
$res=$mysqli->query($sql);
$info=$res->fetch_assoc();
if($info['status']==1){
  
    echo "订单".$out_trade_no."已支付过，请勿重复支付";
    echo '<a href="'.$index.'">返回我的订单</a>';
    header('refresh:10;url='.$index);
    exit;
}
//订单名称，必填
$subject =$info['name'];
//付款金额，必填
//$total_fee = $info['tprice'];
$total_fee =0.01;
//商品描述，可空
$body = $info['desc1'];
 
//构造要请求的参数数组，无需改动
/* //it_b_pay超时时间
该笔订单允许的最晚付款时间，逾期将关闭交易。String
取值范围：1m～15d。
m-分钟，h-小时，d-天，1c-当天（1c-当天的情况下，无论交易何时创建，都在0点关闭）。
该参数数值不接受小数点，如1.5h，可转换为90m。
该参数在请求到支付宝时开始计时。 */
//一小时为过期支付时间
$it_b_pay=floor((($info['create_time']+3600-time())/60));
if($it_b_pay<1){
    echo "订单".$out_trade_no."已过期";
    echo '<a href="'.$index.'">返回我的订单</a>';
    header('refresh:10;url='.$index);
    exit;
}
$parameter = array(
    "service"       => $alipay_config['service'],
    "partner"       => $alipay_config['partner'],
    "seller_id"  => $alipay_config['seller_id'],
    "payment_type"	=> $alipay_config['payment_type'],
    "notify_url"	=> $alipay_config['notify_url'],
    //"notify_url"	=> "http://103.210.236.106:88/notify.php",
    "return_url"	=> $alipay_config['return_url'],
    //"return_url"	=> "http://127.0.0.1/huachuang/alipay/return.php",
    "anti_phishing_key"=>$alipay_config['anti_phishing_key'],
    "exter_invoke_ip"=>$alipay_config['exter_invoke_ip'],
    "out_trade_no"	=> $out_trade_no,
    "subject"	=> $subject,
    "total_fee"	=> $total_fee,
    "body"	=> $body,
    "_input_charset"	=> trim(strtolower($alipay_config['input_charset'])),
    'it_b_pay'=>$it_b_pay.'m',
    //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
    //如"参数名"=>"参数值"
    
);

//建立请求

$alipaySubmit = new AlipaySubmit($alipay_config);
//$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");

echo $html_text;
