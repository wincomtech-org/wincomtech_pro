<?php
/*

 */

namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 关于
 */
class AboutController extends HomebaseController {
	
    //list
	public function list0() {
	    //目前分类最多是2级
	    //产品类别
	    $m_acate=M('acate');
	    $acates=$m_acate->field('name,id')->where('fid=0')->order('sort desc,id asc')->select();
	    foreach ($acates as $k=>$v){
	        $acates[$k]['child']=$m_acate->field('name,id')->where('fid='.$v['id'])->order('sort desc,id asc')->select();
	    }
	    $this->assign('acates',$acates);
	    //获取指定的类别，没有则是第一个
	    $cid=I('cid',0);
	    if($cid!=0){
            //类别下获取所有子类
            $cids=$m_acate->field('id')->where('fid='.$cid)->select();
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
	    $m_about=M('about');
	    $total=$m_about->where($where)->count();
	    
	    $page = $this->page($total, 6);
	    $abouts=$m_about->field('id,name,time')->where($where)->order('sort desc,id asc')->limit($page->firstRow,$page->listRows)->select();
	    
	    $this->assign('page',$page->show('Admin'));
	    $this->assign('abouts',$abouts);
	    $this->assign('scid',$cid);
	    $this->display();
    }
    
    //detail
    public function detail() {
        $m_acate=M('acate');
        $acates=$m_acate->field('name,id')->where('fid=0')->order('sort desc,id asc')->select();
        foreach ($acates as $k=>$v){
            $acates[$k]['child']=$m_acate->field('name,id')->where('fid='.$v['id'])->order('sort desc,id asc')->select();
        }
        $this->assign('acates',$acates);
        
        $id=I('id',0);
        $m=M('About');
        $info=$m->where('id='.$id)->find();
        if(empty($info)){
            $this->error('没有该文章');
        }
        $this->assign('info',$info);
        $this->assign('scid',$info['cid']);
        $this->display();
        
        $m->where('id='.$id)->save(array('count'=>($info['count']+1)));
    }

}


