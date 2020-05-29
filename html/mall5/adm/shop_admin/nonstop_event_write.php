<?php
/*
----------------------------------------------------------------------
file name	 : nonstop_event_write.php
comment		 : 논스톱 이벤트 관리 폼
date		 : 2014-11-27
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "400940";
include "./_common.php";

auth_check($auth[$sub_menu], "r");

if($_POST['mode'] || $_GET['mode']){
	$uid = (int)$_POST['uid'];
}

# 수정 처리
if($_POST['mode'] == 'update'){
	$uid = (int)$_POST['uid'];

	if(!$_POST['it_id']){
		alert('상품코드를 입력해 주세요.');
		exit;
	}

	if(!$_POST['ev_amount']){
		alert('가격을 입력해 주세요.');
		exit;
	}

	if(!$_POST['ev_qty']){
		alert('이벤트 수량을 입력해 주세요.');
		exit;
	}

	$bf_seq = sql_fetch("select seq from yc4_nontop_sale where uid = '".$uid."'");
	$bf_seq = $bf_seq['seq'];



	$comment = mysql_real_escape_string($_POST['comment']);
	$img_url = mysql_real_escape_string(urlencode($_POST['img_url']));

	$update_qry = "
		update
			yc4_nontop_sale
		set
			ev_amount = '".$_POST['ev_amount']."',
			ev_qty = '".$_POST['ev_qty']."',
			seq = '".$_POST['seq']."',
			comment = '".$comment."',
			img_url = '".$img_url."'
		where
			uid = '".$uid."'
	";

	if(!sql_query($update_qry)){
		alert('처리중 오류 발생 관리자에게 문의하세요!');
		exit;
	}

	if($_POST['seq'] != $bf_sql){
		/*
			1. 기존 순서보다 다음 순위의 이벤트 상품을 한칸씩 앞으로 이동
			2. 새로운 순서와 같거나 큰 순서의 상품을 한칸씩 뒤로 이동
		*/
		sql_query("update yc4_nontop_sale set seq = seq - 1 where seq < '".$bf_seq."'");
		sql_query("update yc4_nontop_sale set seq = seq + 1 where seq >= '".$bf_seq."' and uid != '".$uid."'");
	}

	alert('저장이 완료되었습니다.',$_SERVER['PHP_SELF'].'?uid='.$uid);


	exit;
}

if($_POST['insert']){
	if(!$_POST['it_id']){
		alert('상품코드를 입력해 주세요.');
		exit;
	}

	if(!$_POST['ev_amount']){
		alert('가격을 입력해 주세요.');
		exit;
	}

	if(!$_POST['ev_qty']){
		alert('이벤트 수량을 입력해 주세요.');
		exit;
	}

	if(!$_POST['seq']){
		$seq = sql_fetch("select max(seq) + 1 as seq from yc4_nontop_sale");
		$seq = $seq['seq'];
	}else{
		$seq = $_POST['seq'];
	}

	$it_order_onetime_limit_cnt_chk = sql_fetch("select it_order_onetime_limit_cnt yc4_item where it_id = '".$_POST['it_id']."'");

	if($it_order_onetime_limit_cnt_chk['it_order_onetime_limit_cnt']>0){
		$it_order_onetime_limit_fg = 'Y';
	}else{
		$it_order_onetime_limit_fg = 'N';
	}

	$comment = mysql_real_escape_string($_POST['comment']);
	$img_url = mysql_real_escape_string(urlencode($_POST['img_url']));

	$status_chk = sql_fetch("
		select count(*) as cnt from yc4_nontop_sale where status = 2
	");

	if($status_chk['cnt']>0){ // 진행중인 이벤트가 없다면 등록된이벤트가 바로 진행
		$status = 2;
	}else{
		$status = 1;
	}


	$sql = "
		insert into
			yc4_nontop_sale
		(
			it_id,ev_amount,ev_qty,seq,create_dt,comment,status,img_url,it_order_onetime_limit_fg
		)values(
			'".$_POST['it_id']."','".$_POST['ev_amount']."','".$_POST['ev_qty']."','".$seq."','".date('Y-m-d H:i:s')."','".$comment."','".$status."','".$img_url."','".$it_order_onetime_limit_fg."'
		)
	";
	if(!sql_query($sql)){
		alert('처리중 오류 발생 ! 관리자에게 문의하세요.');
	}else{
		$uid = mysql_insert_id();
		alert('저장되었습니다.',$_SERVER['PHP_SELF'].'?uid='.$uid);
	}

	exit;
}


if($_GET['mode'] == 'delete'){
	if(!$uid){
		alert('잘못된 경로로 접근하셨습니다.');
		exit;
	}
	$ev_info = sql_query("
		select
			seq,status
		from
			yc4_nontop_sale
		where
			uid= '".$uid."'
	");
	$seq = $ev_info['seq'];
	$status = $ev_info['status'];

	sql_query("update yc4_nontop_sale set seq = seq - 1 where seq > '".(int)$seq."'");

	# 이미 진행중인 상품일 경우 다음 상품을 진행으로 변경 #
	if($status == 2){
		sql_query("update yc4_nontop_sale set status = '2' where seq = '".(int)$seq."'");
	}

	$del_qry = "
		delete from yc4_nontop_sale where uid = '".$uid."'
	";
	if(!sql_query($del_qry)){
		alert('처리중 오류 발생! 관리자에게 문의하세요.');
	}

	alert('이벤트 상품이 삭제되었습니다.','nonstop_event.php');
	exit;


}





if($_GET['uid']){
	$uid = (int)$_GET['uid'];
	$ev_info = sql_fetch("
		select
			a.*,
			b.it_name
		from
			yc4_nontop_sale a,
			".$g4['yc4_item_table']." b
		where
			a.it_id = b.it_id
			and
			a.uid = '".mysql_real_escape_string($uid)."'
	");
	$input_hidden = "
		<input type='hidden' name='mode' value='update'/>
		<input type='hidden' name='uid' value='".$ev_info['uid']."'/>
	";

}else{
	$input_hidden = "
		<input type='hidden' name='mode' value='insert'/>
	";
}
switch($ev_info['status']){
	case 1 : $status = '대기'; break;
	case 2 : $status = '진행'; break;
	case 3 : $status = '죵료'; break;
	default : $status = '대기'; break;
}



$g4['title'] = '논스톱이벤트관리';
include $g4['full_path']."/adm/admin.head.php";
?>
<style type="text/css">
.table_form  td{
	padding:4px;
}
</style>
<form action="<?=$_SERVER['PHP_SELF']?>" method='post'>
	<?=$input_hidden;?>
	<table width='100%' class='table_form'>
		<col width='15%'/>
		<col width='20%'/>
		<col width='15%'/>
		<col />
		<tr>
			<td>상품코드</td>
			<td colspan='3'><input type="text" name='it_id' value='<?=$ev_info['it_id']?>'/></td>

		</tr>
		<tr>
			<td>상품명</td>
			<td class='it_name' colspan='3'><?=$ev_info['it_name']?></td>
		</tr>
		<tr>
			<td>상품가격</td>
			<td><input type="text" name='ev_amount' value='<?=$ev_info['ev_amount']?>' /></td>
			<td>이벤트재고</td>
			<td><input type="text" name='ev_qty' value='<?=$ev_info['ev_qty']?>' /></td>
		</tr>
		<tr>
			<td>순서</td>
			<td><input type="text" name='seq' value='<?=$ev_info['seq']?>' /></td>
			<td>상태</td>
			<td><?=$status;?></td>
		</tr>
		<tr>
			<td>코멘트</td>
			<td colspan='3'><textarea name="comment" id="" cols="30" rows="10" style='width:100%;'><?=$ev_info['comment']?></textarea></td>
		</tr>
		<tr>
			<td>이미지 URL</td>
			<td colspan='3'><input type="text" name='img_url' value='<?=urldecode($ev_info['img_url'])?>' style='width:100%;'/></td>
		</tr>
		<?if($ev_info['start_dt'] || $ev_info['end_dt']){?>
		<tr>
			<td>시작일</td>
			<td><?=$ev_info['start_dt']?></td>
			<td>종료일</td>
			<td><?=$ev_info['end_dt']?></td>
		</tr>
		<?}?>
	</table>
	<p align='center'>
		<input type="submit" value=' 저장 ' />
		<input type="button" value=' 삭제 ' onclick="ev_it_del();"/>
		<input type="button" value=' 목록 ' onclick="location.href='nonstop_event.php'"/>
	</p>
</form>

<script type="text/javascript">
	function ev_it_del(){
		if(!confirm('해당 논스톱 이벤트 상품을 삭제하시겠습니까?')){
			return false;
		}

		location.href='<?=$_SERVER['PHP_SELF']?>?mode=delete&uid=<?=$ev_info['uid']?>';
	}
</script>
<?php
include $g4['full_path']."/adm/admin.tail.php";
?>