<?php

	define('PATH',str_replace("\\","/",rtrim(dirname(__FILE__),'/').'/'));
	
	include PATH.'../../../config.inc.php';
	
	include BASE.'class/database.class.php';
	
	include BASE.'commons/global.fun.php';
	
	include BASE.'admin/session.inc.php';
	
	if(!strstr($myfun->session_get('yymklist'),'2') and !$myfun->session_get('root')){
		echo $myfun->out_language("非法登录！！",'');
		exit();
	}
	
	//删除链接分类
	if($_SERVER['REQUEST_METHOD'] == 'GET' and isset($_GET['act']) and $_GET['act'] == 'del'){
		$gid = (int)$_GET['gid'];
		if(!!$gid){
			$sql = "select * from `link_info` where gid = '$gid'";
			$result = $mysqli->query($sql);
			$num = $result->num_rows;
			if($num){
				echo $myfun->out_language('无法删除，请先将该类别下所有链接删除！！');
			}else{
				/*获取操作日志信息*/
				$uid = $myfun->session_get('id');
				$addtime = time();
				$ip = $myfun->getIP();
				$row = $mysqli->getField($gid, 'group_name', '`link_group`');
				$group_name = $row['group_name'];
				
				$sql = "delete from `link_group` where id =".$gid;
				$result = $mysqli->query($sql);
				if(!$result){
					$handle = $myfun->_mysql_string('<span class="blue">[ 删除链接分类 ]</span> <span class="red">[ 失败 ]</span> '.$group_name);
					//记录操作日志（失败）
					$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
					$mysqli->query($insert);
					
					echo "删除失败！！！";
					echo "ERROR:".$mysqli->errno."|".$mysqli->error;
					exit;
				}else{
					$handle = $myfun->_mysql_string('<span class="blue">[ 删除链接分类 ]</span> <span class="green">[ 成功 ]</span> '.$group_name);
					//记录操作日志（成功）
					$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
					$mysqli->query($insert);
				}
			}
		}else{
			echo $myfun->out_language('非法删除，删除失败！！');
		}
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>分类列表</title>
<link rel="stylesheet" type="text/css" href="../../resource/css/main_cnt.css" />
<script type="text/javascript" src="../../../public/js/jquery.min.js"></script>
<script type="text/javascript" src="../../resource/js/layout.js"></script>
<script type="text/javascript" src="../../resource/js/check.js"></script>

<link rel="stylesheet" type="text/css" href="../../../public/plugins/jBox/skinblue/jbox.css" />
<script type="text/javascript" src="../../../public/plugins/jBox/jquery.jBox.min.js"></script>
<script type="text/javascript" src="../../../public/plugins/jBox/jquery.jBox-zh-CN.js"></script>
<script type="text/javascript" src="../../resource/js/jbox.js"></script>

<script type='text/javascript'>
<?php if($result){?>
	$(function(){
		$.jBox.prompt('删除成功！！', '提示', 'success', { closed: function () { history.go(-1); } });
	});
<?php }?>
</script>
</head>

<body>
    <div id="admin_cnt">
        <ul class="menu_breadcrumbs">
            <li>当前位置：</li>
            <li>应用模块 >> 友情链接 >>  分类列表</li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>
        <ul class="main_top_menu">
        	<li class="top_menu"><a href="linktypelist.php" class="active">分类列表</a></li>
        	<li class="top_menu"><a href="linktypeset.php">添加分类</a></li>
        </ul>
    	<div id="admin_cnt_inner">
			<div id="addcnt">
                <table class="table_all">
                  <caption>分类列表</caption>
                  <thead>
                  	<tr>
                    	<th width="8%" class="style_th">序号</th>
                    	<th width="25%" class="style_th">分类名称</th>
                        <th width="18%" class="style_th">分类类别</th>
                        <th width="18%" class="style_th">启用状态</th>
                        <th width="15%" class="style_th">排序</th>
                        <th width="16%" class="style_th">操作</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                  	$sql = "select * from `link_group` order by show_order asc";
					$result = $mysqli->query($sql);
					$i=0;
					while($row = $result->fetch_assoc()){
						$i++;
				  ?>
                    <tr>
                      <td class="style_td">
                      	<?php echo $i;?>
                      </td>
                      <td class="style_td">
                      	<?php echo $row['group_name']?>
                      </td>
                       <td class="style_td">
                     	<?php
							$group_type = ($row['group_type'] == 2)?'图片链接':'文本链接';
							echo $group_type;
						?>
                      </td>
                      <td class="style_td">
                      	<?php
                        	if($row['ishdn'] == 0){
								echo '<span class="red">关闭</span>';
							}elseif($row['ishdn'] == 1){
								echo '<span>开启</span>';
							}else{
								echo '<span>未知</span>';
							}
						?>
                      </td>
                      <td class="style_td">
                     	<img src="../../resource/images/rise.gif" width="15" height="15" onclick="updown(0,<?php echo $row['id']?>,'',<?php echo $row['show_order']?>,'showorder_group.php');" />&nbsp;
                        <img src="../../resource/images/fall.gif" width="15" height="15" onclick="updown(1,<?php echo $row['id']?>,'',<?php echo $row['show_order']?>,'showorder_group.php');" />
                      </td>
                      <td class="style_td">
                      	
                        <a href="linktypeeditor.php?gid=<?php echo $row['id'];?>">编辑</a>&nbsp;
                        <a href="linktypelist.php?act=del&gid=<?php echo $row['id'];?>" onclick="return confirm('确认删除该分类?')">删除</a>&nbsp;
                      <?php if($row['group_type'] == 1){?>
                        <a href="addlink.php?gid=<?php echo $row['id'];?>"><span class="red">添加</span></a>
                      <?php }else{?>
                        <a href="addpiclink.php?gid=<?php echo $row['id'];?>"><span class="red">添加</span></a>
                      <?php }?>
                      </td>
                    </tr>
                  <?php
					}
				  ?>
                  </tbody>
                  <tfoot>
                  	<tr><td colspan="6">&nbsp;</td></tr>
                  </tfoot>
                </table>
            </div>
        </div>
	</div>
</body>
</html>