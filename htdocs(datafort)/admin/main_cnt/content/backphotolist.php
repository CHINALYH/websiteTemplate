<?php
	define('PATH',str_replace("\\","/",rtrim(dirname(__FILE__),'/').'/'));
	
	include PATH.'../../../config.inc.php';
	
	include BASE.'class/database.class.php';
	
	include BASE.'commons/global.fun.php';
	
	include BASE.'admin/session.inc.php';
	
	include BASE.'class/page.class.php';
	
	if(!strstr($myfun->session_get('nrgllist'),'1') and !$myfun->session_get('root')){
		echo $myfun->out_language("非法登录！！");
		exit;
	}
	
	$p = (int)$_GET['page']?(int)$_GET['page']:1; //页数
	$funcArr = unserialize($myfun->session_get('funclist'));
	
	//检索栏目设置
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])){
		$keyword = (trim($_POST['keyword'])=="请输入关键字……")?'':trim($_POST['keyword']);
	}
	$keyword = trim($_GET['keyword'])?trim($_GET['keyword']):$keyword;
	//删除文章
	if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['act']) && $_GET['act'] == 'del'){  

		$id = (int)$_GET['id'];
		if($id){
			/*获取操作日志信息*/
			$row = $mysqli->getField($id, 'title', '`photo_info`');
			$title = $row['title'];
			$uid = $myfun->session_get('id');
			$addtime = time();
			$ip = $myfun->getIP();
			
			$sql = "delete from `photo_info` where id = '$id'";
			$result = $mysqli->query($sql);
			if($result){
				$sql = "select photopath from `photo_path_info` where aid = '$id'";
				$result = $mysqli->query($sql);
				while($row = $result->fetch_assoc()){ //删除服务器上的图片
					if(file_exists(BASE.'uploads/photo/thumb/'.$row['photopath'])){
						@unlink(BASE.'uploads/photo/thumb/'.$row['photopath']);
					}
					if(file_exists(BASE.'uploads/photo/'.$row['photopath'])){
						@unlink(BASE.'uploads/photo/'.$row['photopath']);
					}
				}
				
				$sql = "delete from `photo_path_info` where aid = '$id'";
				$result = $mysqli->query($sql);
				if($result){
					$handle = $myfun->_mysql_string('<span class="blue">[ 图片频道删除 ]</span> <span class="green">[ 成功 ]</span> [ '.$title.' ] ');
					//记录操作日志（成功）
					$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
					$mysqli->query($insert);
					echo $myfun->out_language("删除成功!!","");
					exit;
				}else{
					$handle = $myfun->_mysql_string('<span class="blue">[ 图片频道删除 ]</span> <span class="red">[ 失败 ]</span> [ '.$title.' ] ');
					//记录操作日志（失败）
					$insert = "insert into `handle_log_info` (uid,handle,addtime,ip) values ('$uid','$handle','$addtime','$ip')";
					$mysqli->query($insert);
					echo $myfun->out_language("删除失败!!","");
					exit;
				}
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
<title>退稿图片列表</title>
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
            <li>内容管理 >> 退稿图片列表</li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>
        <div id="main_top_seach">
            <form action="" method="post">
              <label for="keyword">关键字：</label><input type="text" name="keyword" id="keyword" size="22" style="padding-left:2px;" value="<?php if(!!$keyword){echo $keyword;}else{?>请输入关键字……<?php }?>" onfocus="if (value =='请输入关键字……'){value =''}" onblur="if (value ==''){value='请输入关键字……'}" /> &nbsp;
              <input type="submit" name="search" value="搜索" />
            </form>
        </div>
    	<div id="admin_cnt_inner">
			<div id="listcnt">
            	<table class="table_special">
                	<caption>图片频道退稿列表</caption>
                    <thead>
                	<tr>
                        <th width="3%"><input type="checkbox" groupall="checkAll" /></th>
                        <th width="34%">图片标题</th>
                        <th width="7%">所属菜单</th>
                        <th width="7%">图片属性</th>
                        <th width="15%">添加人</th>
                        <th width="7%">添加时间</th>
                        <th width="7%">审核发布</th>
                        <th colspan="4">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
						//组织检索条件语句
						$condition = "where tag = 0";
						
						if(!$myfun->session_get('root')){
							$condition .= " and adduid = ".$myfun->session_get('id');
						}
						
						if($keyword){
							$condition.=" and title like '%$keyword%'";
						}
						
						$sql = "select * from `photo_info` $condition";
						$result = $mysqli->query($sql);
						$total = $result->num_rows; //信息总数
						$num = SYSTEM_PAGE_NUM; //每页显示数
						$page=new Page($total, $num, 'mid='.$mid.'&keyword='.$keyword);
						$sql .=" order by istop desc, addtime desc {$page->limit}";
						$result = $mysqli->query($sql);
						while($row = $result->fetch_assoc()){
							//获取菜单信息
							$menuField = array('m_name','m_type','is_check');
							$menuInfo = $mysqli->getField($row['mid'],$menuField,'`menu_info`')
					?>
                	<tr>
                        <td><input type="checkbox" name="items[]" group="checkAll" value="<?php echo $row['id']?>"></td>
                        <td style="text-align:left;">
							<?php
								$istop = $row['istop']?'<span class="red">[置顶] </span>':'';
								echo ''.$istop.$row['title'];
							?>
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
                        
                        <td width="5%" class="jbox"><a href="">预览</a></td>
                        <td width="5%" class="jbox"><a href="photoctn.php?aid=<?php echo $row['id'];?>"><span>编辑</span></a></td>
                        <td width="5%"><span onclick="prompt_info('<?php echo $row['message'];?>','退稿原因');" style="cursor:pointer;">查看</span></td>
                        <td width="5%">
                        	<a href="?act=del&mid=<?php echo $mid;?>&id=<?php echo $row['id']?>&page=<?php echo $p;?>" onclick="return confirm('确定要删除吗? 数据不可恢复！')"><span class="red">删除</span></a>
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
                            <input type="button" value="删除" onclick="option(8)" />&nbsp;
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