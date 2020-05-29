<?php
$sub_menu = "400830";
include "./_common.php";

auth_check($auth[$sub_menu], "r");
$g4['title'] = '오버스탁 상품 이벤트';


$ev_arr = array(
	'1+1' => '1413783986',
	'2+1' => '1413784116',
	'3+1' => '1413785574'
);

if($_POST['mode'] == 'insert'){
	$it_id = (int)trim($_POST['it_id']);
	if(!$_POST['use_yn']){
		$_POST['use_yn'] = 'n';
	}

	$it_id_chk = sql_query("select count(*) from ".$g4['yc4_item_table']." where it_id = '".$it_id."'");
	if($it_id_chk < 1){
		alert('존재하지 않는 상품입니다.');
		exit;
	}

	$insert_qry = "
		insert into
			yc4_over_stock_item
		(
			it_id,use_yn,ov_qty,ev_qty
		)values(
			'".$it_id."','".$_POST['use_yn']."','".(int)$_POST['ov_qty']."','".(int)$_POST['ev_qty']."'
		)
	";
	if(!sql_query($insert_qry)){
		alert('저장중 오류 발생! 다시 시도해 주세요.');
		exit;
	}

	if($_POST['use_yn'] == 'y'){
		$ev_chk = sql_fetch("select count(*) as cnt from ".$g4['yc4_event_item_table']." where ev_id = '".$ev_arr[$_POST['ov_qty'].'+'.$_POST['ev_qty']]."' and it_id = '".$it_id."'");

		if($ev_chk['cnt'] < 1){
			sql_query("insert into ".$g4['yc4_event_item_table']." (ev_id,it_id) values ('".$ev_arr[$_POST['ov_qty'].'+'.$_POST['ev_qty']]."','".$it_id."')");
		}
	}


	alert('저장이 완료되었습니다.',$_SERVER['PHP_SELF']."?it_id=".$it_id);
	exit;
}

if($_POST['mode'] == 'update'){
	$it_id = (int)trim($_POST['it_id']);
	if(!$_POST['use_yn']){
		$_POST['use_yn'] = 'n';
	}

	$it_id_chk = sql_query("select count(*) from ".$g4['yc4_item_table']." where it_id = '".$it_id."'");
	if($it_id_chk < 1){
		alert('존재하지 않는 상품입니다.');
		exit;
	}

	$update_qry = "
		update
			yc4_over_stock_item
		set
			use_yn = '".$_POST['use_yn']."',
			ov_qty = '".$_POST['ov_qty']."',
			ev_qty = '".$_POST['ev_qty']."'
		where
			it_id = '".$it_id."'
	";

	if(!sql_query($update_qry)){
		alert('수정중 오류 발생! 다시 시도해 주세요.');
		exit;
	}

	if($_POST['use_yn'] == 'y'){
		$ev_chk = sql_fetch("select count(*) as cnt from ".$g4['yc4_event_item_table']." where ev_id = '".$ev_arr[$_POST['ov_qty'].'+'.$_POST['ev_qty']]."' and it_id = '".$it_id."'");

		if($ev_chk['cnt'] < 1){
			sql_query("insert into ".$g4['yc4_event_item_table']." (ev_id,it_id) values ('".$ev_arr[$_POST['ov_qty'].'+'.$_POST['ev_qty']]."','".$it_id."')");
		}
	}else{
		sql_query("delete from ".$g4['yc4_event_item_table']." where ev_id = '".$ev_arr[$_POST['ov_qty'].'+'.$_POST['ev_qty']]."' and it_id = '".$it_id."'");
	}


	alert('수정이 완료되었습니다.',$_SERVER['PHP_SELF']."?it_id=".$it_id);
	exit;
}

if($_POST['mode'] == 'delete'){
	$it_id = (int)trim($_POST['it_id']);
	$it_id_chk = sql_query("select count(*) from ".$g4['yc4_item_table']." where it_id = '".$it_id."'");
	if($it_id_chk < 1){
		alert('존재하지 않는 상품입니다.');
		exit;
	}
	$ov_it = sql_fetch("select * from yc4_over_stock_item where it_id = '".$it_id."'");
	$del_qry = "delete from yc4_over_stock_item where it_id = '".$it_id."'";

	if(!sql_query($del_qry)){
		alert('삭제중 오류 발생! 다시 시도해 주세요.');
		exit;
	}

	sql_query("delete from ".$g4['yc4_event_item_table']." where ev_id = '".$ev_arr[$ov_it['ov_qty'].'+'.$ov_it['ev_qty']]."' and it_id = '".$it_id."'");


	alert('삭제이 완료되었습니다.','over_stock_write.php');

	exit;
}

if($_GET['mode'] == 'it_search'){
	if(!trim($_GET['it_id'])){
		exit;
	}
	$it_id = mysql_real_escape_string($it_id);
	$it = sql_fetch("select it_id,it_name from yc4_item where it_id = '".$it_id."'");
	if($it){
		echo get_it_image($it['it_id'].'_s',100,100,$it['it_id']).$it['it_name'];
	}
	exit;
}

if($_GET['it_id']){
	$it = sql_fetch("select a.*,b.it_name from yc4_over_stock_item a, ".$g4['yc4_item_table']." b where a.it_id = '".$it_id."'");
	$input_hidden = "
		<input type='hidden' name='it_id' value='".$_GET['it_id']."'/>
		<input type='hidden' name='mode' value='update'/>
	";
}else{
	$input_hidden = "<input type='hidden' name='mode' value='insert'/>";
}
include $g4['full_path']."/adm/admin.head.php";
?>

<form action="<?php echo $_SERVER['PHP_SELF'];?>" method='post' onsubmit='return frm_sbm_fnc();'>
	<?php echo $input_hidden;?>
	<table>
		<tr>
			<td>상품코드</td>
			<td><input type="text" name='it_id' onchange='it_id_search(this.value);' value='<?php echo $it['it_id'];?>' <?php echo $it ? "readonly":"";?>/></td>
		</tr>
		<tr>
			<td>상품명</td>
			<td class='it_name'><?php if($it) {echo get_it_image($it['it_id'].'_s',100,100,$it['it_id']); echo $it['it_name']; }?></td>
		</tr>
		<tr>
			<td>이벤트정보</td>
			<td>
				<input type="text" name='ov_qty' value='<?php echo $it['ov_qty'];?>'/> + <input type="text" name='ev_qty' value='<?php echo $it['ev_qty'];?>'/>
				<br />
				ex) : 2 + 1
			</td>
		</tr>
		<tr>
			<td>사용여부</td>
			<td><input type="checkbox" name='use_yn' value='y' <?php echo $it['use_yn'] == 'y' ? "checked" : ""?>/></td>
		</tr>
	</table>
	<input type="submit" value='저장' />
	<?php if($it){?>
	<input type="button" value='삭제' onclick='ov_item_del(this.form);'/>
	<?php }?>
	<input type="button" value='목록' onclick="location.href='over_stock.php'" />
</form>

<script type="text/javascript">
<?php if($it){?>
function ov_item_del(f){
	if(!confirm('해당 이벤트 상품을 삭제하시겠습니까?')){
		return false;
	}

	$('input[name=mode]').val('delete');
	f.submit();
}
<?php }?>
function it_id_search(it_id){
	if(it_id == ''){
		return false;
	}

	$.ajax({
		url : '<?php echo $_SERVER['PHP_SELF'];?>',
		mode : 'get',
		data : {
			'mode' : 'it_search',
			'it_id' : it_id
		},success : function ( result ) {
			if(result == ''){
				return false;
			}
			$('.it_name').html(result);
		}
	});
}

function frm_sbm_fnc(){
	if($('input[name=it_id]').val() == ''){
		alert('상품코드를 입력해 주세요');
		$('input[name=it_id]').focus();
		return false;
	}

	if($('input[name=ov_qty]').val() == ''){
		alert('이벤트 정보를 입력해 주세요.');
		$('input[name=ov_qty]').focus();
		return false;
	}

	if($('input[name=ov_qty]').val().replace(/[0-9]/g,'').length>0){
		alert('이벤트 정보는 숫자만 입력해 주세요.');
		$('input[name=ov_qty]').focus();
		return false;
	}

	if($('input[name=ev_qty]').val() == ''){
		alert('이벤트 정보를 입력해 주세요.');
		$('input[name=ev_qty]').focus();
		return false;
	}

	if($('input[name=ev_qty]').val().replace(/[0-9]/g,'').length>0){
		alert('이벤트 정보는 숫자만 입력해 주세요.');
		$('input[name=ev_qty]').focus();
		return false;
	}
}

</script>
<?php
include $g4['full_path']."/adm/admin.tail.php";
?>