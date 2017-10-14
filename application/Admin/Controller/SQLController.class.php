<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Think\Model;
/* 
 * 数据库备份
 *  */
class SQLController extends AdminbaseController {
   
   private $end=";;--";
   private $line="\n";
   private $dir="/data/";
   //文件列表
   public function index(){
       $dir=getcwd().($this->dir);
       $files=scandir($dir);
       
       foreach($files as $v){
           
           if(is_file($dir.$v) && substr($v,strrpos($v, '.'))=='.sqlsql'){
               
               $list[]=$v;
           }
           
       }
       rsort($list);
       $this->assign('list',$list);
       $this->display();
       exit();
       
   }
	public function add(){
	    
	    $dname=C('DB_NAME');
	    
	    $m=new model();
	    $mysql="";
	    //结束符
	    $line=$this->line;
	    $end=($this->end).$line;
	    $dir=getcwd().($this->dir);
	    //得到所有表
	    $tables=$m->query("show tables");
	    
	    foreach($tables as $t){
	        $table=$t['tables_in_'.$dname];
	       
	        $mysql.= "DROP TABLE IF EXISTS `" . $table . "`".$end;  
	        $creat=$m->query("show create table $table");
	        
	        $sql=$creat[0];
	        //创建数据库表结构语句
	        $mysql.=$sql['create table'].$end;
	        
	        $insert=$m->query("select * from $table");
	        //组装insert语句
	       
	        foreach($insert as $k=>$data){
	            if($k==0){
	               
	                //获取数组的键名数组
	                $keys=array_keys($data);
	                 
	                //addslashes — 使用反斜线引用字符串,为了清除`的影响
	                $keys=array_map('addslashes',$keys);
	                 
	                $keys=join('`,`',$keys);
	                $keys="`".$keys."`";
	                $vals=array_values($data);
	                $vals=array_map('addslashes',$vals);
	                $vals=join("','",$vals);
	                $vals="'".$vals."'";
	                $mysql.="insert into $table($keys) values $line ($vals)";
	            }else{
	                
	                $vals=array_values($data);
	                $vals=array_map('addslashes',$vals);
	                $vals=join("','",$vals);
	                $vals="'".$vals."'";
	                $mysql.=",$line($vals)";
	            }
	        }
	        if(count($insert)>=1){
	            $mysql.=$end;
	        }
	        
	    }
	    //存储在data文件夹下
	    $filename=$dir.$dname.date('Ymd-His').".sqlsql";
        
	    $fp = fopen($filename,'wb');
	    if(!$fp){
	        $this->error('文件打开失败',U('index'));
	        
	        exit;
	    }
	    fputs($fp,$mysql);
	    fclose($fp);
	    $this->success('数据备份成功',U('index'));
	    
	    exit();
	    
	}
	
	
	//数据库还原 
	public function restore()
	{
	     
	    $filename=I('id','');
	    $dname=C('DB_NAME');
	    $m=new \mysqli(C('DB_HOST'), C('DB_USER'), C('DB_PWD'), $dname, C('DB_PORT'));
	    $m->set_charset('utf8');
	    
	    //指定要恢复的MySQL备份文件路径,请自已修改此路径
	    $dir=getcwd().($this->dir);
	    $fname=$dir.$filename;
	  
	    if(file_exists($fname)){
	   
	        $sql_value="";
	        $sqls=file($fname);
	        
	        foreach($sqls as $sql){
	            $sql_value.=$sql;
	        }
	        
	        $a=explode(($this->end).($this->line), $sql_value); 
	       
	        $total=count($a)-1;
	        for($i=0;$i<$total;$i++){ 
	            //执行命令
	            $m->query($a[$i]); 
	        }
	      
	        $this->success('数据已还原',U('index'));
	    }else{
	        $this->error('MySQL备份文件不存在，请检查文件路径是否正确',U('index'));
	    }
	    exit();
	} 
	//删除备份
	public function del(){
	    $file=I('id');
	    if(unlink(getcwd().($this->dir).$file)===true){
	        $this->success('备份已删除');
	    }else{
	        $this->error('删除失败');
	    } 
	    
	}
	//删除备份
	public function dels(){
	    $files=I('ids');
	    $dir=getcwd().($this->dir);
	    foreach($files as $file){
	        if(unlink($dir.$file)===false){
	            $this->error('删除失败'); 
	        } 
	    }
	    $this->success('备份已删除');
	    
	    
	}
    
}