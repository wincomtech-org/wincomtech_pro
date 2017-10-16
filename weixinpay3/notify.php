<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
require_once "lib/WxPayApi.php";
require_once 'lib/WxPayNotify.php';
require_once 'log.php';
require_once 'config.php';
//初始化日志
$logHandler= new CLogFileHandler("logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
		   
		   //成功后判断数据库订单状态
		   //保存支付信息s
		    $time=time();
		    $out_trade_no=$result["out_trade_no"];
		    $trade_no=$result["transaction_id"];
		    $buyer_id=$result["open_id"];
		    $total_fee=bcdiv($result['total_fee'],100,2);
		    Log::DEBUG("订单:".$out_trade_no.'已校验支付，开始修改订单状态');
		    $sql="select * from hc_order where oid='{$out_trade_no}' limit 1";
		    $res=$mysqli->query($sql);
		    $info=$res->fetch_assoc();
		    if($info['status']==1){
		        Log::DEBUG("订单:".$out_trade_no.'已支付过');
		        $mysqli->close();
		        return true;
		         
		    }
		    //修改订单状态
		    $sql="update hc_order set status=1,pay_time='{$time}' where oid='{$out_trade_no}'";
		    $mysqli->query($sql);
		    if( $mysqli->affected_rows===1){
		        //保存支付信息
		        $sql="insert into hc_pay(tid,user,oid,type,fee,time)
		        values('{$trade_no}','{$buyer_id}',
		        '{$out_trade_no}',2,{$total_fee},'{$time}')";
		        $mysqli->query($sql);
		        Log::DEBUG("订单:".$out_trade_no.'支付成功');
		        
		    }else{
		        Log::DEBUG('订单'.$out_trade_no.'支付成功,但状态修改失败'); 
		    }
		    $mysqli->close();
		    return true;
		}  
		$mysqli->close();
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array();
		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		return true;
	}
}

Log::DEBUG("begin notify");
$notify = new PayNotifyCallBack();
$notify->Handle(false); 
 


 

