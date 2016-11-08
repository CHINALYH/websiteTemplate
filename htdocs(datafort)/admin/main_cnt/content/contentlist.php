<?php
	define('PATH',str_replace("\\","/",rtrim(dirname(__FILE__),'/').'/'));
	
	include PATH.'../../../config.inc.php';
	
	include BASE.'class/database.class.php';
	
	include BASE.'commons/global.fun.php';
	
	include BASE.'admin/session.inc.php';
	
	include BASE.'class/page.class.php';
	
	include BASE.'class/tree.class.php';
	
	if(!strstr($myfun->session_get('nrgllist'),'1') and !$myfun->session_get('root')){
		echo $myfun->out_language("非法登录！！");
		exit;
	}
	
	$mid = (int)$_GET['mid'];
	$p = (int)$_GET['page']?(int)$_GET['page']:1; //页数
	if($mid){ //根据菜单id获取菜单名称
		if(!$myfun->session_get('root')){ //判断用户是否拥有此栏目权限
			$menulist = $myfun->session_get('menulist');
			$menuArr = explode(',',$menulist);
			if(!in_array($mid,$menuArr)){
				echo $myfun->out_language("您无此菜单操作权限！！！");
				exit();
			}
		}
		$row_menu =$mysqli->getField($mid,'m_name','`menu_info`');
		$menu_name = $row_menu['m_name'];
	}else{
		echo $myfun->out_language("URL链接非法！！！");
		exit;
	}
	
	$funcArr = unserialize($myfun->session_get('funclist'));
	
	//获取url参数
	$ispass = (int)$_GET['ispass']?(int)$_GET['ispass']:'';
	$keyword = trim($_GET['keyword'])?trim($_GET['keyword']):'';
	//检索栏目设置
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])){
		$ispass = (int)trim($_POST['ispass']);    //检索的审核状态（已审核/未审核）
		$keyword = (trim($_POST['keyword'])=="请输入关键字……" or !trim($_POST['keyword']))?'':trim($_POST['keyword']);
	}
	
	
	//删除文章
	if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['act']) && $_GET['act'] == 'del'){  
		$atcid = (int)$_GET['atcid'];
		if($atcid){
			/*获取操作日志信息*/
			$fieldArr = array('title_first','adduser','addtime');
			$row = $mysqli->getField($atcid,$fieldArr,'`content_info`');
			$uid = $myfun->session_get('id');
			$addtime = time();
			$ip = $myfun->getIP();
			
			$sql = "delete from `content_info` where id = '$atcid'";
			$result = $mysqli->query($sql);
			if($result){
				$handle = $myfun->_mysql_string('<span class="blue">[ 文章删除 ]</span> <span class="green">[ 成功 ]</span> [ ID:'.$atcid.' - '.$row['adduser'].' - '.date("Y-m-d",$row['addtime']).' ] '.$row['title_first']);
				//记录操作日志（成功）
				$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
				$mysqli->query($insert);
				echo $myfun->out_language("删除成功!!","contentlist.php?mid=$mid&keyword=$keyword&page=$p");
				exit;
			}else{
				$handle = $myfun->_mysql_string('<span class="blue">[ 文章删除 ]</span> <span class="red">[ 失败 ]</span> [ ID:'.$atcid.' - '.$row['adduser'].' - '.date("Y-m-d",$row['addtime']).' ] '.$row['title_first']);
				//记录操作日志（失败）
				$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
				$mysqli->query($insert);
				echo $myfun->out_language("删除失败!!","contentlist.php?mid=$mid&keyword=$keyword&page=$p");
				exit;
			}
		}else{
			echo $myfun->out_language("URL链接非法！！！");
			exit;
		}
	}
	//获取转移目录
	if($myfun->session_get('root')){
		$sql = "select * from `menu_info` where m_type != 2 and m_type != 6 and m_type != 8 and is_hdn = 0 order by show_order asc";
	}else{
		$sql = "select * from `menu_info` where id in($menulist) and m_type != 2 and m_type != 6 and m_type != 8 and is_hdn = 0 order by show_order asc";
	}
	$result=$mysqli->query($sql);
	$category = array();
	while($row = $result->fetch_assoc()){
		$category[] =  array('id'=>$row['id'],'pid'=>$row['pid'],'name'=>$myfun->_html($row['m_name']),'m_type'=>$row['m_type']);
	}
	$tree = new Tree($category);
	$jboxhtml = '<select name=mid>';
	$jboxhtml .= '<option value=>请选择转移目录……</option>';
	$jboxhtml .= $tree->getTree('0', '<option value=$id $selected>$spacer$name</option>', '0', '', '<optgroup label=$spacer$name></optgroup>'); 
	$jboxhtml  .= '</select>';
	//echo $jboxhtml;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>文章列表</title>
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
            <li>内容管理 >> 文章列表</li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>

        <ul class="main_top_menu  cntlist_ul">
        	<li class="top_menu"><a href="contentlist.php?mid=<?php echo $mid;?>" class="active">文章列表</a></li>
        	<li class="top_menu"><a href="contentadd.php?mid=<?php echo $mid;?>">文章添加</a></li>
            <li id="search">
              <form action="" method="post">
                <input type="radio" name="ispass" id="npass" value="0" <?php if(!$ispass){?>checked="checked"<?php }?> /> <label for="npass">已审核</label> 
                <input type="radio" name="ispass" id="ypass" value="1" <?php if($ispass){?>checked="checked"<?php }?> /> <label for="ypass">未审核</label> &nbsp;
                <label for="keyword">关键字：</label><input type="text" name="keyword" id="keyword" size="22" style="padding-left:2px;" value="<?php if(!!$keyword){echo $keyword;}else{?>请输入关键字……<?php }?>" onfocus="if (value =='请输入关键字……'){value =''}" onblur="if (value ==''){value='请输入关键字……'}" /> &nbsp;
                <input type="submit" name="search" value="搜索" />
           	  </form>
            </li>
        </ul>

    	<div id="admin_cnt_inner">
			<div id="listcnt">
            	<table class="table_special">
                	<caption>文章列表  <?php if($menu_name){echo '[',$menu_name,']';}?> </caption>
                    <thead>
                	<tr>
                        <th width="3%"><input type="checkbox" groupall="checkAll" /></th>
                        <th width="34%">文章标题</th>
                        <th width="7%">所属菜单</th>
                        <th width="7%">文章属性</th>
                        <th width="15%">添加人</th>
                        <th width="7%">添加时间</th>
                        <th width="7%">审核发布</th>
                        <th colspan="4">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php						
						//组织检索条件语句
						$condition = "where mid = '$mid' and tag = 1 and is_delelte = 1";
						if($ispass){
							$condition .= " and ispass != '0'";  //未审核
						}else{
							$condition .= " and ispass = '0'";   //已审核
						}
						
						if(!$myfun->session_get('root')){
							$condition .= " and adduid = ".$myfun->session_get('id');
						}
						
						if($keyword){
							$condition.=" and title_first like '%$keyword%'";
						}
						
						$sql = "select * from `content_info` $condition";
						$result = $mysqli->query($sql);
						$total = $result->num_rows; //信息总数
						$num = SYSTEM_PAGE_NUM; //每页显示数
						$page=new Page($total, $num, "mid=$mid&ispass=$ispass&keyword=$keyword");
						$sql = "select * from `content_info` $condition order by istop desc ,addtime desc {$page->limit}";
						$result = $mysqli->query($sql);
						while($row = $result->fetch_assoc()){
							//获取菜单信息
							$menuField = array('m_name','m_type','is_check');
							$menuInfo = $mysqli->getField($row['mid'],$menuField,'`menu_info`')
					?>
                	<tr>
                        <td><input type="checkbox" name="items[]" group="checkAll" value="<?php echo $row['id']?>"></td>
                        <td style="text-align:left;">
                        	<a href="contentshow.php?mid=<?php echo $row['mid'];?>&atcid=<?php echo $row['id'];?>">
							<?php
								$info = $row['istop']?'<span class="red">[置顶] </span>':'';
								$info .= $row['topctn']?'<span class="red">[头条] </span>':'';
								$info .= $row['focus']?'<span class="red">[焦点] </span>':'';
								echo $info.$row['title_first'];
							?>
                            </a>
                            
                            
                        </td>
                        <td><span class="blue"><?php echo $menuInfo['m_name']?></span></td>
                        <td><?php echo $myfun->getAtcType($menuInfo['m_type'],$row['atc_pic'])?></td>
                        <td>
						<?php
                        	$atcinfo = $mysqli->getField($row['adduid'],'username','`manage_user_info`');
							echo $atcinfo['username'];
                        ?>
                        </td>
                        <td><?php echo date("Y-m-d",$row['addtime'])?></td>
                        <td>
                        <?php
                        	if(trim($row['checkuser'])){
								$checkuserArr = @explode('|',trim($row['checkuser']));
								$i=0;
								$prompt_info = '';
								foreach($checkuserArr as $checkuser){
									$i++;
									$prompt_info .= $checkuser.'<span class=green> 已审</span><br />';
								}
							}else{
								$prompt_info = '暂未审核！';
							}

                        	if(!$row['ispass']){ //通过审核
								echo '<span class="green" style="cursor:pointer;" onclick="prompt_info(\''.$prompt_info.'\',\'审核进度\')">√已审核</span>';
							}else{ //未通过审核
								echo '<span class="red" style="cursor:pointer;" onclick="prompt_info(\''.$prompt_info.'\',\'审核进度\')"> ×未审核</span>';
							}
						?>
                        </td>
                        
                        <td width="5%"><a href="contentshow.php?mid=<?php echo $row['mid'];?>&atcid=<?php echo $row['id'];?>">预览</a></td>
                        <td width="5%">
                        <?php
                        	if($myfun->session_get('root') or $myfun->session_get('ispass')){
                        ?>
                        	<span class="pointer" onclick="atcMoves(<?php echo $row['id'];?>,'<?php echo $jboxhtml;?>')">转移</span>
                        <?php
							}else{
						?>
                        	<span class="gray pointer">转移</span>
                        <?php	
							}
						?>
                        </td>
                        <td width="5%">
                        <?php
                        	if($myfun->session_get('root') or $myfun->session_get('ispass')){
                        ?>
                        	<a href="contenteditor.php?mid=<?php echo $row['mid'];?>&atcid=<?php echo $row['id'];?>"><span>编辑</span></a>
                        <?php
							}else{
						?>
                        	<span class="gray pointer">编辑</span>
                        <?php	
							}
						?>
                        </td>
                        <td width="5%">
                        <?php
                        	if($myfun->session_get('root') or $myfun->session_get('ispass')){
                        ?>
                        	<a href="?act=del&mid=<?php echo $row['mid']?>&atcid=<?php echo $row['id'];?>&page=<?php echo $p;?>" onclick="return confirm('确定要删除吗?')"><span class="red">删除</span></a>
                        <?php
							}else{
						?>
                        	<span class="gray pointer">删除</span>
                        <?php	
							}
						?>
                        </td>
                    </tr>
					<?php
						}
					?>
                    </tbody>
                    <tfoot>
                    <tr>
                    	<td colspan="1"><input type="checkbox" groupall="checkAll" /></td>
                        <td colspan="10" style="text-align:left; padding-left:10px">
                        <?php
                        	if($myfun->session_get('root') or $myfun->session_get('ispass')){
						?>
                            <input type="button" value="删除" onclick="option(1)" />&nbsp;
                        <?php
							}
							if($funcArr[$mid]['top'] or $myfun->session_get('root')){
						?>
                            <input type="button" value="置顶" onclick="option(2)" />&nbsp;
                            <input type="button" value="取消置顶" onclick="option(3)" />&nbsp;
                        <?php
							}
							if($myfun->session_get('root') or $myfun->session_get('ispass')){
						?>
                            <input type="button" value="转移" onclick="atcMoves('','<?php echo $jboxhtml;?>')" />&nbsp;
                        <?php
							}
							if($funcArr[$mid]['copy'] or $myfun->session_get('root')){
						?>
                            <!--<input type="button" value="复制" onclick="" />&nbsp;-->
                        <?php
							}
						?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="11" style="text-align:right;">
                        	<?php echo $page->fpage(array(0,3,4,5,6,7,8,9));?>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
	</div>
</body>
</html>