<?php
include "_common.php";

if($_POST['mode'] == 'del'){
	print_r2($_POST);

	if(!is_array($_POST['it_id'])){
		alert('오류');
		exit;
	}
	$cnt = count($_POST['it_id']);
	for($i=0; $i<$cnt; $i++){
		sql_query("delete from yc4_category_item where ca_id = '".$_POST['sel_ca_id']."' and it_id = '".$_POST['it_id'][$i]."'");
	}
	alert('삭제가 완료되었습니다.',$_SERVER['PHP_SELF'].'?s_id='.$_POST['s_id'].'&ca_id='.$_POST['sel_ca_id']);
	exit;
}

include_once $g4['full_path']."/adm/admin.head.php";

$sql = sql_query("select s_id,name from yc4_station order by sort asc");
while($row = sql_fetch_array($sql)){
	$s_lst .= "<a href='".$_SERVER['PHP_SELF']."?s_id=".$row['s_id']."' class='".($_GET['s_id'] == $row['s_id'] ? "active":"")."'>".$row['name']."</a>";
}
if($_GET['s_id']){
	$sql = sql_query("
		select
			b.ca_id,b.ca_name
		from
			shop_category a
			left join
			yc4_category_new b on  b.ca_id like concat(a.ca_id,'%')
		where
			a.s_id = '".$_GET['s_id']."'
		order by a.sort asc,b.ca_id asc
	");
	while($row = sql_fetch_array($sql)){
		$depth = strlen($row['ca_id'])/2;
		$it_cnt = sql_fetch("select count(*) as cnt from yc4_category_item where ca_id like '".$row['ca_id']."%'");
		$ca_same = substr($_GET['ca_id'],0,strlen($row['ca_id']));
		$it_cnt = $it_cnt['cnt'];
		$ca_list .= "
			<a class='ca_".$depth.($ca_same == $row['ca_id'] ? " active":"")."' href='".$_SERVER['PHP_SELF']."?s_id=".$_GET['s_id']."&ca_id=".$row['ca_id']."'>".$row['ca_name']."(".$it_cnt.")</a>
		";
	}
}

$_GET['page'] = $_GET['page'] ? $_GET['page'] : 1;
$page = $_GET['page'];
$page_param = $_GET;
unset($page_param['page']);
$page_param = http_build_query($page_param);

if($_GET['ca_id']){

	if($_GET['it_name']){
		$sql_where .= "and b.it_name like '%".mysql_escape_string($_GET['it_name'])."%'";
	}
	if($_GET['it_maker']){
		$sql_where .= "and b.it_maker like '%".mysql_escape_string($_GET['it_maker'])."%'";
	}

	$sql_cnt = sql_fetch("
		select
			count(*) as cnt
		from
			yc4_category_item a,
			yc4_item b
		where
			a.it_id = b.it_id
			and a.ca_id like '".$_GET['ca_id']."%'
			".$sql_where."
	");

	// 테이블의 전체 레코드수만 얻음
	$sql_cnt = $sql_cnt['cnt'];


	$rows = $config[cf_page_rows];
	$total_page  = ceil($sql_cnt / $rows);  // 전체 페이지 계산
	if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql = sql_query("
		select
			b.it_id,b.it_name,b.it_maker
		from
			yc4_category_item a,
			yc4_item b
		where
			a.it_id = b.it_id
			and a.ca_id like '".$_GET['ca_id']."%'
			".$sql_where."
		limit $from_record, $rows
	");
	while($row = sql_fetch_array($sql)){

		$it_list .= "
			<tr>
				<td><input type='checkbox' name='it_id[]' value='".$row['it_id']."'/></td>
				<td>".$row['it_id']."</td>
				<td>".$row['it_maker']."</td>
				<td>".$row['it_name']."</td>
			</tr>
		";
	}
}
?>

<style type="text/css">
.station a{
	padding:7px;
	display:inline-block;
}
a.active{
	font-weight:bold;
	background-color:#ff0000;
	color:#ffffff;
}
.category>a{
	display:block;
}
.category>a.ca_2{
	margin-left:40px;
}
.category>a.ca_3{
	margin-left:80px;
}
.category>a.ca_4{
	margin-left:120px;
}
.category>a.ca_5{
	margin-left:160px;
}
</style>
<table width='100%'>
	<tr>
		<td width='40%' valign='top'>
			<div class='station'><?=$s_lst;?></div>
			<div class='category'><?=$ca_list;?></div>
		</td>
		<td width='' valign='top'>
			<form action="<?=$_SERVER['PHP_SELF'];?>" method='get'>
				<input type="hidden" name='s_id' value='<?=$_GET['s_id'];?>' />
				<input type="hidden" name='ca_id' value='<?=$_GET['ca_id'];?>' />
				제품명<input type="text" name='it_name' value="<?=stripslashes($_GET['it_name']);?>" />
				브랜드명<input type="text" name='it_maker' value="<?=stripslashes($_GET['it_maker']);?>" />
				<input type="submit" value='검색' />
			</form>
			<form action="<?=$_SERVER['PHP_SELF'];?>" method='post' onsubmit='return del_chk();'>
				<input type="hidden" name='s_id' value='<?=$_GET['s_id'];?>' />
				<input type="hidden" name='sel_ca_id' value='<?=$_GET['ca_id'];?>' />
				<input type="hidden" name='mode' value='del' />
				<table width='100%'>
					<tr>
						<td></td>
						<td>it_id</td>
						<td>브랜드</td>
						<td>제품명</td>
					</tr>
					<?=$it_list;?>
				</table>
				<div style='text-align:center;'>
					<?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$page_param&page=");?>
				</div>
				<input type="submit" value='삭제' />
			</form>

		</td>
	</tr>
</table>

<script type="text/javascript">
function del_chk(){
	if($('input:checkbox[name=it_id\\[\\]]:checked').length <1){
		alert('삭제할 상품을 선택해 주세요.');
		return false;
	}

	if(!confirm('선택하신 상품을 해당 카테고리에서 삭제하시겠습니까?')){
		return false;
	}

	return true;
}
</script>

<?php
include_once $g4['full_path']."/adm/admin.tail.php";
?>