<?php
$sub_menu = "400830";
include "./_common.php";

auth_check($auth[$sub_menu], "r");
$g4['title'] = '오버스탁 상품 이벤트';



if($_GET['str_it_id']){
	$_GET['it_id'] = trim($_GET['it_id']);
	$search_sql .= " and a.it_id = '".$_GET['str_it_id']."'";
}
if($_GET['str_SKU']){
	$_GET['it_id'] = trim($_GET['SKU']);
	$search_sql .= " and a.SKU = '".$_GET['str_SKU']."'";
}

if($_GET['str_it_name']){
	$search_sql .= " and b.it_name like '%".$_GET['str_it_name']."%'";
}

if($_GET['str_use_yn']){
	$search_sql .= " and a.use_yn = '".$_GET['str_use_yn']."'";
}

$useyn_param = $_GET;
unset($useyn_param['str_use_yn'],$useyn_param['page']);
$useyn_param = http_build_query($useyn_param);

if($_GET['str_ev_qty']){
	$search_sql2 .= " and a.ev_qty = '".$_GET['str_ev_qty']."'";
}
if($_GET['str_ov_qty']){
	$search_sql2 .= " and a.ov_qty = '".$_GET['str_ov_qty']."'";
}

# 오버스탁 이벤트 정보 탭 로드 #
$o_tab = sql_query("
	select
		ov_qty,
		ev_qty,
		concat(ov_qty,'+',ev_qty) as ev
	from
		yc4_over_stock_item a,
		yc4_item b
	where
		a.it_id = b.it_id
		".$search_sql."
	group by ev
	order by ev asc
");
$ev_param = $_GET;
unset($ev_param['str_ov_qty'],$ev_param['str_ev_qty'],$ev_param['page']);
$ev_param = http_build_query($ev_param);
while($row = sql_fetch_array($o_tab)){
	$tab_li .= "
		<li class='".($row['ev_qty'] == $_GET['str_ev_qty'] && $row['ov_qty'] == $_GET['str_ov_qty'] ? "active":"")."'><a href='".$_SERVER['PHP_SELF']."?".$ev_param."&str_ev_qty=".$row['ev_qty']."&str_ov_qty=".$row['ov_qty']."'>".$row['ev']."</a></li>
	";
}
if($tab_li){
	$tab_li .= "";
	$tab_li = "
		<ul class='admin_tab'>
			<li class='".(!$_GET['str_ev_qty'] && !$_GET['str_ov_qty'] ? "active":"")."'><a href='".$_SERVER['PHP_SELF']."?".$ev_param."'>전체</a></li>
			".$tab_li."
			<li style='clear:both; padding:0px; border:none;'></li>
		</ul>
	";
}

$tot_cnt_sql = sql_fetch("
	select
		count(*) as cnt
	from
		yc4_over_stock_item a,
		yc4_item b
	where
		a.it_id = b.it_id
		".$search_sql."
		".$search_sql2."
");
$total_cnt = $tot_cnt_sql['cnt'];
$rows = $config[cf_page_rows];
$total_page  = ceil($total_cnt / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함



# 오버스탁 제품 리스트 로드 #
$o_it_sql = sql_query("
	select
		a.it_id,a.use_yn,a.ov_qty,a.ev_qty,
		b.it_name,b.it_stock_qty,b.SKU
	from
		yc4_over_stock_item a,
		yc4_item b
	where
		a.it_id = b.it_id
		".$search_sql."
		".$search_sql2."
	limit ".$from_record.", ".$rows."
");
while($row = sql_fetch_array($o_it_sql)){
	$list_tr .= "
		<tr>
			<td><input type='checkbox' name='it_id' value='".$row['it_id']."'/></td>
			<td>".$row['it_id']."<br/>".$row['SKU']."</td>
			<td>".get_it_image($row['it_id'].'_s',50,50,$row['it_id'])."</td>
			<td>".$row['it_name']."</td>
			<td align='center'>".$row['ov_qty']."+".$row['ev_qty']."</td>
			<td align='center'>".($row['use_yn'] == 'y' ? "사용":"미사용")."</td>
			<td align='center'>".icon('수정','over_stock_write.php?it_id='.$row['it_id']).'&nbsp;'.icon('삭제',"#\" onclick=\"ev_item_del('".$row['it_id']."'); return false;\"")."</td>
		</tr>
	";
}
if(!$list_tr){
	$list_tr = "
		<tr>
			<td align='center' colspan='7'>데이터가 존재하지 않습니다.</td>
		</tr>
	";
}


include $g4['full_path']."/adm/admin.head.php";
?>
<style type="text/css">
.admin_tab {
	list-style:none;
}
.admin_tab li{
	float:left;
	padding: 5px;
	border:1px solid #dddddd;
}
.admin_tab li.active{
	font-weight:bold;
}
</style>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method='get' name='search_frm'>
	상품코드 <input type="text" name='str_it_id' value='<?php echo $_GET['str_it_id'];?>' />
	상품명 <input type="text" name='str_it_name' value='<?php echo $_GET['str_it_name'];?>' />
	SKU <input type="text" name='str_SKU' value='<?php echo $_GET['str_SKU'];?>' />
	<input type="submit" value='검색' />
</form>

<ul class='admin_tab'>
	<li class='<?php echo !$_GET['str_use_yn'] ? "active":"";?>'><a href="<?php echo $_SERVER['PHP_SELF'].'?'.$useyn_param;?>">전체</a></li>
	<li class='<?php echo $_GET['str_use_yn'] == 'y' ? "active":"";?>'><a href="<?php echo $_SERVER['PHP_SELF'].'?'.$useyn_param.'&str_use_yn=y';?>">사용</a></li>
	<li class='<?php echo $_GET['str_use_yn'] == 'n' ? "active":"";?>'><a href="<?php echo $_SERVER['PHP_SELF'].'?'.$useyn_param.'&str_use_yn=n';?>">미사용</a></li>
	<li style='clear:both; padding:0px; border:none;'></li>
</ul>
<?php echo $tab_li;?>
<table width='100%'>
	<tr>
		<td align='center'><input type="checkbox" class='chk_all' /></td>
		<td align='center'>상품코드<br/>SKU</td>
		<td align='center'></td>
		<td align='center'>상품명</td>
		<td align='center'>이벤트정보</td>
		<td align='center'>사용여부</td>
		<td align='center'><?php echo icon('입력','over_stock_write.php');?></td>
	</tr>
	<?php echo $list_tr ;?>
</table>
<form action="over_stock_write.php" name='item_del_frm'>
	<input type="hidden" name='mode' value='' />
	<input type="hidden" name='it_id' value='' />
</form>
<p align='center'>
<?php
	$qstr = $_GET;
	unset($qstr['page']);
	$qstr = http_build_query($qstr);
	echo get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");
?>
</p>

<script type="text/javascript">
function ev_item_del(it_id){
	if(!confirm('해당 상품을 오버스탁 이벤트에서 삭제하시겠습니까?')){
		return false;
	}
	item_del_frm.mode.value = 'delete';
	item_del_frm.it_id.value = it_id;
	item_del_frm.submit();
}

</script>
<?php
include $g4['full_path']."/adm/admin.tail.php";
?>