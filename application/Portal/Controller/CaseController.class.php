<?php
/*

 */

namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 案例的展示
 */
class CaseController extends HomebaseController {
	
    //list
	public function list0() {
	    //案例行业只有1级
	    //产品类别
	    $m_ccate=M('ccate');
	    $ccates=$m_ccate->field('name,id')->order('sort desc,id asc')->select();
	    
	    $this->assign('ccates',$ccates);
	    //获取指定的类别，没有则是所有类
	    $cid=I('cid',0);
	  
	    
	    if($cid>0){
	        $where['cid']=$cid;
	    }
	    $m_cases=D('Case0View');
	    
	    $total=$m_cases->where($where)->count();
	    
	    $page = $this->page($total, 9);
	    $cases=$m_cases->field('id,name,pic,catename')->where($where)->order('sort desc,id asc')->limit($page->firstRow,$page->listRows)->select();
	    
	    $this->assign('page',$page->show('Admin'));
	    $this->assign('cases',$cases);
	    $this->assign('scid',$cid);
	    $this->display();
    }
    
    //detail
    public function detail() {
        $id=I('id',0);
        $info=M('Cases')->where('id='.$id)->find();
        if(empty($info)){
            $this->error('没有该商品');
        }
        $this->assign('info',$info);
        $this->display();
    }

}


