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
	
	//根据url获取查询信息
	$menustr = trim($_GET['menustr'])?trim($_GET['menustr']):'';
	$adduid = (int)$_GET['adduid']?(int)$_GET['adduid']:'';
	$keyword = trim($_GET['keyword'])?trim($_GET['keyword']):'';
	// 检索栏目设置
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])){
		if(trim($_POST['menustr'])=='检索所有栏目……'){
			$_POST['menustr'] = '';
		}
		$menustr = trim($_POST['menustr'])?trim($_POST['menustr']):'';  //检索的栏目
		$adduid = trim($_POST['adduid'])?trim($_POST['adduid']):'';  //检索的发布者
		$keyword = (trim($_POST['keyword'])=="请输入关键字……" or !trim($_POST['keyword']))?'':trim($_POST['keyword']);
		$_GET['page'] = 1;
	}


	/*--------------------------整合菜单栏目数组用于zTree...(start)------------------------------*/	
	if($myfun->session_get('root')){
		$sql = "select id,pid,m_name,m_type from `menu_info` where is_hdn != 1 and m_type != 6 order by pid asc,show_order asc";
	}else{
		$menulist = $myfun->session_get('menulist');//"登录用户"的栏目权限
		$sql = "select id,pid,m_name,m_type from `menu_info` where is_hdn != 1 and m_type != 6 and id in($menulist) order by pid asc,show_order asc";
	}
	$result = $mysqli->query($sql);
	$menuArr = array();
	while($row = $result->fetch_assoc()){
		if($row['m_type'] == 1){
			$menuArr[] = array(
								'id' => $row['id'],	
								'pid' => $row['pid'],
								'name' => $row['m_name'],
								'isParent' => true,
								'nocheck' => true,
								'open' => true
						  );
		}else{
			if($menustr){ //已提交检索栏目
				$menuSelectArr = explode(',',$menustr);
				if(in_array($row['id'],$menuSelectArr)){
					$menuArr[] = array(
										'id' => $row['id'],	
										'pid' => $row['pid'],
										'name' => $row['m_name'],
										'checked' => true,
										'open' => true
								  );	
				}else{
					$menuArr[] = array(
										'id' => $row['id'],	
										'pid' => $row['pid'],
										'name' => $row['m_name'],
										'open' => true
								  );
				}
			}else{ //未提交检索栏目
				$menuArr[] = array(
									'id' => $row['id'],	
									'pid' => $row['pid'],
									'name' => $row['m_name'],
									'open' => true
							  );
			}
		}
	}
	$arrmenu = json_encode($menuArr);
	
	/*--------------------------整合菜单栏目数组用于zTree...(end)--------------------------------*/

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
			/*删除Sql语句*/
			$sql = "delete from `content_info` where id = '$atcid'";
			$result = $mysqli->query($sql);
			if($result){
				$handle = $myfun->_mysql_string('<span class="blue">[ 文章删除 ]</span> <span class="green">[ 成功 ]</span> [ ID:'.$atcid.' - '.$row['adduser'].' - '.date("Y-m-d",$row['addtime']).' ] '.$row['title_first']);
				//记录操作日志（成功）
				$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
				$mysqli->query($insert);
				echo $myfun->out_language("删除成功!!","listycheck.php?page=$p");
				exit;
			}else{
				$handle = $myfun->_mysql_string('<span class="blue">[ 文章删除 ]</span> <span class="red">[ 失败 ]</span> [ ID:'.$atcid.' - '.$row['adduser'].' - '.date("Y-m-d",$row['addtime']).' ] '.$row['title_first']);
				//记录操作日志（失败）
				$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
				$mysqli->query($insert);
				echo $myfun->out_language("删除失败!!","listycheck.php?page=$p");
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
<title>取(消)审文章列表</title>
<link rel="stylesheet" type="text/css" href="../../resource/css/main_cnt.css" />
<script type="text/javascript" src="../../../public/js/jquery.min.js"></script>
<script type="text/javascript" src="../../resource/js/layout.js"></script>

<link rel="stylesheet" type="text/css" href="../../../public/css/zTreeStyle/zTreeStyle.css" />
<script type="text/javascript" src="../../../public/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="../../../public/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript">	
<!--
	var setting = {
		check: {
			enable: true,
			chkboxType: {"Y":"", "N":""}
		},
		view: {
			dblClickExpand: false
		},
		data: {
			keep: {
				parent:true,
				leaf:true
			},
			simpleData: {
				enable:true,
				idKey: "id",
				pIdKey: "pid"
			}
		},
		callback: {
			beforeClick: beforeClick,
			onCheck: onCheck
		}
	};
	var zNodes = <?php echo $arrmenu;?>;
	
	function beforeClick(treeId, treeNode) {
		var zTree = $.fn.zTree.getZTreeObj("treeMenu");
		zTree.checkNode(treeNode, !treeNode.checked, null, true);
		return false;
	}
	
	function onCheck(e, treeId, treeNode) {
		var zTree = $.fn.zTree.getZTreeObj("treeMenu"),
		nodes = zTree.getCheckedNodes(true),
		v = "";
		for (var i=0, l=nodes.length; i<l; i++) {
			v += nodes[i].id + ",";
		}
		if (v.length > 0 ) v = v.substring(0, v.length-1);
		var menuObj = $("#menustr");
		menuObj.attr("value", v);
	}
	
	function showMenu() {
		var menuObj = $("#menustr");
		var menuOffset = $("#menustr").offset();
		$("#menuContent").css({left:menuOffset.left + "px", top:menuOffset.top + menuObj.outerHeight() + "px"}).slideDown("fast");
	
		$("body").bind("mousedown", onBodyDown);
	}
	
	function hideMenu() {
		$("#menuContent").fadeOut("fast");
		$("body").unbind("mousedown", onBodyDown);
	}
	
	function onBodyDown(event) {
		if (!(event.target.id == "menuBtn" || event.target.id == "menustr" || event.target.id == "menuContent" || $(event.target).parents("#menuContent").length>0)) {
			hideMenu();
		}
	}
	
	$(document).ready(function(){
		$.fn.zTree.init($("#treeMenu"), setting, zNodes);
	});
//-->
</script>

<link rel="stylesheet" type="text/css" href="../../../public/plugins/jBox/skinblue/jbox.css" />
<script type="text/javascript" src="../../../public/plugins/jBox/jquery.jBox.min.js"></script>
<script type="text/javascript" src="../../../public/plugins/jBox/jquery.jBox-zh-CN.js"></script>
<script type="text/javascript" src="../../resource/js/jbox.js"></script>
</head>

<body>
    <div id="admin_cnt">
        <ul class="menu_breadcrumbs">
            <li>当前位置：</li>
            <li>内容管理 >> 取(消)审文章列表</li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>
        <div id="main_top_seach">
          <form action="" method="post">
            <input type="radio" name="is_delelte" id="is_delelte" value="0" checked="checked" /> <label for="npass">取(消)审核</label> &nbsp;&nbsp;        
            <input type="text" id="menustr" name="menustr" readonly="readonly" onclick="showMenu();" style="padding-left:5px;" value="<?php if($menustr){echo $menustr;}else{echo "检索所有栏目……";} ?>" />&nbsp;<a id="menuBtn" href="#" onclick="showMenu(); return false;" class="green">点击选择</a>&nbsp;&nbsp;&nbsp;
            
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
            <div id="menuContent" style=" display:none; position: absolute;">
                <ul id="treeMenu" class="ztree" style="margin-top:0; border:1px solid #ccc; background:#fff; min-width:180px; width:expression_r( document.body.clientWidth < 181?'180px':'auto' );"></ul>
            </div>
          </form>
  		</div>
    	<div id="admin_cnt_inner">
			<div id="listcnt">
            	<table class="table_special">
                	<caption>取(消)审文章列表</caption>
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
						$condition = "where is_delelte = 0";   //取消审核
						if($adduid){
							$condition.=" and adduid = '$adduid'";
						}
						if($menustr){
							$condition.=" and mid in($menustr)";
						}else{
							if($myfun->session_get('menulist')){
								$condition.=" and mid in($menulist)";
							}
						}
						if($keyword){
							$condition.=" and title_first like '%$keyword%'";
						}
						
						$sql = "select * from `content_info` $condition";
						$result = $mysqli->query($sql);
						$total = $result->num_rows; //信息总数
						$num = SYSTEM_PAGE_NUM; //每页显示数
						$page=new Page($total, $num, "menustr=$menustr&adduid=$adduid&keyword=$keyword");
						$sql = "select * from `content_info` $condition order by istop desc, addtime desc {$page->limit}";
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
								$istop = $row['istop']?'<span class="red">[置顶] </span>':'';
								echo $istop.$row['title_first'];
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
                        	<span class="green" style="cursor:pointer;" onclick="prompt_info('<?php echo $prompt_info;?>','审核进度')">√已审核</span>
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
					?>
                    </tbody>
                    <tfoot>
                    <tr>
                    	<td colspan="1"><input type="checkbox" groupall="checkAll" /></td>
                        <td colspan="10" style="text-align:left; padding-left:10px">
                            <input type="button" value="删除" onclick="option(1)" />&nbsp;
                            <input type="button" value="恢复审核" onclick="option(7)" />&nbsp;
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