<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
error_log("notify1:".date('Y-m-d H:i:s')."\r\n",3,'error.log');

require_once "lib/WxPayApi.php";
require_once 'lib/WxPayNotify.php';
require_once 'log.php';
/* 已测试订单验证，不用 */
//初始化日志
$logHandler= new CLogFileHandler("logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
    public function Queryorder($out_trade_no)
	{
		$input = new WxPayOrderQuery();
		//根据交易号查询
		//$input->SetTransaction_id($transaction_id);
		//根据订单号查询
		$input->SetOut_trade_no($out_trade_no);
		$result = WxPayApi::orderQuery($input);
		var_dump($result);
		Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
		    $time=time();
		    $mysqli=new mysqli('localhost', 'root', '', 'huachuang', '3306');
		    $mysqli->set_charset('utf8');
		    //修改订单状态
		    $sql="update hc_order set status=1,pay_time='{$time}' where oid='{$out_trade_no}'";
		    
		    $mysqli->query($sql);
		    //保存支付信息
		    $sql="insert into hc_pay(tid,user,oid,type,fee,time) 
                values('{$result['transaction_id']}','{$result['openid']}',
                '{$out_trade_no}',2,'{$result['total_fee']}','{$time}')";
		    $mysqli->query($sql);
		    
			return true;
		}
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
//根据订单号查询订单状态返回true,,false
$oid=$_GET['oid'];
$notify->Queryorder($oid);
    
 
