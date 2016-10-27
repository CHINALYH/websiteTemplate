<?php
	define('PATH',str_replace("\\","/",rtrim(dirname(__FILE__),'/').'/'));
	
	include PATH.'../../../config.inc.php';
	
	include BASE.'class/database.class.php';
	
	include BASE.'commons/global.fun.php';
	
	include BASE.'admin/session.inc.php';
	
	if(empty($_POST)){
		echo "异步数据传输错误！！";
	}else{
		$atcStr = trim($_POST['atcStr']);
		$mid = (int)trim($_POST['mid']);
		if(!empty($atcStr) and !empty($mid)){
			/*获取操作日志信息*/
			$row = $mysqli->getField($mid,'m_name','`menu_info`');
			$m_name = $row['m_name'];
			$uid = $myfun->session_get('id');
			$addtime = time();
			$ip = $myfun->getIP();
				
			$sql = "UPDATE `content_info` SET mid='$mid' WHERE id in($atcStr)";
			$result = $mysqli->query($sql);
			if($result){
				$handle = '<span class="blue">[ 文章转移 ]</span> <span class="green">[ 成功 ]</span> 将id为('.$atcStr.')的文章，转移到 <span class="green">[ '.$m_name.' ]</span> 栏目中';
				$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
				$query = $mysqli->query($insert);
				echo 0;
			}else{
				$handle = '<span class="blue">[ 文章转移 ]</span> <span class="red">[ 失败 ]</span> 将id为('.$atcStr.')的文章，转移到 <span class="green">[ '.$m_name.' ]</span> 栏目中';
				$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
				$query = $mysqli->query($insert);
				echo '移动失败！！';
			}
		}else{
			echo '参数传递不能为空，请重新操作！！';	
		}
	}
?>