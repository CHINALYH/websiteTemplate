<?php
	define('PATH',str_replace("\\","/",rtrim(dirname(__FILE__),'/').'/'));
	
	include PATH.'../../../config.inc.php';
	
	include BASE.'class/database.class.php';
	
	include BASE.'commons/global.fun.php';
	
	include BASE.'admin/session.inc.php';
	
	include BASE.'class/page.class.php';
	
	if(!strstr($myfun->session_get('nrgllist'),'1') and !$myfun->session_get('root')){
		echo $myfun->out_language("无此权限，非法登录！！");
		exit;
	}
	
	$mid = (int)$_GET['mid'];
	$atcid = (int)$_GET['atcid'];
	$funcArr = unserialize($myfun->session_get('funclist'));

	if($mid){
		if(!$myfun->session_get('root')){ //判断用户是否拥有此栏目权限
			$menulist = explode(',',$myfun->session_get('menulist'));
			if(!in_array($mid,$menulist)){
				echo $myfun->out_language("您无此菜单操作权限！！！");
				exit;
			}
		}
		$fieldArr = array('m_name','is_check');
		$row = $mysqli->getField($mid,$fieldArr,'`menu_info`');
		$menuname = $row['m_name'];
		$ischeck = $row['is_check'];
		
		$sql = "select * from `content_info` where id = '$atcid'";
		$cntinfo = $mysqli->getRowsRst($sql);
		$ispass = $cntinfo['ispass'];
	}else{
		echo $myfun->out_language("URL链接有误！！！");
		exit;
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['act']) && $_GET['act']=='check'){  //提交审核
		//=====获取发布文章的相关信息（文章栏目id，上级审核人，退稿信息）=====
		$fieldArr = array('mid','checkuser','message','adduid','addtime');
		$row = $mysqli->getField($atcid,$fieldArr,'`content_info`');
		$checkuser = trim($row['checkuser']);
		if($checkuser){
			$checkuser = $row['checkuser'].'|'.$myfun->session_get('username');
		}else{
			$checkuser = $myfun->session_get('username');
		}
		
		//======获取栏目的审核级别，并与审核用户的权限匹配======
		if($myfun->session_get('root')){
			$ispass = 0;
		}else{
			$mid = $row['mid']; //栏目id
			$rowmenu = $mysqli->getField($mid,'is_check','`menu_info`'); //获取栏目审核级别
			$ispass = ($rowmenu['is_check'] == $funcArr[$mid]['check'])?0:($funcArr[$mid]['check']+1);
		}
		
		//=========更新文章审核状态=========
		if($ispass == 0){
			$message = '';
		}else{
			$message = $row['message'];
		}
		
		$sql = "update `content_info` set ispass = '$ispass', message = '$message', checkuser = '$checkuser' where id = '$atcid';";

		//===========通过审核的话，采用数增加===========
		if($ispass == 0){
			$adduid = $row['adduid'];
			$year = date('Y',(int)$row['addtime']);
			$mouth = date('m',(int)$row['addtime']);
			$sql .= "update `atc_statistic_info` set p_nums = (p_nums+1) where uid = '$adduid' and by_year = '$year' and by_mouth = '$mouth';";
		}
		
		$result = @$mysqli->multi_query($sql);
		if($result){
			echo $myfun->out_language("审核通过！");
			exit;
		}else{
			echo $myfun->out_language("审核失败！");
			exit;
		}
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['act']) && $_GET['act']=='ytop'){  //提交置顶
		$sql = "update `content_info` set istop = 1 where id = '$atcid'";
		$result = $mysqli->query($sql);
		if($result){
			echo $myfun->out_language("置顶成功！");
			exit;
		}else{
			echo $myfun->out_language("置顶失败！");
			exit;
		}
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['act']) && $_GET['act']=='ntop'){  //取消置顶
		$sql = "update `content_info` set istop = 0 where id = '$atcid'";
		$result = $mysqli->query($sql);
		if($result){
			echo $myfun->out_language("取消置顶成功！");
			exit;
		}else{
			echo $myfun->out_language("取消置顶失败！");
			exit;
		}
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['act']) && $_GET['act']=='del'){  //提交删除
		$sql = "delete from `content_info` where id = '$atcid'";
		$result = $mysqli->query($sql);
		if($result){
			echo '<script type="text/javascript">alert("删除成功！");history.go(-2);</script>';
			exit;
		}else{
			echo $myfun->out_language("删除失败！");
			exit;
		}
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>文章预览</title>
<link rel="stylesheet" type="text/css" href="../../resource/css/main_cnt.css" />
<script type="text/javascript" src="../../../public/js/jquery.min.js"></script>
<script type="text/javascript" src="../../resource/js/layout.js"></script>

<link rel="stylesheet" type="text/css" href="../../../public/plugins/jBox/skinblue/jbox.css" />
<script type="text/javascript" src="../../../public/plugins/jBox/jquery.jBox.min.js"></script>
<script type="text/javascript" src="../../../public/plugins/jBox/jquery.jBox-zh-CN.js"></script>
<script type="text/javascript" src="../../resource/js/jbox.js"></script>
</head>

<body>
    <div id="admin_cnt">
        <ul class="menu_breadcrumbs">
            <li>当前位置：</li>
            <li>内容管理 >> 文章预览 >> <?php echo $menuname;?></li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>
    	<div id="admin_cnt_inner">
			<div id="listcnt">
            	<table class="table_all">
                	<!--<caption>[ <?php echo $menuname;?> ]</caption>-->
					<tr>
                		<td>
                        	<?php
								$color = 'color:'.$cntinfo['title_color'].';';
                            	$bold = $cntinfo['title_bold']?'font-weight:bold;':'';
								$size = 'font-size:'.ARTICLE_TITLE_SIZE.'px;';
								$style = 'style = "'.$color.$bold.$size.'"';
								$secondtitle = $cntinfo['title_second']?'副标题：'.$cntinfo['title_second']:'';
								$is_where = $cntinfo['is_where']?'稿件来源：'.$cntinfo['is_where']:'';
							?>
                        	<span id="firsttitle" <?php echo $style;?>><?php echo $cntinfo['title_first'];?></span><br />
                            <?php if($secondtitle){?>
                            <span id="secondtitle"><?php echo $secondtitle;?></span><br />
                            <?php }?>
                            <p style="padding:6px 0 2px;">发布人：<?php echo $cntinfo['adduser'];?>&nbsp;&nbsp;<?php echo $is_where;?>&nbsp;&nbsp;浏览：<?php echo $cntinfo['times'];?>&nbsp;&nbsp;添加时间：<?php echo date("Y-m-d",$cntinfo['addtime']);?></p>
                        </td>
                    </tr>
                    <tr>
                		<td style="text-align:left;">
                        	<div id="content" style="font-size:<?php echo ARTICLE_CTN_SIZE?>px;">
								<?php echo $myfun->db2form($cntinfo['atc_cnt']);?>
                            </div>
                        </td>
                    </tr>
                	<tr>
                		<th align="right">
                        <?php 
						  if($cntinfo['tag']){
							if($cntinfo['ispass'] and (($myfun->session_get('root') or ($funcArr[$mid]['check'] == $cntinfo['ispass'])))){
						?>
                        	<a href="?act=check&mid=<?php echo $mid;?>&atcid=<?php echo $atcid;?>">通过审核</a>&nbsp;&nbsp;
                            <span style="color:#333; cursor:pointer;" onclick="sendback(6,<?php echo $atcid;?>)">退稿</span>&nbsp;&nbsp;
						<?php }
                        	if($myfun->session_get('root') or $funcArr[$mid]['top']){
								$act = $cntinfo['istop']?'ntop':'ytop';
								$name = $cntinfo['istop']?'取消置顶':'置顶';
						?>
                        	<a href="?act=<?php echo $act;?>&mid=<?php echo $mid;?>&atcid=<?php echo $atcid;?>"><?php echo $name;?></a>&nbsp;&nbsp;
					    <?php
							}
						?>

                         	<a href="contenteditor.php?mid=<?php echo $mid;?>&atcid=<?php echo $atcid;?>">编辑</a>&nbsp;&nbsp;
                        <?php
						  }
						?>
                            <a href="?act=del&mid=<?php echo $mid;?>&atcid=<?php echo $atcid;?>" onclick="return confirm('确定删除该篇文章？');">删除</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </th>
                    </tr> 
                </table>
            </div>
        </div>
	</div>
</body>
</html>