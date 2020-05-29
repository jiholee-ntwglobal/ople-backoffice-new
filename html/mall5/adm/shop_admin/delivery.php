<?
$sub_menu = "400910";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");




if($_GET['od_id']){
	$search_qry .= (($search_qry) ? ' and ':' where ')." od_id like '%".$_GET['od_id']."%'";
}


if($_GET['od_b_name']){
	$search_qry .= (($search_qry) ? ' and ':' where ')." od_b_name like '%".$_GET['od_b_name']."%'";
}

if($_GET['mb_id']){
	$search_qry .= (($search_qry) ? ' and ':' where ')." mb_id like '%".$_GET['mb_id']."%'";
}

if($search_qry){
	$sql = sql_query("select od_id,on_uid,mb_id,od_b_name,od_invoice,od_time, (select count(*) from ".$g4['yc4_cart_table']." where on_uid = ".$g4['yc4_order_table'].".on_uid) as item_cnt from ".$g4['yc4_order_table']." ".$search_qry." order by od_time desc");

	while($row = sql_fetch_array($sql)){
		$list_tr .= "
			<tr>
				<td><a href='".$g4['shop_path']."/orderinquiryview.php?od_id=".$row['od_id']."&on_uid=".$row['on_uid']."'>".$row['od_id']."</a></td>
				<td>".$row['mb_id']."</td>
				<td>".$row['od_b_name']."</td>
				<td><a href='#' onclick=\"$('#".$row['od_id']."').attr('src','http://216.74.54.38/delivery_admin.php?ots=".$row['od_id']."').show(); return false;\">".$row['od_invoice']."</a></td>
				<td>".$row['item_cnt']."</td>
				<td>".$row['od_time']."</td>
			</tr>
			<tr>
				<td colspan='6'><iframe width='100%' height='400' id='".$row['od_id']."' style='display:none;'></iframe></td>
			</tr>
		";
	}
}




$g4[title] = "배송조회";
include_once ("$g4[admin_path]/admin.head.php");
?>

<form action="<?=$_SERVER['PHP_SELF']?>" method='get'>
	주문번호 : <input type="text" name='od_id' value='<?=$_GET['od_id'];?>'/>
	받으시는분(이름) : <input type="text" name='od_b_name' value='<?=$_GET['od_b_name']?>' />
	아이디 : <input type="text" name='mb_id' value='<?=$_GET['mb_id']?>' />
	<input type="submit" value='검색' />
</form>

<table width='100%'>
	<tr>
		<td>주문번호</td>
		<td>아이디</td>
		<td>주문자</td>
		<td>운송장번호</td>
		<td>상품갯수</td>
		<td>주문일자</td>
	</tr>
	<?=$list_tr;?>
</table>


<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
