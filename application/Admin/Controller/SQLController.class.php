<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Think\Model;
/* 
 * 数据库备份
 *  */
class SQLController extends AdminbaseController {
	public function backup(){
	   exit('暂时停用');
	    $dname=C('DB_NAME');
	    
	    $m=new model();
	    $mysql= "";
	    //得到所有表
	    $tables=$m->query("show tables");
	    
	    foreach($tables as $t){
	        $table=$t['tables_in_'.$dname];
	        $creat=$m->query("show create table $table");
	        
	        $sql=$creat[0];
	        //创建数据库表结构语句
	        $mysql.=$sql['create table'].";\r\n";
	        $insert=$m->query("select * from $table");
	        //组装insert语句
	        foreach($insert as $k=>$data){
	            if($k==0){
	                $keys=array_keys($data);
	                $keys=array_map('addslashes',$keys);
	                $keys=join(',',$keys);
	                $keys="".$keys."";
	                $vals=array_values($data);
	                $vals=array_map('addslashes',$vals);
	                $vals=join("','",$vals);
	                $vals="'".$vals."'";
	                $mysql.="insert into $table($keys) values($vals)";
	            }else{
	                
	                $vals=array_values($data);
	                $vals=array_map('addslashes',$vals);
	                $vals=join("','",$vals);
	                $vals="'".$vals."'";
	                $mysql.=",\r\n($vals)";
	            }
	        }
	        if(count($insert)>=1){
	            $mysql.=";\r\n";
	        }
	        
	    }
	    //存储在data文件夹下
	    $filename=getcwd().'/data/'.$dname.date('Ymd').".sql";
        //$filename="C://".$dname.date('Ymd-His').".sql";
	    $fp = fopen($filename,'w');
	    fputs($fp,$mysql);
	    fclose($fp);
	    echo "数据备份成功,生成备份文件".$filename;
	    exit();
	    
	}
    
}