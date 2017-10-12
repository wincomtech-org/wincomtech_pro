<?php
/*

 */

namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 产品报价
 */
class OfferController extends HomebaseController {
	
    //list
	public function list0() {
	    //目前产品分类最多是2级
	    //产品类别
	    $m_ocate=M('Ocate');
	    $ocates=$m_ocate->field('name,id')->where('fid=0')->order('sort desc,id asc')->select();
	    foreach ($ocates as $k=>$v){
	        $ocates[$k]['child']=$m_ocate->field('name,id')->where('fid='.$v['id'])->order('sort desc,id asc')->select();
	    }
	    $this->assign('ocates',$ocates);
	    //获取指定的类别，没有则是第一个
	    $cid=I('cid',0);
	    if($cid!=0){
    	    //类别下获取所有子类
    	    $cids=$m_ocate->field('id')->where('fid='.$cid)->select();
    	    if(empty($cids)){
    	        $where['cid']=$cid;
    	    }else{
    	        $cidss=array($cid);
    	        foreach ($cids as $v){
    	            $cidss[]=$v['id'];
    	        }
    	        $where['cid']=array('in',$cidss);
    	    }
	    }
	    $m_offer=M('Offer');
	    $total=$m_offer->where($where)->count();
	    
	    $page = $this->page($total, 6);
	    $offers=$m_offer->field('id,name,time')->where($where)->order('sort desc,id asc')->limit($page->firstRow,$page->listRows)->select();
	    
	    $this->assign('page',$page->show('Admin'));
	    $this->assign('offers',$offers);
	    $this->assign('scid',$cid);
	    $this->display();
    }
    
    //detail
    public function detail() {
        $m_ocate=M('Ocate');
        $ocates=$m_ocate->field('name,id')->where('fid=0')->order('sort desc,id asc')->select();
        foreach ($ocates as $k=>$v){
            $ocates[$k]['child']=$m_ocate->field('name,id')->where('fid='.$v['id'])->order('sort desc,id asc')->select();
        }
        $this->assign('ocates',$ocates);
        
        $id=I('id',0);
        $m=M('Offer');
        $info=$m->where('id='.$id)->find();
        if(empty($info)){
            $this->error('没有该数据');
        }
        $this->assign('info',$info);
        $this->assign('scid',$info['cid']);
        $this->display();
        $m->where('id='.$id)->save(array('count'=>($info['count']+1)));
    }

}


