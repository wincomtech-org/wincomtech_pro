<?php
/*

 */

namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 模板的展示
 */
class TemplateController extends HomebaseController {
	
    //list
	public function list0() {
	    //目前模板只有一级分类
	    //产品类别
	    $m_tcate=M('tcate');
	    $tcates=$m_tcate->field('name,id')->where('fid=0')->order('sort desc,id asc')->select();
	    
	    $this->assign('tcates',$tcates);
	    //获取指定的类别，没有则是所有类
	    $cid=I('cid',0);
	  
	    
	    if($cid>0){
	        $where['cid']=$cid;
	    }
	    $m_template=D('Template0View');
	    
	    $total=$m_template->where($where)->count();
	    
	    $page = $this->page($total, 9);
	    $templates=$m_template->field('id,name,pic,catename')->where($where)->order('sort desc,id asc')->limit($page->firstRow,$page->listRows)->select();
	    
	    $this->assign('page',$page->show('Admin'));
	    $this->assign('templates',$templates);
	    $this->assign('scid',$cid);
	    $this->display();
    }
    
    //detail
    public function detail() {
        $id=I('id',0);
        //price_id标记当前商品的属性
        $price_id=I('price_id',0);
        $m=M('Template');
        $info=$m->where('id='.$id)->find();
        if(empty($info)){
            $this->error('没有该商品');
        }
        $m->where('id='.$id)->save(array('count'=>$info['count']+1));
        //查询对应的属性和价格
        $where=array(
            'pid'=>$id,
            'type'=>1,
        );
        $prices=D('Price0View')->order('aid asc')->where($where)->select();
        if($price_id==0){
            $price_id=$prices[0]['id'];
        }
        $this->assign('prices',$prices);
        $this->assign('price_id',$price_id);
        $this->assign('info',$info);
        $this->display();
    }

}


