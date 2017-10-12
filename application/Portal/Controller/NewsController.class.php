<?php
/*

 */

namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 新闻
 */
class NewsController extends HomebaseController {
	
    //list
	public function list0() {
	    //目前产品分类最多是2级
	    //产品类别
	    $m_ncate=M('Ncate');
	    $ncates=$m_ncate->field('name,id')->where('fid=0')->order('sort desc,id asc')->select();
	    foreach ($ncates as $k=>$v){
	        $ncates[$k]['child']=$m_ncate->field('name,id')->where('fid='.$v['id'])->order('sort desc,id asc')->select();
	    }
	    $this->assign('ncates',$ncates);
	    //获取指定的类别，没有则是第一个
	    $cid=I('cid',0);
	    if($cid!=0){
    	    //类别下获取所有子类
    	    $cids=$m_ncate->field('id')->where('fid='.$cid)->select();
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
	    $m_news=M('News');
	    $total=$m_news->where($where)->count();
	    
	    $page = $this->page($total, 6);
	    $news=$m_news->field('id,name,time,pic,desc')->where($where)->order('sort desc,id asc')->limit($page->firstRow,$page->listRows)->select();
	    
	    $this->assign('page',$page->show('Admin'));
	    $this->assign('news',$news);
	    $this->assign('scid',$cid);
	    $this->display();
    }
    
    //detail
    public function detail() {
        $m_ncate=M('Ncate');
        $ncates=$m_ncate->field('name,id')->where('fid=0')->order('sort desc,id asc')->select();
        foreach ($ncates as $k=>$v){
            $ncates[$k]['child']=$m_ncate->field('name,id')->where('fid='.$v['id'])->order('sort desc,id asc')->select();
        }
        $this->assign('ncates',$ncates);
        
        $id=I('id',0);
        $m=M('News');
        $info=$m->where('id='.$id)->find();
        
        if(empty($info)){
            $this->error('没有该新闻');
        }
       
        $this->assign('info',$info);
        $this->display();
        $m->where('id='.$id)->save(array('count'=>($info['count']+1)));
    }

}


