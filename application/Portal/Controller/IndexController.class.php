<?php
/*

 */

namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 首页
 */
class IndexController extends HomebaseController {
	
    //首页
	public function index() {
	    //产品展示
	    $products=M('Product')->field('id,name,pic')->order('sort desc,id asc')->limit('0,6')->select();
	    $this->assign('products',$products);
	    
	    //案例展示
	    $cases=D('Case0View')->field('id,name,pic,catename')->order('sort desc,id asc')->limit('0,6')->select();
	    $this->assign('cases',$cases);
	    
	    //新闻动态
	    $news=M('News')->field('id,name,desc,pic')->order('sort desc,id desc')->limit('0,6')->select();
	    $this->assign('news',$news);
	   
	    $this->display();
    }

}


