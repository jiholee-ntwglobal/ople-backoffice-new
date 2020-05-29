<?php
/*
----------------------------------------------------------------------
file name	 : nonstop_event.php
comment		 : 논스톱 이벤트 관리 리스트
date		 : 2014-11-27
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "400940";
include "./_common.php";

auth_check($auth[$sub_menu], "r");
$g4['title'] = '논스톱이벤트관리';

if($_GET['status']){
	$sql_where .= " and a.status = '".(int)$_GET['status']."'";
}

# 이벤트 리스트 로드
$sql = sql_query("
	select
		a.*,
		b.it_name
	from
		yc4_nontop_sale a,
		".$g4['yc4_item_table']." b
	where
		a.it_id = b.it_id
		".$sql_where."
");
while($row = sql_fetch_array($sql)){
	switch($row['status']){
		case 1 : $status = '대기'; break;
		case 2 : $status = '진행'; break;
		case 3 : $status = '종료'; break;
	}
	$list_tr .= "
		<tr>
			<td>".$row['seq']."</td>
			<td>".$row['it_id']."</td>
			<td>".$row['it_name']."</td>
			<td>".$row['ev_amount']."</td>
			<td>".$row['ev_qty']."</td>
			<td>".$status."</td>
			<td>".$row['start_dt']."</td>
			<td>".$row['end_dt']."</td>
			<td>".icon('수정','nonstop_event_write.php?uid='.$row['uid']).icon('삭제','nonstop_event_write.php?mode=delete&uid='.$row['uid'])."</td>
		</tr>
	";
}

include $g4['full_path']."/adm/admin.head.php";

?>

<style type="text/css">
.list_tab{
	list-style:none;
	overflow:hidden;
}
.list_tab > li{
	float:left;
	border: 1px solid #dddddd;
	padding:5px;
}

.list_tab > li.active{
	font-weight:bold;
}
</style>
<ul class='list_tab'>
	<li class='<?=!$_GET['status'] ? "active":""?>'><a href="<?=$_SERVER['PHP_SELF'];?>">전체</a></li>
	<li class='<?=$_GET['status'] == 1 ? "active":""?>'><a href="<?=$_SERVER['PHP_SELF'];?>?status=1">대기</a></li>
	<li class='<?=$_GET['status'] == 2 ? "active":""?>'><a href="<?=$_SERVER['PHP_SELF'];?>?status=2">진행</a></li>
	<li class='<?=$_GET['status'] == 3 ? "active":""?>'><a href="<?=$_SERVER['PHP_SELF'];?>?status=3">종료</a></li>
</ul>
<table width='100%'>
	<col width='40'/>
	<col width='100'/>
	<col />
	<col width='50'/>
	<col width='100'/>
	<col width='50'/>
	<col width='100'/>
	<col width='100'/>
	<col />
	<tr>
		<td>순서</td>
		<td>상품코드</td>
		<td>상품명</td>
		<td>가격</td>
		<td>수량</td>
		<td>상태</td>
		<td>시작일</td>
		<td>종료일</td>
		<td><?php echo icon('입력','nonstop_event_write.php');?></td>
	</tr>
	<?php echo $list_tr;?>
</table>

<?php
include $g4['full_path']."/adm/admin.tail.php";
?>