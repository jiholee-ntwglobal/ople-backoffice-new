<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2019-04-05
 * File: hana_bigdata_10shop.php
 */

$sub_menu = "500560";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "하나빅데이터 서프라이즈샵";

if(!isset($_GET['uid']) || trim($_GET['uid']) == ''){
	//back;
}
$ev_uid	= trim($_GET['uid']);

define('bootstrap', true);

include '../admin.head.php';

//SELECT e.uid, e.value1 as it_id, e.value2 as msrp, e.value3 as iherb_amount, e.value4 as qty, e.value5 as sales_qty, e.value6 as ori_it_id, e.value7 AS sort_order
//, i.it_amount_usd, i.it_name, i.it_maker
//FROM yc4_event_data e
//LEFT JOIN yc4_item i ON i.it_id = e.value6
//WHERE e.ev_code = 'hana_bigdata_2019' AND e.ev_data_type = '".$event_info['uid']."' order by cast(e.value7 as int)
//SELECT count(*) FROM yc4_event_data e WHERE e.ev_code = 'hana_bigdata_2019' AND e.ev_data_type = '".$event_info['uid']."'

$sql	= "
	SELECT
	 	e.uid, e.value1 AS it_id, e.value2 AS msrp, e.value3 AS iherb_amount, e.value4 AS qty, e.value5 AS sales_qty, e.value6 AS ori_it_id, e.value7 AS sort_order
	  ,	i.it_amount_usd, i.it_name, i.it_maker
	FROM yc4_event_data e
	LEFT JOIN yc4_item i ON i.it_id = e.value6
	WHERE e.ev_code = 'hana_bigdata_2019' AND e.ev_data_type = '".$ev_uid."' ORDER BY CAST(e.value7 AS int)
";
$res	= sql_query($sql);
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<script type="text/javascript">
function button_event(a,b,c){
	if (confirm("정말 " + b + "하시겠습니까??") == true){
		location.replace("./eventresearch_action.php?seqno=" + a + "&mode=" + c);
	}else{
		return;
	}
}
</script>

<table class="table table-hover table-bordered table-condensed table-striped">
	<thead>
		<tr>
			<th colspan="12" style="text-align: center;">하나 빅데이터 10달러샵 관리</th>
		</tr>
		<tr>
<!--			<td align="center">체크</td>-->
			<td align="center">it_id</td>
			<td align="center">원본 it_id</td>
			<td align="center">브랜드</td>
			<td align="center">상품명</td>
			<td align="center">Msrp</td>
			<td align="center">iherb</td>
			<td align="center">qty</td>
			<td align="center">sales</td>
			<td align="center">현오플 상품가</td>
			<td align="center">표시할인율</td>
			<td align="center">노출순서</td>
			<td align="center">수정</td>
		</tr>
	</thead>
	<tbody>
	<?php
	while($row=mysql_fetch_assoc($res)){
		$name_arr			= explode("||", $row['it_name']);
		$row['item_name']	= $name_arr[1];
        $row['dis_rate']	= ($row['msrp']==0 || $row['msrp']=="") ? @round(($row['it_amount_usd']-10)/$row['it_amount_usd']*100): @round(($row['msrp']-10)/$row['msrp']*100);
		
		?>
		<tr>
<!--			<td>--><?php //echo $row['uid'];?><!--</td>-->
			<td><?php echo $row['it_id'];?></td>
			<td><?php echo $row['ori_it_id'];?></td>
			<td><?php echo $row['it_maker'];?></td>
			<td><?php echo $row['item_name'];?></td>
			<td><?php echo $row['msrp'];?></td>
			<td><?php echo $row['iherb_amount'];?></td>
			<td><?php echo $row['qty'];?></td>
			<td><?php echo $row['sales_qty'];?></td>
			<td><?php echo $row['it_amount_usd'];?></td>
			<td><?php echo $row['dis_rate'];?>%</td>
			<td><?php echo $row['sort_order'];?></td>
			<td><a href="./hana_bigdata_10shop_item_form.php?uid=<?php echo $row['uid'];?>&ev_uid=<?php echo $ev_uid;?>" target="_blank">상품 수정</a></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<?php 
include_once ("$g4[admin_path]/admin.tail.php");
?>