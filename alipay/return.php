<?php
require_once 'AlipayNotify.class.php';
require_once 'AlipaySubmit.class.php';
require_once 'config.php';

//计算得出通知验证结果 
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyReturn();

$log='alipay.log';
if($verify_result) {//验证成功
    //请在这里加上商户的业务逻辑程序代码
    
    $out_trade_no = $_GET['out_trade_no'];//商户订单号
    $trade_no = $_GET['trade_no'];//支付宝交易号
    $trade_status = $_GET['trade_status'];//交易状态
    $total_fee=$_GET['total_fee'];//支付金额
    $buyer_email=$_GET['buyer_email']; //买家付款账号buyer_id
    $buyer_id=$_GET['buyer_id']; //买家付款账号buyer_id
    
    
    if($_GET['trade_status'] == 'TRADE_SUCCESS' || $_GET['trade_status'] == 'TRADE_SUCCESS' ) {
        //判断该笔订单是否在商户网站中已经做过处理
        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
        //如果有做过处理，不执行商户的业务程序
        
        //修改订单表的支付金额，支付时间，是否支付状态，支付状态
        $time=time();
        $sql="select * from hc_order where oid='{$out_trade_no}' limit 1";
        $res=$mysqli->query($sql);
        $info=$res->fetch_assoc();
        if($info['status']==1){
            error_log(date('Y-m-d H:i:s').':订单'.$out_trade_no.'已支付过'."\r\n",3,$log);
            echo "订单".$out_trade_no."已支付过，重复支付请联系客服";
            echo '<a href="'.$index.'">返回我的订单</a>';
            header('refresh:10;url='.$index);
            exit;
        }
        //修改订单状态
        $sql="update hc_order set status=1,pay_time='{$time}' where oid='{$out_trade_no}'";
        $mysqli->query($sql);
        if( $mysqli->affected_rows===1){
            //保存支付信息
            $sql="insert into hc_pay(tid,user,oid,type,fee,time)
            values('{$trade_no}','{$buyer_id}',
            '{$out_trade_no}',1,{$total_fee},'{$time}')";
            $mysqli->query($sql);
            error_log(date('Y-m-d H:i:s').':订单'.$out_trade_no.'支付成功'."\r\n",3,$log);
            echo "支付成功";
        }else{
            error_log(date('Y-m-d H:i:s').':订单'.$out_trade_no.'支付成功,但状态修改失败'."\r\n",3,$log);
            echo "支付成功，由于数据库升级没有更新您的订单状态，请联系管理员手动操作，您的订单号为：{$out_trade_no}";
        }
        
    }else {
        
        error_log(date('Y-m-d H:i:s').':订单'.$out_trade_no.'支付失败'."\r\n",3,$log);
        echo '支付失败';
        
    }
    
}else {
    //验证失败
    //如要调试，请看alipay_notify.php页面的verifyReturn函数
    
    error_log(date('Y-m-d H:i:s').':订单'.$out_trade_no.'验证失败'."\r\n",3,$log);
    echo '验证失败';
    
}
echo '<a href="'.$index.'">返回我的订单</a>';
header('refresh:10;url='.$index);
exit;