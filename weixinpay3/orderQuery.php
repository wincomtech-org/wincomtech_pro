<?php
/* 
 * 查询微信订单
 *  */
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
require_once "lib/WxPayApi.php";
require_once 'log.php';

//初始化日志
$logHandler= new CLogFileHandler("logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

if(isset($_REQUEST["out_trade_no"]) && $_REQUEST["out_trade_no"] != ""){
    $out_trade_no = $_REQUEST["out_trade_no"];
    $input = new WxPayOrderQuery();
    $input->SetOut_trade_no($out_trade_no);
   
    $result=WxPayApi::orderQuery($input);
    if($result['trade_state']=='SUCCESS'){
         $time=time();
         $mysqli=new mysqli('localhost', 'root', 'root', 'hcpro', '3306');
         $mysqli->set_charset('utf8');
         //修改订单状态
         $sql="update hc_order set status=1,pay_time='{$time}' where oid='{$out_trade_no}'";
         $mysqli->query($sql);
         //保存支付信息
         $fee=bcdiv($result['total_fee'],100,2);
         $sql="insert into hc_pay(tid,user,oid,type,fee,time)
         values('{$result['transaction_id']}','{$result['openid']}',
         '{$out_trade_no}',2,'{$fee}','{$time}')";
         $mysqli->query($sql);
    } 
    echo $result['trade_state'];
    exit();
}