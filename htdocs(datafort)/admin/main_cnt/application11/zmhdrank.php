<?php

	define('PATH',str_replace("\\","/",rtrim(dirname(__FILE__),'/').'/'));
	
	include PATH.'../../../config.inc.php';
	
	include BASE.'class/database.class.php';
	
	include BASE.'commons/global.fun.php';
	
	include BASE.'admin/session.inc.php';
	
	if(!strstr($myfun->session_get('yymklist'),'5') and !$myfun->session_get('root')){
		echo $myfun->out_language("非法登录！！",'');
		exit();
	}
	
	$ranktype = ((int)$_GET['type'] == 2)?(int)$_GET['type']:1;
	
	if($ranktype == 1){
		$menuname = "来信排行榜";
	}elseif($ranktype == 2){
		$menuname = "回复率排行榜";
	}else{
		echo $myfun->out_language("URL链接非法！！！");
		exit;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>政民互动统计</title>
<link rel="stylesheet" type="text/css" href="../../resource/css/main_cnt.css" />
<script type="text/javascript" src="../../../public/js/jquery.min.js"></script>
<style type="text/css">
table{
	width:100%;
	border:1px solid #f0f0f0;
	border-bottom:none;
}
table caption{
	height:29px;
	line-height:29px;
	text-align:left;
	border:1px solid #efefef;
	padding-left:10px;
	background:url(../../../public/images/zmhd/th_bg.png) repeat-x;
}
table th{
	height:24px;
	text-align:center;
	color:#D06C00;
	border:1px solid #fff;
	padding:2px 5px;
	background:#F6EFE3;
}
table td{
	height:24px;
	text-align:center;
	padding:4px 5px;
	background:url(../../../public/images/bottom_line.png) repeat-x left bottom;
}
table td.left_td{
	text-align:left;
}
</style>
</head>

<body>
    <div id="admin_cnt">
        <ul class="menu_breadcrumbs">
            <li>当前位置：</li>
            <li>应用模块 >> 政民互动统计 >> <?php echo $menuname?></li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>
        <ul class="main_top_menu">
        	<li class="top_menu"><a href="zmhdrank.php?type=1" <?php if($ranktype == 1){?>class="active"<?php }?>>来信排行榜</a></li>
        	<li class="top_menu"><a href="zmhdrank.php?type=2" <?php if($ranktype == 2){?>class="active"<?php }?>>回复率排行榜</a></li>
            <li id="printbtn"><a href="javascript:window.print();"><span>打印</span></a></li>
        </ul>
    	<div id="admin_cnt_inner">
			<div id="addcnt">
                <table border="0">
                  <caption>
                      <span class="green">『政民互动』<?php echo $menuname;?></span>
                  </caption>
                  <tr>
                    <th width="10%">排名</th>
                    <th width="36%">部 门</th>
                    <th width="18%">留言数</th>
                    <th width="18%">回复数</th>
                    <th width="18%">回复率</th>
                  </tr>
                  <?php
                    if($ranktype == 1){
                        $sql = "select departid,subnum,replynum,(replynum/subnum) as num from `interact_statistic_info` order by subnum desc, replynum desc";
                    }elseif($ranktype == 2){
                        $sql = "select departid,subnum,replynum,(replynum/subnum) as num from `interact_statistic_info` order by num desc ,subnum desc";
                    }
                    $result = $mysqli->query($sql);
                    $i = 0;
                    while($row = $result->fetch_assoc()){
                        $i++;
                        $num = round($row['num'],2)*100;
                        //获取部门名称
                        $departinfo = $mysqli->getField($row['departid'],'departname','`manage_user_info`');
                        $departname =  $departinfo['departname'];
                  ?>
                  <tr>
                    <td><?php echo $i;?></td>
                    <td class="left_td"><?php echo $departname;?></td>
                    <td><?php echo $row['subnum'];?></td>
                    <td><?php echo $row['replynum'];?></td>
                    <td><?php echo $num.'%';?></td>
                  </tr>
                  <?php
                    }
                  ?>
                </table>
            </div>
        </div>
	</div>
</body>
</html>