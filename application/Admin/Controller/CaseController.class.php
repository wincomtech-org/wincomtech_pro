<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Think\Model;
/* 
 * 后台控制
 *  */
class CaseController extends AdminbaseController {
	
    //分类管理首页
    public function cate_index(){
        $m=M('Ccate');
    	
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
        $m=M('Ccate');
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
        $m=M('Ccate');
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
        $m=M('Ccate');
        
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
        //删除分类还要删除子类和所属案例
       
        //删除所属商品的图片
        pic_del('Cases', array($id),1);
        //删除所属案例
        
        M('Cases')->where('cid='.$id)->delete();
        //删除类
        M('Ccate')->where('id='.$id)->delete();
        $this->success('删除成功');

        
    }
    
    //案例管理首页
    public function index(){
        //有cid则读取cid下的案例，没有则是全部
        $cid=I('cid',0);
        $name=I('name','');
        $m_cate=M('Ccate');
       
        $map=array();
        if($cid!==0){
             
            //所有子类
            $map['cid']=array('eq',$cid);
           
        }
        if($name!==''){
            $map['name']=array('like','%'.$name.'%');
           
        }
       
        //统计案例数据分页
        $total=M('Cases')->where($map)->count();
        $page = $this->page($total, 10);
        $list=D('Case0View')->where($map)->order('sort desc,id asc')->limit($page->firstRow,$page->listRows)->select();
        
        //所有分类
        $cate_list=$m_cate->order('sort desc')->select();
         
        $this->assign('page',$page->show('Admin'));
        $this->assign('list',$list);
        $this->assign('cate_list',$cate_list);
        $this->assign('pro','案例');
        $this->assign('scid',$cid);
        $this->assign('sname',$name);
        $this->display();
    }
    
    //案例删除
    public function del(){
        $id=I('id',0);
        pic_del('Cases',$id);
        $row=M('Cases')->where('id='.$id)->delete();
        if($row===1){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    //产品批量删除
    public function dels(){
        $ids=I('ids',array());
        $m=M('Cases');
        pic_del('Cases',$ids);
        $map['id']=array('in',$ids);
        $row=$m->where($map)->delete();
        if($row>=1){
            $this->success('删除成功');
            exit;
        }else{
            $this->error('删除失败');
        }
    }
    //案例详情
    public function info(){
        $id=I('id',0);
        $info=M('Cases')->where('id='.$id)->find();
        //所有分类
        $cate_list=M('Ccate')->order('path asc')->select();
        
        $this->assign('cate_list',$cate_list);
        $this->assign('info',$info);
        $this->assign('pro','案例');
       
        $this->display();
        
    }
    
    //案例编辑
    public function edit(){
        $id=I('id',0);
        $name=I('name','');
        $cid=I('cid',0);
        $sort=I('sort',0);
        $price=I('price',0);
        
        if(!preg_match('/^[0-9]{1,2}$/',$sort) ){
            $this->error('排序为0-99的数字'.$sort);
        }
        if(!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/',$price) ){
            $this->error('价格为最多2位小数数字');
        }
        if($name=='' || $cid==0){
            $this->error('名称和类别不能为空');
        }
        
        $data=array(
            'name'=>$name,
            'cid'=>$cid,
            'sort'=>$sort,
            'price'=>$price,
            'pic'=>I('pic',''),
            'website'=>I('website',''),
            'desc'=>I('desc',''),
            'content'=>$_POST['content'],
          
            
        );
        $row=M('Cases')->where('id='.$id)->save($data);
        if($row===1){
            $this->success('保存成功');
        }else{
            $this->error('保存失败');
        }
        
    }
    
    //案例添加页面
    public function add(){
        $cid=I('cid',0);
       
        //所有分类
        $cate_list=M('Ccate')->order('path asc')->select();
        
        $this->assign('cate_list',$cate_list);
        $this->assign('scid',$cid);
        $this->assign('pro','案例');
        $this->display();
        
    }
    
    //添加案例执行
    public function add_do(){
       
        $name=I('name','');
        $cid=I('cid',0);
        $sort=I('sort',0);
        $price=I('price',0);
        
        if(!preg_match('/^[0-9]{1,2}$/',$sort) ){
            $this->error('排序为0-99的数字'.$sort);
        }
        if(!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/',$price) ){
            $this->error('价格为最多2位小数数字');
        }
        if($name=='' || $cid==0){
            $this->error('名称和类别不能为空');
        }
        
        $data=array(
            'name'=>$name,
            'cid'=>$cid,
            'sort'=>$sort,
            'price'=>$price,
            'pic'=>I('pic',''),
            'website'=>I('website',''),
            'desc'=>I('desc',''),
            'content'=>$_POST['content'],
            
        );
        $row=M('Cases')->add($data);
        if($row>0){
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
        
    }
    
}