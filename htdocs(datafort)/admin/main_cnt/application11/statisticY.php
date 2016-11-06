<?php

	define('PATH',str_replace("\\","/",rtrim(dirname(__FILE__),'/').'/'));
	
	include PATH.'../../../config.inc.php';
	
	include BASE.'class/database.class.php';
	
	include BASE.'commons/global.fun.php';
	
	include BASE.'admin/session.inc.php';
	
	/*if(!strstr($myfun->session_get('yymklist'),'5') and !$myfun->session_get('root')){
		echo $myfun->out_language("非法登录！！",'');
		exit();
	}*/
	
	$year = date("Y");
	
	$sql = "select sum(u_nums) as utotal,sum(p_nums) as ptotal from `atc_statistic_info` where by_year = '$year'";
	$row = $mysqli->getRowsRst($sql);
	$utotal = $row['utotal'];  //上传总数
	$ptotal = $row['ptotal'];  //采用总数
	
	/*月统计信息*/
	$sql = "select * from `atc_statistic_info` where by_year = '$year' order by u_nums desc;";
	$result = $mysqli->query($sql);
	$dataUArr = array(); //投稿信息
	$dataPArr = array(); //采用信息
	while($row = $result->fetch_assoc()){
		$uplode = @round($row['u_nums']/$utotal*100);
		$pass = @round($row['p_nums']/$ptotal*100);
		$filedArr = $mysqli->getField($row['uid'],'departname','`manage_user_info`');
		$departname = $filedArr['departname'];
		
		$dataUArr[] = "['".$departname."',$uplode]";
		$dataPArr[] = "['".$departname."',$pass]";
	}
	$dataUStr = implode(',',$dataUArr);
	$dataPStr = implode(',',$dataPArr);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>投稿统计</title>
<link rel="stylesheet" type="text/css" href="../../resource/css/main_cnt.css" />
<script type="text/javascript" src="../../../public/js/jquery.min.js"></script>
<script type="text/javascript" src="../../resource/js/layout.js"></script>
<script type="text/javascript" src="../../../public/plugins/Highcharts/js/highcharts.js"></script>
<script type="text/javascript">
	var chartM;
	var chartY;
	$(function(){
		 chartM = new Highcharts.Chart({
            chart: {
                renderTo: 'containerU',
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: '日照莒县各部门"年"投稿饼状图'
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.percentage.toPrecision(2) +' %';
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        formatter: function() {
                            return '<b>'+ this.point.name +'</b>: '+ this.percentage.toPrecision(2) +' %';
                        }
                    }
                }
            },
            series: [{
                type: 'pie',
                name: '投稿统计',
                data: [<?php echo $dataUStr;?>]
            }]
		 });
		 
		 chartY = new Highcharts.Chart({
            chart: {
                renderTo: 'containerP',
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: '日照莒县各部门"年"采用饼状图'
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.percentage.toPrecision(2) +' %';
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        formatter: function() {
                            return '<b>'+ this.point.name +'</b>: '+ this.percentage.toPrecision(2) +' %';
                        }
                    }
                }
            },
            series: [{
                type: 'pie',
                name: '投稿统计',
                data: [<?php echo $dataPStr;?>]
            }]
		 });
	});
</script>
</head>

<body>
    <div id="admin_cnt">
        <ul class="menu_breadcrumbs">
            <li>当前位置：</li>
            <li>应用模块 >> 投稿统计 >> 年统计</li>
            <li id="back"><a href="javascript:history.go(-1);"><span>返回</span></a></li>
        </ul>
        <ul class="main_top_menu">
        	<li class="top_menu"><a href="statisticM.php">月统计</a></li>
        	<li class="top_menu"><a href="statisticY.php" class="active">年统计</a></li>
            <li id="printbtn"><a href="javascript:window.print();"><span>打印</span></a></li>
        </ul>
    	<div id="admin_cnt_inner">
			<div id="addcnt">
            	<div id="containerU" class="container" style="height: 300px;"></div>
                <div id="containerP" class="container" style="height: 300px;"></div>
                <table class="table_all">
                  <caption>年统计列表柱状图</caption>
                  <thead>
                  	<tr>
                   	  	<th width="22%" class="style_th">单位名称</th>
                        <th width="78%" class="style_th">统计结果</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php					
					$sql = "select * from `atc_statistic_info` where by_year = '$year' order by u_nums desc;";
					$result = $mysqli->query($sql);
					while($row = $result->fetch_assoc()){
						$uplode = @round($row['u_nums']/$utotal*100);
						$pass = @round($row['p_nums']/$ptotal*100);
						$pass = $pass?$pass:0.5;
				  ?>
                    <tr>
                      <td class="style_td">
                      	<?php
							$filedArr = $mysqli->getField($row['uid'],'departname','`manage_user_info`');
							echo $filedArr['departname'];
						?>
                      </td>
                      <td class="style_td" style="text-align:left;">
                      	<table width="100%">
                          <tr>
                            <td width="10%">投稿：<?php echo $row['u_nums'];?></td>
                            <td width="90%">
                            	<div class="s_upload" title="<?php echo $uplode.'%'?>" style="width:<?php echo $uplode;?>%;">&nbsp;</div>
                            </td>
                          </tr>
                          <tr>
                            <td>采用：<?php echo $row['p_nums'];?></td>
                            <td>
                            	<div class="s_pass" title="<?php echo $pass.'%'?>" style="width:<?php echo $pass;?>%;">&nbsp;</div>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  <?php
					}
				  ?>
                  </tbody>
                  <tfoot>
                  	<tr><td colspan="3">&nbsp;</td></tr>
                  </tfoot>
                </table>
            </div>
        </div>
	</div>
</body>
</html>