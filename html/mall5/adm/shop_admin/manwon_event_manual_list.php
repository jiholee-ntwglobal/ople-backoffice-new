<?php
$sub_menu = "500600";
include "./_common.php";

auth_check($auth[$sub_menu], "r");
$g4['title'] = '만원의 행복 이벤트 순서 수동변경';


$category = $_GET['category'] ? $_GET['category'] : 0;


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
		manwon_event_manual m,
		yc4_item i
	where
		m.it_id = i.it_id and
		m.category = '$category'
");
$total_cnt = $tot_cnt_sql['cnt'];
$rows = $config[cf_page_rows];
$total_page  = ceil($total_cnt / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함



# 수동등록 제품 리스트 로드 #
$o_it_sql = sql_query("
	select
		i.it_id,i.it_name,m.uid,m.sort
	from
		manwon_event_manual m,
		yc4_item i
	where
		m.it_id = i.it_id and
		m.category = '$category'
	order by m.sort asc
	limit ".$from_record.", ".$rows."
");
while($row = sql_fetch_array($o_it_sql)){
	$list_tr .= "
		<tr>
			<td><input type='checkbox' name='it_id' value='".$row['it_id']."'/></td>
			<td>".$row['it_id']."</td>
			<td>".get_it_image($row['it_id'].'_s',50,50,$row['it_id'])."</td>
			<td>".$row['it_name']."</td>			
			<td align='center'>".$row['sort']."</td>
			<td align='center'>".icon('수정','manwon_event_manual_write.php?category='.$category.'&uid='.$row['uid']).'&nbsp;'.icon('삭제',"#\" onclick=\"ev_item_del('".$row['uid']."'); return false;\"")."</td>
		</tr>
	";
}
if(!$list_tr){
	$list_tr = "
		<tr>
			<td align='center' colspan='6'>데이터가 존재하지 않습니다.</td>
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


<ul class='admin_tab'>
	<li class='<?php echo $category == '0' ? "active":"";?>'><a href="<?php echo $_SERVER['PHP_SELF'].'?category=0'; ?>">전체 베스트</a></li>
	<li class='<?php echo $category == '3' ? "active":"";?>'><a href="<?php echo $_SERVER['PHP_SELF'].'?category=3'; ?>">건강식품</a></li>
	<li class='<?php echo$category == '4' ? "active":"";?>'><a href="<?php echo $_SERVER['PHP_SELF'].'?category=4'; ?>">생활</a></li>
	<li class='<?php echo$category == '5' ? "active":"";?>'><a href="<?php echo $_SERVER['PHP_SELF'].'?category=5'; ?>">출산/육아</a></li>
	<li class='<?php echo$category == '1' ? "active":"";?>'><a href="<?php echo $_SERVER['PHP_SELF'].'?category=1'; ?>">뷰티용품</a></li>
	<li class='<?php echo$category == '2' ? "active":"";?>'><a href="<?php echo $_SERVER['PHP_SELF'].'?category=2'; ?>">식품</a></li>
	<li style='clear:both; padding:0px; border:none;'></li>
</ul>
<?php echo $tab_li;?>
<table width='100%'>
	<tr>
		<td align='center'><input type="checkbox" class='chk_all' /></td>
		<td align='center'>상품코드</td>
		<td align='center'></td>
		<td align='center'>상품명</td>
		<td align='center'>정렬순서</td>
		<td align='center'><?php echo icon('입력','manwon_event_manual_write.php?category='.$category);?></td>
	</tr>
	<?php echo $list_tr ;?>
</table>
<form action="manwon_event_manual_write.php" method="post" name='item_del_frm'>
	<input type="hidden" name='mode' value='' />
	<input type="hidden" name='uid' value='' />
	<input type="hidden" name='category' value='<?php echo $category; ?>' />
</form>
<p align='center'>
<?php
	$qstr = $_GET;
	unset($qstr['page']);
	$qstr = http_build_query($qstr);
	echo get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");
?>
</p>
<a href="#" onclick="update_data()">데이터 업데이트</a>
<form name="update_data_frm" target="update_frame" action="/mall5/update_manwon_event_item.php">
</form>
<iframe id="update_frame" name="update_frame" border="0" width="0" height="0"></iframe>
<script type="text/javascript">
function ev_item_del(uid){
	if(!confirm('해당 상품을 만원의 이벤트 수동등록에서 삭제하시겠습니까?')){
		return false;
	}
	item_del_frm.mode.value = 'delete';
	item_del_frm.uid.value = uid;
	item_del_frm.submit();
}

function update_data(){
	if(confirm("데이터를 업데이트하겠습니꺄?")){
		document.update_data_frm.submit();
	} return false;
}
</script>
<?php
include $g4['full_path']."/adm/admin.tail.php";
?>