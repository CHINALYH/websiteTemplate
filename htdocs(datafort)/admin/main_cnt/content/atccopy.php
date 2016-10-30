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
			$sql = "select * from `content_info` where id in($atcStr)";
			$result = $mysqli->query($sql);
			$sql_insert = '';
			while($row = $result->fetch_assoc()){
				$arr = $myfun->_mysql_string($row);
				$title_first = $arr['title_first'];
				$title_second = $arr['title_second'];
				$title_color = $arr['title_color'];
				$title_bold = $arr['title_bold'];
				$more_menu = $arr['more_menu'];
				$link_url = $arr['link_url'];
				$atc_cnt = $arr['atc_cnt'];
				$summary = $arr['summary'];
				$atc_pic = $arr['atc_pic'];
				$telephone = $arr['telephone'];
				$istop = $arr['istop'];
				$topctn = $arr['topctn'];
				$focus = $arr['focus'];
				$ispass = $arr['ispass'];
				$tag = $arr['tag'];
				$randnum = $arr['randnum'];
				$message = $arr['message'];
				$is_where = $arr['is_where'];
				$is_delelte = $arr['is_delelte'];
				$is_sync = $arr['is_sync'];
				$is_tg = $arr['is_tg'];
				$checkuser = $arr['checkuser'];
				$adduser = $arr['adduser'];
				$addtime = $arr['addtime'];
				$times = $arr['times'];
				$adduid = $arr['adduid'];
				$ip = $arr['ip'];
				
				$sql_insert .= "insert into `content_info` (
				mid, title_first, title_second, title_color, title_bold, more_menu, link_url, atc_cnt, summary,
				atc_pic, telephone, istop, topctn, focus, ispass, tag, randnum, message, is_where, is_delelte,
				is_sync, is_tg, checkuser, adduser, addtime, times, adduid, ip) 
				values
			   ('$mid','$title_first','$title_second','$title_color','$title_bold','$more_menu','$link_url',
		       '$atc_cnt','$summary','$atc_pic','$telephone','$istop','$topctn','$focus','$ispass','$tag',
			   '$randnum','$message','$is_where','$is_delelte','$is_sync','$is_tg','$checkuser',
			   '$adduser','$addtime','$times','$adduid','$ip');";
			}

			/*获取操作日志信息*/
			$row = $mysqli->getField($mid,'m_name','`menu_info`');
			$m_name = $row['m_name'];
			$optionid = $myfun->session_get('id');
			$addtime = time();
			$ip = $myfun->getIP();

			$result = $mysqli->multi_query($sql_insert);
			//------------//
			$mysqli = new Database();
			if($result){
				$handle = '<span class="blue">[ 文章复制 ]</span> <span class="green">[ 成功 ]</span> 将id为('.$atcStr.')的文章，复制到 <span class="green">[ '.$m_name.' ]</span> 栏目中';
				$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$optionid','$handle','$addtime','$ip')";
				$query = $mysqli->query($insert);
				echo 0;
			}else{
				$handle = '<span class="blue">[ 文章复制 ]</span> <span class="red">[ 失败 ]</span> 将id为('.$atcStr.')的文章，复制到 <span class="green">[ '.$m_name.' ]</span> 栏目中';
				$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$optionid','$handle','$addtime','$ip')";
				$query = $mysqli->query($insert);
				echo '复制失败！！';
			}
		}else{
			echo '参数传递不能为空，请重新操作！！';	
		}
	}
?>