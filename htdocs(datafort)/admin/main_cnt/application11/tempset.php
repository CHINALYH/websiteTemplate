<?php
	define('PATH',str_replace("\\","/",rtrim(dirname(__FILE__),'/').'/'));
	include PATH.'../../../config.inc.php';
	include BASE.'class/database.class.php';
	include BASE.'commons/global.fun.php';
	include BASE.'admin/session.inc.php';
	include BASE.'class/FileSystem.class.php';
	include BASE.'class/FileAction.class.php';
	
	if(!strstr($myfun->session_get('yymklist'),'4') and !$myfun->session_get('root')){
		echo $myfun->out_language("非法登录！！",'');
		exit();
	}
	
	/* 如果接收到了活动字符串，则先调用FileAction对象中的option方法进行处理 */
	if(isset($_POST["action"])){
		$fileaction=new FileAction($_POST["filename"], $_POST["action"]); 
		$fileaction->option(); 
	}
	
	$dirname = $_GET["dirname"]?$_GET["dirname"]:BASE.'ztzl/';
	
	$fs=new FileSystem($dirname,'『专题专栏』模版管理','编辑');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../../resource/css/main_cnt.css" />
<title>专题专栏模版管理</title>
<script type="text/javascript" src="../../../public/js/jquery.min.js"></script>
<script type="text/javascript" src="../../resource/js/layout.js"></script>
<style type="text/css">
p#topmenu{
	height:32px;
	line-height:32px;
	font-size:12px;
	border:1px solid #FFBE7A;
	margin-bottom:5px;
	padding:0 5px;
	background:#FFFCED;
}
p#topmenu a{
	display:inline-block;
	line-height:22px;
	cursor:pointer;
	padding-left:10px;
	background:url(../../../public/images/pages.gif) left top;
}
p#topmenu a span{
	display:inline-block;
	line-height:22px;
	padding-right:10px;
	background:url(../../../public/images/pages.gif) right top;
}
p#btminfo{
	height:30px;
	line-height:30px;
	font-size:13px;
	text-align:right;
	border:1px solid #C0E1E8;
	border-top:none;
	padding-right:10px;
}
</style>
</head>

<body>
  <div id="admin_cnt">
        <ul class="menu_breadcrumbs">
            <li>当前位置：</li>
            <li>应用模块 >> 模版管理</li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>
        <div id="admin_cnt_inner">
            <div id="listcnt">
        <?php
            //$fs->getMenu();                            //调用文件系统中的方法获取菜单
            $fs->fileList();
            echo $fs->getDirInfo();
        ?>
            </div>
        </div>
	</div>
</body>
</html>