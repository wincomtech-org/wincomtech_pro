<?php

require_once 'AlipayNotify.class.php';
require_once 'AlipaySubmit.class.php';
class AlipayController{

	//支付发送
	public function send(){
		header("content-type:text/html;charset=utf-8");
		/**************************请求参数**************************/
		//商户订单号，商户网站订单系统中唯一订单号，必填
		$out_trade_no = I('oid',0);
		$where=array('oid'=>$out_trade_no);
		$info=M('Order')->where($where)->find();
		//订单名称，必填
		$subject =$info['name'];
	
		//付款金额，必填
		//$total_fee = $_GET['total_fee'];
		$total_fee =0.01;
		
		//商品描述，可空
		$body = $info['desc1'].'dd';
		
			
		/************************************************************/
		$alipay_config=C("ALIPAY_CONFIG");
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service"       => $alipay_config['service'],
				"partner"       => $alipay_config['partner'],
				"seller_id"  => $alipay_config['seller_id'],
				"payment_type"	=> $alipay_config['payment_type'],
				"notify_url"	=> $alipay_config['notify_url'],
		    //"notify_url"	=> "http://103.210.236.106:88/notify.php",
				//"return_url"	=> $alipay_config['return_url'],
		    "return_url"	=> "http://127.0.0.1/huachuang/aa/aa.php",
				"anti_phishing_key"=>$alipay_config['anti_phishing_key'],
				"exter_invoke_ip"=>$alipay_config['exter_invoke_ip'],
				"out_trade_no"	=> $out_trade_no,
				"subject"	=> $subject,
				"total_fee"	=> $total_fee,
				"body"	=> $body,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
				//其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
				//如"参数名"=>"参数值"
		
		);
		 
		//建立请求
		
		$alipaySubmit = new \Pay\Alipay\AlipaySubmit($alipay_config);
		
		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
		error_log('cont:'.$html_text."\r\n",3,'alipay.log');
		echo $html_text;
	}
	
	
	
}