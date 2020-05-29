<?php 
/*
----------------------------------------------------------------------
file name	 : admin_main_block_cms.php
comment		 : 관리자 메인용 html block 생성(고객센터)
date		 : 2015-05-15
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/
include "db.config.php";

$ople_link = mysql_connect($ople_db['host'], $ople_db['id'], $ople_db['pw']);



$db_selected = mysql_select_db('okflex5');



$admin_rs = mysql_query("select mb_id from g4_auth group by mb_id");

$admin_arr = array('admin');

while($data = mysql_fetch_assoc($admin_rs)){
	array_push($admin_arr,$data['mb_id']);
}


$notice_rs = mysql_query("select bo_notice from g4_board where bo_table = 'qa'");
$notice_info = mysql_fetch_assoc($notice_rs);

$notice_id_arr = explode(PHP_EOL,$notice_info['bo_notice']);

$sql = "SELECT sum(1) as total,
				sum(
					  if(
							 isnull(b.wr_num)
						 AND a.wr_reply != 'A'
						 AND a.wr_is_comment != '1'
						 AND a.wr_id NOT IN ('".implode("','",$notice_id_arr)."')
						 AND a.mb_id NOT IN ('".implode("','",$notice_id_arr)."')
						 AND DATE_FORMAT(a.wr_datetime,'%Y')>=2015,
						 1,
						 0)) as no_answer,
				sum(
					  if(
							 isnull(b.wr_num)
						 AND a.wr_reply != 'A'
						 AND a.wr_is_comment != '1'
						 AND a.wr_id NOT IN ('".implode("','",$notice_id_arr)."')
						 AND a.mb_id NOT IN ('".implode("','",$notice_id_arr)."')
						 AND DATE_FORMAT(a.wr_datetime,'%Y%m%d')=DATE_FORMAT(NOW(),'%Y%m%d'),
						 1,
						 0)) as today_no_answer,
				sum(
					  if(
							 !isnull(b.wr_num)
						 AND (a.wr_reply != 'A' and a.wr_is_comment != '1')						 
						 AND DATE_FORMAT(a.wr_datetime,'%Y%m%d')=DATE_FORMAT(NOW(),'%Y%m%d'),
						 1,
						 0)) as today_answer
				   
			  FROM g4_write_qa a
				   LEFT OUTER JOIN g4_write_qa b
					  ON     (b.wr_reply = 'A' OR b.wr_is_comment = '1')
						 AND a.wr_num = b.wr_num";


$rs = mysql_query($sql);


$cnt_info = mysql_fetch_assoc($rs);

// 스케쥴러 히스토리
error_reporting(E_ALL);
ini_set("display_errors", 1);

$ch = curl_init();

$data = array(
		'channel'=>'ople',
		'process_code'=>'main_block_cms',
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
				<td height='24'><img src='../adm/img/icon_title.gif' width=20 height=9> <font color='#525252'><b>오플 고객센터</b></font> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			</tr>
		</table>
		<table width=100% cellpadding=0 cellspacing=0>
			<tr>
				<td height=3></td>
			</tr>			
		</table>
		</td>
		<td width=20% align=right><a href='./shop_admin/iframe_ople_board.php'><img src='../adm/img/icon_more.gif' width='43' height='11' border=0 align=absmiddle></a></td>
	</tr>
</table>
<table width=100% cellpadding=0 cellspacing=0>
	<tr><td colspan='4' class='line1'></td></tr>
	<tr class='bgcol1 bold col1 ht center'>
		<td>총 등록수</td>
		<td>미답변 갯수</td>
		<td>금일 답변 갯수</td>
		<td>금일 미답변 갯수</td>		
	</tr>
	<tr><td colspan='6' class='line2'></td></tr>
	<tr class='list1 col1 ht center' height="30">
		<td><?php echo number_format($cnt_info['total']); ?></td>
		<td><?php echo number_format($cnt_info['no_answer']); ?></td>
		<td><?php echo number_format($cnt_info['today_answer']); ?></td>
		<td><?php echo number_format($cnt_info['today_no_answer']); ?></td>
	</tr>
	<tr><td colspan='4' class='line2'></td></tr>
</table>
<br/>
<?php
$notice_rs = mysql_query("select bo_notice from g4_board where bo_table = 'iqa'");
$notice_info = mysql_fetch_assoc($notice_rs);

$notice_id_arr = explode(PHP_EOL,$notice_info['bo_notice']);

$sql = "SELECT sum(1) as total,
				sum(
					  if(
							 isnull(b.wr_num)
						 AND a.wr_reply != 'A'
						 AND a.wr_is_comment != '1'
						 AND a.wr_id NOT IN ('".implode("','",$notice_id_arr)."')
						 AND a.mb_id NOT IN ('".implode("','",$notice_id_arr)."')
						 AND DATE_FORMAT(a.wr_datetime,'%Y')>=2015,
						 1,
						 0)) as no_answer,
				sum(
					  if(
							 isnull(b.wr_num)
						 AND a.wr_reply != 'A'
						 AND a.wr_is_comment != '1'
						 AND a.wr_id NOT IN ('".implode("','",$notice_id_arr)."')
						 AND a.mb_id NOT IN ('".implode("','",$notice_id_arr)."')
						 AND DATE_FORMAT(a.wr_datetime,'%Y%m%d')=DATE_FORMAT(NOW(),'%Y%m%d'),
						 1,
						 0)) as today_no_answer,
				sum(
					  if(
							 !isnull(b.wr_num)
						 AND (a.wr_reply != 'A' and a.wr_is_comment != '1')						 
						 AND DATE_FORMAT(a.wr_datetime,'%Y%m%d')=DATE_FORMAT(NOW(),'%Y%m%d'),
						 1,
						 0)) as today_answer
				   
			  FROM g4_write_iqa a
				   LEFT OUTER JOIN g4_write_iqa b
					  ON     (b.wr_reply = 'A' OR b.wr_is_comment = '1')
						 AND a.wr_num = b.wr_num";

$rs = mysql_query($sql);

$cnt_info = mysql_fetch_assoc($rs);
?>
<table width=100% cellpadding=0 cellspacing=0>
	<tr>
		<td width=80% align=left>
		<table border='0' cellpadding='0' cellspacing='1'>
			<tr>
				<td height='24'><img src='../adm/img/icon_title.gif' width=20 height=9> <font color='#525252'><b>아이해피 고객센터</b></font> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			</tr>
		</table>
		<table width=100% cellpadding=0 cellspacing=0>
			<tr>
				<td height=3></td>
			</tr>			
		</table>
		</td>
		<td width=20% align=right><a href='./shop_admin/iframe_ihappy_board.php'><img src='../adm/img/icon_more.gif' width='43' height='11' border=0 align=absmiddle></a></td>
	</tr>
</table>
<table width=100% cellpadding=0 cellspacing=0>
	<tr><td colspan='4' class='line1'></td></tr>
	<tr class='bgcol1 bold col1 ht center'>
		<td>총 등록수</td>
		<td>미답변 갯수</td>
		<td>금일 답변 갯수</td>
		<td>금일 미답변 갯수</td>		
	</tr>
	<tr><td colspan='6' class='line2'></td></tr>
	<tr class='list1 col1 ht center' height="30">
		<td><?php echo number_format($cnt_info['total']); ?></td>
		<td><?php echo number_format($cnt_info['no_answer']); ?></td>
		<td><?php echo number_format($cnt_info['today_answer']); ?></td>
		<td><?php echo number_format($cnt_info['today_no_answer']); ?></td>
	</tr>
	<tr><td colspan='4' class='line2'></td></tr>
</table>
<br/>