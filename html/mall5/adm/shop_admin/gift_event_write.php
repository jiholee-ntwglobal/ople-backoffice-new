<?
$sub_menu = "500500";

include_once("./_common.php");

auth_check($auth[$sub_menu], "r");


if($_POST['mode'] == 'insert'){
	$it_maker = mysql_escape_string($_POST['it_maker']);
	$insertQ = "
		insert into 
			yc4_free_gift_event
		(
			name,
			event_type,
			it_maker,
			ca_id,
			st_dt,
			en_dt,
			comment,
			create_dt,
			create_id,
			priod_view
		)values(
			'".$_POST['name']."',
			'".$_POST['event_type']."',
			'".$it_maker."',
			'".$_POST['ca_id']."',
			'".$_POST['st_dt']."',
			'".$_POST['en_dt']."',
			'".$_POST['comment']."',
			'".date('Y-m-d H:i:s')."',
			'".$member['mb_id']."',
			'".$_POST['priod_view']."'
		)
	";
	if(!sql_query($insertQ)){
		alert('저장줄 오류발생! 다시 시도해 주세요.');
	}
	alert('저장이 완료되었습니다.','gift_event.php');
	exit;
}

if($_POST['mode'] == 'update'){
	$it_maker = mysql_escape_string($_POST['it_maker']);
	$update_qry = "
		update 
			yc4_free_gift_event
		set
			name = '".$_POST['name']."',
			event_type = '".$_POST['event_type']."',
			it_maker = '".$it_maker."',
			ca_id = '".$_POST['ca_id']."',
			st_dt = '".$_POST['st_dt']."',
			en_dt = '".$_POST['en_dt']."',
			comment = '".$_POST['comment']."',
			update_dt = '".date('Y-m-d H:i:s')."',
			update_id = '".$member['mb_id']."',
			priod_view = '".$_POST['priod_view']."'
		where
			bid = '".$_POST['bid']."'
	";
	
	if(!sql_query($update_qry)){
		alert('저장줄 오류발생! 다시 시도해 주세요.');
	}
	alert('저장이 완료되었습니다.',$_SERVER['PHP_SELF'].'?bid='.$_POST['bid']);
	exit;
}

if($_GET['bid']){
	$event = sql_fetch("
		select
			a.*
		from
			yc4_free_gift_event a
			left join
			yc4_category b on a.ca_id = b.ca_id
		where
			a.bid = '".$_GET['bid']."'
	");
	switch($event['event_type']){
		case 'B' : 
			$target = $event['it_maker']; 
			$target_nm = " name='it_maker'";
			break;
		case 'C' : 
			$target = $event['ca_id']; 
			$target_nm = " name='ca_id'";
			$ca_name = "<br/><span>".$event['ca_name']."</span>";
			break;
		default :
			$target_nm = " disabled";
			break;
	}
}

$g4[title] = "구매금액별 이벤트등록";
include_once ("$g4[admin_path]/admin.head.php");
?>
<?=subtitle("구매금액별 이벤트등록")?>
<form action="<?=$_SERVER['PHP_SELF']?>" method='post'>
	<input type="hidden" name='mode' value='<?=($event) ? 'update' : 'insert';?>'/>
	<?if($event){?>
	<input type="hidden" name='bid' value='<?=($event['bid'])?>' />
	<?}?>
	<table width='100%'>
		<colgroup width=15%></colgroup>
		<colgroup width=35% bgcolor=#FFFFFF></colgroup>
		<colgroup width=15%></colgroup>
		<colgroup width=35% bgcolor=#FFFFFF></colgroup>
		<tr><td colspan=4 height=2 bgcolor=0E87F9></td></tr>
		<tr class='ht'>
			<td>이벤트명</td>
			<td colspan='3'><input type="text" name='name' value='<?=$event['name'];?>'/></td>
		</tr>
		<tr class='ht'>
			<td>이벤트 타입</td>
			<td>
				<select name="event_type" id="">
					<option value="A" <?=($event['event_type'] == 'A') ? 'selected':''?>>모든상품</option>
					<option value="B" <?=($event['event_type'] == 'B') ? 'selected':''?>>브랜드 이벤트</option>
					<option value="C" <?=($event['event_type'] == 'C') ? 'selected':''?>>카테고리 이벤트</option>
				</select>
			</td>
			<td>브랜드명/카테고리ID</td>
			<td><input type="text" class='target' <?=$target_nm;?> value='<?=$target;?>'/><?=$ca_name;?></td>
		</tr>
		<tr class='ht'>
			<td>시작일</td>
			<td><input type="text" name='st_dt' value='<?=$event['st_dt']?>'/></td>
			<td>종료일</td>
			<td><input type="text" name='en_dt' value='<?=$event['en_dt']?>'/></td>
		</tr>
		<tr class='ht'>
			<td>이벤트설명</td>
			<td colspan='3'><input type="text" name='comment' value='<?=$event['comment']?>'/></td>
		</tr>
		<tr class='ht'>
			<td>이벤트 기간 표시</td>
			<td>
				<input type="radio" name='priod_view' value='Y' <?=($event['priod_view'] == 'Y') ? 'checked':''?>/>표시
				<input type="radio" name='priod_view' value='N' <?=($event['priod_view'] == 'N') ? 'checked':''?>/>미표시
				<input type="radio" name='priod_view' value='C' <?=($event['priod_view'] == 'C') ? 'checked':''?>/>별도공지
			</td>
		</tr>
	</table>
	<p align='center'>
		<input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
	    <input type=button class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='gift_event.php';">
	</p>
</form>


<script type="text/javascript">
$('select[name=event_type]').change(function(){
	switch($(this).val()){
		case 'A' : 
			$('.target').removeAttr('name');
			$('.target').val('');
			$('.target').attr('disabled');
			break;
		case 'B' : 
			$('.target').val('');
			$('.target').attr('name','it_maker');
			break;
		case 'C' : 
			$('.target').val('');
			$('.target').attr('name','ca_id');
			break;
	}
});
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>