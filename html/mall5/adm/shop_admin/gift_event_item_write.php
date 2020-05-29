<?
$sub_menu = "500500";

include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

# 상품 등록 처리 #
if($_POST['mode'] == 'insert'){
	if(!sql_fetch("select it_id from yc4_item where it_id = '".$_POST['it_id']."'")){
		alert('존재하지 않는 상품입니다.');
	}
	$show_amount = ($_POST['show_amount']) ? "y":"n";
	$sql = "
		insert into 
			yc4_free_gift_event_item
		(
			bid,it_id,od_amount,create_dt,create_id,`use`,show_amount
		)values(
			'".$_POST['bid']."','".$_POST['it_id']."','".$_POST['od_amount']."','".date('Y-m-d H:i:s')."','".$member['mb_id']."','".$_POST['use']."','".$show_amount."'
		)
	";
	if(!sql_query($sql)){
		alert('등록중 오류 발생! 다시 시도해 주세요.');
	}

	alert('구매금액별 이벤트 상품등록이 완료되었습니다.','gift_event_item_list.php?bid='.$_POST['bid']);
	exit;
}

# 상품 수정 처리 #
if($_POST['mode'] == 'update'){
	if(!sql_fetch("select it_id from yc4_item where it_id = '".$_POST['it_id']."'")){
		alert('존재하지 않는 상품입니다.');
	}
	$show_amount = ($_POST['show_amount']) ? "y":"n";
	$sql = "
		update
			yc4_free_gift_event_item
		set
			bid = '".$_POST['bid']."',
			it_id = '".$_POST['it_id']."',
			od_amount = '".$_POST['od_amount']."',
			`use` = '".$_POST['use']."',
			show_amount = '".$show_amount."'
		where
			uid = '".$_POST['uid']."'
	";
	if(!sql_query($sql)){
		alert('등록중 오류 발생! 다시 시도해 주세요.');
	}
	alert('구매금액별 이벤트 상품수정이 완료되었습니다.','gift_event_item_list.php?bid='.$_POST['bid']);
	exit;
}


# 이벤트 삭제 처리 #
if($_POST['mode'] == 'delete'){
	// 해당 이벤트에 해당하는 상품도 삭제한다.
	$sql = "delete from yc4_free_gift_event where bid = '".$_POST['bid']."'";
	$sql2 = "delete from yc4_free_gift_event_item where bid = '".$_POST['bid']."'";

	sql_query($sql);
	sql_query($sql2);
	alert('이벤트 삭제가 완료되었습니다.','gift_event.php');
		
	exit;
}

# 상품 검색 #
if($_POST['mode'] == 'item_search'){
	$data = sql_fetch("select it_name from yc4_item where it_id = '".$_POST['it_id']."'");
	echo $data['it_name'];
	
	exit;
}


if($_GET['uid']){
	$event_item = sql_fetch("
		select 
			a.*,
			b.it_name
		from
			yc4_free_gift_event_item a
			left join
			yc4_item b on a.it_id = b.it_id
		where
			a.uid = '".$_GET['uid']."'
	");
}

# 이벤트 리스트 로드 #
$event_list_qry = sql_query("
	select
		a.*,
		b.ca_name
	from
		yc4_free_gift_event a
		left join
		yc4_category b on a.ca_id = b.ca_id
	order by a.create_dt desc
");

while($event_list = sql_fetch_array($event_list_qry)){
	switch($event_list['event_type']){
		case 'A' : $event_type = '전상품'; break;
		case 'B' : $event_type = '브랜드'; break;
		case 'C' : $event_type = '카테고리'; break;
	}
	if($event_list['ca_name']){
		$event_desc = $event_list['ca_name'];
	}else{
		$event_desc = $event_list['it_maker'];
	}
	$event_list_option .= "
		<option value='".$event_list['bid']."' ".(($event_item['bid'] == $event_list['bid']) ? 'selected':'').">[".$event_type."]".$event_list['name']." - ".$event_desc."</option>\n
	";
}


$g4[title] = "구매금액별 이벤트 상품 등록";
include_once ("$g4[admin_path]/admin.head.php");
?>
<form action="<?=$_SERVER['PHP_SELF']?>" method='post' onsubmit="return event_item_fnc();">
	<input type="hidden" name='mode' value='<?=($event_item) ? 'update' : 'insert';?>'/>
	<?if($event_item){?>
	<input type="hidden" name='uid' value='<?=$event_item['uid'];?>' />
	<?}?>
	<table width='100%'>
		<colgroup width=15%></colgroup>
		<colgroup width=35% bgcolor=#FFFFFF></colgroup>
		<colgroup width=15%></colgroup>
		<colgroup width=35% bgcolor=#FFFFFF></colgroup>
		<tr><td colspan=4 height=2 bgcolor=0E87F9></td></tr>
		
		<tr class='ht'>
			<td>이벤트 선택</td>
			<td><select name="bid" id=""><?=$event_list_option;?></select></td>
			<td>가격설정</td>
			<td><input type="text" name='od_amount' value='<?=$event_item['od_amount'];?>'/>원 이상 구매 시</td>
		</tr>

		<tr class='ht'>
			<td>상품코드</td>
			<td><input type="text" name='it_id' value='<?=$event_item['it_id'];?>'/></td>
			<td>상품명</td>
			<td class='it_name'><?=$event_item['it_name']?></td>
		</tr>
		<tr class='ht'>
			<td>사용여부</td>
			<td><input type="checkbox" name='use' value='Y' <?=($event_item['use'] == 'Y') ? 'checked':''?>/></td>
			<td>제품가격표시</td>
			<td><input type="checkbox" name='show_amount' value='y' <?=($event_item['show_amount'] == 'y') ? 'checked':''?>/></td>
		</tr>
	</table>
	<p align='center'>
		<input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
	    <input type=button class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='gift_event_item_list.php?bid=<?=$_GET['bid']?>';">
		<?if($event_item){?>
		<input type="button" class='btn1' accesskey='d' value='  삭  제  ' onclick="gift_event_del('<?=$event_item['bid']?>');"/>
		<?}?>
	</p>
</form>
<?if($event_item){?>
<form action="<?=$_SERVER['PHP_SELF']?>" method='post' id='del_frm'>
	<input type="hidden" name='bid' value='<?=$event_item['bid']?>'/>
	<input type="hidden" name='mode' value=''/>
</form>
<?}?>

<script type="text/javascript">
item_chk = <?=($event_item) ? 'true' : 'false'?>;
$('input[name=it_id]').change(function(){
	$(this).val($(this).val().replace(/[^0-9]/g,'')); // 숫자 이외의 값은 삭제
	var it_id = $(this).val();
	if(it_id == ''){
		var msg = '상품코드를 입력해 주세요.';
		$('.it_name').text(msg);
		item_chk = false;
		return false;
	}
	$.ajax({
		url : '<?=$_SERVER['PHP_SELF']?>',
		type : 'post',
		data : {
			'mode' : 'item_search',
			'it_id' : it_id
		},success : function (result) {
			if(result == ''){
				var msg = '존재하지 않는 상품입니다.';
				$('.it_name').text(msg);
				item_chk = false;
				return false;
			}
			$('.it_name').text(result);
			item_chk = true;

		}
	});
	
});

function event_item_fnc(){
	if($('input[name=od_amount]').val().replace(/[^0-9]/g,'') == ''){
		alert('가격설정을 입력해 주세요.');
		$('input[name=od_amount]').focus();
		return false;
	}

	if($('input[name=it_id]').val().replace(/[^0-9]/g,'') == ''){
		alert('상품코드를 입력해 주세요.');
		$('input[name=it_id]').focus();
		return false;
	}

	if(item_chk == false){
		alert('올바른 상품코드가 아닙니다.');
		$('input[name=it_id]').focus();
		return false;
	}

	$('input[name=od_amount]').val($('input[name=od_amount]').val().replace(/[^0-9]/g,''));
	$('input[name=it_id]').val($('input[name=it_id]').val().replace(/[^0-9]/g,''));
		

	return true;
}

<?if($event_item){?>
function gift_event_del (bid){
	if(!confirm('구매금액별 이벤트 상품을 삭제하시겠습니까?\n삭제시 해당 이벤트의 상품 설정도 삭제됩니다.(실제상품에 영향 없음)')){
		return false;
	}

	del_frm.mode.value = 'delete';
	del_frm.submit();
}
<?}?>
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>