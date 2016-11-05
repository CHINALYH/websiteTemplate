<?php
	define('PATH',str_replace("\\","/",rtrim(dirname(__FILE__),'/').'/'));
	
	include PATH.'../../../config.inc.php';
	
	include BASE.'class/database.class.php';
	
	include BASE.'commons/global.fun.php';
	
	include BASE.'admin/session.inc.php';
	
	include BASE.'class/page.class.php';
	
	include BASE.'class/tree.class.php';
	
	$tag=$_GET['tag'];
	if($tag==1){
		$tagname="普通";
		}
	if($tag==2){
		$tagname="职务";
		}	
	if(!strstr($myfun->session_get('nrgllist'),'1') and !$myfun->session_get('root')){
		echo $myfun->out_language("非法登录！！");
		exit;
	}
	
	//获取url参数
	
	$keyword = trim($_GET['keyword'])?trim($_GET['keyword']):'';
	//检索栏目设置
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])){
	
		$keyword = (trim($_POST['keyword'])=="请输入关键字……" or !trim($_POST['keyword']))?'':trim($_POST['keyword']);
	}
	
	
	//删除文章
	if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['act']) && $_GET['act'] == 'del'){  
			$sql = "delete from `cyxx_info` where id = '$atcid'";
			$result = $mysqli->query($sql);
			if($result){
			
				echo $myfun->out_language("删除成功!!","chuanyuan_list.php?tag=$tag&keyword=$keyword&page=$p");
				exit;
			}else{	
				echo $myfun->out_language("删除失败!!","chuanyuan_list.php?tag=$tag&keyword=$keyword&page=$p");
				exit;
			}
	}
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
    <li>船员信息管理 >> 船员信息列表</li>
    <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
  </ul>
  <ul class="main_top_menu  cntlist_ul">
    <li class="top_menu"><a href="chuanyuan_list.php?tag=<?php echo $tag;?>" class="active"><?php echo $tagname; ?>船员信息列表</a></li>
    <li class="top_menu"><a href="chuanyuan_add.php?tag=<?php echo $tag;?>"><?php echo $tagname; ?>船员信息添加</a></li>
    <li id="search">
      <form action="" method="post">
        <label for="keyword">姓名关键字：</label>
        <input type="text" name="keyword" id="keyword" size="22" style="padding-left:2px;" value="<?php if(!!$keyword){echo $keyword;}else{?>请输入关键字……<?php }?>" onfocus="if (value =='请输入关键字……'){value =''}" onblur="if (value ==''){value='请输入关键字……'}" />
        &nbsp;
        <input type="submit" name="search" value="搜索" />
      </form>
    </li>
  </ul>
  <div id="admin_cnt_inner">
    <div id="listcnt">
      <table class="table_special">
        <caption>
        <?php echo $tagname; ?>船员信息列表
        </caption>
        <thead>
          <tr>
            <th width="5%"><input type="checkbox" groupall="checkAll" /></th>
            <th width="15%">姓名</th>
            <th width="17%">职务</th>
            <th width="16%">发证日期</th>
            <th width="16%">培训机构</th>
            <th width="17%">发证机构</th>
            <th colspan="4">操作</th>
          </tr>
        </thead>
        <tbody>
          <?php						
						//组织检索条件语句
						$condition = "where tag = '$tag' ";
				
						if($keyword){
							$condition.=" and  name like '%$keyword%' ";
						}
						$sql = "select * from `cyxx_info` $condition";
						$result = $mysqli->query($sql);
						$total = $result->num_rows; //信息总数
						$num = SYSTEM_PAGE_NUM; //每页显示数
						$page=new Page($total, $num, "tag=$tag&keyword=$keyword");
						$sql = "select * from `cyxx_info` $condition order by addtime desc {$page->limit}";
						$result = $mysqli->query($sql);
						while($row = $result->fetch_assoc()){
	
					?>
          <tr>
            <td><input type="checkbox" name="items[]" group="checkAll" value="<?php echo $row['id']?>"></td>
            <td style="text-align:center;"><?php
							
								echo $row['name'];
							?></td>
            <td><span class="blue">
              <?php 
							$atcinfo = $mysqli->getField($row['zhiwu'],'link_name','`fenlei_info`');
						 echo $atcinfo ['link_name'];?>
              </span></td>
            <td><?php echo date("Y-m-d",$row['addtime'])?></td>
            <td><?php
							echo $row['pxjg'];
                        ?></td>
            <td><?php
							echo $row['fzjg'];
                        ?></td>
            <td width="7%"><?php
                        	if($myfun->session_get('root') or $myfun->session_get('ispass')){
                        ?>
              <a href="chuanyuan_editor.php?tag=<?php echo $row['tag'];?>&atcid=<?php echo $row['id'];?>"><span>编辑</span></a>
              <?php
							}else{
						?>
              <span class="gray pointer">编辑</span>
              <?php	
							}
						?></td>
            <td width="7%"><?php
                        	if($myfun->session_get('root') or $myfun->session_get('ispass')){
                        ?>
              <a href="?act=del&mid=<?php echo $row['mid']?>&atcid=<?php echo $row['id'];?>&page=<?php echo $p;?>" onclick="return confirm('确定要删除吗?')"><span class="red">删除</span></a>
              <?php
							}else{
						?>
              <span class="gray pointer">删除</span>
              <?php	
							}
						?></td>
          </tr>
          <?php
						}
					?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="1"><input type="checkbox" groupall="checkAll" /></td>
            <td colspan="10" style="text-align:left; padding-left:10px"><input type="button" value="删除" onclick="option(1)" />
              &nbsp;</td>
          </tr>
          <tr>
            <td colspan="11" style="text-align:right;"><?php echo $page->fpage(array(0,3,4,5,6,7,8,9));?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
</body>
</html>