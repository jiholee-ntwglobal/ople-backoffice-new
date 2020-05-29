<?php
/*
----------------------------------------------------------------------
file name	 : bl_item_stock.php
comment		 : 이벤트 상품 별도 재고관리
date		 : 2014-11-26
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "400950";
include "_common.php";
auth_check($auth[$sub_menu], "w");


switch($_GET['use_yn']){
	case 'y' :
		$sql_where .= " and a.use_yn = 'y' ";
		break;
	case 'n' :
		$sql_where .= " and a.use_yn = 'n' ";
		break;
}

switch($_GET['isupdate']){
	case 'y' :
		$sql_where .= " and a.isupdate = 'y' ";
		break;
	case 'n' :
		$sql_where .= " and a.isupdate = 'n' ";
		break;
}

if($_GET['it_id']){
	$_GET['it_id'] = trim($_GET['it_id']);
	$sql_where .= " and a.it_id = '".$_GET['it_id']."'";
}

if($_GET['sku']){
	$_GET['sku'] = trim($_GET['sku']);
	$sql_where .= " and b.SKU = '".$_GET['sku']."'";
}

if($_GET['it_name']){
	$_GET['it_name'] = trim($_GET['it_name']);
	$sql_where .= " and b.it_name like '%".mysql_real_escape_string($_GET['it_name'])."%'";
}

# 상품 데이터 로드 #
$sql = sql_query("
	select
		a.uid,
		a.it_id,
		a.qty,a.sell_cnt, a.ch_amount,a.create_dt,
		b.it_maker,b.SKU,
		b.it_name,
		b.it_amount
	from
		yc4_bl_event_item_stock a,
		".$g4['yc4_item_table']." b
	where
		a.it_id = b.it_id
		".$sql_where."
");

while($row = sql_fetch_array($sql)){
	$list_tr .= "
		<tr>
			<td>".$row['it_id']." / ".$row['SKU']."</td>
			<td>".number_format($row['qty'])."</td>
			<td>".number_format($row['sell_cnt'])."</td>
			<td>".number_format($row['ch_amount'])."</td>
			<td>".$row['it_maker']."</td>
			<td>".$row['it_name']."</td>
			<td>".number_format($row['it_amount'])."</td>
			<td>".$row['create_dt']."</td>
			<td>".icon('수정','bl_item_stock_write.php?uid='.$row['uid']).icon('삭제','bl_item_stock_write.php?mode=delete&uid='.$row['uid'])."</td>
		</tr>
	";
}

$g4[title] = "별도 재고관리 상품";
include $g4['full_path']."/adm/admin.head.php";
?>
<style type="text/css">
.tab_wrap{
	overflow:hidden;
}
.tab_wrap > ul{
	overflow:hidden;
	list-style:none;
}
.tab_wrap > ul > li {
	float:left;
	border:1px solid #dddddd;
	padding: 5px;
}
.tab_wrap > ul > li:hover{
	background-color:#eeeeee;
}
.tab_wrap > ul > li.active{
	font-weight:bold;
}
.tab_left{
	float:left;
}
.tab_right{
	float:right;
}
</style>

<div>
	<form action="<?=$_SERVER['PHP_SELF']?>">
		상품코드 <input type="text" name='it_id' value='<?=trim($_GET['it_id']);?>'/>
		UPC <input type="text" name='sku'  value='<?=trim($_GET['sku']);?>'/>
		상품명 <input type="text" name='it_name'  value='<?=trim($_GET['it_name']);?>'/>
		<input type="submit" value='검색' />
	</form>
</div>

<div class='tab_wrap'>
	<ul class='tab_right'>
		<li class='<?= !$_GET['isupdate'] ? "active":""?>'><a href="<?=$_SERVER['PHP_SELF'];?>?use_yn=<?=$_GET['use_yn'];?>">전체</a></li>
		<li class='<?= $_GET['isupdate'] == 'n' ? "active":""?>'><a href="<?=$_SERVER['PHP_SELF'];?>?use_yn=<?=$_GET['use_yn'];?>&isupdate=n">미처리</a></li>
		<li class='<?= $_GET['isupdate'] == 'y' ? "active":""?>'><a href="<?=$_SERVER['PHP_SELF'];?>?use_yn=<?=$_GET['use_yn'];?>&isupdate=y">품절처리완료</a></li>
	</ul>
</div>

<table width='100%'>
	<col width='80'/>
	<col width='50'/>
	<col width='50'/>
	<col width='50'/>
	<col width='100'/>
	<col />
	<col width='50'/>
	<col width='100'/>
	<col />
	<tr align='center'>
		<td>it_id / UPC</td>
		<td>입력재고</td>
		<td>판매수량</td>
		<td>변경가격</td>
		<td>브랜드명</td>
		<td>상품명</td>
		<td>가격</td>
		<td>생성일</td>
		<td><?php echo icon('입력','bl_item_stock_write.php');?></td>
	</tr>
	<?php echo $list_tr;?>
</table>


<?php
include $g4['full_path']."/adm/admin.tail.php";
?>