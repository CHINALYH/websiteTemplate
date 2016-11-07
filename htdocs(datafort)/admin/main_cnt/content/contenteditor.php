<?php
	define('PATH',str_replace("\\","/",rtrim(dirname(__FILE__),'/').'/'));
	
	include PATH.'../../../config.inc.php';
	
	include BASE.'class/database.class.php';
	
	include BASE.'commons/global.fun.php';
	
	include BASE.'admin/session.inc.php';

	if(!strstr($myfun->session_get('nrgllist'),'1') and !$myfun->session_get('root')){
		echo $myfun->out_language("非法登录！！");
		exit;
	}
	$funcArr = unserialize($myfun->session_get('funclist'));

	$mid = (int)$_GET['mid'];
	
	$atcid = (int)$_GET['atcid'];
	$uid = $myfun->session_get('id');
	$gid = $myfun->session_get('gid');
	$pid = $myfun->session_get('pid');
	$tag = $myfun->session_get('tag');
	$menulist = $myfun->session_get('menulist');
	if($mid){
		if(!$myfun->session_get('root')){ //判断用户是否拥有此栏目权限
			$menulist = explode(',',$myfun->session_get('menulist'));
			if(!in_array($mid,$menulist)){
				echo $myfun->out_language("您无此菜单操作权限！！！");
				exit;
			}
		}
		$menuArr = explode(',',$menulist);
		$fieldArr = array('m_name','m_type','is_check','abspath');
		$row = $mysqli->getField($mid,$fieldArr,'`menu_info`');

		$abspath = $row['abspath'];
		$zwgkArr = explode('-',$abspath);
		$zid = $zwgkArr[1];
		
		$fieldArr = array('m_name','m_type','is_check');
		$row = $mysqli->getField($mid,$fieldArr,'`menu_info`');
		$menuname = $row['m_name'];
		$menutype = $row['m_type'];
		$ischeck = $row['is_check'];
		
		$sql = "select * from `content_info` where id = '$atcid'";
		$cntinfo = $mysqli->getRowsRst($sql);
	}else{
		echo $myfun->out_language("URL链接有误！！！");
		exit;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>文章编辑</title>
<link rel="stylesheet" type="text/css" href="../../resource/css/main_cnt.css" />
<script type="text/javascript" src="../../../public/js/jquery.min.js"></script>
<script type="text/javascript" src="../../resource/js/layout.js"></script>
<script type="text/javascript" src="../../resource/js/check.js"></script>


<script type="text/javascript" src="../../../kindeditor/kindeditor.js"></script>
<script type="text/javascript" src="../../../kindeditor/lang/zh_CN.js"></script>
<script>
    KindEditor.ready(function(K) {
        var editor = K.create('#atc_cnt', {
			themeType : 'newskin',
			urlType : 'absolute',
            uploadJson : '../../../kindeditor/php/upload_json.php',
            fileManagerJson : '../../../kindeditor/php/file_manager_json.php',
            allowFileManager : true,              //控制是否能浏览服务器文件的功能按钮
			items : [
			<?php if($myfun->session_get('root')){?>'source', '|', <?php }?> 'undo', 'redo', '|', 'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
			'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
			'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
			'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', '/',
			'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
			'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image','multiimage',
			'flash', 'media','flv', 'insertfile', 'table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
			'anchor', 'link', 'unlink'
			]
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
		
		/*K('#getsummary').click(function(){  //自动获取编辑器中的内容摘要
			var content = editor.text();
			$('#summary').val(content);

		});*/
    });
	function setValue(value){
		$('#atc_pic').attr("value",value);
		$('#piclist').hide();
	}
</script>
<?php
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mend'])){  //提交编辑
		array_pop($_POST);  //去除数组中最后一个元素
		if(empty($_POST['link_url']) && empty($_POST['atc_cnt'])){  //提交数据分析
			echo $myfun->out_language("跳转链接和文章内容不能同时为空！！！");
			exit;
		}elseif(!empty($_POST['link_url']) && !empty($_POST['atc_cnt'])){
			$_POST['atc_cnt'] = '';
		}
		unset($_POST['imgFile']);
		$_POST['summary'] = $myfun->form2db($_POST['summary']);
		$_POST['istop'] = $_POST['istop']?$_POST['istop']:0;
		$_POST['topctn'] = $_POST['topctn']?$_POST['topctn']:0;
		$_POST['focus'] = $_POST['focus']?$_POST['focus']:0;
		$_POST['is_sync'] = (int)$_POST['is_sync']?(int)$_POST['is_sync']:0;
		$_POST['title_bold'] = (int)$_POST['title_bold']?(int)$_POST['title_bold']:0;
		
		$_POST['addtime'] = strtotime($_POST['addtime']);
		$_POST['tag'] = 1;
	
		$arr = $myfun->_mysql_string($_POST);  //转义特殊字符

		$arrNew = array();
		foreach($arr as $key => $value){
			$arrNew[] = $key.' = \''.$value.'\'';
		}
		$arr_str = implode(',', $arrNew);
		if($arr['randnum']){
			$sql = "update `content_info` set $arr_str where randnum = '$arr[randnum]'";
		}else{
			$sql = "update `content_info` set $arr_str where id = '$atcid'";
		}
		//echo $sql;
		//exit;
		$result = $mysqli->query($sql);
		
		/*获取操作日志信息*/
		$fieldArr = array('title_first','adduser','addtime');
		$row = $mysqli->getField($atcid,$fieldArr,'`content_info`');
		$uid = $myfun->session_get('id');
		$addtime = time();
		$ip = $myfun->getIP();
		
		if($result){ //更新文章成功
			$handle = $myfun->_mysql_string('<span class="blue">[ 文章编辑 ]</span> <span class="green">[ 成功 ]</span> [ ID:'.$atcid.' - '.$row['adduser'].' - '.date("Y-m-d",$row['addtime']).' ] '.$row['title_first']);
			//记录操作日志（成功）
			$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
			$result = $mysqli->query($insert);
			/*echo $myfun->out_language("文章编辑成功!!");*/
			if($result){
				//-----------------------------同步到日照市政务网------------------------------
				if($_POST['is_sync']){
			?>
				<script type="text/javascript">
					var url = "http://htgl.rizhao.gov.cn/Super_Manage_Cms/interface/jxgov/option.php?C_Name=%E8%8E%92%E5%8E%BF%E4%BA%BA%E6%B0%91%E6%94%BF%E5%BA%9C&C_Num=a799f31b839c024d3d56d2489a636a18&synid=<?php echo $atcid;?>&callback=?";
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
					echo $myfun->out_language("文章编辑成功!!");
				}
			}
			exit;
		}else{
			$handle = $myfun->_mysql_string('<span class="blue">[ 文章编辑 ]</span> <span class="red">[ 失败 ]</span> [ ID:'.$atcid.' - '.$row['adduser'].' - '.date("Y-m-d",$row['addtime']).' ] '.$row['title_first']);
			//记录操作日志（失败）
			$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
			$mysqli->query($insert);
			echo $myfun->out_language("文章编辑失败!!");
			exit;
		}
	}
?>
</head>

<body>
    <div id="admin_cnt">
        <ul class="menu_breadcrumbs">
            <li>当前位置：</li>
            <li>内容管理 >> 文章编辑</li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>

    	<div id="admin_cnt_inner">
			<div id="addcnt"><form action="?mid=<?php echo $mid?>&atcid=<?php echo $atcid?>" method="post" id="addcontent_form">
            	<table class="table_all">
                  <caption>文章编辑 [ <?php echo $menuname?> ]</caption>
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
                      	 <option value="0" <?php if($cntinfo['loudongleve']==0){?> selected="selected" <?php }?> >0</option>
                         <option value="1" <?php if($cntinfo['loudongleve']==1){?> selected="selected" <?php }?>>1</option>
                         <option value="2" <?php if($cntinfo['loudongleve']==2){?> selected="selected" <?php }?>>2</option>
                         <option value="3" <?php if($cntinfo['loudongleve']==3){?> selected="selected" <?php }?>>3</option>
                         <option value="4" <?php if($cntinfo['loudongleve']==4){?> selected="selected" <?php }?>>4</option>
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
                    	if($cntinfo['randnum']){ //若存在随机数，则显示同步栏目
					?>
                    <!-- <tr>
                      <th class="th_title">同时发布到：</th>
                      <td>
                      	<?php
                    							$more_menu = $cntinfo['more_menu'];
                        	$sql = "select m_name from `menu_info` where id in($more_menu)";
                    							$result = $mysqli->query($sql);
                    							while($row = $result->fetch_assoc()){
                    								$m_name_arr[] = $row['m_name'];
                    							}
                    							echo '<span class="green">'.implode(' / ',$m_name_arr).'</span>';
                    						?> 
                        <input type="hidden" name="randnum" value="<?php echo $cntinfo['randnum']?>" />
                      </td>
                    </tr> -->
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
                     <?php /*?>   <?php
                    if($zid==113){?>
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
                      	<textarea name="atc_cnt" id="atc_cnt" style="width:780px;height:300px;"><?php echo htmlspecialchars($myfun->db2form($cntinfo['atc_cnt']));?></textarea>
                        <span class="red">[若跳转链接为空，此项必填]</span>
                      </td>
                    </tr>
                   
                    <tr>
                      <th class="th_title">内容提要：</th>
                      <td>
                      	<textarea name="summary" id="summary" style="width:500px;height:100px;"><?php echo $cntinfo['summary'];?></textarea>
                        <span id="getsummary" class="green" style="cursor:pointer;"></span>
                      </td>
                    </tr> 
                   
                    <?php 
                    	if($menutype == '7'){
                    ?>
                    <tr>
                      <th class="th_title">上传视频：</th>
                      <td>
                      	<input type="text" id="atc_video" name="atc_video" class="cntinput" value="<?php echo $cntinfo['atc_video'];?>"  />
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
                      <th class="th_title">文章置顶：</th>
                      <td><input type="checkbox" name="istop" id="istop" value="1"<?php if($cntinfo['istop'] == '1'){?> checked="checked"<?php }?>> <label for="istop">置顶</label>
                      	  <input type="checkbox" name="topctn" id="topctn" value="1"<?php if($cntinfo['topctn'] == '1'){?> checked="checked"<?php }?>> <label for="topctn">头条新闻</label>
                          <!--<input type="checkbox" name="focus" id="focus" value="1"<?php if($cntinfo['focus'] == '1'){?> checked="checked"<?php }?>> <label for="focus">焦点图</label>--></td>
                    </tr>
                    <?php
						}
					?>
                 <!--   <tr>
                    	<th class="th_title">审核人：</th>
                        <td>
                        	<input type="text" name="edituser" size="18" id="edituser" value="<?php echo $cntinfo['edituser'];?>" />
                            <span class="red">[必填]</span>
                        </td>
                    </tr>-->
                    <?php
                    	if($funcArr[$mid]['interfaced'] or $myfun->session_get('root')){
					?>
                    <!-- <tr>
                    	<th class="th_title">同步接口：</th>
                        <td>
                        <?php
                        	if($cntinfo['is_sync'] != 1){
                    						?>
                        	<input type="checkbox" name="is_sync" id="is_sync" value="1" />
                            <span class="red">[ 是否同步到日照市政务网 ]</span>
                        <?php
                    							}else{
                    						?>
                        	<span class="gray">[ 已同步到日照市政务网 ]</span>
                        <?php		
                    							}
                    						?>
                        </td>
                    </tr> -->
                    <?php
						}
					?>
                    <tr>
                      <th class="th_title">添加属性：</th>
                      <td>
                      	稿件来源：<input size="18" type="text" name="is_where" id="is_where" class="inputtext" value="<?php echo $cntinfo['is_where'];?>" />&nbsp;&nbsp;&nbsp;
                        添加时间：<input type="text" name="addtime" id="addtime" class="inputtext" size="22" value="<?php echo date("Y-m-d H:i:s",$cntinfo['addtime'])?>" />&nbsp;&nbsp;&nbsp;
                        浏览次数：<input type="text" name="times" id="times" class="inputtext" size="8" value="<?php echo $cntinfo['times']?>" />&nbsp;&nbsp;&nbsp;
                        <span class="red">[如不做修改，保留默认即可]</span>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="2" align="center">
                        <input type="submit" name="mend" id="mend" value="编辑提交" />
                      </td>
                    </tr>
                  </tfoot>
                </table></form>
            </div>
        </div>
	</div>
</body>
</html>