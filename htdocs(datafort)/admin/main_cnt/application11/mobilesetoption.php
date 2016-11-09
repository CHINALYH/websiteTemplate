<?php
	define('PATH',str_replace("\\","/",rtrim(dirname(__FILE__),'/').'/'));
	
	include PATH.'../../../config.inc.php';
	
	include BASE.'class/database.class.php';
	
	include BASE.'class/mobileset.class.php';
	
	include BASE.'commons/global.fun.php';
	
	include BASE.'admin/session.inc.php';
	
	if(!strstr($myfun->session_get('yymklist'),'1') and !$myfun->session_get('root')){
		echo $myfun->out_language("非法登录！！",'');
		exit();
	}
	
	// 添加
	if($_SERVER['REQUEST_METHOD'] == 'POST' and isset($_POST['add'])){
		$ms = new MoblieSet();

		/*获取操作日志信息*/
		$uid = $myfun->session_get('id');
		$addtime = time();
		$ip = $myfun->getIP();

		if($ms->validateForm($_POST)){
			if($ms->writeConfig("config.inc.php",$_POST)){
				$handle = $myfun->_mysql_string('<span class="blue">[ 编辑短信参数 ]</span> <span class="green">[ 成功 ]</span> ');
				
				//记录操作日志（成功）
				$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
				$mysqli->query($insert);
				
				echo $myfun->out_language($ms->getMessList());
			}else{
				$handle = $myfun->_mysql_string('<span class="blue">[ 编辑短信参数 ]</span> <span class="red">[ 失败 ]</span> ');
				//记录操作日志（失败）
				$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
				$mysqli->query($insert);
				echo $myfun->out_language($ms->getMessList());
			}	
		}else{
			$handle = $myfun->_mysql_string('<span class="blue">[ 编辑短信参数 ]</span> <span class="red">[ 失败 ]</span> ');
			//记录操作日志（失败）
			$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
			$mysqli->query($insert);
			echo $myfun->out_language($ms->getMessList());
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>短信参数设置</title>
<link rel="stylesheet" type="text/css" href="../../resource/css/main_cnt.css" />
<script type="text/javascript" src="../../../public/js/jquery.min.js"></script>
<script type="text/javascript" src="../../resource/js/layout.js"></script>
<script type="text/javascript" src="../../resource/js/check.js"></script>
</head>

<body>
    <div id="admin_cnt">
        <ul class="menu_breadcrumbs">
            <li>当前位置：</li>
            <li>应用模块 >> 短信参数设置</li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>
    	<div id="admin_cnt_inner">
			<div id="addcnt">
            	<form action="" method="post" id="mobileoptionform">
            	<table class="table_special">
                  <caption>短信参数设置</caption>
                  <tbody>
                    <tr>
                      <th class="th_title">&nbsp;</th>
                      <td>
                      	<span class="blue">本参数为短信发送核心参数，请慎重设置</span>
                      </td>
                    </tr>
                    <tr>
                      <th class="th_title">短信服务器：</th>
                      <td>
                      	<input type="text" id="mobile_server_url" name="mobile_server_url" value="<?php echo MOBILE_SERVER_URL;?>" class="cntinput red" style="background:#F4F8FC;" />
                        <span class="red">*</span> <span class="gray">（短信服务器接口地址）</span>
                      </td>
                    </tr>
                    <tr>
                      <th class="th_title">默认信息内容：</th>
                      <td>
                        <textarea name="mobile_default_msg" cols="81" rows="6"><?php echo MOBILE_DEFAULT_MSG;?></textarea>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                  	<tr>
                      <td colspan="2" style="padding-left:400px;">
                      	<input type="submit" name="add" id="add" value="保存设置" class="btn" />
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