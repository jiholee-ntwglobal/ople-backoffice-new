<?php
/*
----------------------------------------------------------------------
file name	 : main_hotdeal_item.php
comment		 : 메인 핫딜존 관리
date		 : 2015-01-22
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "600300";
include "_common.php";
auth_check($auth[$sub_menu], "r");


$qstr = $qstr2 = $qstr3 = $_GET;
unset($qstr['page'],$qstr['m_type']);
unset($qstr2['page'],$qstr2['fg']);
$qstr = http_build_query($qstr);
$qstr2 = http_build_query($qstr2);
$qstr3 = http_build_query($qstr3);


if($_GET['mode'] == 'delete'){
	if(!$_GET['uid']){
		alert('잘못된 경로로 접근하셨습니다.');
		exit;
	}
	$sql = "
		delete from yc4_hotdeal_item where uid = '".$_GET['uid']."'
	";

	if(!sql_query($sql)){
		alert('처리중 오류 발생! 관리자에게 문의하세요.');
		exit;
	}

	alert('삭제가 완료되었습니다.',$_SERVER['PHP_SELF']);

	exit;
}


switch($_GET['fg']){
	case 'W' : $search_sql .= "and a.flag = 'W' "; break;
	case 'Y' : $search_sql .= "and a.flag = 'Y' "; break;
	case 'E' : $search_sql .= "and a.flag = 'E' "; break;
}

# 검색 처리 #
if($_GET['it_id']){
	$search_sql .= " and  a.it_id = '".$_GET['it_id']."'";
}
if($_GET['it_name']){
	$search_sql .= " and  b.it_name like '%".$_GET['it_name']."%'";
}
if($_GET['it_maker']){
	$search_sql .= " and  b.it_maker like '%".$_GET['it_maker']."%'";
}




# 핫딜존 데이터 로드 #
$sql = sql_query("
	select
		a.*,
		b.it_name
	from
		yc4_hotdeal_item a,
		".$g4['yc4_item_table']." b

	where
		a.it_id = b.it_id
		".$search_sql."
");

$list_tr = '';
//if($member['mb_id']=='dev' || $member['mb_id']=='ople_mrs'){
while($row = sql_fetch_array($sql)){
	switch($row['flag']){
		case 'W' : $flag = '대기'; break;
		case 'Y' : $flag = '진행'; break;
		case 'N' : $flag = '종료'; break;
	}
	$msrp_krw = $row['it_amount_msrp'] * $default['de_conv_pay'];
	$list_tr .= "
		<tr>
			<td>".$row['it_id']."</td>
			<td><img width='150' src='".$row['img_link']."'/></td>
			<td>".get_item_name($row['it_name'],'list')."</td>
			<td>
				￦".number_format($row['it_event_amount'])." ($".$row['it_event_amount_usd'].")
				<br/>
				￦".number_format($msrp_krw)." ($".$row['it_amount_msrp'].")
				<br/>
				".get_dc_percent($row['it_event_amount'],$msrp_krw)."%
			</td>
			<td>".$row['sell_qty']."<br/>".$row['qty']."<br/>".($row['qty']-$row['sell_qty'])."</td>
			<td>".$flag."</td>
			<td>".icon('수정',$g4['shop_admin_path'].'/main_hotdeal_item_write.php?uid='.$row['uid'])." ".icon('삭제','#','',"onclick=\"hotdeal_item_del('".$row['uid']."'); return false;\"")."</td>
		</tr>
	";
}
//}else{
//while($row = sql_fetch_array($sql)){
//	switch($row['flag']){
//		case 'W' : $flag = '대기'; break;
//		case 'Y' : $flag = '진행'; break;
//		case 'N' : $flag = '종료'; break;
//	}
//	$msrp_krw = $row['it_amount_msrp'] * $default['de_conv_pay'];
//	$list_tr .= "
//		<tr>
//			<td>".$row['it_id']."</td>
//			<td><img width='150' src='".$row['img_link']."'/></td>
//			<td>".get_item_name($row['it_name'],'list')."</td>
//			<td>
//				￦".number_format($row['it_event_amount'])." ($".usd_convert($row['it_event_amount']).")
//				<br/>
//				￦".number_format($msrp_krw)." ($".$row['it_amount_msrp'].")
//				<br/>
//				".get_dc_percent($row['it_event_amount'],$msrp_krw)."%
//			</td>
//			<td>".$row['sell_qty']."<br/>".$row['qty']."<br/>".($row['qty']-$row['sell_qty'])."</td>
//			<td>".$flag."</td>
//			<td>".icon('수정',$g4['shop_admin_path'].'/main_hotdeal_item_write.php?uid='.$row['uid'])." ".icon('삭제','#','',"onclick=\"hotdeal_item_del('".$row['uid']."'); return false;\"")."</td>
//		</tr>
//	";
//}
//}

include_once $g4['admin_path']."/admin.head.php";
?>
<style type="text/css">
.admin_table_tab > ul{
	list-style:none;
}
.admin_table_tab > ul > li {
	float:left;
	padding:5px;
	border:1px solid #dddddd;
}
.admin_table_tab > ul > li.active{
	font-weight:bold;
}
</style>

<form action="<?php echo $_SERVER['PHP_SELF'];?>" method='get'>
	제품코드 <input type="text" name='it_id' value="<?php echo $_GET['it_id']?>"/>
	제품명 <input type="text" name='it_name' value="<?php echo $_GET['it_name']?>"/>
	브랜드명 <input type="text" name='it_maker' value="<?php echo $_GET['it_maker']?>"/>
	<input type="submit" value='검색' />
</form>

<div class='admin_table_tab' style='overflow:hidden;'>
	<ul style='float:left'>
		<li<?php echo !$_GET['fg'] ? " class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF']."?".$qstr2;?>">전체</a></li>
		<li<?php echo $_GET['fg'] == 'W' ? " class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF']."?".$qstr2."&fg=W";?>">대기</a></li>
		<li<?php echo $_GET['fg'] == 'Y' ? " class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF']."?".$qstr2."&fg=Y";?>">진행</a></li>
		<li<?php echo $_GET['fg'] == 'E' ? " class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF']."?".$qstr2."&fg=E";?>">종료</a></li>
	</ul>
	<ul style='float:right;'>
		<li class='active'><a href="#">상품관리</a></li>
		<li><a href="<?php echo $g4['shop_admin_path'];?>/main_hotdeal_item_list.php">진행 상품관리</a></li>
	</ul>
</div>

<table width='100%'>
	<tr>
		<td>상품코드</td>
		<td></td>
		<td>상품명</td>
		<td>이벤트가<br />MSRP</td>
		<td>판매수량<br/>이벤트수량<br/>잔여수량</td>
		<td>상태</td>
		<td><?php echo icon('입력',$g4['shop_admin_path'].'/main_hotdeal_item_write.php')?></td>
	</tr>
	<?php echo $list_tr;?>
</table>

<script type="text/javascript">
	function hotdeal_item_del( uid ){
		if(!confirm('해당 상품을 핫딜존에서 삭제하시겠습니까?')){
			return false;
		}

		location.href='<?php echo $_SERVER['REMOVE_ADDR'];?>?mode=delete&uid='+uid;

	}
</script>

<?php
include_once $g4['admin_path']."/admin.tail.php";