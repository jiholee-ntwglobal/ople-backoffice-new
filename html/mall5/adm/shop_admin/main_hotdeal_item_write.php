<?php
/*
----------------------------------------------------------------------
file name	 : main_hotdeal_item_write.php
comment		 : 메인 핫딜존 폼
date		 : 2015-01-22
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "600300";
include "_common.php";
auth_check($auth[$sub_menu], "w");


//if($member['mb_id']=='dev' || $member['mb_id']=='ople_mrs'){
	
# insert 처리 시작 #
if($_POST['mode'] == 'insert'){
	array_walk($_POST,'trim');

	if(!$_POST['it_id']){
		alert('상품 코드를 입력해 주세요.');
		exit;
	}

	$it_id_chk = sql_fetch("select count(*) as cnt from ".$g4['yc4_item_table']." where it_id = '".$_POST['it_id']."'");

	if($it_id_chk['cnt'] < 1){
		alert('상품코드가 올바르지 않습니다.');
		exit;
	}

	if(!$_POST['it_event_amount_usd'] || !is_numeric($_POST['it_event_amount_usd']) || $_POST['it_event_amount_usd'] < 0){
		alert('올바른 이벤트가를 입력해 주세요.');
		exit;
	}

	if(!$_POST['qty'] || !is_numeric($_POST['qty']) || $_POST['qty'] < 0){
		alert('올바른 이벤트 수량을 입력해 주세요.');
		exit;
	}
	if(round($_POST['it_event_amount_usd']*$default['de_conv_pay']) <= 0){
		alert('상품가격을 책정할 수 없습니다.');
		exit;
	}

	$sql = "
		insert into
			yc4_hotdeal_item (
			it_id	
		,	qty
		,	flag
		,	mb_id
		,	sort
		,	img_link
		,	comment
		,	it_event_amount_usd
		,	it_event_amount	
		,	it_amount_msrp
		,	create_dt
		)values(
			'".$_POST['it_id']."'
		,	'".$_POST['qty']."'
		,	'".$_POST['flag']."'
		,	'".$member['mb_id']."'
		,	0
		,	'".$_POST['img_link']."'
		,	'".$_POST['comment']."'
		,	'".number_format($_POST['it_event_amount_usd'],2)."'
		,	'".round($_POST['it_event_amount_usd']*$default['de_conv_pay'])."'
		,	'".number_format($_POST['it_amount_msrp'],2)."'
		,	'".$g4['time_ymdhis']."'
		)
	";

	if(!sql_query($sql)){
		alert('저장중 오류 발생! 관리자에게 문의하세요.');
		exit;
	}
	alert('저장이 완료되었습니다.',$g4['shop_admin_path'].'/main_hotdeal_item.php');

	exit;
}

# update 처리 시작 #
if($_POST['mode'] == 'update'){
	array_walk($_POST,'trim');

	if(!$_POST['uid']){
		alert('잘못된 경로로 접근하였습니다.');
		exit;
	}
	if(!$_POST['it_event_amount_usd'] || !is_numeric($_POST['it_event_amount_usd']) || $_POST['it_event_amount_usd'] < 0){
		alert('올바른 이벤트가를 입력해 주세요.');
		exit;
	}

	if(!$_POST['qty'] || !is_numeric($_POST['qty']) || $_POST['qty'] < 0){
		alert('올바른 이벤트 수량을 입력해 주세요.');
		exit;
	}
	if(round($_POST['it_event_amount_usd']*$default['de_conv_pay']) <= 0){
		alert('상품가격을 책정할 수 없습니다.');
		exit;
	}

	$sql = "
		update
			yc4_hotdeal_item
		set
			it_event_amount		= '".round($_POST['it_event_amount_usd']*$default['de_conv_pay'])."',
			it_event_amount_usd	= '".number_format($_POST['it_event_amount_usd'],2)."',
			it_amount_msrp		= '".number_format($_POST['it_amount_msrp'],2)."',
			qty					= '".$_POST['qty']."',
			img_link			= '".$_POST['img_link']."',
			flag				= '".$_POST['flag']."',
			comment				= '".$_POST['comment']."',
			update_id			= '".$member['mb_id']."',
			update_dt			= '".$g4['time_ymdhis']."'
		where
			uid = '".$_POST['uid']."'
	";

	if(!sql_query($sql)){
		alert('저장중 오류 발생! 관리자에게 문의하세요.');
		exit;
	}
	alert('저장이 완료되었습니다.',$g4['shop_admin_path'].'/main_hotdeal_item.php');

	exit;
}

if($_POST['mode'] == 'get_item_name'){
	array_walk($_GET,'trim');

	$sql = sql_fetch("select it_name from ".$g4['yc4_item_table']." where it_id = '".$_POST['it_id']."'");

	echo get_item_name($sql['it_name']);
	exit;
}

if($_GET['uid']){
	array_walk($_GET,'trim');
	$data = sql_fetch("
		select
			a.*,
			b.it_name
		from
			yc4_hotdeal_item a,
			".$g4['yc4_item_table']." b
		where
			a.uid = '".$_GET['uid']."'
	");
}

if($data){
	$input_hidden = "
		<input type='hidden' name='mode' value='update'/>
		<input type='hidden' name='uid' value='".$_GET['uid']."'/>
	";
}else{
	$input_hidden = "
		<input type='hidden' name='mode' value='insert'/>
	";
}

include_once $g4['admin_path']."/admin.head.php";
?>
<form name='frm' action="<?php echo $_SERVER['PHP_SELF'];?>" method='post'>
	<?php echo $input_hidden;?>
	<table width='100%'>
		<tr>
			<td width='10%'>상품코드</td>
			<td colspan='3'>
				<?php
				if($data['it_id']){
					echo $data['it_id'];
				}else{
				?>
				<input type="text" name='it_id' value='<?php echo $data['it_id']?>' onchange='ajax_get_item_name(this.value);'/>
				<?}?>
			</td>
		</tr>
		<tr class='it_name_tr'>
			<td>상품명</td>
			<td class='it_name' colspan='3'><?php echo $data['it_name'];?></td>
		</tr>
		<tr>
			<td>환율</td>
			<td colspan='3'><?php echo $default['de_conv_pay']?></td>
		</tr>
		<tr>
			<td>이벤트가(USD)</td>
			<td><input type="text" name='it_event_amount_usd' value='<?php echo $data['it_event_amount_usd']?>' onblur='tran_krw()' /></td>
			<td>이벤트가(KRW)</td>
			<td><input type="text" name='it_event_amount' value='<?php echo $data['it_event_amount']?>' disabled/></td>
		</tr>
		<tr>
			<td>MSRP_USD</td>
			<td colspan='3'><input type="text" name='it_amount_msrp' value='<?php echo $data['it_amount_msrp']?>'/></td>
		</tr>
		<tr>
			<td>수량</td>
			<td colspan='3'><input type="number" name='qty' min='1' value='<?php echo $data['qty']?>'/></td>
		</tr>
		<tr>
			<td>이미지</td>
			<td colspan='3'>
				<div class='img_link_wrap'></div>
				<input type="text" name='img_link' value='<?php echo $data['img_link'];?>' onchange='img_preview();' />
			</td>
		</tr>
		<tr>
			<td>COMMENT</td>
			<td colspan='3'>
				<textarea name="comment" id="" cols="30" rows="10"><?php echo $data['comment']?></textarea>
			</td>
		</tr>
		<tr>
			<td>상태</td>
			<td colspan='3'>
				<select name="flag" id="">
					<option value="W"<?=$data['flag'] == 'W' ? " selected":"";?>>대기</option>
					<option value="Y"<?=$data['flag'] == 'Y' ? " selected":"";?>>진행</option>
					<?php if($data){?>
					<option value="E"<?=$data['flag'] == 'E' ? " selected":"";?>>종료</option>
					<?}?>
				</select>
			</td>
		</tr>
	</table>
	<p align='center'><input type="button" value="저장" onclick="chk_form()" /><a href="<?php echo $g4['shop_admin_path'];?>/main_hotdeal_item.php">목록</a></p>
</form>


<script type="text/javascript">
	function ajax_get_item_name(it_id){
		var it_name = '';
		$.ajax({
			url : '<?php echo $_SERVER['PHP_SELF'];?>',
			type : 'post',
			data : {
				'mode' : 'get_item_name',
				'it_id' : it_id
			},
			success: function ( result ) {
				$('.it_name').text(result);
			}
		});
	}

	function img_preview(){
		var url = $('input[name=img_link]').val();
		$('.img_link_wrap').html("<img src='"+url+"'/>");
	}

	if($('input[name=img_link]').val() != ''){
		img_preview();
	}
	
	function tran_krw(){
		var amount_krw	= Math.round($('input[name=it_event_amount_usd]').val()*<?php echo $default['de_conv_pay'] ?>);
		$('input[name=it_event_amount]').val(amount_krw);
	}
	
	function chk_form(){
		
		if($.trim($('input[name=it_event_amount_usd]').val())==''){
			alert("상품가격을 입력해주세요");
			$('input[name=it_event_amount_usd]').focus();
			return;
		}
		if(isNaN($('input[name=it_event_amount_usd]').val()) || $('input[name=it_event_amount_usd]').val() < 0){
			alert("올바른 상품가격을 입력해주세요");
			$('input[name=it_event_amount_usd]').focus();
			return;
		}
		if($.trim($('input[name=qty]').val())==''){
			alert("상품수량을 입력해주세요");
			$('input[name=qty]').focus();
			return;
		}
		if(isNaN($('input[name=qty]').val()) || $('input[name=qty]').val() < 0){
			alert("올바른 상품수량을 입력해주세요");
			$('input[name=qty]').focus();
			return;
		}
		
		$('form').submit();
	}
</script>
<?php


include_once $g4['admin_path']."/admin.tail.php";