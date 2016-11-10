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

	if($_SERVER['REQUEST_METHOD'] == 'POST' and isset($_POST['sub'])){
		
		array_pop($_POST);  //去除数组中最后一个元素
		
		// 获取数据表内最大排序号
		$sql = "select show_order from `link_group` order by show_order desc limit 1";
		$result = $mysqli->query($sql);
		$row = $result->fetch_assoc();
		$_POST['show_order'] = @(int)$row['show_order']+1;
		
		$arr = $myfun->_mysql_string($_POST);  //转义特殊字符
		$str_key = implode(',',array_keys($arr));
		$str_val = implode('\',\'',array_values($arr));
		$sql = "insert into `link_group` ($str_key) values ('$str_val')";

		/*获取操作日志信息*/
		$uid = $myfun->session_get('id');
		$addtime = time();
		$ip = $myfun->getIP();

		$result = $mysqli->query($sql);
		if(!$result){
			$handle = $myfun->_mysql_string('<span class="blue">[ 添加链接分类 ]</span> <span class="red">[ 失败 ]</span> '.$arr['group_name']);
			//记录操作日志（失败）
			$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
			$mysqli->query($insert);

			echo "插入数据失败！！！";
			echo "ERROR:".$mysqli->errno."|".$mysqli->error;
			exit;
		}else{
			$handle = $myfun->_mysql_string('<span class="blue">[ 添加链接分类 ]</span> <span class="green">[ 成功 ]</span> '.$arr['group_name']);
			//记录操作日志（成功）
			$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
			$mysqli->query($insert);
		}
	}
	
	$mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>添加友情链接分类</title>
<link rel="stylesheet" type="text/css" href="../../resource/css/main_cnt.css" />
<script type="text/javascript" src="../../../public/js/jquery.min.js"></script>
<script type="text/javascript" src="../../resource/js/layout.js"></script>
<script type="text/javascript" src="../../resource/js/check.js"></script>

<link rel="stylesheet" type="text/css" href="../../../public/plugins/jBox/skinblue/jbox.css" />
<script type="text/javascript" src="../../../public/plugins/jBox/jquery.jBox.min.js"></script>
<script type="text/javascript" src="../../../public/plugins/jBox/jquery.jBox-zh-CN.js"></script>
<!--<script type="text/javascript" src="../../resource/js/jxgov.js"></script>-->
<script type='text/javascript'>
<?php if($result){?>
	$(function(){
		$.jBox.prompt('分类添加成功！！', '提示', 'success', { closed: function () { history.go(-1); } });
	});
<?php }?>
</script>
</head>
<body>
    <div id="admin_cnt">
        <ul class="menu_breadcrumbs">
            <li>当前位置：</li>
            <li>应用模块 >> 友情链接 >>  添加分类</li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>
        <ul class="main_top_menu">
        	<li class="top_menu"><a href="linktypelist.php">分类列表</a></li>
        	<li class="top_menu"><a href="linktypeset.php" class="active">添加分类</a></li>
        </ul>
    	<div id="admin_cnt_inner">
			<div id="addcnt">
            	<form action="" method="post" id="linkgroupform">
            	<table class="table_all">
                  <caption>添加分类</caption>
                  <tbody>
                    <tr>
                      <th class="th_title">分类名称：</th>
                      <td>
                      	<input type="text" id="group_name" name="group_name" size="45" />
                      </td>
                    </tr>
                    <tr>
                      <th class="th_title">分类类型：</th>
                      <td>
                      	<?php
                        	echo $myfun->setLinkType();
						?>
                      </td>
                    </tr>
                    <tr>
                      <th class="th_title">启用：</th>
                      <td>
                        <input type="radio" name="ishdn" id="yhdn" value="1" checked="checked" /> <label for="yhdn">开启</label> &nbsp;
                        <input type="radio" name="ishdn" id="nhdn" value="0" /> <label for="nhdn">关闭</label>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                  	<tr>
                      <td colspan="2" align="center">
                      	<input type="submit" name="sub" id="sub" value="提交" class="btn" />
                      </td>
                    </tr>
                  </tfoot>
                </table>
                </form>
            </div>
        </div>
	</div>
</body>
</html>