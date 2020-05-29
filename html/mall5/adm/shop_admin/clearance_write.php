<?php
/*
----------------------------------------------------------------------
file name	 : clearance_write.php
comment		 : 클리어런스 등록 및 수정
date		 : 2015-02-26
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "300880";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

function clearance_item_event_insert(){
    // 클리어런스 테이블과 클리어런스 이벤트 테이블 동기화
    global $g4;
    $sql = sql_query("select DISTINCT a.it_id from yc4_clearance_item a where a.it_id not in (select b.it_id from yc4_event_item b where b.ev_id = '1424920190' and b.it_id = a.it_id)");
    $in = "";
    while($row= sql_fetch_array($sql)){
        $in .= ($in ? ",":"")."('1424920190','".$row['it_id']."')";
    }
    if($in){
        sql_query("insert into yc4_event_item (ev_id, it_id) values ".$in);
    }

    $sql = sql_query("
      select b.it_id from yc4_event_item b where b.ev_id = '1424920190' and b.it_id not in (select DISTINCT a.it_id from yc4_clearance_item a where a.it_id = b.it_id)
    ");
    $in = '';
    while($row = sql_fetch_array($sql)){
        $in .= ($in ? ",":"")."'".$row['it_id']."'";
    }

    if($in){
        sql_query("delete from yc4_event_item where ev_id = '1424920190' and it_id in (".$in.")");
    }
}


if($_POST['mode'] == 'update'){

	$update_set = '';

	if($_POST['ori_it_id']){
		$update_set .= ", ori_it_id = '".mysql_real_escape_string(trim($_POST['ori_it_id']))."'";
	}else{
		$update_set .= ", ori_it_id = null";
	}

	if($_POST['exp_date']){
		$update_set .= ", exp_date = '".mysql_real_escape_string(trim($_POST['exp_date']))."'";
	}else{
		$update_set .= ", exp_date = null";
	}

	if($_POST['comment_column_nm']){
		$update_set .= ", comment_column_nm = '".mysql_real_escape_string(trim($_POST['comment_column_nm']))."'";
	}else{
		$update_set .= ", comment_column_nm = null";
	}

	if($_POST['comment_column_val']){
		$update_set .= ", comment_column_val = '".mysql_real_escape_string(trim($_POST['comment_column_val']))."'";
	}else{
		$update_set .= ", comment_column_val = null";
	}


	$sql = "
		update
			yc4_clearance_item
		set
			qty = ".(int)$_POST['qty'].",
			msrp = '".trim($_POST['msrp'])."'
			".$update_set."
		where
			it_id = '".mysql_real_escape_string(trim($_POST['it_id']))."'
	";

	if(sql_query($sql)){
        clearance_item_event_insert();
        alert('저장이 완료되었습니다.','clearance_list.php');
		exit;
	}else{
		alert('처리중 오류 발생! 관리자에게 문의하세요.');
		exit;
	}

	exit;
}

if($_POST['mode'] == 'insert'){

	$it_id_chk = sql_fetch("select count(*) as cnt from yc4_clearance_item where it_id = '".mysql_real_escape_string(trim($_POST['it_id']))."'");

	$update_set = '';

	if($_POST['ori_it_id']){
		$update_set .= ", ori_it_id = '".mysql_real_escape_string(trim($_POST['ori_it_id']))."'";
	}else{
		$update_set .= ", ori_it_id = null";
	}

	if($_POST['exp_date']){
		$update_set .= ", exp_date = '".mysql_real_escape_string(trim($_POST['exp_date']))."'";
	}else{
		$update_set .= ", exp_date = null";
	}

	if($_POST['comment_column_nm']){
		$update_set .= ", comment_column_nm = '".mysql_real_escape_string(trim($_POST['comment_column_nm']))."'";
	}else{
		$update_set .= ", comment_column_nm = null";
	}

	if($_POST['comment_column_val']){
		$update_set .= ", comment_column_val = '".mysql_real_escape_string(trim($_POST['comment_column_val']))."'";
	}else{
		$update_set .= ", comment_column_val = null";
	}

	$sql = "
		insert into
			yc4_clearance_item
		set
			it_id = '".mysql_real_escape_string(trim($_POST['it_id']))."',
			qty = ".(int)$_POST['qty'].",
			msrp = '".trim($_POST['msrp'])."'
			".$update_set."
	";

	if(sql_query($sql)){
        clearance_item_event_insert();
		alert('저장이 완료되었습니다.','clearance_list.php');
		exit;
	}else{
		alert('처리중 오류 발생! 관리자에게 문의하세요.');
		exit;
	}
}

if($_GET['mode'] == 'delete'){
	$sql = "
		delete from yc4_clearance_item where it_id = '".mysql_real_escape_string(trim($_GET['it_id']))."'
	";

	if(sql_query($sql)){
        clearance_item_event_insert();
		alert('삭제 완료되었습니다.','clearance_list.php');
		exit;
	}else{
		alert('처리중 오류 발생! 관리자에게 문의하세요.');
		exit;
	}

	exit;
}


if($_GET['it_id']){
	$data = sql_fetch("
		select
			a.* ,
			b.it_name,
			b.it_amount
		from
			yc4_clearance_item a,
			".$g4['yc4_item_table']." b
		where
			a.it_id = b.it_id
			and
			a.it_id = '".trim($_GET['it_id'])."'
	");
}


if($data){
	$input_hidden = "
		<input type='hidden' name='mode' value='update'/>
		<input type='hidden' name='it_id' value='".$data['it_id']."'/>
	";
}else{
	$input_hidden = "<input type='hidden' name='mode' value='insert'/>";
}

$g4[title] = "클리어런스 상품 리스트";
include_once ("$g4[admin_path]/admin.head.php");
?>


<form action="<?php echo $_SERVER['PHP_SELF'];?>" method='post' onsubmit="return frm_chk(this);">
	<?php echo $input_hidden;?>
	<table width='100%'>
		<tr>
			<td>오플 상품코드</td>
			<td>
			<?php
				if($data){
					echo $data['it_id'];
				}else{
					echo "<input type='text' name='it_id'/>";
				}
			?>
			</td>
		</tr>
		<tr>
			<td>상품명</td>
			<td class='it_name'><?php echo $data['it_name']?></td>
		</tr>
		<tr>
			<td>판매가격</td>
			<td class='it_amount'><?php echo $data['it_amount']?></td>
		</tr>
		<tr>
			<td>정상제품 상품코드</td>
			<td><input type="text" name='ori_it_id' value='<?php echo $data['ori_it_id']?>' /></td>
		</tr>
		<tr>
			<td>수량</td>
			<td><input type="text" name='qty' value='<?php echo $data['qty']?>' /></td>
		</tr>
        <tr>
            <td>MSRP</td>
            <td><input type="text" name="msrp" value="<?php echo $data['msrp'];?>"/></td>
        </tr>
		<tr>
			<td>유통기한</td>
			<td><input type="text" name='exp_date' value='<?php echo $data['exp_date'];?>' /></td>
		</tr>
		<tr>
			<td>추가설명 필드명</td>
			<td><input type="text" name='comment_column_nm' value="<?php echo $data['comment_column_nm'];?>" /></td>
		</tr>
		<tr>
			<td>추가설명 필드데이터</td>
			<td><input type="text" name='comment_column_val' value="<?php echo $data['comment_column_val'];?>" /></td>
		</tr>
	</table>
	<p>
		<input type="submit" value='저장' />
		<?php
			if($data){
				echo "<input type='button' value='삭제' onclick=\"item_del();\"/>";
			}
		?>
		<input type="button" value='목록' onclick="location.href='clearance_list.php';" />
	</p>
</form>

<script type="text/javascript">
function frm_chk(f){
	if(f.it_id.value == ''){
		alert('상품코드를 입력해 주세요.');
		return false;
	}

	if(f.qty.value.replace(/[^0-9]/g,'') == ''){
		alert('수량을 입력해 주세요');
		f.qty.value = f.qty.value.replace(/[^0-9]/g,'');
		return false;
	}

	return true;
}
function item_del(){
	if(!confirm('클리어런스 상품을 삭제하시겠습니까?')){
		return false;
	}

	location.href="<?=$_SERVER['PHP_SELF'];?>?mode=delete&it_id=<?php echo $data['it_id'];?>";
}

</script>

<?php
include_once ("$g4[admin_path]/admin.tail.php");
?>