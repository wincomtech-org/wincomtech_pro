<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Think\Model;
/* 
 * 后台产品控制
 *  */
class ProductController extends AdminbaseController {
	
    //分类管理首页
    public function cate_index(){
        $m=M('Cate');
    	
    	$total=$m->count();
    	$page = $this->page($total, 10);
    	$list=$m->order('path asc')->limit($page->firstRow,$page->listRows)->select();
    	
    	//无限分类
    	foreach($list as $k=>$v){
    	    //substr_count($text, 'is');统计字符串出现次数，
    	    $len=substr_count($v['path'], '-')-2;
    	    $list[$k]['level']='';
    	    for($i=0;$i<$len;$i++){
    	        $list[$k]['level'].='--|';
    	    }
    	}
    	
    	$this->assign('page',$page->show('Admin'));
        $this->assign('list',$list);
       
    	$this->display();
    }
    
    //分类添加页面
    public function cate_add(){
        $fid=I('id',0);
        
        $list=M('Cate')->where('fid=0')->order('sort desc')->select();
        
        $this->assign('fid',$fid);
        $this->assign('list',$list);
        
        $this->display();
    }
    
    //添加类别执行
    public function cate_add_do(){
        $fid=I('parent',0);
        $name=I('name','');
        $sort=I('sort',0);
        if(empty($name)){
            $this->error('类名不能为空');
        }
        $m=M('Cate');
        $data=array(
            'name'=>$name,
            'fid'=>$fid,
            'sort'=>$sort,
            
        );
        //添加分类
        $insert=$m->data($data)->add();
        if($insert<1){
            $this->error('数据错误，请刷新后重试');
        }
        //给新分类添加path
        if($fid==0){
            $path='-0-'.$insert;
        }else{
            $tmp=$m->where('id='.$fid)->find();
            $path=$tmp['path'].'-'.$insert;
        }
        $row=$m->data(array('path'=>$path))->where('id='.$insert)->save();
        if($row===1){
            $this->success('添加成功');
        }else{
            $this->error('数据错误，请刷新后重试');
        }
    }
    //分类修改页面
    public function cate_edit(){
        $id=I('id',0);
        $m=M('Cate');
        $info=$m->where('id='.$id)->find();
        
        $list=M('Cate')->order('path asc')->select();
        //无线分类
        foreach($list as $k=>$v){
            //substr_count($text, 'is');统计字符串出现次数，
            $len=substr_count($v['path'], '-')-2;
            $list[$k]['level']='';
            for($i=0;$i<$len;$i++){
                $list[$k]['level'].='--|';
            }
        }
        
        $this->assign('list',$list);
        $this->assign('info',$info);
        
        $this->display();
    }
    
    //分类修改执行
    public function cate_edit_do(){
        $id=I('id',0);
        $fid=I('parent',0);
        $name=I('name','');
        $sort=I('sort',0);
        if(empty($name)){
            $this->error('类名不能为空');
        }
        $m=M('Cate');
        
        $data=array(
            'name'=>$name,
            'sort'=>$sort,
           
        );
        
        $info=$m->where('id='.$id)->find();
        if($info['fid']==$fid){
            $row=$m->data($data)->where('id='.$id)->save();
            if($row===1){
                $this->success('保存成功');
                exit;
            }else{
                $this->error('数据错误，请刷新后重试');
            }
        }
        //如果改变了父类则path会变化，其子类path也要变
        $data['fid']=$fid;
        if($fid==0){
            $new_path='-0-'.$id;
        }else{
            $fpath=$m->where('id='.$fid)->find();
            $new_path=$fpath['path'].'-'.$id;
        }
        $data['path']=$new_path;
        //原path
        $old_path=$info['path'].'-';
        //开启事务
        $m->startTrans();
        $row=$m->data($data)->where('id='.$id)->save();
        if($row===1){
            $map['path']=array('like',$old_path.'%');
            //得到所有的子类
            $tmp=$m->where($map)->select();
            $new_path=$new_path.'-';
            foreach ($tmp as $k=>$v){
                //子类的path替换
                $child_path=str_replace($old_path, $new_path, $v['path']);
                $row=$m->data(array('path'=>$child_path))->where('id='.$v['id'])->save();
                if($row!==1){
                    $m->rollback();
                    $this->error('数据错误，请刷新后重试');
                }
            }
            $this->success('保存成功le');
            $m->commit();
            exit;
            
        }else{
            $m->rollback();
            $this->error('数据错误，请刷新后重试');
        }
        
        
        
        
    }
    
    //分类删除
    public function cate_del(){
        $id=I('id',0);
        //删除分类还要删除子类和所属产品
        $m=M('Cate');
        $path=$m->field('path')->where('id='.$id)->find();
        
        $map['path']=array('like',$path['path'].'-%');
        $cates=$m->field('id')->where($map)->select();
        $cids=array($id);
        foreach ($cates as $v){
            $cids[]=$v['id'];
        }
        //删除所有子类
        
        $map_cate['id']=array('in',$cids);
        $m->where($map_cate)->delete();
        
        
        //删除所属产品
        $map_product['cid']=array('in',$cids);
        //删除所属商品的图片 
        pic_del('Product', $cids,1);
        M('Product')->where($map_product)->delete();
        $this->success('删除成功');

        
    }
    
    //产品管理首页
    public function index(){
        //有cid则读取cid下的产品，没有则是全部
        $cid=I('cid',0);
        $name=I('name','');
        $m_cate=M('Cate');
       
        $map=array();
        if($cid!==0){
            //取得所有子类
            $path=$m_cate->field('path')->where('id='.$cid)->find();
            $map_cate['path']=array('like',$path['path'].'-%');
            $cates=$m_cate->field('id')->where($map_cate)->select();
            $cids=array($cid);
            foreach ($cates as $v){
                $cids[]=$v['id'];
            }
            //所有子类
            $map['cid']=array('in',$cids);
           
        }
        if($name!==''){
            $map['name']=array('like','%'.$name.'%');
           
        }
       
        //统计产品数据分页
        $total=M('Product')->where($map)->count();
        $page = $this->page($total, 10);
        $list=D('Product0View')->where($map)->order('sort desc,id asc')->limit($page->firstRow,$page->listRows)->select();
        //获取价格
        $where=array('type'=>2);
        foreach ($list as $k=>$v){
            $where['pid']=$v['id'];
            $list[$k]['price']=M('Price')->order('aid asc')->where($where)->select();
        }
        //获取属性分类
        $attrs=M('Attr')->select();
        //所有分类
        $cate_list=$m_cate->order('path asc')->select();
        foreach($cate_list as $k=>$v){
            //substr_count($text, 'is');统计字符串出现次数，
            $len=substr_count($v['path'], '-')-2;
            $cate_list[$k]['level']='';
            for($i=0;$i<$len;$i++){
                $cate_list[$k]['level'].='--|';
            }
        }
      
        $this->assign('page',$page->show('Admin'));
        $this->assign('list',$list);
        $this->assign('attrs',$attrs);
        $this->assign('cate_list',$cate_list);
        $this->assign('pro','产品');
        $this->assign('scid',$cid);
        $this->assign('sname',$name);
        $this->display();
    }
    
    //产品删除
    public function del(){
        $id=I('id',0);
        $m=M('Product');
        pic_del('Product',$id);
        
        $row=$m->where('id='.$id)->delete();
        if($row===1){
            $this->success('删除成功');
            exit;
        }else{
            $this->error('删除失败');
        }
    }
    //产品批量删除
    public function dels(){
        $ids=I('ids',array());
        $m=M('Product');
        pic_del('Product',$ids);
        $map['id']=array('in',$ids);
        $row=$m->where($map)->delete(); 
        if($row>=1){ 
            $this->success('删除成功');
            exit;
        }else{
            $this->error('删除失败');
        }
    }
    
    //产品详情
    public function info(){
        $id=I('id',0);
        $info=M('Product')->where('id='.$id)->find();
        //所有分类
        $cate_list=M('Cate')->order('path asc')->select();
        $fcate='';
        $list=array();
        foreach($cate_list as $k=>$v){
            if($v['fid']==0){
                $fcate=$v['name'];
            }else{
                $list[]=$v;
                $list[count($list)-1]['name']=$fcate.'--'.$v['name'];
            }
        }
        //查询对应的属性和价格
        $where=array('type'=>2,'pid'=>$id);
        $prices=D('Price0View')->order('aid asc')->where($where)->select();
        $this->assign('prices',$prices);
        $this->assign('cate_list',$list);
        $this->assign('info',$info);
        $this->assign('pro','产品');
       
        $this->display();
        
    }
    
    //产品编辑
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
            'pic'=>I('pic',''),
            'program'=>I('program',''),
            'sql'=>I('sql',''),
            'platform'=>I('platform',''),
            'website'=>I('website',''),
            'desc'=>I('desc',''),
            'content1'=>$_POST['content1'],
            'content2'=>$_POST['content2'],
            'content3'=>$_POST['content3'],
            'content4'=>$_POST['content4'],
            'psw1'=>I('psw1',''),
            'psw2'=>I('psw2',''),
            'link1'=>I('link1',''),
            'link2'=>I('link2',''),
            
        );
        $m=M('Product');
        $m->startTrans();
        $row=$m->where('id='.$id)->save($data);
        if($row===0 || $row===1){
            //添加后添加价格
            $attrs=M('Attr')->select();
            $data=array();
            $m_price=M('Price');
            foreach ($attrs as $k=>$v){
                $data=array( 
                    'price'=>$price[$k], 
                );
                $where=array(
                    'pid'=>$pid,
                    'aid'=>$v['id'], 
                    'type'=>2,
                );
                $row=$m_price->where($where)->save($data);
                if($row!==1 && $row!==0){
                    $m->rollback();
                    $this->error('修改失败');
                }
            }
           
            $m->commit();
            $this->success('修改成功');
           
        }else{
            $m->rollback();
            $this->error('修改失败');
        }
        
    }
    
    //产品添加页面
    public function add(){
        $cid=I('cid',0);
       
        //所有分类
        $cate_list=M('Cate')->order('path asc')->select();
        $fcate='';
        $list=array();
        foreach($cate_list as $k=>$v){
            if($v['fid']==0){
                $fcate=$v['name'];
            }else{
                $list[]=$v;
                $list[count($list)-1]['name']=$fcate.'--'.$v['name'];
            }
        }
        $attrs=M('Attr')->select();
        $this->assign('attrs',$attrs);
        $this->assign('cate_list',$list);
        $this->assign('scid',$cid);
        $this->assign('pro','产品');
        $this->display();
        
    }
    
    //添加产品执行
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
            'pic'=>I('pic',''),
            'program'=>I('program',''),
            'sql'=>I('sql',''),
            'platform'=>I('platform',''),
            'website'=>I('website',''),
            'desc'=>I('desc',''),
            'content1'=>$_POST['content1'],
            'content2'=>$_POST['content2'],
            'content3'=>$_POST['content3'], 
            'content4'=>$_POST['content4'],
            'psw1'=>I('psw1',''),
            'psw2'=>I('psw2',''),
            'link1'=>I('link1',''),
            'link2'=>I('link2',''),
            
        );
        $m=M('Product');
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
                    'type'=>2,
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