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
	
	// 添加
	if($_SERVER['REQUEST_METHOD'] == 'POST' and isset($_POST['add'])){
		
		$message = $myfun->_mysql_string($_POST['message']);
		$telephone = $_POST['telephone'];
		
		$myfun->sendMsg($message,$telephone,true);

	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>发布短信提醒</title>
<link rel="stylesheet" type="text/css" href="../../resource/css/main_cnt.css" />
<script type="text/javascript" src="../../../public/js/jquery.min.js"></script>
<script type="text/javascript" src="../../resource/js/layout.js"></script>
<script type="text/javascript" src="../../resource/js/check.js"></script>
</head>

<body>
    <div id="admin_cnt">
        <ul class="menu_breadcrumbs">
            <li>当前位置：</li>
            <li>应用模块 >> 发布短信提醒</li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>
    	<div id="admin_cnt_inner">
			<div id="addcnt">
            	<form action="" method="post" id="mobilesendform">
            	<table class="table_special">
                  <caption>发布短信提醒</caption>
                  <tbody>
                    <tr>
                      <th class="th_title">短信内容：</th>
                      <td>
                        <textarea name="message" id="message" cols="81" rows="6"><?php echo MOBILE_DEFAULT_MSG;?></textarea>
                        （<span class="gray">短信内容不得超过140个字符</span>）
                      </td>
                    </tr>
                    <tr>
                      <th class="th_title">短信接收人：</th>
                      <td>
                      	<ul id="mobilelist">
                        <?php
                        	$sql = "select departname,cellphone from `person_info`";
							$result = $mysqli->query($sql);
							while($row = $result->fetch_assoc()){
						?>
                        	<li><input type="checkbox" name="telephone[]" value="<?php echo $row['cellphone']?>" /> <?php echo $row['departname'];?></li>
                        <?php
							}
						?>
                        </ul>
                        
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                  	<tr>
                      <td colspan="2" style="padding-left:400px;">
                      	<input type="submit" name="add" id="add" value="发送短信" class="btn" />
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