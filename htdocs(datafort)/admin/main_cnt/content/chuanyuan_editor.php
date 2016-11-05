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

	$tag = (int)$_GET['tag'];
	if($tag==1){
	$tagname="普通";
	}
	if($tag==2){
		$tagname="职务";
		}	
	$atcid = (int)$_GET['atcid'];
	$uid = $myfun->session_get('id');
	$gid = $myfun->session_get('gid');

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
<script type="text/javascript" src="../../../public/plugins/DatePicker/WdatePicker.js"></script>
<?php
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mend'])){  //提交编辑
		array_pop($_POST);  //去除数组中最后一个元素
	    $fzrq=$_POST['fzrq'];
		$_POST['fzrq'] = strtotime($fzrq);
		$arr = $myfun->_mysql_string($_POST);  //转义特殊字符
		$arrNew = array();
		foreach($arr as $key => $value){
			$arrNew[] = $key.' = \''.$value.'\'';
		}
		$arr_str = implode(',', $arrNew);
		$sql = "update `cyxx_info` set $arr_str where id = '$atcid'";
		$result = $mysqli->query($sql);
		if($result){ //更新文章成功
			echo $myfun->out_language("文章编辑成功!!");
		    exit;
		}else{
		
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
    <?php
        $sql2="select * from cyxx_info where id='$atcid'";
		$rst2=$mysqli->query($sql2);
		$cntinfo=$rst2->fetch_assoc();
		?>
    <div id="addcnt">
      <form action="?mid=<?php echo $mid?>&atcid=<?php echo $atcid?>" method="post" id="addcontent_form">
        <table class="table_all">
          <caption>
          船员信息编辑 [ <?php echo $menuname?> ]
          </caption>
          <thead style="display:none;">
          
            <td colspan="2">&nbsp;</td>
              </thead>
          <tbody>
          <tbody>
            <tr>
              <th class="th_title">姓　　名：</th>
              <td><input type="text" id="name" name="name" class="cntinput" value="<?php echo $cntinfo['name']?>" />
                <span class="red">[必填]</span></td>
            </tr>
            <tr>
              <th class="th_title">职　　务：</th>
              <td><select name="zhiwu" style="width:135px; height:30px; line-height:30px;">
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
                  <option  name="zhiwu" value="<?php echo $row3['id']?>" <?php if($cntinfo['zhiwu']==$row3['id']){?> selected="selected" <?Php }?>><?php echo $row3['link_name']?></option>
                  <?php
					}
				?>
                </select>
                <span class="red">[必填]</span></td>
            </tr>
            <tr>
              <th class="th_title">发证日期：</th>
              <td><input type="text" id="fzrq" class="Wdate" name="fzrq" onClick="WdatePicker()" readonly="readonly" value="<?php echo date('Y-m-d',$cntinfo['fzrq']);?>" />
                <span class="red">[必填]</span></td>
            </tr>
            <tr>
              <th class="th_title">培训机构：</th>
              <td><input type="text" id="pxjg" name="pxjg" class="cntinput" value="<?php echo $cntinfo['pxjg']?>" />
                <span class="red">[必填]</span></td>
            </tr>
            <tr>
              <th class="th_title">发证机构：</th>
              <td><input type="text" id="fzjg" name="fzjg" class="cntinput" value="<?php echo $cntinfo['fzjg']?>" />
                <span class="red">[必填]</span></td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2" align="center"><input type="submit" name="mend" id="mend" value="编辑提交" /></td>
            </tr>
          </tfoot>
        </table>
      </form>
    </div>
  </div>
</div>
</body>
</html>