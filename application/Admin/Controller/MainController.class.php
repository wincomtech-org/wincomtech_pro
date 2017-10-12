<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class MainController extends AdminbaseController {
	
    public function index(){
    	
        //查询得到统计信息
       
        $m_order=M('Order');
        $m_user=M('Users');
        $count=array();
        //计算月份
        $time=time();
        $date=getdate($time);
        $year=$date['year'];
        $mon=$date['mon'];
        //完成的订单查询
         //已支付
        $where['status']=array('eq',1);
        //注册用户
        $whereUser['user_type']=array('eq',2);
        //总订单，总用户
        $count['order'][0]=$m_order->where($where)->count();
        $count['money'][0]=$m_order->where($where)->sum('tprice');
        $count['user'][0]=$m_user->where($whereUser)->count();
        if(empty($count['money'][0])){
            $count['money'][0]='0.00';
        }
        $times[13]=$time;
        
         
        //计算前12个月每月的数据
        for($i=12;$i>0;$i--){
            
            $labels[$i]=$year.'-'.$mon;
            //stime用于datetime格式的数据库计算
            $stime=$labels[$i].'-01 00:00:00';
            if($i==12){
                $stime1=date('Y-m-d H:i:s',$time);
            }else{
                $stime1=$labels[$i+1].'-01 00:00:00';
            }
            
            $times[$i]=strtotime($stime);
            //订单数
            $where['create_time']=array('between',array($times[$i],$times[$i+1]));
            $count['order'][$i]=$m_order->where($where)->count();
            $count['money'][$i]=$m_order->where($where)->sum('tprice');
            if(empty($count['money'][$i])){
                $count['money'][$i]=0;
            }
            //用户数
            $whereUser['create_time']=array('between',array($stime,$stime1));
           
            $count['user'][$i]=$m_user->where($whereUser)->count();
           
            $mon--;
            if($mon==0){
                $year--;
                $mon=12;
            }
        }
        
        $this->assign('labels',$labels)->assign('count',$count);
       
        $this->display();
    }
}