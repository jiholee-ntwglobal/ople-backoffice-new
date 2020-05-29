<?php 
/*
----------------------------------------------------------------------
file name	 : admin_main_block_iq.php
comment		 : 관리자 메인용 html block 생성(상품문의)
date		 : 2015-05-15
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/
include "db.config.php";

$ople_link = mysqli_connect($ople_db['host'], $ople_db['id'], $ople_db['pw'], 'okflex5');

//$db_selected = mysql_select_db('okflex5');

$sql = "SELECT sum(1) AS total_cnt,
		   sum(if(iq_answer = '', 1, 0)) AS no_answer_cnt,
		   sum(
			  if(date_format(iq_time, '%Y%m%d') = date_format(NOW(), '%Y%m%d'),
				 1,
				 0))
			  AS today_cnt,
		   sum(
			  if(
					 iq_answer = ''
				 AND date_format(iq_time, '%Y%m%d') =
						date_format(NOW(), '%Y%m%d'),
				 1,
				 0))
			  AS today_no_answer_cnt
	  FROM yc4_item_qa";

$rs = mysqli_query($ople_link, $sql);

$cnt_info = mysqli_fetch_assoc($rs);

// 스케쥴러 히스토리
error_reporting(E_ALL);
ini_set("display_errors", 1);

$ch = curl_init();

$data = array(
		'channel'=>'ople',
		'process_code'=>'main_block_iq',
		'process_code_sub1'=>null,
		'process_code_sub2'=>null,
		'process_server_ip'=>'209.216.56.107',
		'auth_key'=>'87b5f92afe813417ea006a2b67431d70');

curl_setopt($ch, CURLOPT_URL,"http://209.216.56.104/web_service/scheduler.php");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_HEADER, 1);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec ($ch);

$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $header_size);
$body = substr($response, $header_size);

curl_close ($ch);
?>
<table width=100% cellpadding=0 cellspacing=0>
	<tr>
		<td width=80% align=left>
		<table border='0' cellpadding='0' cellspacing='1'>
			<tr>
				<td height='24'><img src='../adm/img/icon_title.gif' width=20 height=9> <font color='#525252'><b>상품문의</b></font> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			</tr>
		</table>
		<table width=100% cellpadding=0 cellspacing=0>
			<tr>
				<td height=3></td>
			</tr>			
		</table>
		</td>
		<td width=20% align=right><a href='./shop_admin/itemqalist.php'><img src='../adm/img/icon_more.gif' width='43' height='11' border=0 align=absmiddle></a></td>
	</tr>
</table>
<table width=100% cellpadding=0 cellspacing=0>
	<tr><td colspan='4' class='line1'></td></tr>
	<tr class='bgcol1 bold col1 ht center'>
		<td>총 등록수</td>
		<td>미답변 갯수</td>
		<td>금일 등록 갯수</td>
		<td>금일 미답변 갯수</td>		
	</tr>
	<tr><td colspan='6' class='line2'></td></tr>
	<tr class='list1 col1 ht center' height="30">
		<td><?php echo number_format($cnt_info['total_cnt']); ?></td>
		<td><?php echo number_format($cnt_info['no_answer_cnt']); ?></td>
		<td><?php echo number_format($cnt_info['today_cnt']); ?></td>
		<td><?php echo number_format($cnt_info['today_no_answer_cnt']); ?></td>
	</tr>
	<tr><td colspan='4' class='line2'></td></tr>
</table>
<br/>