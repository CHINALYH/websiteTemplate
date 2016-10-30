<?php
	define('PATH',str_replace("\\","/",rtrim(dirname(__FILE__),'/').'/'));
	
	include PATH.'../../../config.inc.php';
	
	include BASE.'class/database.class.php';
	
	include BASE.'commons/global.fun.php';
	
	include BASE.'admin/session.inc.php';
	
	if(empty($_POST)){
		echo "异步数据传输错误！！";
	}else{
		$mid = (int)$_POST['mid'];
		$title = $myfun->_mysql_string($_POST['title']);
		$ctn = $myfun->_mysql_string($_POST['ctn']);
		$istop = (int)$_POST['istop'];
		$iswhere = $myfun->_mysql_string($_POST['iswhere']);
		$ispass = (int)$_POST['ispass'];
		$addtime = strtotime($_POST['addtime']);
		$times = (int)$_POST['times'];
		$checkuser = $myfun->_mysql_string($_POST['checkuser']);
		$adduser = $myfun->session_get('username');
		$adduid = $myfun->session_get('id');
		
		$sql = "insert into `photo_info` (mid,title,content,istop,iswhere,ispass,adduser,addtime,times,adduid,checkuser) values ('$mid','$title','$ctn',$istop,'$iswhere',$ispass,'$adduser','$addtime',$times,$adduid,'$checkuser')";
		$result = $mysqli->query($sql);
		if($result){
			$myfun->session_set('photoaid',$mysqli->insert_id);
			echo 0;
		}else{
			echo '添加失败！';
		}
	}
?>