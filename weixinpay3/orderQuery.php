<?php
/* 
 * 查询微信订单
 *  */
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
require_once "lib/WxPayApi.php";
require_once 'log.php';
require_once 'config.php';
//初始化日志
$logHandler= new CLogFileHandler("logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

if(isset($_REQUEST["out_trade_no"]) && $_REQUEST["out_trade_no"] != ""){
    $out_trade_no = $_REQUEST["out_trade_no"];
    $input = new WxPayOrderQuery();
    $input->SetOut_trade_no($out_trade_no);
   
    $result=WxPayApi::orderQuery($input);
    if($result['trade_state']=='SUCCESS'){
        Log::DEBUG("return-订单:".$out_trade_no.'已校验支付，开始修改订单状态');
         $time=time();
          //查询是否已修改了状态
         $sql="select * from hc_order where oid='{$out_trade_no}' limit 1";
         $res=$mysqli->query($sql);
         $info=$res->fetch_assoc();
         if($info['status']==1){
             Log::DEBUG("return-检测到订单:".$out_trade_no.'已支付');
             echo $result['trade_state'];
             $mysqli->close();
             exit();
             
         }
         //修改订单状态
         $sql="update hc_order set status=1,pay_time='{$time}' where oid='{$out_trade_no}'";
         $mysqli->query($sql);
         //保存支付信息
         if( $mysqli->affected_rows===1){
             //保存支付信息
             $fee=bcdiv($result['total_fee'],100,2);
             $sql="insert into hc_pay(tid,user,oid,type,fee,time)
             values('{$result['transaction_id']}','{$result['openid']}',
             '{$out_trade_no}',2,'{$fee}','{$time}')";
             $mysqli->query($sql);
             Log::DEBUG("return-订单:".$out_trade_no.'支付成功');
             
         }else{
             Log::DEBUG('return-订单'.$out_trade_no.'支付成功,但状态修改失败');
         }
        
    } 
    echo $result['trade_state'];
    $mysqli->close();
   
    exit();
}