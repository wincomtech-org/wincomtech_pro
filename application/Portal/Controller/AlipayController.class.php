<?php
namespace Portal\Controller;

use Common\Controller\HomebaseController;
/*
 * alipay  */
class AlipayController extends HomebaseController{
    public function send0(){
        logResult('send0');
        echo "<a href='http://hcpro.wincomtech.cn/Portal/Alipay/send.php'>send</a>";
        $url=U('Portal/Alipay/send');
        echo "<a href='".$url."'>send-url</a>";
    }
	 public function send(){
	     logResult('send');
	     $time=time();
	     $money=66;
	     //商户订单号，商户网站订单系统中唯一订单号，必填
	     $out_trade_no = 'zz'.$time;
	     
	     //订单名称，必填
	     $subject ='zz充值￥'.$money;
	     error_log($subject."\r\n",3,'alipay.log');
	     //付款金额，必填
	     //$total_fee = $_GET['total_fee'];
	     $total_fee =0.01;
	     
	     //商品描述，可空
	     $body = $subject;
	     $alipay_config=C("ALIPAY_CONFIG");
	     //构造要请求的参数数组，无需改动
	     $parameter = array(
	         "service"       => $alipay_config['service'],
	         "partner"       => $alipay_config['partner'],
	         "seller_id"  => $alipay_config['seller_id'],
	         "payment_type"	=> $alipay_config['payment_type'],
	         
	         "notify_url"	=> $alipay_config['notify_url'],
	         "return_url"	=> $alipay_config['return_url'],
	        
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
	     vendor('Alipay.AlipaySubmit');
	     $alipaySubmit = new \AlipaySubmit($alipay_config);
	     
	     $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
	     error_log('cont:'.$html_text."\r\n",3,'alipay.log');
	     echo $html_text;
	 }
	 
	 public function return0(){
	     
	     //计算得出通知验证结果
	     $alipay_config=C("ALIPAY_CONFIG");
	     vendor('Alipay.AlipayNotify');
	     $alipayNotify = new \AlipayNotify($alipay_config);
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
	             echo '支付成功';
	             error_log(date('Y-m-d H:i:s').':订单'.$out_trade_no.'支付成功'."\r\n",3,$log);
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
	     $index=U('User/Info/index');
	     echo '<a href="'.$index.'">返回我的</a>';
	     
	    
	     exit;
	 }
	 public function notify0(){
	      
	     //计算得出通知验证结果
	     $alipay_config=C("ALIPAY_CONFIG");
	     vendor('Alipay.AlipayNotify');
	     $alipayNotify = new \AlipayNotify($alipay_config);
	     $verify_result = $alipayNotify->verifyNotify();
	     $log='alipay.log';
	     logResult('alipay-notify-start');
	     
	     if($verify_result) {//验证成功
	         logResult('alipay-notify-success00');
	         $notify_data = $alipayNotify->decrypt($_POST['notify_data']);
	         $doc = new \DOMDocument();
	         $doc->loadXML($notify_data);
	         if( ! empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) {
	             
	             $out_trade_no = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
	             
	             $trade_no = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
	             
	             $trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
	             if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
	                 //以下是数据库操作代码
	                 logResult('alipay-notify-success');
	                 //数据库操作结束
	                 
	             }
	         }
	     }
	     else {
	         logResult('alipay-notify-fail');
	        
	     }
	 }
	 
   
}
