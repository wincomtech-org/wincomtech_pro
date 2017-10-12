<?php

namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Think\Model;
/**
 *
 * @author zz
 *
 */
class OrderController extends AdminbaseController {


   //订单首页list
    function index(){
        $m=M('Order');
        //查询条件
        
        $name=I('name','');
        $sid=I('sid',99);
        $price1=I('price1','');
        $price2=I('price2','');
        $time1=I('datetime1','');
        $time2=I('datetime2','');
        $status=array(
            0=>'未支付',
            1=>'已支付',
            2=>'已过期',
        );
        $this->assign('sid',$sid)->assign('status',$status)->assign('name',$name)->assign('price1',$price1)->assign('price2',$price2)
        ->assign('time1',$time1)->assign('time2',$time2);
        if($name!=''){
            $where['name']=array('like','%'.$name.'%');
        }
        if($sid!=99){
            $where['status']=array('eq',$sid);
        }
        if($price1>$price2 ){
            $this->error('错误价格区间');
        }
        if($price2!=0 && $price2>$price1 ){
            $where['tprice']=array('between',array($price1,$price2));
        }
        
        if($time1!=''){
            $time1=strtotime($time1);
            if($time2!=''){
                $time2=strtotime($time2);
                if($time1>$time2){
                    $this->error('错误时间区间');
                }
                $where['create_time']=array('between',array($time1,$time2));
            }else{
                $where['create_time']=array('egt',$time1);
            }
            
        }elseif($time2!=''){
            $time2=strtotime($time2);
            $where['create_time']=array('elt',$time2);
        }
       
        $total=$m->where($where)->count();
        
        $page = $this->page($total,10);
        $list=$m->order('oid desc')->where($where)->limit($page->firstRow,$page->listRows)->select();
       
        
        $this->assign('page',$page->show('Admin'));
        $this->assign('list',$list);
        session('order_where',$where);
        $this->display();
      
      
    }
    //导出excel
    function excel(){
        $m=M('Order');
        //查询条件
        $where=session('order_where','');
        $list=$m->order('oid desc')->where($where)->select();
        
        $this->create_xls($list,'order.xls');
        
        exit();
        
        
    }
    //导出excel的方法
    function create_xls($data,$filename='order.xls'){
        ini_set('max_execution_time', '0');
        vendor('PHPExcel.PHPExcel');
        $filename=str_replace('.xls', '', $filename).'.xls';
        $phpexcel = new \PHPExcel();
        //设置excel属性
        $phpexcel->getProperties()
        ->setCreator("Maarten Balliauw")
        ->setLastModifiedBy("Maarten Balliauw")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Test result file");
        //设置sheet表名
        $phpexcel->getActiveSheet()->setTitle('订单导出表');
       //设置表数据
        //批量直接导出
        //$phpexcel->getActiveSheet()->fromArray($data);
       //设置第一行的标题
        $phpexcel->setActiveSheetIndex(0)->setCellValue('A1', '订单编号')->setCellValue('B1', '订单名称')
        ->setCellValue('C1', '订单价格')->setCellValue('D1', '联系人')->setCellValue('E1', '联系号码')
        ->setCellValue('F1', '下单时间')->setCellValue('G1', '下单ip')->setCellValue('H1', '订单状态')
        ->setCellValue('I1', '用户备注')->setCellValue('J1', '管理员备注');
        //循环设置单元格的数据
        foreach ($data as $k=>$v){
            $num=$k+2;
            $time=date('Y-m-d H:i',$v['create_time']);
            switch ($v['status']){
                case 0:
                    $status='未支付';
                    break;
                case 1:
                    $status='已支付';
                    break;
                case 2:
                    $status=='已过期';
                    break;
                default:$status=='其他状态';
            }
            //设置文本格式
            $str=\PHPExcel_Cell_DataType::TYPE_STRING;
            $phpexcel->setActiveSheetIndex(0)
            ->setCellValueExplicit('A'.$num, $v['oid'],$str)
            ->setCellValue('B'.$num, $v['name'])
            ->setCellValueExplicit('C'.$num, $v['tprice'],$str)
            ->setCellValue('D'.$num, $v['uname'])
            ->setCellValueExplicit('E'.$num, $v['utel'],$str)
            ->setCellValueExplicit('F'.$num, $time,$str)
            ->setCellValueExplicit('G'.$num, $v['ip'],$str)
            ->setCellValue('H'.$num, $status)
            ->setCellValue('I'.$num, $v['desc1'])
            ->setCellValue('J'.$num, $v['desc2']);
        }
        //在浏览器输出
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$filename");
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
       
        $objwriter = \PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
        $objwriter->save('php://output');
        exit;
    }
   public function info(){
       $oid=I('oid',0);
       if(empty($oid)){
           //$_SERVER['HTTP_REFERER']
           $this->error('没有选择订单');
           
       }
       $where=array('oid'=>$oid);
       $list=D('Oinfo0View')->where($where)->select();
       /*D('Order0View')关联出错？2017-10-12  */
       //$info=D('Order0View')->where($where)->find();
       $info=M('Order')->where($where)->find();
       $user=M('Users')->field('user_nicename')->where('id='.$info['uid'])->find();
       $info['user_nicename']=$user['user_nicename'];
       switch ($info['status']){
           case 0:
               $status='未支付';
               break;
           case 1:
               $status='已支付';
               break;
           case 2:
               $status=='已过期';
               break;
           default:$status=='其他状态';
       }
       
       $this->assign('status',$status);
       foreach ($list as $k=>$v){
           if($v['type']==1){
               $list[$k]['link']=U('Portal/Template/detail',array('id'=>$v['pid'],'price_id'=>$v['price_id']));
           }else{
               $list[$k]['link']=U('Portal/Product/detail',array('id'=>$v['pid'],'price_id'=>$v['price_id']));
           }
       }
      
       $this->assign('info',$info);
       $this->assign('list',$list);
       
       $this->display();
   }
   
   //保存备注
   public function edit(){
       $oid=I('oid',0);
       $desc2=I('desc2','');
       $row=M('Order')->where(array('oid'=>$oid))->save(array('desc2'=>$desc2));
       if($row===1 || $row===0){
           $this->success('保存成功');
       }else{
           $this->error('保存失败');
       }
   }
    
}

