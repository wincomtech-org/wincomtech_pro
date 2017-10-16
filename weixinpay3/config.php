<?php
header("content-type:text/html;charset=utf-8");
ini_set('date.timezone','Asia/Shanghai');
$mysqli=new mysqli('localhost', 'root', '', 'hcpro', '3306');
$mysqli->set_charset('utf8');
$index="http://hcpro.wincomtech.cn/user/order/index";
 