<?php

$sub_menu = "200700";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");


$sql = sql_query("
SELECT
  a.mb_id,
  a.mb_recommend
FROM
  g4_member AS a,
  g4_member AS b
WHERE
  a.mb_recommend = b.mb_id AND
  a.mb_recommend != '' AND
  date_format(
    a.mb_datetime,
    '%Y%m%d') >= '20150320'
");

$mb_id_arr = array();
/*
$mb_id_arr = array('추천인 아이디','추천자 아이디');
*/
while($row = sql_fetch_array($sql)){
	$mb_id_arr[trim($row['mb_id'])] = $row['mb_recommend'];
}

$recom_od_arr = array();
$recom_od_arr2 = array();
foreach($mb_id_arr as $mb_id => $recom_id){
	$chk = sql_fetch("
		select count(*) as cnt,sum(od_receipt_bank + od_receipt_card + od_receipt_point) as total_amount from ".$g4['yc4_order_table']." where mb_id = '".$mb_id."' 
		and od_receipt_bank + od_receipt_card + od_receipt_point >= od_temp_bank + od_temp_card + od_temp_point - od_dc_amount
	");
	if($chk['cnt']>0){

		
		if(!isset($recom_od_arr[trim($recom_id)])){
			$recom_od_arr[trim($recom_id)] = 0;
		}
		if(!isset($recom_od_arr2[trim($recom_id)])){
			$recom_od_arr2[trim($recom_id)] = 0;
		}

		$recom_od_arr[trim($recom_id)] += 1;
		$recom_od_arr2[trim($recom_id)] += $chk['total_amount'];
	}
}



arsort($recom_od_arr);

$g4[title] = "추천인 랭킹";
include_once ("$g4[admin_path]/admin.head.php");
$no = 1;
?>

<style type="text/css">
.contents_table td{
	padding:5px;
}
</style>
<table width="100%" style='border-collapse: collapse;' border='1' class='contents_table'>
	<col width='60'/>
	<col width=''/>
	<col width='150'/>
	<col width='200'/>
	<tr>
		<td>랭킹</td>
		<td>추천받은 아이디</td>
		<td>주문건이 있는 추천인수</td>
		<td>추천인 누적 주문 금액</td>
	</tr>
	<?php
		foreach($recom_od_arr as $mb_id => $cnt){
			echo "
				<tr>
					<td>".$no."</td>
					<td>".$mb_id."</td>
					<td align='right'>".number_format($cnt)."</td>
					<td align='right'>".number_format($recom_od_arr2[$mb_id])."</td>
				</tr>
			";
			$no++;
		}
	?>
</table>

<?php
include_once ("$g4[admin_path]/admin.tail.php");
?>