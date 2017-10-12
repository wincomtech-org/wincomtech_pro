<?php
/*

 */

namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 产品的展示
 */
class ProductController extends HomebaseController {
	
    //list
	public function list0() {
	    //目前产品分类最多是2级
	    //产品类别
	    $m_cate=M('cate');
	    $cates=$m_cate->field('name,id')->where('fid=0')->order('sort desc,id asc')->select();
	    foreach ($cates as $k=>$v){
	        $cates[$k]['child']=$m_cate->field('name,id')->where('fid='.$v['id'])->order('sort desc,id asc')->select();
	    }
	    $this->assign('cates',$cates);
	    //获取指定的类别，没有则是第一个
	    $cid=I('cid',0);
	    if($cid!=0){
    	    //类别下获取所有子类
    	    $cids=$m_cate->field('id')->where('fid='.$cid)->select();
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
	    $m_product=M('Product');
	  //分页 
	    $total=$m_product->where($where)->count();
	    
	    $page = $this->page($total, 6);
	    $products=$m_product->field('id,name,pic')->where($where)->order('sort desc,id asc')->limit($page->firstRow,$page->listRows)->select();
	    
	    $this->assign('page',$page->show('Admin'));
	    $this->assign('products',$products);
	    //标记cid
	    $this->assign('scid',$cid);
	    $this->display();
    }
    
    //detail
    public function detail() {
        $id=I('id',0);
        //price_id标记当前商品的属性
        $price_id=I('price_id',0);
        $info=M('Product')->where('id='.$id)->find();
        if(empty($info)){
            $this->error('没有该商品');
        }
        //查询对应的属性和价格
        $where=array(
            'pid'=>$id,
            'type'=>2,
        );
        $prices=D('Price0View')->order('aid asc')->where($where)->select();
        if($price_id==0){
            $price_id=$prices[0]['id'];
        }
        $this->assign('prices',$prices);
        $this->assign('price_id',$price_id);
        $info['links1']=explode(';', $info['link1']);
        $info['links2']=explode(';', $info['link2']);
        if(empty($info['links1'][count($info['links1'])-1])){
            unset($info['links1'][count($info['links1'])-1]);
        }
        if(empty($info['links2'][count($info['links2'])-1])){
            unset($info['links2'][count($info['links2'])-1]);
        }
        
        $this->assign('info',$info);
        $this->display();
        
         
       
    }

}


