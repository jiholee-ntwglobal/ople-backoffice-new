<?php
/*
----------------------------------------------------------------------
file name	 : event_item_stock_write.php
comment		 : 이벤트 상품 별도 재고관리
date		 : 2014-11-26
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "300860";
include "_common.php";
auth_check($auth[$sub_menu], "w");


$event_ev_id_arr = array(
	1416554401,
	1416554384,
	1416554369,
	1416554353,
	1416554336,
	1416554318,
	1416554266,
	1416554243,
	1416554215,
	1416554201,
	1416554179,
	1416554149,
	1416554128,
	1416554091,
	1416554034,
	1413783986
);




if($_POST['mode'] == 'insert'){
	$it_id = trim($_POST['it_id']);

	if($it_id == ''){
		alert('상품코드를 입력해주세요.');
		exit;
	}

	# 맞는 상품 코드인지 체크
	$chk = sql_fetch("select count(*) as cnt from ".$g4['yc4_item_table']." where it_id = '".$it_id."' ");
	if($chk['cnt']<1){
		alert('존재하지 않는 상품코드 입니다.');
		exit;
	}

	# 이미 등록된 상품코드인지 체크
	$chk2 = sql_fetch("select count(*) as cnt from yc4_event_item_stock where it_id = '".$it_id."'");
	if($chk2['cnt']>0){
		alert('이미 등록된 상품입니다.');
		exit;
	}
	if((int)$_POST['qty'] == 0){
		alert('재고수량을 입력해 주세요');
		exit;
	}
	$comment = mysql_real_escape_string($_POST['comment']);
	$use_yn = $_POST['use_yn'] ? $_POST['use_yn'] : 'n';



	$sql = "
		insert into
			yc4_event_item_stock
		(
			it_id,qty,ch_amount,create_dt,comment,use_yn
		)values(
			'".$it_id."','".(int)$_POST['qty']."','".(int)$_POST['ch_amount']."','".date('Y-m-d H:i:s')."','".$comment."','".$use_yn."'
		)
	";

	if(!sql_query($sql)){
		alert('처리중 오류 발생! 관리자에게 문의하세요');

	}else{
		alert('저장되었습니다.','event_item_stock.php');
	}

	exit;
}


if($_POST['mode'] == 'update'){
	$it_id = trim($_POST['it_id']);

	if($it_id == ''){
		alert('상품코드를 입력해주세요.');
		exit;
	}
	if((int)$_POST['qty'] == 0){
		alert('재고수량을 입력해 주세요');
		exit;
	}
	$comment = mysql_real_escape_string($_POST['comment']);
	$use_yn = $_POST['use_yn'] ? $_POST['use_yn'] : 'n';


	$sql = "
		update
			yc4_event_item_stock
		set
			qty = '".(int)$_POST['qty']."',
			ch_amount = '".(int)$_POST['ch_amount']."',
			comment = '".$comment."',
			use_yn = '".$use_yn."'
		where
			uid = '".(int)$_POST['uid']."'
	";


	if(!sql_query($sql)){
		alert('처리중 오류 발생! 관리자에게 문의하세요');

	}else{
		alert('저장되었습니다.','event_item_stock.php');
	}

	exit;
}

if($_GET['mode'] == 'delete'){
	//$it_id = trim($_GET['it_id']);
	$uid = trim($_GET['uid']);
	if($uid == ''){
		alert('잘못된 경로로 접근하였습니다.');
		exit;
	}
	$sql = "delete from yc4_event_item_stock where uid = '".$uid."'";

	if(!sql_query($sql)){
		alert('처리중 오류 발생! 관리자에게 문의하세요');

	}else{
		alert('삭제되었습니다.','event_item_stock.php');
	}

	exit;

}



if($_POST['mode'] == 'item_info_ajax'){
	# 상품 정보 로드 #
	$it_id = trim($_POST['it_id']);
	$it = sql_fetch("select it_name,it_amount,it_maker from ".$g4['yc4_item_table']." where it_id = '".$it_id."'");
	if($it){
		$result .= "<error>0</error>";
		$result .= "<it_name>".$it['it_name']."</it_name>";
		$result .= "<it_amount>".number_format($it['it_amount'])."</it_amount>";
		$result .= "<it_maker>".$it['it_maker']."</it_maker>";

	}else{
		$result = "<error>1</error>";
	}

	@header("Content-Type: text/html; charset=UTF-8");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>";
	echo $result;


	exit;
}



if($_GET['it_id']){
	$it_id = trim($_GET['it_id']);
	$it = sql_fetch("
		select
			a.*,
			b.it_name,b.it_amount,b.it_maker
		from
			yc4_event_item_stock a,
			".$g4['yc4_item_table']." b
		where
			a.it_id = b.it_id
			and
			a.it_id = '".$it_id."'
	");


	$mode = "update";
	$it_id_frm = $it_id;
	$hidden_frm = "
		<input type='hidden' name='it_id' value='".$it_id."'/>
		<input type='hidden' name='uid' value='".$it['uid']."'/>

	";
	$del_btn = "<input type='button' value='삭제' onclick='it_delete();'/>";

}else{
	$mode = 'insert';
	$it_id_frm = "<input type='text' name='it_id'/>";
}

$g4[title] = "별도 재고관리 상품";
include $g4['full_path']."/adm/admin.head.php";
?>

<form action="<?=$_SERVER['PHP_SELF'];?>" method='post'>
	<input type="hidden" name='mode' value='<?php echo $mode;?>' />
	<?php echo $hidden_frm;?>
	<table width='100%'>
		<col width='15%'/>
		<col width='35%'/>
		<col width='15%'/>
		<col width='35%'/>
		<tr>
			<td>상품코드</td>
			<td><?php echo $it_id_frm;?></td>
			<td>수량</td>
			<td><input type="text" name='qty' value='<?=$it['qty']?>'/></td>
		</tr>

		<tr>
			<td>상품명</td>
			<td class='it_name'><?php echo $it['it_name']?></td>
			<td>브랜드명</td>
			<td class='it_maker'><?php echo $it['it_maker']?></td>
		</tr>

		<tr>
			<td>품절시 변경될 가격</td>
			<td><input type="text" name='ch_amount' value='<?php echo $it['ch_amount']?>'/></td>
			<td>상품가격</td>
			<td class='it_amount'><?php echo number_format($it['it_amount']);?></td>
		</tr>
		<tr>
			<td>메모</td>
			<td colspan='3'><textarea name="comment" id="" style='width:100%;' rows="10"><?php echo $it['comment'];?></textarea></td>
		</tr>
		<tr>
			<td>사용</td>
			<td><input type="checkbox" name='use_yn' value='y' <?=$it['use_yn'] == 'y' ? "checked":""?>/></td>
		</tr>
	</table>
	<p align='center'><input type="submit" value=' 저장 ' /><?=$del_btn;?> <input type="button" value=' 목록 ' onclick="location.href='event_item_stock.php'" /></p>

</form>

<script type="text/javascript">

$('input[name=it_id]').change(function(){
	$.ajax({
		url : '<?=$_SERVER['PHP_SELF']?>',
		type : 'post',
		datatype : 'xml',
		data : {
			'mode' : 'item_info_ajax',
			'it_id' : $(this).val()
		},success : function (result) {
			var xml = $(result);
			var it_name = '';
			var it_maker = '';
			var it_amount = '';

			if($(xml[1]).text() == 0){
				it_name = $(xml[2]).text();
				it_maker = $(xml[4]).text();
				it_amount = $(xml[3]).text();
			}
			$('.it_name').text(it_name);
			$('.it_maker').text(it_maker);
			$('.it_amount').text(it_amount);

		}
	});
});

function it_delete(){
	if(!confirm('해당 상품의 이벤트 제고관리를 삭제하시겠습니까?')){
		return false;
	}

	location.href="<?php echo $_SERVER['PHP_SELF'];?>?mode=delete&it_id=<?php echo trim($_GET['uid']);?>";
	return false;
}
</script>

<?php
include $g4['full_path']."/adm/admin.tail.php";
?>