<?php

namespace Admin\Controller;
use Common\Controller\AdminbaseController;
/**
 *
 * @author Innovation
 *
 */
class CompanyController extends AdminbaseController {


    //编辑
    function index(){
        $m=M('Company');
        $total=$m->count();
        $page = $this->page($total, 10);
        $list=$m->order('type asc,name asc')->limit($page->firstRow,$page->listRows)->select();
        $this->assign('page',$page->show('Admin'));
        $this->assign('list',$list);
        $this->display();
    }
    //编辑
    function edit(){
        
        $info=M('Company')->where('id='.I('id',0))->find();
        
        $this->assign('info',$info);
        //不同类别到不同的页面
        $this->display('edit'.$info['type']);
    }
    //编辑
    function doEdit(){
        $data=array(
            'title'=>I('title'),
          
            'content'=>I('content')
        );
        $id=I('id',0);
        $row=M('Company')->data($data)->where('id='.$id)->save();
        if($row===1){
            $this->success('修改成功');
        }elseif($row===0){
            $this->success('未修改');
        }else{
            $this->error('修改失败');
        }
        
    }
   
}

?>