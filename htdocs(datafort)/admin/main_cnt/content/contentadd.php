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
	$mid = (int)$_GET['mid'];
	$uid = $myfun->session_get('id');
	$gid = $myfun->session_get('gid');
	$pid = $myfun->session_get('pid');
	$tag = $myfun->session_get('tag');
	$trueip = $myfun->session_get('trueip');
	$menulist = $myfun->session_get('menulist');
	$funcArr = unserialize($myfun->session_get('funclist'));
	
	if($mid){
	
		if(!$myfun->session_get('root')){ //判断用户是否拥有此栏目权限
			$menulist = explode(',',$myfun->session_get('menulist'));
			if(!in_array($mid,$menulist)){
				echo $myfun->out_language("您无该栏目操作的权限！！！");
				exit;
			}
		}
		$menuArr = explode(',',$menulist);
		$fieldArr = array('m_name','m_type','is_check','abspath');
		$row = $mysqli->getField($mid,$fieldArr,'`menu_info`');

		$abspath = $row['abspath'];
		$zwgkArr = explode('-',$abspath);
		$zid = $zwgkArr[1];
		
		$fieldArr = array('m_name','m_type','is_check');  //栏目信息
		$row = $mysqli->getField($mid,$fieldArr,'`menu_info`');
		$menuname = $row['m_name'];
		$menutype = $row['m_type'];
		$ischeck = $row['is_check'];
		
		if($menutype == 2){ //获取单篇文章内容
			$sql = "select * from `content_info` where mid = '$mid'";
			$cntinfo = $mysqli->getRowsRst($sql);
		}
	}else{
		echo $myfun->out_language("URL链接有误！！！");
		exit;
	}
	
	/*--------------------------整合菜单栏目数组用于zTree...(start)------------------------------*/	
	if($myfun->session_get('root')){
		$sql = "select id,pid,m_name,m_type from `menu_info` where is_hdn != 1 and id != '$mid' and m_type !=2 and m_type !=6 and m_type !=8 order by pid asc,show_order asc";
	}else{
		$menulist = $myfun->session_get('menulist');//"登录用户"的栏目权限
		$sql = "select id,pid,m_name,m_type from `menu_info` where is_hdn != 1 and id in($menulist) and id != '$mid' and m_type !=2 and m_type !=6 and m_type !=8 order by pid asc,show_order asc";
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
								'nocheck' => true
								/*'open' => true*/
						  );
		}else{
			$menuArr[] = array(
								'id' => $row['id'],	
								'pid' => $row['pid'],
								'name' => $row['m_name']
								/*'open' => true*/
						  );
		}
	}
	$arrmenu = json_encode($menuArr);
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
<script type="text/javascript" src="../../resource/js/check.js"></script>

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
		var menuObj = $("#more_menu");
		menuObj.attr("value", v);
	}
	
	function showMenu() {
		var menuObj = $("#more_menu");
		var menuOffset = $("#more_menu").offset();
		$("#menuContent").css({left:menuOffset.left + "px", top:menuOffset.top + menuObj.outerHeight() + "px"}).slideDown("fast");
	
		$("body").bind("mousedown", onBodyDown);
	}
	
	function hideMenu() {
		$("#menuContent").fadeOut("fast");
		$("body").unbind("mousedown", onBodyDown);
	}
	
	function onBodyDown(event) {
		if (!(event.target.id == "menuBtn" || event.target.id == "more_menu" || event.target.id == "menuContent" || $(event.target).parents("#menuContent").length>0)) {
			hideMenu();
		}
	}
	
	$(document).ready(function(){
		$.fn.zTree.init($("#treeMenu"), setting, zNodes);
	});
//-->
</script>

<script type="text/javascript" src="<?php echo APPPATH;?>kindeditor/kindeditor.js"></script>
<script type="text/javascript" src="<?php echo APPPATH;?>kindeditor/lang/zh_CN.js"></script>
<script type="text/javascript">
    KindEditor.ready(function(K) {
        var editor = K.create('#atc_cnt', {
			themeType : 'newskin',
			urlType : 'absolute',
            uploadJson : '<?php echo APPPATH;?>kindeditor/php/upload_json.php',
            fileManagerJson : '<?php echo APPPATH;?>kindeditor/php/file_manager_json.php',
            allowFileManager : true,              //控制是否能浏览服务器文件的功能按钮
			items : [
			<?php if($myfun->session_get('root')){?>'source', '|', <?php }?>'undo', 'redo', '|', 'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
			'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
			'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
			'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', '/',
			'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
			'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image','multiimage',
			'flash', 'media','flv', 'insertfile', 'table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
			'anchor', 'link', 'unlink'
			],
            afterCreate : function() {
                var self = this;
                K.ctrl(document, 13, function() {
                    self.sync();
                    K('#addcontent_form').submit();
                });
                K.ctrl(self.edit.doc, 13, function() {
                    self.sync();
                    K('#addcontent_form').submit();
                });
            }
        });
		K('#getsrc').click(function(){  //自动获取编辑器中的图片
			var imgHtml = editor.image();
			if(imgHtml){
				$('#piclist').show();
				$('#piclist').html(imgHtml);
			}else{
				alert("编辑器中没有图片");
			}
		});

		<?php if($menutype == '7'){?>
		/*上传视频*/
		var uploadbutton1 = K.uploadbutton({
			button : K('#mediabtn')[0],
			fieldName : 'imgFile',
			url : '<?php echo APPPATH;?>kindeditor/php/upload_json.php?dir=media',
			afterUpload : function(data) {
				if (data.error === 0) {
					var url = K.formatUrl(data.url, 'absolute');
					K('#atc_video').val(url);
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
		<?php }?>

		/*上传图片*/
		var uploadbutton2 = K.uploadbutton({
			button : K('#picbtn')[0],
			fieldName : 'imgFile',
			url : '<?php echo APPPATH;?>kindeditor/php/upload_json.php?dir=image',
			afterUpload : function(data) {
				if (data.error === 0) {
					var url = K.formatUrl(data.url, 'absolute');
					K('#atc_pic').val(url);
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
	
	function setValue(value){
		$('#atc_pic').attr("value",value);
		$('#piclist').hide();
	}
</script>
<?php
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sub'])){  //-------发布文章-------
		array_pop($_POST);  //去除数组中最后一个元素
		unset($_POST['imgFile']);
		if(empty($_POST['link_url']) && empty($_POST['atc_cnt'])){  //提交数据分析
			echo $myfun->out_language("跳转链接和文章内容不能同时为空！！！");
			exit;
		}elseif(!empty($_POST['link_url']) && !empty($_POST['atc_cnt'])){
			$_POST['atc_cnt'] = '';
		}
		$_POST['summary'] = $myfun->form2db($_POST['summary']);
		$_POST['istop'] = (int)$_POST['istop']?(int)$_POST['istop']:0;
		$_POST['topctn'] = (int)$_POST['topctn']?(int)$_POST['topctn']:0;
		$_POST['focus'] = (int)$_POST['focus']?(int)$_POST['focus']:0;
		$_POST['is_sync'] = (int)$_POST['is_sync']?(int)$_POST['is_sync']:0;
		$_POST['title_bold'] = (int)$_POST['title_bold']?(int)$_POST['title_bold']:0;
		
		$_POST['tag'] = 1; //是否退稿（1否）
		$_POST['addtime'] = strtotime($_POST['addtime']);
		$_POST['times'] = (int)$_POST['times']?(int)$_POST['times']:0;
		$_POST['ip'] = $myfun->getIP();
		if($menutype == 2 and $cntinfo){  //------编辑单篇文章-------
			$arr = $myfun->_mysql_string($_POST);  //转义特殊字符
			/*print_r($arr);*/
			$arrNew = array();
			foreach($arr as $key => $value){
				$arrNew[] = $key.' = \''.$value.'\'';
			}
			$arr_str = implode(',', $arrNew);
			$sql = "update `content_info` set $arr_str where mid = '$mid';";
			if($arr['link_url']){
				$sql .= "update `menu_info` set link_url = '".$arr['link_url']."' where id = '$mid';";
			}
		}else{  //------发布新文章-------
			$_POST['adduid'] = $myfun->session_get('id');
			$_POST['addgid'] = $myfun->session_get('gid');
			$_POST['addpid'] = $myfun->session_get('pid')?$myfun->session_get('pid'):0;
			
			if($_POST['more_menu'] == '请选择栏目……' or empty($_POST['more_menu'])){  //----无附加栏目添加-----
				$_POST['more_menu'] = '';
			
				if($myfun->session_get('root')){ //超级管理员不需要审核
					$_POST['ispass'] = 0;
					$_POST['checkuser'] = $myfun->session_get('username');
				}else{  //普通管理员根据栏目情况确定审不审核
					$u_addnums = 0; //上传累加数
					$p_addnums = 0; //采用累加数
					if($ischeck){ //需要审核
						if($funcArr[$mid]['check']){
							if($ischeck > $funcArr[$mid]['check']){ //有普通审核权限的人，发布文章不需要从头审
								$u_addnums = 1; //上传累加数
								$_POST['ispass'] =  $funcArr[$mid]['check']+1;
							}else{ //有最终审核权限的人，发布文章不用审核
								$u_addnums = 1;
								$p_addnums = 1; //采用累加数
								$_POST['ispass'] = 0;
							}
							$_POST['checkuser'] = $myfun->session_get('username');
						}else{
							$u_addnums = 1; //上传累加数
							$_POST['ispass'] = 1;
						}
					}else{  //不需要审核
						$u_addnums = 1;
						$p_addnums = 1;
						$_POST['ispass'] = 0;
						$_POST['checkuser'] = $myfun->session_get('username');
					}
				}		
				$arr = $myfun->_mysql_string($_POST);  //转义特殊字符
				
				$str_key = @implode(',',array_keys($arr));
				$str_val = @implode('\',\'',array_values($arr));
				$sql = "insert into `content_info` ($str_key) values ('$str_val');";
				//echo $sql;
				//exit;
				if($menutype == 2 and $arr['link_url']){
					$sql .= "update `menu_info` set link_url = '".$arr['link_url']."' where id = '$mid';";
				}
			}else{
				$_POST['randnum'] = time().rand(10000, 99999);
				$_POST['more_menu'] = trim($_POST['more_menu'].','.$_POST['mid']);
				$midStr = $_POST['more_menu'];
				$midAtr = explode(',',$midStr);
				$u_addnums = 0; //上传累加数
				$p_addnums = 0; //采用累加数
				$sql ='';
				foreach($midAtr as $menuid){
					if($myfun->session_get('root')){ //超级管理员不需要审核
						$_POST['ispass'] = 0;
						$_POST['checkuser'] = $myfun->session_get('username');
					}else{  //普通管理员根据栏目情况确定审不审核
						$return = $mysqli->getField($menuid,'is_check','`menu_info`');
						$ischeck = $return['is_check'];
						if($ischeck){ //需要审核
							if($funcArr[$menuid]['check']){
								if($ischeck > $funcArr[$menuid]['check']){ //有普通审核权限的人，发布文章不需要从头审
									$u_addnums = $u_addnums + 1;  //上传累加数
									$_POST['ispass'] = $funcArr[$menuid]['check']+1;
								}else{ //有最终审核权限的人，发布文章不用审核
									$u_addnums = $u_addnums + 1;
									$p_addnums = $p_addnums + 1;
									$_POST['ispass'] = 0;
								}
								$_POST['checkuser'] = $myfun->session_get('username');
							}else{
								$u_addnums = $u_addnums + 1;  //上传累加数
								$_POST['ispass'] = 1;
							}
						}else{  //不需要审核
							$u_addnums = $u_addnums + 1;
							$p_addnums = $p_addnums + 1;
							$_POST['ispass'] = 0;
							$_POST['checkuser'] = $myfun->session_get('username');
						}
					}
					$_POST['mid'] = $menuid;
					$arr = $myfun->_mysql_string($_POST);  //转义特殊字符
					
					
					$str_key = implode(',',array_keys($arr));
					$str_val = implode('\',\'',array_values($arr));
					$sql .= "insert into `content_info` ($str_key) values ('$str_val');";
					
					
				}
			}
		}
		$ispass=$_POST['ispass'];
		//echo $sql;exit;
		$result = $mysqli->multi_query($sql);
		
		$synid = $mysqli->insert_id; //若同步操作，需要次id标注（下方用到）
		
		/**********************无需审核的用户或栏目发布时就加积分*****************************************/
		if($ispass==0){
			$y=date('Y');
			$m=date('m');
			$d=date('d');
			$sql= "insert into `save_log_info`(uid,trueip,content_id,group_id,addtime,tag,y,m,d,jifen) values ('$id','$trueip','$synid','$gid','$_POST[times]','3','$y','$m','$d','2');";
			$mysqli->query($sql);
			}
		if($ispass==1){
			$y=date('Y');
			$m=date('m');
			$d=date('d');
			$sql= "insert into `save_log_info`(uid,trueip,content_id,group_id,addtime,tag,y,m,d,jifen) values ('$id','$trueip','$synid','$gid','$_POST[times]','3','$y','$m','$d','0.5');";
			$mysqli->query($sql);
			}	
		
		/*获取操作日志信息*/
		$uid = $myfun->session_get('id');
		$addtime = time();
		$ip=gethostbyname($_ENV['COMPUTERNAME']);;
		$mysqli = new Database(); //注意：此处有待进一步验证，每次multi_query 后，就有这个问题（链接数据库失效）
		if($result){
			if($u_addnums > 0 || $p_addnums > 0){
				/*更新投稿数*/
				$year = date("Y");
				$mouth = date("m");
				$sql = "SELECT * FROM `atc_statistic_info` WHERE uid='$uid' AND by_year='$year' AND by_mouth='$mouth'";
				$result = $mysqli->query($sql);
				$num = $result->num_rows;

				if($num > 0){
					$sql="UPDATE `atc_statistic_info` SET u_nums=(u_nums+$u_addnums),p_nums=(p_nums+$p_addnums) WHERE uid='$uid' AND by_year='$year' AND by_mouth='$mouth'";
				}else{
					$sql="INSERT INTO `atc_statistic_info` (uid,u_nums,p_nums,by_year,by_mouth) VALUES ('$uid','$u_addnums','$p_addnums','$year','$mouth')";
				}
				$mysqli->query($sql);
			}
			
			/*----记录操作日志----*/
			if($menutype == 2){
				$handle = $myfun->_mysql_string('<span class="blue">[ 文章更新 ]</span> <span class="green">[ 成功 ]</span> <span class="green">[ '.$menuname.' ]</span>');
			}else{
				$handle = $myfun->_mysql_string('<span class="blue">[ 文章添加 ]</span> <span class="green">[ 成功 ]</span> '.$arr['title_first']);
			}
			//记录操作日志（成功）
			$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
			$result = $mysqli->query($insert);
			/*echo $myfun->out_language("文章发布成功!!","contentadd.php?mid=$mid");*/
			if($result){
				//-----------------------------同步到日照市政务网------------------------------
				if($_POST['is_sync']){
			?>
				<script type="text/javascript">
					var url = "http://htgl.rizhao.gov.cn/Super_Manage_Cms/interface/jxgov/option.php?C_Name=%E8%8E%92%E5%8E%BF%E4%BA%BA%E6%B0%91%E6%94%BF%E5%BA%9C&C_Num=a799f31b839c024d3d56d2489a636a18&synid=<?php echo $synid;?>&callback=?";
					jQuery.getJSON(url, function(data){
						if(data.rst == 1){
						<?php
							$sql = "update `content_info` set is_sync = 1 where id = $synid";
							$mysqli->query($sql);
						?>
							alert("文章发布(同步)成功!!!");
							history.go(-1);
						}else{
							alert("文章发布成功,同步失败!!"+data.rst);
							history.go(-1);
						}
					});
				</script>
			<?php
				}else{
					echo $myfun->out_language("文章发布成功!!","contentadd.php?mid=$mid");
				}
			}
			exit;
		}else{
			if($menutype == 2){
				$handle = $myfun->_mysql_string('<span class="blue">[ 文章更新 ]</span> <span class="red">[ 失败 ]</span> <span class="green">[ '.$menuname.' ]</span>');
			}else{
				$handle = $myfun->_mysql_string('<span class="blue">[ 文章添加 ]</span> <span class="red">[ 失败 ]</span> '.$arr['title_first']);
			}
			//记录操作日志（失败）
			$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
			$mysqli->query($insert);
			//echo $sql;
			echo $myfun->out_language("文章发布失败!!","contentadd.php?mid=$mid");
			exit;
		}
	}
?>
</head>

<body>
    <div id="admin_cnt">
        <ul class="menu_breadcrumbs">
            <li>当前位置：</li>
            <li>内容管理 >> 文章添加</li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>
        <?php
        	if($menutype != 2){
		?>
        <ul class="main_top_menu">
        	<li class="top_menu"><a href="contentlist.php?mid=<?php echo $mid;?>">文章列表</a></li>
        	<li class="top_menu"><a href="contentadd.php?mid=<?php echo $mid;?>" class="active">文章添加</a></li>
        </ul>
        <?php
			}
		?>
    	<div id="admin_cnt_inner">
			<div id="addcnt"><form action="?mid=<?php echo $mid?>" method="post" id="addcontent_form">
            	<table class="table_special">
                  <caption>文章发布 [ <?php echo $menuname?> ]</caption>
                  <thead style="display:none;"><td colspan="2">&nbsp;</td></thead>
                  <tbody>
                  <?php
                  if($mid==11){
				  ?>
                  <tr>
                      <th class="th_title">漏洞编号：</th>
                      <td>
                      	<input type="text" id="loudongcode" name="loudongcode" class="cntinput" value="<?php echo $cntinfo['loudongcode']?>" />
                        <span class="red">[必填]</span>
                      </td>
                    </tr>
                    <tr>
                      <th class="th_title">漏洞级别：</th>
                      <td>
                      <select name="loudongleve">
                      	 <option value="0">0</option>
                         <option value="1">1</option>
                         <option value="2">2</option>
                         <option value="3">3</option>
                         <option value="4">4</option>
                      </select>
                      
                        <span class="red">[必填]</span>
                      </td>
                    </tr>
                    <tr>
                      <th class="th_title">漏洞名称：</th>
                      <td>
                      	<input type="text" id="loudongname" name="loudongname" class="cntinput" value="<?php echo $cntinfo['loudongname']?>" />
                        <span class="red">[必填]</span>
                      </td>
                    </tr>
                    <?php }?>
                    <tr>
                      <th class="th_title">文章标题：</th>
                      <td>
                      	<input type="text" id="title_first" name="title_first" class="cntinput" value="<?php echo $cntinfo['title_first']?>" />
                        <span class="red">[必填]</span>
                      </td>
                    </tr>
                    <!-- <tr>
                      <th class="th_title">副标题：</th>
                      <td><input type="text" id="title_second" name="title_second" class="cntinput" value="<?php echo $cntinfo['title_second']?>" /></td>
                    </tr>
                    <tr>
                      <th class="th_title">标题颜色：</th>
                      <td>
                        <select name="title_color" id="title_color">
                          <option value="black" style="color:black;"<?php if($cntinfo['title_color'] == 'black'){?> selected="selected"<?php }?>>
                          ▅▅▅▅▅▅▅▅▅▅▅▅▅▅▅▅</option>
                          <option value="red" style="color:red;"<?php if($cntinfo['title_color'] == 'red'){?> selected="selected"<?php }?>>
                          ▅▅▅▅▅▅▅▅▅▅▅▅▅▅▅▅</option>
                          <option value="blue" style="color:blue;"<?php if($cntinfo['title_color'] == 'blue'){?> selected="selected"<?php }?>>
                          ▅▅▅▅▅▅▅▅▅▅▅▅▅▅▅▅</option>
                          <option value="green" style="color:green;"<?php if($cntinfo['title_color'] == 'green'){?> selected="selected"<?php }?>>
                          ▅▅▅▅▅▅▅▅▅▅▅▅▅▅▅▅</option>
                          <option value="orange" style="color:orange;"<?php if($cntinfo['title_color'] == 'orange'){?> selected="selected"<?php }?>>
                          ▅▅▅▅▅▅▅▅▅▅▅▅▅▅▅▅</option>
                          <option value="navy" style="color:navy;"<?php if($cntinfo['title_color'] == 'navy'){?> selected="selected"<?php }?>>
                          ▅▅▅▅▅▅▅▅▅▅▅▅▅▅▅▅</option>
                        </select>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input name="title_bold" type="checkbox" id="title_bold" value="1"<?php if($cntinfo['title_bold'] == '1'){?> checked="checked"<?php }?>>
                        <label for="title_bold"><span style="font-weight:bold;">是否加粗</span></label>
                      </td>
                    </tr> -->
                    <?php
                    	if($menutype != 2){
					?>
                    <tr>
                      <th class="th_title">同时发布到：</th>
                      <td>
                      	<input type="text" id="more_menu" name="more_menu" readonly="readonly" onclick="showMenu();" style="padding-left:5px;" value="请选择栏目……" />&nbsp;<a id="menuBtn" href="#" onclick="showMenu(); return false;" class="green">点击选择</a>&nbsp;&nbsp;&nbsp;
                        <div id="menuContent" style=" display:none; position: absolute;">
                            <ul id="treeMenu" class="ztree" style="margin-top:0; width:180px; border:1px solid #ccc; background:#fff;"></ul>
                        </div>
                      </td>
                    </tr>
                    <?php
						}
					?>
                    <tr>
                      <th class="th_title">跳转连接：</th>
                      <td>
                      	<input type="text" id="link_url" name="link_url" class="cntinput" value="<?php echo $cntinfo['link_url'];?>" />
                        <span class="red">[添加后，文章内容无效]</span> 绝对路径，示例 http://www.baidu.com 
                      </td>
                    </tr>
                  <?php /*?>  <?php
                    if($zid==9999){?>
					<tr>
                      <th class="th_title">主题分类：</th>
                      <td>
					  	<?php echo $myfun->zwgkSubjectType($cntinfo['SubjectType'])?>
                        <span class="red">[必填]</span></td>
                    </tr>
                    <tr>
                      <th class="th_title">服务对象分类：</th>
                      <td>
					  	<?php echo $myfun->zwgkobjectType($cntinfo['ObjectType'])?>
                        <span class="red">[必填]</span></td>
                    </tr>
                    <tr>
                      <th class="th_title">公开类型：</th>
                      <td>
					  	<?php echo $myfun->zwgkOpenType($cntinfo['OpenType'])?>
                        <span class="red">[必填]</span></td>
                    </tr>	
					<?php
                    }
					?><?php */?>
                    <tr>
                      <th class="th_title">文章内容：</th>
                      <td>
                      	<textarea name="atc_cnt" id="atc_cnt" style="width:780px;height:300px;"><?php echo htmlspecialchars($cntinfo['atc_cnt']);?></textarea>
                        <span class="red">[若跳转链接为空，此项必填]</span>
                      </td>
                    </tr>
                  
                  <tr>
                      <th class="th_title">内容提要：</th>
                      <td>
                      	<textarea name="summary" id="summary" style="width:500px;height:100px;"><?php echo $cntinfo['summary'];?></textarea>
                        <span id="getsummary" class="green" style="cursor:pointer;"> </span>
                      </td>
                    </tr> 
                  
                    <?php 
                    	if($menutype == '7' or $mid==17){
                    ?>
                    <tr>
                      <th class="th_title">上传视频：</th>
                      <td>
                      	<input type="text" id="atc_video" name="atc_video" class="cntinput" value="<?php echo $cntinfo['atc_video'];?>" readonly="readonly" />
                         <button id="mediabtn" value="点击上传">点击上传</button>
                        <span class="green">[ 支持格式：flv、mp4； ]</span>
                      </td>
                    </tr>
                    <?php 
                    	}
                    ?>
                    <tr>
                      <th class="th_title">封面图片：</th>
                      <td>
                      	<input type="text" id="atc_pic" name="atc_pic" class="cntinput" value="<?php echo $cntinfo['atc_pic'];?>"  />
                      	<button id="picbtn" value="点击上传">点击上传</button> 或 
                        <span id="getsrc" class="green" style="cursor:pointer;">[ 自动获取 ]</span>
                        <div id="piclist" style="display:none;"></div>
                      </td>
                    </tr>
                    <?php
                    	if(($funcArr[$mid]['top'] == 1) or $myfun->session_get('root')){
					?>
                    <tr>
                      <th class="th_title">文章属性：</th>
                      <td><input type="checkbox" name="istop" id="istop" value="1"<?php if($cntinfo['istop'] == '1'){?> checked="checked"<?php }?>> <label for="istop">置顶</label>
                      	  <input type="checkbox" name="topctn" id="topctn" value="1"<?php if($cntinfo['topctn'] == '1'){?> checked="checked"<?php }?>> <label for="topctn">头条新闻</label>
                          </td>
                    </tr>
                    <?php
						}
					?>
               <!--     <tr>
                    	<th class="th_title">审核人：</th>
                        <td>
                        	<input type="text" name="edituser" size="18" id="edituser" />
                            <span class="red">[必填]</span>
                        </td>
                    </tr>-->
                    <?php
                    	if($funcArr[$mid]['interfaced'] or $myfun->session_get('root')){
					?>
                    <!-- <tr>
                    	<th class="th_title">同步接口：</th>
                        <td>
                        	<input type="checkbox" name="is_sync" id="is_sync" value="1" />
                            <span class="red">[ 是否同步到日照市政务网 ]</span>
                        </td>
                    </tr> -->
                    <?php
						}
					?>
                    <tr>
                      <th class="th_title">添加属性：</th>
                      <td>
                      	稿件来源：<input size="18" type="text" name="is_where" id="is_where" class="inputtext" value="中安比特" /> <span class="red">[必填]</span>&nbsp;&nbsp;&nbsp;
                        添加时间：<input type="text" name="addtime" id="addtime" class="inputtext" size="22" value="<?php if($menutype == 2 and $cntinfo){echo date("Y-m-d H:i:s",$cntinfo['addtime']);}else{echo date("Y-m-d H:i:s");}?>" />&nbsp;&nbsp;&nbsp;
                        <?php
                        	if($myfun->session_get('root')){
						?>
                        浏览次数：<input type="text" name="times" id="times" class="inputtext" size="8" value="<?php if($menutype == 2 and $cntinfo){echo $cntinfo['times'];}else{echo 0;}?>" />&nbsp;&nbsp;&nbsp;
                        <span class="red">[如不做修改，保留默认即可]</span>
                        <?php
							}
						?>
                        <input type="hidden" name="adduser" value="<?php if($menutype == 2 and $cntinfo){echo $cntinfo['adduser'];}else{echo $myfun->session_get('username');}?>" />
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="2" align="center">
                      	<input type="hidden" name="mid" value="<?php echo $mid;?>" />
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