<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Think\Model;
/* 
 * 数据库备份
 *  */
class SQLController extends AdminbaseController {
   
   
	public function backup(){
	   
	    $dname=C('DB_NAME');
	    
	    $m=new model();
	    $mysql="";
	    //得到所有表
	    $tables=$m->query("show tables");
	    
	    foreach($tables as $t){
	        $table=$t['tables_in_'.$dname];
	       
	        $mysql.= "DROP TABLE IF EXISTS `" . $table . "`;\n";  
	        $creat=$m->query("show create table $table");
	        
	        $sql=$creat[0];
	        //创建数据库表结构语句
	        $mysql.=$sql['create table'].";\r\n";
	        
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
	                $mysql.="insert into $table($keys) values \n ($vals)";
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
	   /*  $url=U('restore');
	    echo $url;
	    echo "<a href='".$url."'>点击备份</a>"; */
	    exit();
	    
	}
	
	//数据库还原--未完善
	public function restore()
	{
	    $dname=C('DB_NAME');
	    
	    $m=new model();
	    //指定要恢复的MySQL备份文件路径,请自已修改此路径
	    $fname=getcwd().'/data/'.$dname.date('Ymd').".sql";
	  
	    if(file_exists($fname)){
	        $sql_value="";
	        $cg=0;
	        $sb=0;
	        $sqls=file($fname);
	        foreach($sqls as $sql){
	            $sql_value.=$sql;
	        }
	        $a=explode(";\r\n", $sql_value);
	        
	        $total=count($a)-1;
	        for($i=0;$i<$total;$i++){
	            //执行命令
	            if($m->query($a[$i])){
	                $cg+=1;
	            }else{
	                $sb+=1;
	                $sb_command[$sb]=$a[$i];
	            }
	        }
	        $arr['cg'] = $cg;
	        $arr['total'] = $total;
	        $arr['sb'] = $sb;
	        // 显示错误信息
	        var_dump($arr);
	        
	    }else{
	        echo 'MySQL备份文件不存在，请检查文件路径是否正确';
	    }
	    exit();
	} 
	
    
}