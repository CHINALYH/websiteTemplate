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
	
	$id = (int)$_GET['id'];
	if(!$id){
		echo $myfun->out_language('路径错误，非法操作！！');
		exit();
	}else{
		//获取分类信息
		$sql = "select * from `advert_info` where id = '$id'";
		$query = $mysqli->query($sql);
		$row = $query->fetch_assoc();
	}

	if($_SERVER['REQUEST_METHOD'] == 'POST' and isset($_POST['mend'])){
		
		$arr = $myfun->_mysql_string($_POST);  //转义特殊字符
		$linkname = $arr['linkname'];
		$linkurl = $arr['linkurl'];
		$linkpic = $arr['linkpic'];
		$ishdn = $arr['ishdn'];
		
		/*获取操作日志信息*/
		$uid = $myfun->session_get('id');
		$addtime = time();
		$ip = $myfun->getIP();
		
		$sql = "update `advert_info` set linkname = '$linkname',linkurl = '$linkurl',linkpic = '$linkpic',ishdn = '$ishdn' where id = '$id'";

		$result = $mysqli->query($sql);
		if(!$result){
			$handle = $myfun->_mysql_string('<span class="blue">[ 编辑飘窗 ]</span> <span class="red">[ 失败 ]</span> '.$row['linkname']);
			//记录操作日志（失败）
			$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
			$mysqli->query($insert);

			echo "插入数据失败！！！";
			echo "ERROR:".$mysqli->errno."|".$mysqli->error;
			exit;
		}else{
			$handle = $myfun->_mysql_string('<span class="blue">[ 编辑飘窗 ]</span> <span class="green">[ 成功 ]</span> '.$row['linkname']);
			//记录操作日志（成功）
			$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
			$mysqli->query($insert);
		}
	}
	
	$mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>飘窗编辑</title>
<link rel="stylesheet" type="text/css" href="../../resource/css/main_cnt.css" />
<script type="text/javascript" src="../../../public/js/jquery.min.js"></script>
<script type="text/javascript" src="../../resource/js/layout.js"></script>
<script type="text/javascript" src="../../resource/js/check.js"></script>

<link rel="stylesheet" type="text/css" href="../../../public/plugins/jBox/skinblue/jbox.css" />
<script type="text/javascript" src="../../../public/plugins/jBox/jquery.jBox.min.js"></script>
<script type="text/javascript" src="../../../public/plugins/jBox/jquery.jBox-zh-CN.js"></script>

<link rel="stylesheet" type="text/css" href="../../../kindeditor/themes/default/default.css" />
<script type="text/javascript" src="../../../kindeditor/kindeditor-min.js"></script>

<script>
	KindEditor.ready(function(K) {
		var uploadbutton = K.uploadbutton({
			button : K('#image')[0],
			fieldName : 'imgFile',
			url : '../../../kindeditor/php/upload_json.php?dir=image',
			afterUpload : function(data) {
				if (data.error === 0) {
					var url = K.formatUrl(data.url, 'absolute');
					K('#linkpic').val(url);
				} else {
					alert(data.message);
				}
			},
			afterError : function(str) {
				alert('自定义错误信息: ' + str);
			}
		});
		uploadbutton.fileBox.change(function(e) {
			uploadbutton.submit();
		});
	});
</script>
<script type='text/javascript'>
<?php if($result){?>
	$(function(){
		$.jBox.prompt('添加成功！！', '提示', 'success', { closed: function () { history.go(-1); } });
	});
<?php }?>
</script>
</head>
<body>
    <div id="admin_cnt">
        <ul class="menu_breadcrumbs">
            <li>当前位置：</li>
            <li>应用模块 >> 广告模块 >>  飘窗编辑</li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>
    	<div id="admin_cnt_inner">
			<div id="addcnt">
            	<form action="?id=<?php echo $id;?>" method="post" id="advertform">
            	<table class="table_all">
                  <caption>飘窗编辑</caption>
                  <tbody>
                    <tr>
                      <th class="th_title">名称：</th>
                      <td>
                      	<input type="text" id="linkname" name="linkname" value="<?php echo $row['linkname']?>" size="45" />
                      </td>
                    </tr>
                    <tr>
                      <th class="th_title">链接地址：</th>
                      <td>
                      	<input type="text" id="linkurl" name="linkurl" value="<?php echo $row['linkurl']?>" size="45" /> <span class="red">格式如：http://www.baidu.com</span>
                      </td>
                    </tr>
                    <tr>
                      <th class="th_title">链接图片：</th>
                      <td>
                        <input type="text" name="linkpic" value="<?php echo $row['linkpic']?>" id="linkpic" size="45" />
                        <input type="button" id="image" value="上传图片" />
                      </td>
                    </tr>
                    <tr>
                      <th class="th_title">是否隐藏：</th>
                      <td>
                        <input type="radio" name="ishdn" id="yhdn" value="0" <?php if($row['ishdn']==0){?>checked="checked"<?php }?> /> <label for="yhdn">否</label> &nbsp;
                        <input type="radio" name="ishdn" id="nhdn" value="1" <?php if($row['ishdn']==1){?>checked="checked"<?php }?> /> <label for="nhdn">是</label>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                  	<tr>
                      <td colspan="2" align="center">
                      	<input type="submit" name="mend" id="mend" value="提交" class="btn" />
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