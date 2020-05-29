<?
$sub_menu = "500500";

include_once("./_common.php");



auth_check($auth[$sub_menu], "r");


# 탭 처리 #
switch($_GET['tab']){
	case 'all' : break;
	case 'disabled' : $where = (($where) ? '' : ' where ') . "a.st_dt > '".date('Ymd')."' or a.en_dt < '".date('Ymd')."'"; break;
	default : $where = (($where) ? '' : ' where ') . "a.st_dt <= '".date('Ymd')."' and a.en_dt >= '".date('Ymd')."'"; break;
}



# 이벤트 리스트 로드 #
$event_qry = sql_query("
	select 
		a.bid,
		a.name,
		a.event_type,
		a.it_maker,
		a.ca_id,
		a.st_dt,
		a.en_dt,
		a.comment,
		a.create_dt,
		b.ca_name,
		(select count(*) from yc4_free_gift_event_item where bid = a.bid and `use` = 'Y') as use_item_cnt,
		(select count(*) from yc4_free_gift_event_item where bid = a.bid) as item_cnt
	from 
		yc4_free_gift_event a
		left join 
		yc4_category b on a.ca_id = b.ca_id
	".$where."
");
while($event = sql_fetch_array($event_qry)){
	switch($event['event_type']){
		case 'A' : $event_type = '전상품'; break;
		case 'B' : $event_type = '브랜드'; break;
		case 'C' : $event_type = '카테고리'; break;
	}
	$st_dt = substr($event['st_dt'],0,4).'-'.substr($event['st_dt'],4,2).'-'.substr($event['st_dt'],6,2);
	$en_dt = substr($event['en_dt'],0,4).'-'.substr($event['en_dt'],4,2).'-'.substr($event['en_dt'],6,2);
	$list_tr .= "
		<tr class='ht'>
			<td align='center'>".$event['bid']."</td>
			<td>".$event['name']."</td>
			<td align='center'>".$event_type."</td>
			<td>".(($event['ca_name']) ? $event['ca_name'] : $event['it_maker'])."</td>
			<td align='center'>".$event['use_item_cnt'].'/'.number_format($event['item_cnt'])."개</td>
			<td align='center'>".$st_dt." ~ ".$en_dt."</td>
			<td align='center'>".$event['create_dt']."</td>
			<td align='center'>".icon("수정", "./gift_event_write.php?bid=".$event['bid']."")."&nbsp;".icon("보기", './gift_event_item_list.php?bid='.$event['bid'])."</td>
		</tr>
		
	";
}

$g4[title] = "구매금액별 이벤트관리";
include_once ("$g4[admin_path]/admin.head.php");
?>
<style type="text/css">
.tab_warp a {
	float:left;
	padding:5px 10px;
	border:1px solid #dddddd;
}
.tab_warp a.active{
	font-weight:bold;
}
</style>
<?=subtitle("구매금액별 이벤트관리")?>
<div class='tab_warp'>
	<a href="<?=$_SERVER['PHP_SELF'];?>" class='<?=(!$_GET['tab'] ? 'active' : '')?>'>진행</a>
	<a href="<?=$_SERVER['PHP_SELF'];?>?tab=disabled" class='<?=($_GET['tab'] == 'disabled') ? 'active':''?>'>마감</a>
	<a href="<?=$_SERVER['PHP_SELF'];?>?tab=all" class='<?=($_GET['tab'] == 'all') ? 'active':''?>'>전체</a>
</div>
<table width='100%'>
	<tr class='ht' align='center'>
		<td>이벤트 코드</td>
		<td>이벤트명</td>
		<td>이벤트 타입</td>
		<td>브랜드명/카테고리명</td>
		<td>상품갯수</td>
		<td>기간</td>
		<td>등록일</td>
		<td><?=icon("입력", "./gift_event_write.php");?></td>
	</tr>
	<?=$list_tr;?>
</table>
<?
include_once ("$g4[admin_path]/admin.tail.php");
?>