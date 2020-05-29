<?php
include "_common.php";

$_GET['it_id'] = $_GET['it_id'] ? trim($_GET['it_id']) : '';

$_GET['st_dt_y'] = $_GET['st_dt_y'] ? $_GET['st_dt_y'] : date('Y');
$_GET['st_dt_m'] = $_GET['st_dt_m'] ? $_GET['st_dt_m'] : date('m');
$_GET['st_dt_d'] = $_GET['st_dt_d'] ? $_GET['st_dt_d'] : date('d');
$_GET['en_dt_y'] = $_GET['en_dt_y'] ? $_GET['en_dt_y'] : date('Y');
$_GET['en_dt_m'] = $_GET['en_dt_m'] ? $_GET['en_dt_m'] : date('m');
$_GET['en_dt_d'] = $_GET['en_dt_d'] ? $_GET['en_dt_d'] : date('d');

$st_dt = $_GET['st_dt_y'] . '-' . $_GET['st_dt_m'] . '-' . $_GET['st_dt_d'] ;
$en_dt = $_GET['en_dt_y'] . '-' . $_GET['en_dt_m'] . '-' . $_GET['en_dt_d'] ;

$sql = "
	select
		sum(b.ct_qty) as cnt,
		sum(b.ct_qty * b.ct_amount) as amount
	from
		yc4_order a
		left join
		yc4_cart b on a.on_uid = b.on_uid
	where
		b.it_id = '".$_GET['it_id']."'
		and
		left(a.od_time,10) between '".$st_dt."' and '".$en_dt."'
";


$sql2 = "
	and
	b.ct_status in ('준비','배송','완료')
";

if($_GET['it_id']){
	$sales = sql_fetch($sql);
	$sales_cnt = $sales['cnt'];
	$all = sql_fetch($sql.$sql2);
	$all_cnt = $all['cnt'];
}


include $g4['full_path']."/adm/admin.head.php";

?>

<form action="<?=$_SERVER['PHP_SELF']?>" method='get'>
	it_id <input type="text" name='it_id' value='<?php echo $_GET['it_id'];?>' />
	시작일
	<select name="st_dt_y" id="">
	<?php
	for($y=date('Y'); $y>=date('Y')-10; $y--){
		echo "<option value='".$y."' ".($y == $_GET['st_dt_y'] ? "selected":"").">".$y."</option>";
	}
	?>
	</select>
	<select name="st_dt_m" id="">
	<?php
	for($m=1; $m<=12; $m++){
		$m = str_pad($m,2,0,STR_PAD_LEFT);
		echo "<option value='".$m."' ".($m == $_GET['st_dt_m'] ? "selected":"").">".$m."</option>";
	}
	?>
	</select>
	<select name="st_dt_d" id="">
	<?php
	for($d=1; $d<=31; $d++){
		$d = str_pad($d,2,0,STR_PAD_LEFT);
		echo "<option value='".$d."' ".($d == $_GET['st_dt_d'] ? "selected":"").">".$d."</option>";
	}
	?>
	</select>
	종료일

	<select name="en_dt_y" id="">
	<?php
	for($y=date('Y'); $y>=date('Y')-10; $y--){
		echo "<option value='".$y."' ".($y == $_GET['en_dt_y'] ? "selected":"").">".$y."</option>";
	}
	?>
	</select>
	<select name="en_dt_m" id="">
	<?php
	for($m=1; $m<=12; $m++){
		$m = str_pad($m,2,0,STR_PAD_LEFT);
		echo "<option value='".$m."' ".($m == $_GET['en_dt_m'] ? "selected":"").">".$m."</option>";
	}
	?>
	</select>
	<select name="en_dt_d" id="">
	<?php
	for($d=1; $d<=31; $d++){
		$d = str_pad($d,2,0,STR_PAD_LEFT);
		echo "<option value='".$d."' ".($d == $_GET['en_dt_d'] ? "selected":"").">".$d."</option>";
	}
	?>
	</select>
	<input type="submit" value='조회' />
</form>
<?php if($_GET['it_id']){?>
<br />
<table width='100%' border='1'>
	<tr>
		<td>상품코드</td>
		<td>총판매량</td>
		<td>총판매량금액</td>
		<td>판매량(입금확인)</td>
		<td>판매량(입금확인)금액</td>
		<td>재고수량</td>
	</tr>
	<tr>
		<td><?php echo $_GET['it_id'];?></td>
		<td><?php echo number_format($sales_cnt);?></td>
		<td><?php echo number_format($sales['amount']);?></td>
		<td><?php echo number_format($all_cnt);?></td>
		<td><?php echo number_format($all['amount']);?></td>
		<td><?php echo number_format(get_it_stock_qty($_GET['it_id']));?></td>
	</tr>
</table>


<?php }?>
<?php
include $g4['full_path']."/adm/admin.tail.php";
?>