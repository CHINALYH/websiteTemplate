<?php
	define('PATH',str_replace("\\","/",rtrim(dirname(__FILE__),'/').'/'));
	
	include PATH.'../../../config.inc.php';
	
	include BASE.'class/database.class.php';
	
	include BASE.'commons/global.fun.php';
	
	include BASE.'admin/session.inc.php';
	
	include BASE.'class/page.class.php';
	
	include BASE.'class/tree.class.php';
	
	if(!strstr($myfun->session_get('nrgllist'),'2') and !$myfun->session_get('root')){
		echo $myfun->out_language("非法登录！！");
		exit;
	}
	
	$p = (int)$_GET['page']?(int)$_GET['page']:1; //页数
	$funcArr = unserialize($myfun->session_get('funclist'));  //栏目基本权限
	if(!$myfun->session_get('root')){
		$menulist = $myfun->session_get('menulist');  //用户菜单栏目权限
	}
	
	//根据url获取查询信息
	$menuid = (int)$_GET['menuid']?(int)$_GET['menuid']:'';
	$adduid = (int)$_GET['adduid']?(int)$_GET['adduid']:'';
	$keyword = trim($_GET['keyword'])?trim($_GET['keyword']):'';
	// 检索栏目设置
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])){
		$menuid = trim($_POST['menuid'])?trim($_POST['menuid']):'';  //检索的栏目
		$adduid = trim($_POST['adduid'])?trim($_POST['adduid']):'';  //检索的发布者
		$keyword = (trim($_POST['keyword'])=="请输入关键字……" or !trim($_POST['keyword']))?'':trim($_POST['keyword']);
		$_GET['page'] = 1;
	}
    
	// 需要审核的菜单(检索)列表
	
	if($myfun->session_get('root')){
		$m_select = '<select name="menuid">';
		$m_select .= "<option value=''>检索所有栏目……</option>";
		$sql = "select id,m_name from `menu_info` where is_check != 0 and is_hdn = 0 and m_type != 6 order by pid asc ,show_order asc";
		$result = $mysqli->query($sql);
		while($row = $result->fetch_assoc()){
			$checked = ($menuid == $row['id'])?"selected='selected'":'';
			$m_select .= "<option value='".$row['id']."' $checked>".$row['m_name']."&nbsp;</option>\n";
		}
		$m_select .= '</select>';
	}else{
		$m_select = '<select name="menuid">';
		$m_select .= "<option value=''>检索所有栏目……</option>";
		$sql = "select id,m_name from `menu_info` where is_check != 0 and is_hdn = 0 and id in($menulist) and m_type != 6 order by pid asc ,show_order asc";
		$result = $mysqli->query($sql);
		$i = 0;
			while($row = $result->fetch_assoc()){

				$checked = ($menuid == $row['id'])?"selected='selected'":'';
				$m_select .= "<option value='".$row['id']."' $checked>".$row['m_name']."&nbsp;</option>\n";
				$menuArr.=$row['id'].',';
				
			}
		}
		$m_select .= '</select>';
		$menuStr = substr($menuArr,0,strlen($menuArr)-1); 
		echo $menuStr;
	
	if(!$menuid and !$menuStr and !$myfun->session_get('root')){
		echo $myfun->out_language("您无栏目需要审核，请联系管理员！！");
		exit;
	}elseif($menuid){
		$row_menu =$mysqli->getField($menuid,'m_name','`menu_info`');
		$menu_name = $row_menu['m_name'];
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
		$category[] =  array('id'=>$row['id'],'pid'=>$row['pid'],'name'=>$row['m_name'],'m_type'=>$row['m_type']);
	}
	$tree = new Tree($category);
	$jboxhtml = '<select name=mid>';
	$jboxhtml .= '<option value=>请选择转移目录……</option>';
	$jboxhtml .= $tree->getTree('0', '<option value=$id $selected>$spacer$name</option>', '0', '', '<optgroup label=$spacer$name></optgroup>'); 
	$jboxhtml  .= '</select>';
	
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
				echo $myfun->out_language("删除成功!!","listncheck.php?page=$p");
				exit;
			}else{
				$handle = $myfun->_mysql_string('<span class="blue">[ 文章删除 ]</span> <span class="red">[ 失败 ]</span> [ ID:'.$atcid.' - '.$row['adduser'].' - '.date("Y-m-d",$row['addtime']).' ] '.$row['title_first']);
				//记录操作日志（失败）
				$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
				$mysqli->query($insert);
				echo $myfun->out_language("删除失败!!","listncheck.php?page=$p");
				exit;
			}
		}else{
			echo $myfun->out_language("URL链接非法！！！");
			exit;
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>未审文章列表</title>
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
            <li>内容管理 >> 未审文章列表</li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>
        <div id="main_top_seach">
          <form action="" method="post">
            <input type="radio" name="ispass" id="ypass" value="1" checked="checked" /> <label for="ypass">未审核</label> &nbsp;&nbsp;
            <span class="green">审核栏目：</span>
			<?php
            	echo $m_select;
			?> &nbsp;&nbsp;
            <select name="adduid">
                <option value="">检索所有添加人……</option>
            <?php
            	$sql = "select id,username from `manage_user_info` order by show_order asc";
				$result = $mysqli->query($sql);
				while($row = $result->fetch_assoc()){
			?>
            	<option value="<?php echo $row['id']?>" <?php if($adduid == $row['id']){?>selected="selected"<?php }?>><?php echo $row['username']?></option>
            <?php		
				}
			?>
            </select> &nbsp;&nbsp;
            <label for="keyword">关键字：</label><input type="text" name="keyword" id="keyword" size="22" style="padding-left:2px;" value="<?php if(!!$keyword){echo $keyword;}else{?>请输入关键字……<?php }?>" onfocus="if (value =='请输入关键字……'){value =''}" onblur="if (value ==''){value='请输入关键字……'}" /> &nbsp;
            <input type="submit" name="search" value="检索" />
          </form>
  		</div>
    	<div id="admin_cnt_inner">
			<div id="listcnt">
            	<table class="table_special">
                	<caption>未审文章列表  <?php if($menu_name){echo '[',$menu_name,']';}?> </caption>
                    <thead>
                	<tr>
                        <th width="3%"><input type="checkbox" groupall="checkAll" /></th>
                        <th width="30%">文章标题</th>
                        <th width="8%">所属菜单</th>
                        <th width="6%">文章属性</th>
                        <th width="17%">添加人</th>
                        <th width="8%">添加时间</th>
                        <th width="8%">审核发布</th>
                        <th colspan="4">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
						//组织检索条件语句
						if($myfun->session_get('root')){
							$condition = "where ispass != 0 and tag = 1 and is_delelte = 1";   //未审核
							if($adduid){
								$condition.=" and adduid = '$adduid'";
							}
							if($menuid){
								$condition.=" and mid = '$menuid'";
							}
						}else{
							$condition = "where ispass !=0 and tag = 1 and is_delelte = 1";   //未审核
							if($adduid){
								$condition.=" and adduid = '$adduid'";
							}
							if($menuid){
								$condition.=" and mid = '$menuid'";
							}else{
								$condition.=" and mid in($menuStr)";
							}
						}
						if($keyword){
							$condition.=" and title_first like '%$keyword%'";
						}

						$sql = "select * from `content_info` $condition";
						
						$result = $mysqli->query($sql);
						$total = $result->num_rows; //信息总数
						$num = SYSTEM_PAGE_NUM; //每页显示数
						$page=new Page($total, $num, "menuid=$menuid&adduid=$adduid&keyword=$keyword");
						$sql = "select * from `content_info` $condition order by istop desc, addtime desc {$page->limit}";
						//echo $sql;
						$result = $mysqli->query($sql);
						while($row = $result->fetch_assoc()){
						  $mid = (int)$row['mid'];
						 // if(($row['ispass'] <= $funcArr[$mid]['check']) or $myfun->session_get('root')){
							//获取菜单信息
							$menuField = array('m_name','m_type','is_check');
							$menuInfo = $mysqli->getField($row['mid'],$menuField,'`menu_info`');
					?>
                	<tr>
                        <td><input type="checkbox" name="items[]" group="checkAll" value="<?php echo $row['id']?>"></td>
                        <td style="text-align:left;">
                        <?php
                        	if($row['message']){
						?>
                        	<span onclick="prompt_info('<?php echo $row['message'];?>','退稿原因');" style="cursor:pointer;" class="green">[查看]</span>
                        <?php
							}
						?>
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
                        <td><?php echo $myfun->getAtcType($menuInfo['m_type'])?></td>
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
						?>
                        	<span class="red" style="cursor:pointer;" onclick="prompt_info('<?php echo $prompt_info;?>','审核进度')"> ×未审核(<?php echo $row['ispass'].'-'.$menuInfo['is_check'];?>)</span>
                        </td>
						<td width="5%"><a href="contentshow.php?mid=<?php echo $row['mid'];?>&atcid=<?php echo $row['id'];?>">预览</a></td>
                        <td width="5%">
                        	<span class="pointer" onclick="atcMoves(<?php echo $row['id'];?>,'<?php echo $jboxhtml;?>')">转移</span>
                        </td>
                        <td width="5%">
                        	<a href="contenteditor.php?mid=<?php echo $row['mid'];?>&atcid=<?php echo $row['id'];?>"><span>编辑</span></a>
                        </td>
                        <td width="5%">
                        	<a href="?act=del&mid=<?php echo $row['mid']?>&atcid=<?php echo $row['id'];?>&page=<?php echo $p;?>" onclick="return confirm('确定要删除吗?')"><span class="red">删除</span></a>
                        </td>
                    </tr>
                    <?php
						  }
				//		}
					?>
                    </tbody>
                    <tfoot>
                    <tr>
                    	<td colspan="1"><input type="checkbox" groupall="checkAll" /></td>
                        <td colspan="10" style="text-align:left; padding-left:10px">
                            <input type="button" value="删除" onclick="option(1)" />&nbsp;
                            <input type="button" value="置顶" onclick="option(2)" />&nbsp;
                            <input type="button" value="取消置顶" onclick="option(3)" />&nbsp;
                            <input type="button" value="转移" onclick="atcMoves('','<?php echo $jboxhtml;?>')" />&nbsp;
                            <input type="button" value="复制" onclick="atcCopys('','<?php echo $jboxhtml;?>')" />&nbsp;
                            <input type="button" value="取消审核" onclick="option(5)" />&nbsp;
                        	<input type="button" value="通过审核" onclick="option(4)" />&nbsp;
                        	<input type="button" value="退稿" onclick="option(6)" />&nbsp;
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