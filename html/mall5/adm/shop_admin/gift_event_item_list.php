<?
$sub_menu = "500500";

include_once("./_common.php");

auth_check($auth[$sub_menu], "r");




if($_GET['bid']){
	$where = (($where) ? ' and ': ' where ') . "a.bid = '".$_GET['bid']."'";
}


# 구매금액별 이벤트 상품 리스트 로드 #
$event_item_qry = sql_query("
	select 
		a.*,
		b.it_name
	from 
		yc4_free_gift_event_item a
		left join
		yc4_item b on a.it_id = b.it_id
	".$where."

");

while($event_item = sql_fetch_array($event_item_qry)){
	switch($event_item['use']){
		case 'Y' : $event_item_use = 'O'; break;
		default : $event_item_use = 'X'; break;
	}
	$list_tr .= "
		<tr class='ht'>
			<td align='center'>".$event_item['bid']."</td>
			<td>".$event_item['it_id']."</td>
			<td>".$event_item['it_name']."</td>
			<td align='center'>".$event_item_use."</td>
			<td align='right'>".number_format($event_item['od_amount'])."</td>
			<td align='center'>".str_replace(' ','<br/>',$event_item['create_dt'])."</td>
			<td align='center'>".icon('수정','gift_event_item_write.php?uid='.$event_item['uid'])."</td>
		</tr>
	";
}
if(!$list_tr){
	$list_tr = "
		<tr class='ht'>
			<td align='center' colspan='6'>데이터가 존재하지 않습니다.</td>
		</tr>
	";
}

$g4[title] = "구매금액별 이벤트 상품 관리";
include_once ("$g4[admin_path]/admin.head.php");
?>


<table width='100%'>
	<col width='8%'/>
	<col width='10%'/>
	<col width=''/>
	<col width='8%'/>
	<col width='8%'/>
	<col width='10%'/>
	<col width='5%'/>
	<tr class='ht' align='center'>
		<td>이벤트코드</td>
		<td>상품코드</td>
		<td>상품명</td>
		<td>사용여부</td>
		<td>구매가격</td>
		<td>등록일</td>
		<td><?=icon('입력','gift_event_item_write.php?bid='.$_GET['bid']);?></td>
	</tr>
	<?=$list_tr;?>
</table>
<p align='center'>
    <input type=button class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='gift_event.php';">
</p>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>