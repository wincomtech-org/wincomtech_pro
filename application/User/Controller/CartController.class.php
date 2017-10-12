<?php
namespace User\Controller;

use Common\Controller\MemberbaseController;

class CartController extends MemberbaseController {
	
	function _initialize(){
		parent::_initialize();
		$this->assign('flag','cart');
		bcscale(2);
	}
	
    // 购物车首页
	public function index() {
	    
	    $where=array('uid'=>session('user.id'),'ctype'=>1);
	    $total=M('Cart')->where($where)->count();
	   
		$page = $this->page($total, 10);
		$list=D('Cart0View')->where($where)->order('utime desc')->limit($page->firstRow,$page->listRows)->select();
		
		$this->assign('page',$page->show('Admin'));
		 $m1=M('Template');
		 $m2=M('Product');
		 
		 //查询得到商品名
		foreach ($list as $k=>$v){
		    $list[$k]['zprice']= bcmul($list[$k]['price'], $list[$k]['count']);
		    //商品删除了，属性表就没有数据了，但cart表中数据未删除干净，查询数据库会出错
		    if(empty($v['pid'])){
		        $list[$k]['name']='商品已下架';
		        $list[$k]['link']='javascript:;';
		        continue;
		    }
		    if($v['type']==1){
		        $temp=$m1->field('name')->where('id='.$v['pid'])->find();
		       
		        $list[$k]['name']=$temp['name'];
		        $list[$k]['link']=U('Portal/Template/detail',array('id'=>$v['pid'],'price_id'=>$v['price_id']));
		       
		    }else{
		        $temp=$m2->field('name')->where('id='.$v['pid'])->find();
		       
		        $list[$k]['name']=$temp['name'];
		        $list[$k]['link']=U('Portal/Product/detail',array('id'=>$v['pid'],'price_id'=>$v['price_id']));
		      }
		      //如果商品删除了，但是价格属性表还没删除干净，可以在此处提示
		    if(empty($temp)){
		        $list[$k]['name']='商品已下架';
		        $list[$k]['link']='javascript:;';
		        continue;
		    }
		    
		    
		}
		
		$temp=M('Attr')->select();
		foreach ($temp as $v){
		    $attrs[$v['id']]=$v['name'];
		}
		$this->assign('attrs',$attrs);
		$this->assign('list',$list);
		 
		$this->display();
    }
   
    //批量删除
    public function dels(){
        $cids=I('cids',array());
        $row=M('Cart')->where(array('id'=>array('in',$cids)))->delete();
        if($row>0){
            $data=array('errno'=>1,'error'=>'删除成功');
        }else{
            $data=array('errno'=>0,'error'=>'删除失败');
        }
        $this->ajaxReturn($data);
        exit;
    }
    
    //ajax增减
    public function reduce(){
        
        $row=M('Cart')->where('id='.I('id',0))->save(array('count'=>I('count',0)));
        if($row===1){
            $data=array('errno'=>1,'error'=>'修改成功');
        }else{
            $data=array('errno'=>0,'error'=>'修改失败');
        }
        $this->ajaxReturn($data);
        exit;
    }
    //ajaxstatus
    public function status(){
        
        $row=M('Cart')->where('id='.I('id',0))->save(array('status'=>I('status',1)));
        if($row===1 ||$row===0 ){
            $data=array('errno'=>1,'error'=>'修改成功');
        }else{
            $data=array('errno'=>0,'error'=>'修改失败');
        }
        $this->ajaxReturn($data);
        exit;
    }
    //ajaxstatus
    public function selectAll(){
        $ids=I('ids',array());
        if(empty($ids)){
            $data=array('errno'=>1,'error'=>'修改成功');
            $this->ajaxReturn($data);
            exit;
        }
        $map['id']=array('in',$ids);
        $row=M('Cart')->where($map)->save(array('status'=>I('status',1)));
        if($row===1 ||$row===0 ){
            $data=array('errno'=>1,'error'=>'修改成功');
        }else{
            $data=array('errno'=>0,'error'=>'修改失败');
        }
        $this->ajaxReturn($data);
        exit;
    }
    
    //ajax加入购物车
    public function insert(){
        
        $m=M('Cart');
        $price_id=I('price_id',0);
       $time=time();
        $uid=session('user.id');
        if(empty($uid)||$price_id==0 ){
            $data=array('errno'=>0,'error'=>'数据错误');
            $this->ajaxReturn($data);
            exit;
        }
       
        $where=array(
            'uid'=>$uid,
            'price_id'=>$price_id,
            'ctype'=>1,
        );
        
        $info=$m->where($where)->find();
        if(empty($info)){
            
            $where['count']=1;
            $where['ctime']=$time;
            $where['utime']=$time;
            $row=$m->add($where);
            
        }else{
            $where=array(
                'count'=>1+$info['count'],
                'utime'=>$time,
            );
            $row=$m->where('id='.$info['id'])->save($where);
        }
        if($row>=1){
            $data=array('errno'=>1,'error'=>'修改成功');
        }else{
            $data=array('errno'=>0,'error'=>'修改失败');
        }
        $this->ajaxReturn($data);
        exit;
    }
    //快捷购买
    public function order_post(){
        //先加入购物车，再跳到订单确认页
        $m=M('Cart');
        $price_id=I('price_id',0);
        $time=time();
        $uid=session('user.id');
        if(empty($uid)||$price_id==0 ){
             
            $this->error('数据错误');
             
        }
        
        $data=array(
            'uid'=>$uid,
            'price_id'=>$price_id,
            'ctype'=>2,
            'count'=>1,
           'ctime'=>$time,
            'utime'=>$time,
        ); 
        $cid=$m->add($data); 
        if($cid<1){
            $this->error('数据错误');
        } 
        $this->redirect(U('order',array('cid'=>array($cid))));
    }
    //进入订单确认页
    public function order(){
        $cids=I('cid',array());
        if(empty($cids)){
            
            $this->error('没有选择商品');
            
        }
        $map['id']=array('in',$cids);
        $list=D('Cart0View')->where($map)->select();
      
        $m1=M('Template');
        $m2=M('Product');
        $tcount=0;
        $tprice=0;
        //查询得到商品名
        foreach ($list as $k=>$v){
            $list[$k]['zprice']= bcmul($v['price'], $v['count']);
            $tprice= bcadd($list[$k]['zprice'], $tprice);
            $tcount+=$v['count'];
            if($v['type']==1){
                $temp=$m1->field('name,pic')->where('id='.$v['pid'])->find();
                
                $list[$k]['link']=U('Portal/Template/detail',array('id'=>$v['pid'],'price_id'=>$v['price_id']));
            }else{
                $temp=$m2->field('name,pic')->where('id='.$v['pid'])->find();
              
                $list[$k]['link']=U('Portal/Product/detail',array('id'=>$v['pid'],'price_id'=>$v['price_id']));
            }
            $list[$k]['name']=$temp['name'];
            $list[$k]['pic']=$temp['pic'];
            
        }
        
        
        $this->assign('list',$list);
        $this->assign('tprice',$tprice);
        $this->assign('tcount',$tcount);
        
        $this->display();
    }
    
    //确认下单
    public function order_do(){
        $cids=I('cid',array());
        if(empty($cids)){
            //$_SERVER['HTTP_REFERER']
            $this->error('没有选择商品');
            
        }
        $utel=I('utel',0);
        if(!preg_match('/(^(13\d|15[^4\D]|17[13678]|18\d)\d{8}|170[^346\D]\d{7})$/',$utel)){
            $this->error('手机号码格式不对');
        }
        $uname=I('uname','');
        $uid=session('user.id');
        $map['id']=array('in',$cids);
        $list=D('Cart0View')->where($map)->select();
        
        $m1=M('Template');
        $m2=M('Product');
        $tcount=0;
        $tprice=0;
        //生成订单号时间+uid
        $oid=date('YmdHis'.$uid);
        
        //添加订单详情
        $m_oinfo=M('Oinfo');
        $m_oinfo->startTrans();
       
        //查询得到商品名
        foreach ($list as $k=>$v){
            $zprice= bcmul($v['price'], $v['count']);
            $tprice= bcadd($zprice, $tprice);
          
            $tcount+=$v['count'];
            if($v['type']==1){
                $temp=$m1->field('name,pic')->where('id='.$v['pid'])->find();
                
            }else{
                $temp=$m2->field('name,pic')->where('id='.$v['pid'])->find();
            }
           if($k==0){
               $name=$temp['name'];
           }
            $data=array(
                'oid'=>$oid,
                'pid'=>$v['pid'],
                'name'=>$temp['name'],
                'pic'=>$temp['pic'],
                'price'=>$v['price'],
                'count'=>$v['count'],
                'zprice'=>$zprice,
                'price_id'=>$v['price_id'],
            );
            $insert=$m_oinfo->add($data);
            if($insert<1){
                $m_oinfo->rollback();
                $this->error(C('DATAERROR'));
            }
        }
        if($tcount>1){
            $name.='...*'.$tcount;
        }
        //添加订单表
        $data=array(
            'oid'=>$oid,
            'uid'=>$uid,
            'name'=>$name,
            'tprice'=>$tprice,
            'tcount'=>$tcount,
            'create_time'=>time(),
            'status'=>0,
            'uname'=>$uname,
            'utel'=>$utel,
            'ip'=>get_client_ip(0,true),
        );
        $m_order=M('Order');
      
        $insert=$m_order->add($data);
        if($insert<1){
            $m_oinfo->rollback();
            
            $this->error(C('DATAERROR'));
        }
        $m_oinfo->commit();
        //订单添加后删除购物车数据
        M('Cart')->where(array('id'=>array('in',$cids)))->delete();
       
        //跳转到支付页面，此处到订单列表页
        $this->redirect(U('User/Order/pay',array('oid'=>$oid)));
    }
    
   
}
