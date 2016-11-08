<?php

	define('PATH',str_replace("\\","/",rtrim(dirname(__FILE__),'/').'/'));
	
	include PATH.'../../../config.inc.php';
	
	include BASE.'class/database.class.php';
	
	include BASE.'commons/global.fun.php';
	
	include BASE.'admin/session.inc.php';
	
	if(!strstr($myfun->session_get('yymklist'),'3') and !$myfun->session_get('root')){
		echo $myfun->out_language("非法登录！！",'');
		exit();
	}
	
	//删除飘窗
	if($_SERVER['REQUEST_METHOD'] == 'GET' and isset($_GET['act']) and $_GET['act'] == 'del'){
		$id = (int)$_GET['id'];
		if(!!$id){
				/*获取操作日志信息*/
				$uid = $myfun->session_get('id');
				$addtime = time();
				$ip = $myfun->getIP();
				$row = $mysqli->getField($id, 'linkname', '`advert_info`');
				$linkname = $row['linkname'];
				
				$sql = "delete from `advert_info` where id =".$id;
				$result = $mysqli->query($sql);
				if(!$result){
					$handle = $myfun->_mysql_string('<span class="blue">[ 删除飘窗 ]</span> <span class="red">[ 失败 ]</span> '.$linkname);
					//记录操作日志（失败）
					$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
					$mysqli->query($insert);
					
					echo "删除失败！！";
					echo "ERROR:".$mysqli->errno."|".$mysqli->error;
					exit;
				}else{
					$handle = $myfun->_mysql_string('<span class="blue">[ 删除飘窗 ]</span> <span class="green">[ 成功 ]</span> '.$linkname);
					//记录操作日志（成功）
					$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
					$mysqli->query($insert);
					echo $myfun->out_language("删除成功！！",'');
					exit();
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
<title>飘窗列表</title>
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
            <li>应用模块 >> 广告模块 >>  飘窗列表</li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>
        <ul class="main_top_menu">
        	<li class="top_menu"><a href="piaochuanglist.php" class="active">飘窗列表</a></li>
        	<li class="top_menu"><a href="piaochuangadd.php">添加飘窗</a></li>
        </ul>
    	<div id="admin_cnt_inner">
			<div id="addcnt">
                <table class="table_all">
                  <caption>飘窗列表</caption>
                  <thead>
                  	<tr>
                    	<th width="5%" class="style_th">序号</th>
                    	<th width="35%" class="style_th">名称</th>
                    	<th width="20%" class="style_th">缩略图</th>
                        <th width="12%" class="style_th">类别</th>
                        <th width="12%" class="style_th">启用状态</th>
                        <th colspan="2" class="style_th">操作</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                  	$sql = "select * from `advert_info` where type_id = 1 order by addtime desc";
					$result = $mysqli->query($sql);
					$i=0;
					while($row = $result->fetch_assoc()){
						$i++;
				  ?>
                    <tr>
                      <td class="style_td">
                      	<?php echo $i;?>
                      </td>
                      <td class="style_td" style="text-align:left;">
                      	<a href="<?php echo $row['linkurl']?>" target="_blank">
                      		<?php echo $row['linkname']?>
                        </a>
                      </td>
                      <td class="style_td">
                      	<a href="<?php echo $row['linkurl']?>" target="_blank">
                      		<img src="<?php echo $row['linkpic']?>" height="45" />
                        </a>
                      </td>
                       <td class="style_td">
                     	<?php
							$type = ($row['type_id'] == 1)?'飘窗':'对联';
							echo $type;
						?>
                      </td>
                      <td class="style_td">
                      	<?php
                        	if($row['ishdn'] == 0){
								echo '<span>开启</span>';
							}elseif($row['ishdn'] == 1){
								echo '<span class="red">关闭</span>';
							}else{
								echo '<span>未知</span>';
							}
						?>
                      </td>
                      <td width="8%" class="style_td">
                      <a href="piaochuangeditor.php?id=<?php echo $row['id'];?>">编辑</a></td>
                      <td width="8%" class="style_td"><a href="?act=del&id=<?php echo $row['id'];?>" onclick="return confirm('确认删除吗？删除后不可恢复！')">删除</a></td>
                    </tr>
                  <?php
					}
				  ?>
                  </tbody>
                  <tfoot>
                  	<tr><td colspan="7">&nbsp;</td></tr>
                  </tfoot>
                </table>
            </div>
        </div>
	</div>
</body>
</html>