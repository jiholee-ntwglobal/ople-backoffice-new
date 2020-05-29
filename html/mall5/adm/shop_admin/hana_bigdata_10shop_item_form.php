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

if(isset($_POST['uid'])){

    $ev_uid = trim($_POST['ev_uid']);
    $ev_item_uid = trim($_POST['uid']);
    $msrp = trim($_POST['msrp']);
    $iherb = trim($_POST['iherb']);
    $qty = trim($_POST['qty']);
    $sort_order = trim($_POST['sort_order']);

    if(!is_numeric($sort_order) || !is_numeric($qty) || !is_numeric($msrp)){
        alert('MSRP, 판매 수량, 노출 순서를 전부 입력했는지 확인해주세요.');
    }

    if(!$ev_item_uid || $ev_item_uid==''){
        alert('필수값이 존재하지 않습니다. 개발팀에 문의해주세요.');
    }


sql_query("update yc4_event_data
            set value2 = '".$msrp."', value3 = '".$iherb."', value4 = '".$qty."', value7 = '".$sort_order."'
	        WHERE uid='".$ev_item_uid."'
");

    alert('수정을 왼료하였습니다','./hana_bigdata_10shop_item_form.php?uid='.$ev_item_uid.'&ev_uid='.$ev_uid);
}

if(!isset($_GET['uid']) || trim($_GET['uid']) == ''){
	//back;
}


$ev_item_uid = trim($_GET['uid']);

$ev_uid	= trim($_GET['ev_uid']);

// post data update area
//	alert($msg,$url);

define('bootstrap', true);

include '../admin.head.php';

//SELECT e.uid, e.value1 as it_id, e.value2 as msrp, e.value3 as iherb_amount, e.value4 as qty, e.value5 as sales_qty, e.value6 as ori_it_id, e.value7 AS sort_order
//, i.it_amount_usd, i.it_name, i.it_maker
//FROM yc4_event_data e
//LEFT JOIN yc4_item i ON i.it_id = e.value6
//WHERE e.ev_code = 'hana_bigdata_2019' AND e.ev_data_type = '".$event_info['uid']."' order by cast(e.value7 as int)
//SELECT count(*) FROM yc4_event_data e WHERE e.ev_code = 'hana_bigdata_2019' AND e.ev_data_type = '".$event_info['uid']."'

$ev_item_data = sql_fetch("
	SELECT
	 	e.uid
	  ,	e.ev_data_type AS info_uid
	  ,	e.value1 AS it_id
	  , e.value2 AS msrp
	  , e.value3 AS iherb_amount
	  , e.value4 AS qty
	  , e.value5 AS sales_qty
	  , e.value6 AS ori_it_id
	  , e.value7 AS sort_order
	  ,	i.it_amount_usd, i.it_name, i.it_maker
	FROM yc4_event_data e
	LEFT JOIN yc4_item i ON i.it_id = e.value6
	WHERE e.uid='".$ev_item_uid."'
");

?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" name='frm'>
    <input type="hidden" name="uid" value="<?php echo $ev_item_data['uid'];?>">
    <input type="hidden" name="ev_uid" value="<?php echo $ev_uid;?>">
	<table name="content" class="table table-striped" style="width:800px">
		<tr>
			<th colspan="2">하나빅데이터 10달러샵 상품정보수정</th>
		</tr>
		<tr>
			<td colspan="2"><?php echo $ev_item_data['it_name']; ?></td>
		</tr>
		<tr>
			<td><b>이벤트 상품코드</b></td>
			<td><b>원본 상품코드</b></td>
		</tr>
		<tr>
			<td><?php echo $ev_item_data['it_id']; ?></td>
			<td><?php echo $ev_item_data['ori_it_id']; ?></td>
		</tr>
		<tr>
			<td><b>MSRP</b></td>
			<td><input type="text" name='msrp' value='<?php echo $ev_item_data['msrp']; ?>'/></td>
		</tr>
		<tr>
			<td><b>Iherb</b></td>
			<td><input type="text" name='iherb' value='<?php echo $ev_item_data['iherb_amount']; ?>'/></td>
		</tr>
		<tr>
			<td><b>판매 준비 수량</b></td>
			<td><input type="text" name='qty' value='<?php echo $ev_item_data['qty']; ?>'/></td>
		</tr>
		<tr>
			<td><b>현재 판매 수량</b></td>
			<td><?php echo $ev_item_data['sales_qty'];?></td>
		</tr>
		<tr>
			<td><b>노출 순서</b></td>
			<td><input type="text" name='sort_order' value='<?php echo $ev_item_data['sort_order']; ?>'/></td>
		</tr>
		<tr>
			<td colspan="2" align="center">
                <button type="button" onclick="location.href='./hana_bigdata_10shop_detail.php?uid=<?php echo $ev_uid;?>'">리스트</button>
                <button type="submit">수정</button>
			</td>
		</tr>-->
	</table>
</form>

<?php 
include_once ("$g4[admin_path]/admin.tail.php");
?>