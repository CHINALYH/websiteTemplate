<?php
	define('PATH',str_replace("\\","/",rtrim(dirname(__FILE__),'/').'/'));
	
	include PATH.'../../../config.inc.php';
	
	include BASE.'class/database.class.php';
	
	include BASE.'commons/global.fun.php';
	
	include BASE.'admin/session.inc.php';

	if(!strstr($myfun->session_get('nrgllist'),'1') and !$myfun->session_get('root')){
		echo $myfun->out_language("无此权限，非法登录！！");
		exit;
	}
    $tag=$_GET['tag'];//传过来的值
	if($tag==1){
		$tagname="普通";
		}
	if($tag==2){
		$tagname="职务";
		}	
	$uid = $myfun->session_get('id');
	$gid = $myfun->session_get('gid');
	$pid = $myfun->session_get('pid');
	
	/*--------------------------整合菜单栏目数组用于zTree...(end)--------------------------------*/	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>文章添加</title>
<link rel="stylesheet" type="text/css" href="../../resource/css/main_cnt.css" />
<script type="text/javascript" src="../../../public/js/jquery.min.js"></script>
<script type="text/javascript" src="../../resource/js/layout.js"></script>
<script type="text/javascript" src="../../../public/plugins/DatePicker/WdatePicker.js"></script>
<!--<script type="text/javascript" src="../../resource/js/check.js"></script>-->
<?php
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sub'])){  //-------发布文章-------
		array_pop($_POST);  //去除数组中最后一个元素
	
		$_POST['addtime'] = time();
		$_POST['addip'] = $myfun->getIP();
		$_POST['adduid'] = $myfun->session_get('id');
		$_POST['addgid'] = $myfun->session_get('gid');
		$fzrq=$_POST['fzrq'];
		$_POST['fzrq'] = strtotime($fzrq);
		$arr = $myfun->_mysql_string($_POST);  //转义特殊字符
		$str_key = @implode(',',array_keys($arr));
		$str_val = @implode('\',\'',array_values($arr));
		$sql = "insert into `cyxx_info` ($str_key) values ('$str_val') ";
		
		$rst=$mysqli->query($sql);
		if($rst){
		   echo $myfun->out_language("船员信息发布成功!!","chuanyuan_add.php?tag=$tag");
		   exit;
		}else{
		   echo $myfun->out_language("船员信息发布失败!!","chuanyuan_add.php?tag=$tag");
		   exit;
			}
	}
?>
</head>

<body>
    <div id="admin_cnt">
        <ul class="menu_breadcrumbs">
            <li>当前位置：</li>
            <li>船员管理 >> 船员添加</li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>
        <?php
        	if($menutype != 2){
		?>
        <ul class="main_top_menu">
        	<li class="top_menu"><a href="chuanyuan_list.php?tag=<?php echo $tag;?>"><?php echo $tagname;?>船员信息列表</a></li>
        	<li class="top_menu"><a href="chuanyuan_add.php?tag=<?php echo $tag;?>" class="active"><?php echo $tagname;?>船员信息添加</a></li>
        </ul>
        <?php
			}
		?>
    	<div id="admin_cnt_inner">
			<div id="addcnt">
            <form action="?tag=<?php echo $tag?>" method="post" id="addcontent_form">
            	<table class="table_special">
                  <caption>	 
                   <?php
                  if($tag==1){
					  echo "普通船员";
					  }
				   if($tag==2){
					echo "职务船员";
					} 
				  ?></caption>
                  <thead style="display:none;"><td colspan="2">
			
                  </td></thead>
                  <tbody>
                    <tr>
                      <th class="th_title">姓　　名：</th>
                      <td>
                      	<input type="text" id="name" name="name" class="cntinput" value="<?php echo $cntinfo['name']?>" />
                        <span class="red">[必填]</span>
                      </td>
                    </tr>
                    <tr>
                      <th class="th_title">职　　务：</th>
                      <td>
                     <select name="zhiwu" style="width:135px; height:30px; line-height:30px;">
					<?php
                    $field = array('id','group_name');
                    $menuinfo = $mysqli->getField(1, $field, 'fenlei_group');
                    ?>
                    <option name="zhiwu" value="">==请选择职位==</option>
                     <?php
					$sql3 = "select * from `fenlei_info` where gid = '".$menuinfo['id']."' order by show_order asc ,addtime desc";
					$result3 = $mysqli->query($sql3);
					while($row3 = $result3->fetch_assoc()){
				?>
					<option  name="zhiwu" value="<?php echo $row3['id']?>"><?php echo $row3['link_name']?></option>
				<?php
					}
				?>
                  </select>
                      
                        <span class="red">[必填]</span>
                      </td>
                    </tr>
                        <tr>
                      <th class="th_title">发证日期：</th>
                      <td>
                     <input type="text" id="fzrq" class="Wdate" name="fzrq" onClick="WdatePicker()" readonly="readonly" />
                      
                        <span class="red">[必填]</span>
                      </td>
                    </tr>
                        <tr>
                      <th class="th_title">培训机构：</th>
                      <td>
                      	<input type="text" id="pxjg" name="pxjg" class="cntinput" value="<?php echo $cntinfo['pxjg']?>" />
                        <span class="red">[必填]</span>
                      </td>
                    </tr>
                        <tr>
                      <th class="th_title">发证机构：</th>
                      <td>
                      	<input type="text" id="fzjg" name="fzjg" class="cntinput" value="<?php echo $cntinfo['fzjg']?>" />
                        <span class="red">[必填]</span>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="2" align="center">
                      	<input type="hidden" name="tag" value="<?php echo $tag;?>" />
                        <input type="submit" name="sub" id="sub" value="文章发布" />&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="reset" name="reset" id="reset" value="重新设置" />
                      </td>
                    </tr>
                  </tfoot>
                </table></form>
            </div>
        </div>
	</div>
</body>
</html>