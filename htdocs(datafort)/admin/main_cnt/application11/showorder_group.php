<?php
	define('PATH',str_replace("\\","/",rtrim(dirname(__FILE__),'/').'/'));
	
	include PATH.'../../../config.inc.php';
	
	include BASE.'class/database.class.php';
	
	include BASE.'commons/global.fun.php';
	
	include BASE.'admin/session.inc.php';
	
	if(!strstr($myfun->session_get('yymklist'),'1') and !$myfun->session_get('root')){
		echo $myfun->out_language("非法登录！！",'');
		exit();
	}
	
	if(empty($_POST)){
		echo "异步数据传输错误！！";
	}else{
		$act = trim($_POST['act']);
		$id = (int)trim($_POST['id']);
		$num = (int)trim($_POST['num']);
		$showorder = (int)trim($_POST['showorder']);
		
		if(isset($act) and !empty($id) and !empty($num) and !empty($showorder)){
			if($act == 0){ //上移
				$sql="SELECT id,show_order FROM `link_group` WHERE show_order<'$showorder' ORDER BY show_order DESC LIMIT 0, $num";
				$result = $mysqli->query($sql);
				$rownum = $result->num_rows;
				if($rownum != $num){
					echo "上升位置过大，请重新填写!";
					exit();
				}
				$uidArr[] = $id;
				$showorderArr[] = $showorder;
				
				while($row = $result->fetch_assoc()){
					$uidArr[] = $row["id"];                  //排序序号数组
					$showorderArr[] = $row["show_order"];	 //ID数组
				}
				
				$sql = "UPDATE `link_group` SET show_order=".$showorderArr[$num]." WHERE id=".$id;
				$mysqli->query($sql);
				
				for($i=0;$i<($num);$i++){
					$sql = "UPDATE `link_group` SET show_order=".$showorderArr[$i]." WHERE id=".$uidArr[$i+1]."";
					$result = $mysqli->query($sql);
				}
				if($result){
					echo '0';//上移成功！！
				}else{
					echo '上移失败！！';
				}
			}elseif($act == 1){ //下移
				$sql="SELECT id,show_order FROM `link_group` WHERE show_order>'$showorder' ORDER BY show_order ASC LIMIT 0, $num";
				$result = $mysqli->query($sql);
				$rownum = $result->num_rows;
				if($rownum != $num){
					echo "下降位置过大，请重新填写!";
					exit();
				}
				$uidArr[] = $id;
				$showorderArr[] = $showorder;
				
				while($row = $result->fetch_assoc()){
					$uidArr[] = $row["id"];                  //排序序号数组
					$showorderArr[] = $row["show_order"];	 //ID数组
				}
				
				$sql = "UPDATE `link_group` SET show_order=".$showorderArr[$num]." WHERE id=".$id;
				$mysqli->query($sql);
				
				for($i=0;$i<($num);$i++){
					$sql = "UPDATE `link_group` SET show_order=".$showorderArr[$i]." WHERE id=".$uidArr[$i+1]."";
					$result = $mysqli->query($sql);
				}
				if($result){
					echo 1;//下移成功！！
				}else{
					echo '下移失败！！';
				}
			}else{
				echo "上升下降操作有误！！";
			}
		}else{
			echo "非法传输数据！！";	
		}
	}
?>