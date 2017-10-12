<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Think\Model;
/* 
 * 后台控制
 *  */
class TemplateController extends AdminbaseController {
	
    //分类管理首页
    public function cate_index(){
        $m=M('Tcate');
    	
    	$total=$m->count();
    	$page = $this->page($total, 10);
    	$list=$m->order('path asc')->limit($page->firstRow,$page->listRows)->select();
    	
    	 
    	$this->assign('page',$page->show('Admin'));
        $this->assign('list',$list);
       
    	$this->display();
    }
    
    //分类添加页面
    public function cate_add(){
        
        $this->display();
    }
    
    //添加类别执行
    public function cate_add_do(){
        
        $name=I('name','');
        $sort=I('sort',0);
        if(empty($name)){
            $this->error('类名不能为空');
        }
        $m=M('Tcate');
        $data=array(
            'name'=>$name,
            'fid'=>0,
            'sort'=>$sort,
            
        );
        //添加分类
        $insert=$m->data($data)->add();
        if($insert<1){
            $this->error('数据错误，请刷新后重试');
        }else{
            $this->success('添加成功');
        } 
    }
    //分类修改页面
    public function cate_edit(){
        $id=I('id',0);
        $m=M('Tcate');
        $info=$m->where('id='.$id)->find();
        $this->assign('info',$info);
        
        $this->display();
    }
    
    //分类修改执行
    public function cate_edit_do(){
        $id=I('id',0);
       
        $name=I('name','');
        $sort=I('sort',0);
        if(empty($name)){
            $this->error('类名不能为空');
        }
        $m=M('Tcate');
        
        $data=array(
            'name'=>$name,
            'sort'=>$sort,
           
        );
        
       
        $row=$m->data($data)->where('id='.$id)->save();
        if($row===1){
            $this->success('保存成功');
            exit;
        }else{
            $this->error('数据错误，请刷新后重试');
        }
        
    }
    
    //分类删除
    public function cate_del(){
        $id=I('id',0);
        //删除分类还要删除子类和所属模板
        $m=M('Tcate');
         
       
        //删除所属商品的图片
        pic_del('Template', array($id),1);
        //删除所属模板
        $map_product['cid']=array('in',$cids);
        M('Template')->where($map_product)->delete();
        //删除类
        $map_cate['id']=$id;
        $m->where($map_cate)->delete();
        $this->success('删除成功');

        
    }
    
    //模板管理首页
    public function index(){
        //有cid则读取cid下的模板，没有则是全部
        $cid=I('cid',0);
        $name=I('name','');
        $m_cate=M('Tcate');
       
        $map=array();
        if($cid!==0){
            //所有子类
            $map['cid']=array('eq',$cid);
        }
        if($name!==''){
            $map['name']=array('like','%'.$name.'%');
           
        }
       
        //统计模板数据分页
        $total=M('Template')->where($map)->count();
        $page = $this->page($total, 10);
        $list=D('Template0View')->where($map)->order('sort desc,id asc')->limit($page->firstRow,$page->listRows)->select();
        
        //所有分类
        $cate_list=$m_cate->order('sort desc')->select();
        //获取价格
        
        $where=array('type'=>1);
        foreach ($list as $k=>$v){
            $where['pid']=$v['id'];
            $list[$k]['price']=M('Price')->order('aid asc')->where($where)->select();
        }
        //获取属性分类
        $attrs=M('Attr')->select();
        $this->assign('attrs',$attrs);
        $this->assign('page',$page->show('Admin'));
        $this->assign('list',$list);
        $this->assign('cate_list',$cate_list);
        $this->assign('pro','模板');
        $this->assign('scid',$cid);
        $this->assign('sname',$name);
        $this->display();
    }
    
    //模板删除
    public function del(){
        $id=I('id',0);
        pic_del('Template',$id);
        //删除模板后还要删除price表和cart表
        $row=M('Template')->where('id='.$id)->delete();
        
        if($row===1){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    //产品批量删除
    public function dels(){
        $ids=I('ids',array());
        $m=M('Template');
        pic_del('Template',$ids);
        $map['id']=array('in',$ids);
        $row=$m->where($map)->delete();
        if($row>=1){
            $this->success('删除成功');
            exit;
        }else{
            $this->error('删除失败');
        }
    }
    //模板详情
    public function info(){
        $id=I('id',0);
        $info=M('Template')->where('id='.$id)->find();
        //所有分类
        $cate_list=M('Tcate')->order('sort desc')->select();
        //查询对应的属性和价格
        $where=array('type'=>2,'pid'=>$id);
        $prices=D('Price0View')->order('aid asc')->where($where)->select();
        $this->assign('prices',$prices);
        $this->assign('cate_list',$cate_list);
        $this->assign('info',$info);
        $this->assign('pro','模板');
       
        $this->display();
        
    }
    
    //模板编辑
    public function edit(){
        $id=I('id',0);
        $name=I('name','');
        $cid=I('cid',0);
        $sort=I('sort',0);
        $price=I('price',array());
        
        if(!preg_match('/^[0-9]{1,2}$/',$sort) ){
            $this->error('排序为0-99的数字'.$sort);
        }
        foreach($price as $v){
            if(!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/',$v) ){
                $this->error('价格为最多2位小数数字');
            }
        }
        if($name=='' || $cid==0){
            $this->error('名称和类别不能为空');
        }
        
        $data=array(
            'name'=>$name,
            'cid'=>$cid,
            'sort'=>$sort,
            'theme'=>I('theme',''),
            'color'=>I('color',''),
            'pic'=>I('pic',''),
            'website'=>I('website',''),
            'desc'=>I('desc',''),
            'content'=>$_POST['content'],
            
        );
        $m=M('Template');
        $m->startTrans();
        $row=$m->where('id='.$id)->save($data);
        if($row===1 || $row===0){
            //添加后添加价格
            $attrs=M('Attr')->select();
            $data=array();
            $m_price=M('Price');
            foreach ($attrs as $k=>$v){
                $data=array(
                    'price'=>$price[$k],
                );
                $where=array(
                    'pid'=>$id,
                    'aid'=>$v['id'],
                    'type'=>1,
                );
                $row=$m_price->where($where)->save($data);
                if($row!==1 && $row!==0){
                    $m->rollback();
                    $this->error('修改失败1');
                }
            }
            
            $m->commit();
            $this->success('修改成功');
            
        }else{
            $m->rollback();
            $this->error('修改失败2');
        }
        
    }
    
    //模板添加页面
    public function add(){
        $cid=I('cid',0);
       
        //所有分类
        $cate_list=M('Tcate')->order('sort desc')->select();
        $attrs=M('Attr')->select();
        $this->assign('attrs',$attrs);
        $this->assign('cate_list',$cate_list);
        $this->assign('scid',$cid);
        $this->assign('pro','模板');
        $this->display();
        
    }
    
    //添加模板执行
    public function add_do(){
       
        $name=I('name','');
        $cid=I('cid',0);
        $sort=I('sort',0);
        $price=I('price',array());
        
        if(!preg_match('/^[0-9]{1,2}$/',$sort) ){
            $this->error('排序为0-99的数字'.$sort);
        }
        foreach($price as $v){
            if(!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/',$v) ){
                $this->error('价格为最多2位小数数字');
            }
        }
        if($name=='' || $cid==0){
            $this->error('名称和类别不能为空');
        }
        
        $data=array(
            'name'=>$name,
            'cid'=>$cid,
            'sort'=>$sort,
             'theme'=>I('theme',''),
            'color'=>I('color',''),
            'pic'=>I('pic',''),
            'website'=>I('website',''),
            'desc'=>I('desc',''),
            'content'=>$_POST['content'],
            
        );
        
        $m=M('Template');
        $m->startTrans();
        $pid=$m->add($data);
        if($pid>0){
            //添加后添加价格
            $attrs=M('Attr')->select();
            $data=array();
            foreach ($attrs as $k=>$v){
                $data[]=array(
                    'pid'=>$pid,
                    'aid'=>$v['id'],
                    'price'=>$price[$k],
                    'type'=>1,
                );
            }
            $row=M('Price')->addAll($data);
            if($row>=1){
                $m->commit();
                $this->success('添加成功');
            }else{
                $m->rollback();
                $this->error('价格添加失败');
            }
            
        }else{
            $m->rollback();
            $this->error('添加失败');
        }
        
    }
    
}