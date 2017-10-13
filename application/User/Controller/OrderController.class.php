<?php
namespace User\Controller;

use Common\Controller\MemberbaseController;

class OrderController extends MemberbaseController {
	
	function _initialize(){
		parent::_initialize();
		$this->assign('flag','order');
	}
	
    // 首页
	public function index() {
	   $m=M('Order');
	   $where=array('uid'=>session('user.id'),'state'=>1);
	   $total=$m->where($where)->count();
	    
		$page = $this->page($total, 10);
		$list=$m->where($where)->order('oid desc')->limit($page->firstRow,$page->listRows)->select();
		
		
		$time0=time();
		$data=array('status'=>2);
		$where=array('oid'=>$v['oid']);
		//遍历查看是否有支付过期
        $list=orders_outtime($list);
		$this->assign('page',$page->show('Admin'));
		$this->assign('list',$list);
		 
		$this->display();
    }
    //删除
    public function del(){
        $data=array('state'=>2,'delete_time'=>time());
        $row=M('Order')->where('oid='.I('oid'))->save($data);
        if($row>0){
            $data=array('errno'=>1,'error'=>'删除成功');
        }else{
            $data=array('errno'=>0,'error'=>'删除失败');
        }
        $this->ajaxReturn($data);
        exit;
    }
    
     
    
    //进入订单详情页
    public function order(){
        $oid=I('oid',0);
        if(empty($oid)){
            //$_SERVER['HTTP_REFERER']
            $this->error('没有选择订单');
            
        }
        $where=array('oid'=>$oid);
        $list=M('Oinfo')->where($where)->select();
        $outtime=C('OUTTIME');
        $info=M('Order')->where($where)->find();
         
        switch ($info['status']){
            case 0:
                
                $time=$info['create_time']+$outtime-time();
                if($time<5){
                    $info['status']=2;
                    M('Order')->where($where)->save(array('status'=>2));
                    $info['link']='javascript:;';
                    $info['linkname']='已过期';
                }else{
                    $info['link']=U('pay',$where);
                    $info['linkname']='支付';
                }
                break;
            case 1:
                $info['link']='javascript:;';
                $info['linkname']='已支付';
                break;
            case 2:
                $info['link']='javascript:;';
                $info['linkname']='已过期';
                break;
            default:
                $info['link']='javascript:;';
                $info['linkname']='其他状态';
                
        }
        
        
        $this->assign('info',$info);
        $this->assign('list',$list);
        
        $this->display();
    }
    //支付页面
    public function pay(){
        $oid=I('oid','');
        $m=M('Order');
        $info=$m->where(array('oid'=>$oid))->find();
        if($info['status']!=0){
            $this->redirect('index');
            exit;
        }
        $outtime=$info['create_time']+C('OUTTIME');
        $time=$outtime-time();
        
        if($time<1 && $info['status']==0){
            $m->where(array('oid'=>$oid))->save(array('status'=>2));
            $this->redirect('index');
            exit;
        }
        
        //获取微信支付二维码
        /**
         * 流程：
         * 1、调用统一下单，取得code_url，生成二维码
         * 2、用户扫描二维码，进行支付
         * 3、支付完成之后，微信服务器会通知支付成功
         * 4、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
         */ 
       
        require_once getcwd().'/weixinpay3/lib/WxPayApi.php';
        require_once getcwd().'/weixinpay3/example/WxPayNativePay.php';
        require_once getcwd().'/weixinpay3/example/log.php';
        $notify = new \NativePay();
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($info['name']);
        $input->SetAttach("test");
        $input->SetOut_trade_no($oid);
        $input->SetTotal_fee("1");  //此处以人民币分为最小单位，1为0.01元
        $input->SetTime_start(date("YmdHis"),$info['create_time']);
        $input->SetTime_expire(date("YmdHis", $outtime));
        $input->SetGoods_tag("test");
        //$input->SetNotify_url("http://127.0.0.1/huachuang/weixinpay3/notify.php");
        $input->SetNotify_url("http://hcpro.wincomtech.cn/weixinpay3/notify.php");
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id("123456789");
        $result = $notify->GetPayUrl($input);
        
        $info['weixinUrl']= urlencode($result["code_url"]);
        
        $this->assign('info',$info);
        
        $this->assign('time',$time);
        $this->display();
    }
    
    //支付过期
    public function outtime(){
        $oid=I('oid','');
        $m=M('Order');
        $info=$m->where(array('oid'=>$oid))->find();
        $time=$info['create_time']+3600-time();
        
        if($time<1 && $info['status']==0){
            $m->where(array('oid'=>$oid))->save(array('status'=>2));
            $data=array('errno'=>1,'error'=>'已过期');
            
        }else{
            $data=array('errno'=>0,'error'=>'未过期');
        }
        $this->ajaxReturn($data);
        exit;
       
    }
    
    
     
}
