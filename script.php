<?php
/**
 * Created by PhpStorm.
 * User: mf
 * Date: 2017/7/3
 * Time: 16:46
 */
#!/usr/local/php/bin/php -q
session_start();
define( "DB_PASSWORD", "qiancheng1006" ); // 服务器登陆密码
define( "DB_NAME", "menhai" ); // 数据库名称
define( "DB_USER", "cdb_outerroot" ); // 服务器登陆用户
define( "DB_HOST", "58d4cd194c86e.sh.cdb.myqcloud.com" ); // 服务器IP地址或名称
define( "DB_PORT", "3905" ); // 服务器登陆端口号
define( "DB_CHARSET", "utf8" ); // 数据库默认语言
define( "DB_DEBUG", false ); // 是否设置为调试模式
define( "DB_CLOSE", false ); // 是否主动关闭数据库连接
$con=mysqli_connect(DB_HOST.":".DB_PORT,DB_USER,DB_PASSWORD);
mysqli_query ( "SET character_set_connection = ".DB_CHARSET.",
character_set_results = ".DB_CHARSET.", character_set_client =
binary");
if (!$con) die("Mysql Error:".mysqli_error());
mysqli_select_db(DB_NAME,$con);
//$dates=date()
$sqll="insert into test(NAME,name)
values('mf')";
$resl=mysqli_query($sqll);
echo "成功...";