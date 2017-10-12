<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Think\Model;
/* 
 * 后台控制
 *  */
class NewsController extends AdminbaseController {
	
    //分类管理首页
    public function cate_index(){
        $m=M('Ncate');
    	
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
        
        $list=M('Ncate')->where('fid=0')->order('sort desc')->select();
        
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
        $m=M('Ncate');
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
        $m=M('Ncate');
        $info=$m->where('id='.$id)->find();
        
        $list=M('Ncate')->order('path asc')->select();
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
        $m=M('Ncate');
        
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
            $this->success('保存成功');
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
        //删除分类还要删除子类和所属新闻
        $m=M('Ncate');
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
        //删除所属商品的图片
        pic_del('News', $cids,1);
        //删除所属新闻
        $map_product['cid']=array('in',$cids);
        M('News')->where($map_product)->delete();
        $this->success('删除成功');

        
    }
    
    //新闻管理首页
    public function index(){
        //有cid则读取cid下的新闻，没有则是全部
        $cid=I('cid',0);
        $name=I('name','');
        $m_cate=M('Ncate');
       
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
       
        //统计新闻数据分页
        $total=M('News')->where($map)->count();
        $page = $this->page($total, 10);
        $list=D('News0View')->where($map)->order('sort desc,id asc')->limit($page->firstRow,$page->listRows)->select();
        
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
        $this->assign('cate_list',$cate_list);
        $this->assign('pro','新闻');
        $this->assign('scid',$cid);
        $this->assign('sname',$name);
        $this->display();
    }
    
    //新闻删除
    public function del(){
        $id=I('id',0);
        pic_del('News',$id);
        $row=M('News')->where('id='.$id)->delete();
        if($row===1){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    //产品批量删除
    public function dels(){
        $ids=I('ids',array());
        $m=M('News');
        pic_del('News',$ids);
        $map['id']=array('in',$ids);
        $row=$m->where($map)->delete();
        if($row>=1){
            $this->success('删除成功');
            exit;
        }else{
            $this->error('删除失败');
        }
    }
    //新闻详情
    public function info(){
        $id=I('id',0);
        $info=M('News')->where('id='.$id)->find();
        //所有分类
        $cate_list=M('Ncate')->order('path asc')->select();
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
        
        $this->assign('cate_list',$list);
        $this->assign('info',$info);
        $this->assign('pro','新闻');
       
        $this->display();
        
    }
    
    //新闻编辑
    public function edit(){
        $id=I('id',0);
        $name=I('name','');
        $cid=I('cid',0);
        $sort=I('sort',0);
        $author=I('author','');
        
        if(!preg_match('/^[0-9]{1,2}$/',$sort) ){
            $this->error('排序为0-99的数字'.$sort);
        }
         
        if($name=='' || $cid==0 || $author==''){
            $this->error('名称,类别,作者不能为空');
        }
        
        $data=array(
            'name'=>$name,
            'cid'=>$cid,
            'sort'=>$sort,
            'author'=>$author,
            'pic'=>I('pic',''),
            'time'=>time(),
            'desc'=>I('desc',''),
            'content'=>$_POST['content'],
            
        );
        $row=M('News')->where('id='.$id)->save($data);
        if($row===1){
            $this->success('保存成功');
        }else{
            $this->error('保存失败');
        }
        
    }
    
    //新闻添加页面
    public function add(){
        $cid=I('cid',0);
       
        //所有分类
        $cate_list=M('Ncate')->order('path asc')->select();
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
        $this->assign('cate_list',$list);
        $this->assign('scid',$cid);
        $this->assign('pro','新闻');
        $this->display();
        
    }
    
    //添加新闻执行
    public function add_do(){
       
        $name=I('name','');
        $cid=I('cid',0);
        $sort=I('sort',0);
        
        $author=I('author','');
        
        if(!preg_match('/^[0-9]{1,2}$/',$sort) ){
            $this->error('排序为0-99的数字'.$sort);
        }
        
        if($name=='' || $cid==0 || $author==''){
            $this->error('名称,类别,作者不能为空');
        }
        $pic=I('pic','');
        if($pic==''){
            $pic='admin/20170921/59c3712d0d9ce.png';
        }
        $data=array(
            'name'=>$name,
            'cid'=>$cid,
            'sort'=>$sort,
           
            'pic'=>$pic,
            'author'=>$author,
            'time'=>time(),
            'desc'=>I('desc',''),
            'content'=>$_POST['content'],
            'count'=>0,
            
        );
        $row=M('News')->add($data);
        if($row>0){
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
        
    }
    
}