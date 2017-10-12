<?php

namespace Admin\Controller;
use Common\Controller\AdminbaseController;
/**
 *
 * @author Innovation
 *
 */
class AttrController extends AdminbaseController {

/* 
    //编辑
    function index(){
        $m=M('Attr');
        $total=$m->count();
        $page = $this->page($total, 10);
        $list=$m->order('id asc')->limit($page->firstRow,$page->listRows)->select();
        $this->assign('page',$page->show('Admin'));
        $this->assign('list',$list);
        $this->display();
    }
    //编辑
    function edit(){
        
        $info=M('Attr')->where('id='.I('id',0))->find();
        
        $this->assign('info',$info);
        $this->display();
    }
    //编辑
    function doEdit(){
        $data=array(
            'name'=>I('name',''),
          
        );
        $id=I('id',0);
        $row=M('Attr')->data($data)->where('id='.$id)->save();
        if($row===1){
            $this->success('修改成功');
        }elseif($row===0){
            $this->success('未修改');
        }else{
            $this->error('修改失败');
        }
        
    }
    //add
    function add(){
        
        $this->display();
    }
    //增加
    function doAdd(){
        $data=array(
            'name'=>I('name',''),
            
        );
       
        $row=M('Attr')->add($data);
        if($row>=1){
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
        
    }
    
    //删除
    function del(){
        $data=array(
            'id'=>I('id',0),
            
        );
        
        $row=M('Attr')->where($data)->delete();
        if($row===1){
            $this->success('已删除');
        }else{
            $this->error('删除失败');
        }
        
    }
    */
}

?>