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

	if($_SERVER['REQUEST_METHOD'] == 'POST' and isset($_POST['sub'])){
		
		$arr = $myfun->_mysql_string($_POST);  //转义特殊字符
		$linkname = $arr['linkname'];
		$linkurl = $arr['linkurl'];
		$linkpic = $arr['linkpic'];
		$ztc = $arr['ztc'];
		$zt = $arr['zy'];
		
		$ishdn = $arr['ishdn'];
		$type_id = 2; //对联
		$adduser = $myfun->session_get('username');
		$addtime = time();
		
		/*获取操作日志信息*/
		$uid = $myfun->session_get('id');
		$addtime = time();
		$ip = $myfun->getIP();
		
		$sql = "insert into `advert_info` (linkname,linkurl,linkpic,ztc,zy,type_id,ishdn,adduser,addtime) values ('$linkname','$linkurl','$linkpic','$ztc','$zy','$type_id','$ishdn','$adduser','$addtime')";
		$result = $mysqli->query($sql);
		if(!$result){
			$handle = $myfun->_mysql_string('<span class="blue">[ 添加对联 ]</span> <span class="red">[ 失败 ]</span> '.$arr['linkname']);
			//记录操作日志（失败）
			$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
			$mysqli->query($insert);

			echo "插入数据失败！！！";
			echo "ERROR:".$mysqli->errno."|".$mysqli->error;
			exit;
		}else{
			$handle = $myfun->_mysql_string('<span class="blue">[ 添加对联 ]</span> <span class="green">[ 成功 ]</span> '.$arr['linkname']);
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
<title>对联添加</title>
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
		var uploadbutton1 = K.uploadbutton({
			button : K('#image1')[0],
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
		uploadbutton1.fileBox.change(function(e) {
			uploadbutton1.submit();
		});
	});
	
	KindEditor.ready(function(K) {
		var uploadbutton2 = K.uploadbutton({
			button : K('#image2')[0],
			fieldName : 'imgFile',
			url : '../../../kindeditor/php/upload_json.php?dir=image',
			afterUpload : function(data) {
				if (data.error === 0) {
					var url = K.formatUrl(data.url, 'absolute');
					K('#linkpicmore').val(url);
				} else {
					alert(data.message);
				}
			},
			afterError : function(str) {
				alert('自定义错误信息: ' + str);
			}
		});
		uploadbutton2.fileBox.change(function(e) {
			uploadbutton2.submit();
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
            <li>应用模块 >> 广告模块 >>  对联添加</li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>
        <ul class="main_top_menu">
        	<li class="top_menu"><a href="ztclist.php">直通车列表</a></li>
        	<li class="top_menu"><a href="ztcadd.php" class="active">添加直通车</a></li>
        </ul>
    	<div id="admin_cnt_inner">
			<div id="addcnt">
            	<form action="" method="post" id="advertform">
            	<table class="table_all">
                  <caption>添加直通车</caption>
                  <tbody>
                    <tr>
                      <th class="th_title">名称：</th>
                      <td>
                      	<input type="text" id="linkname" name="linkname" size="45" />
                      </td>
                    </tr>
                    <tr>
                      <th class="th_title">链接地址：</th>
                      <td>
                      	<input type="text" id="linkurl" name="linkurl" size="45" /> <span class="red">格式如：http://www.baidu.com</span>
                      </td>
                    </tr>
                    <tr>
                      <th class="th_title">链接图片：</th>
                      <td>
                        <input type="text" name="linkpic" id="linkpic" size="45" />
                        <input type="button" id="image1" value="上传图片" />
                      </td>
                    </tr>
                  <?php /*?>  <tr>
                      <th class="th_title">链接图片：</th>
                      <td>
                        <input type="text" name="linkpicmore" id="linkpicmore" size="45" />
                        <input type="button" id="image2" value="上传图片" />
                      </td>
                    </tr><?php */?>
                      <tr>
                      <th class="th_title">是否属于直通车：</th>
                      <td>
                        <input type="radio" name="ztc" id="yhdn" value="1" checked="checked" /> <label for="yhdn">是</label> &nbsp;
                        <input type="radio" name="ztc" id="nhdn" value="0" /> <label for="nhdn">否</label>
                      </td>
                    </tr>
                      <tr>
                      <th class="th_title">显示位置：</th>
                      <td>
                        <input type="radio" name="zy" id="yhdn" value="0" checked="checked" /> <label for="yhdn">左</label> &nbsp;
                        <input type="radio" name="zy" id="nhdn" value="1" /> <label for="nhdn">右</label>
                      </td>
                    </tr>
                    <tr>
                      <th class="th_title">是否隐藏：</th>
                      <td>
                        <input type="radio" name="ishdn" id="yhdn" value="0" checked="checked" /> <label for="yhdn">否</label> &nbsp;
                        <input type="radio" name="ishdn" id="nhdn" value="1" /> <label for="nhdn">是</label>
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