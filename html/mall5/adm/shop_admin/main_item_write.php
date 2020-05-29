<?php
/*
----------------------------------------------------------------------
file name	 : main_item_write.php
comment		 : 메인 상품 진열 상품 등록
date		 : 2015-01-16
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "600200";
include "_common.php";
auth_check($auth[$sub_menu], "w");

if($_POST['mode'] == 'insert'){

	array_walk($_POST,'mysql_real_escape_string');
	array_walk($_POST,'trim');


	# 종료일 계산 #
	$en_dt_chk = mysql_fetch_assoc(mysql_query("
		select
			en_dt
		from
			yc4_main_item
		where
			en_dt > '".$_POST['st_dt']." 00:00:00'
		order by
			en_dt asc
		limit 1
	"));

	if($en_dt_chk['en_dt']){
		$en_dt = $en_dt_chk['en_dt'];
	}else{
		$en_dt = '9999-12-31 23:59:59';
	}

	$update_sql = "
		update
			yc4_main_item
		set
			en_dt = '".second_minus($_POST['st_dt'])."'
		where
			m_type = '".$_POST['m_type']."'
			and
			en_dt <= '".$en_dt."'
		order by en_dt desc
		limit 1
	";
	sql_query($update_sql);



	$sql = "
		insert into
			yc4_main_item
		(
			m_type, it_id,
			st_dt,en_dt,
			mb_id, create_dt,
			img_link
		)values(
			'".$_POST['m_type']."','".$_POST['it_id']."',
			'".$_POST['st_dt']." 00:00:00','".$en_dt."',
			'".$member['mb_id']."', '".$g4['time_ymdhis']."',
			'".$_POST['img_link']."'
		)
	";
	if(!sql_query($sql)){
		alert('처리중 오류 발생! 관리자에게 문의하세요.');
		exit;
	}


	alert('저장이 완료되었습니다.',$g4['shop_admin_path'].'/main_item.php');
	exit;
}

if($_POST['mode'] == 'update'){

	array_walk($_POST,'mysql_real_escape_string');
	array_walk($_POST,'trim');


	if($_POST['st_dt']){
		$sql = "
			update
				yc4_main_item
			set
				en_dt = '".second_minus($_POST['st_dt'])."'
			where
				uid != '".$_POST['uid']."'
				and
				en_dt <= '".$_POST['st_dt']." 00:00:00'
			order by en_dt desc
			limit 1
		";
		sql_query($sql);
		$update_set = "st_dt = '".$_POST['st_dt']." 00:00:00',\n";
	}

	$sql = "
		update
			yc4_main_item
		set
			it_id = '".$_POST['it_id']."',
			".$update_set."
			update_dt = '".$g4['time_ymdhis']."',
			update_mb_id = '".$member['mb_id']."',
			img_link = '".$_POST['img_link']."'
		where
			uid = '".$_POST['uid']."'
	";
	if(!sql_query($sql)){
		alert('처리중 오류 발생! 관리자에게 문의하세요.');
		exit;
	}
	alert('저장이 완료되었습니다.',$g4['shop_admin_path'].'/main_item.php');
	exit;
}

$m_type_arr = array(
	'H' => 'HOT',
	'N' => 'NEW',
	'B' => 'BEST',
	'M' => '만원의행복'
);


if($_GET['uid']){
	$_GET['uid'] = trim($_GET['uid']);

	$data = sql_fetch("
		select
			*,
			if(st_dt <='".$g4['time_ymdhis']."' ,'y',null) as del_fg
		from
			yc4_main_item
		where
			uid= '".$_GET['uid']."'
	");
	$hidden_input = "
		<input type='hidden' name='uid' value='".$_GET['uid']."'/>
		<input type='hidden' name='mode' value='update'/>
	";
}else{
	$hidden_input = "
		<input type='hidden' name='mode' value='insert'/>
	";
}

include_once $g4['admin_path']."/admin.head.php";
?>

<form action="<?=$_SERVER['PHP_SELF']?>" method='post' onsubmit='return frm_chk(this);'>
	<?php echo $hidden_input;?>
	<table width='100%'>
		<tr>
			<td>구분</td>
			<td>
				<?php
				if($data){
					echo $m_type_arr[$data['m_type']];
				}else{
				?>
				<select name="m_type" id="">
					<?php
						foreach($m_type_arr as $val => $name){
							echo "<option value='".$val."'".($val == $data['m_type'] ? " selected":"").">".$name."</option>";
						}
					?>
				</select>
				<?php }?>
			</td>
		</tr>
		<tr>
			<td>상품코드</td>
			<td><input type="text" name='it_id' value='<?php echo $data['it_id'];?>' /></td>
		</tr>

		<tr>
			<td>기간</td>
			<td>
				<?php
				if(!$data['del_fg']){
				?>
				<input type="text" name='st_dt' value='<?php echo trim(preg_replace("/\d{2}:*:\d{2}:*:\d{2}/i", "", $data['st_dt'])) ;?>' />
				<?php }else{
					echo $data['st_dt'];
				}?>
				~
				<?php
				if($data){
					echo $data['en_dt'];
				}
				?>
				예)2015-01-01
			</td>
		</tr>
		<tr>
			<td>이미지 링크</td>
			<td><input type="text" name='img_link' value='<?php echo $data['img_link'];?>' /></td>
		</tr>
	</table>
	<p align='center'>
		<input type="submit" value='저장' />
		<?php if(!$data['del_fg']){?>
		<input type="button" value='삭제' onclick="main_item_del();" />
		<?php }?>
		<a href="<?php echo $g4['shop_admin_path'];?>/main_item.php">목록</a>

	</p>
</form>

<form action="<?php echo $g4['shop_admin_path'];?>/main_item.php" method='post' name='del_frm'>
	<input type="hidden" name='mode' value='delete' />
	<input type="hidden" name='uid' value='' />
	<input type="hidden" name='qstr' value='<?php echo $qstr3;?>' />
</form>


<?php if($data){?>
<script type="text/javascript">
function main_item_del(){
	if(!confirm('해당 상품을 메인 진열에서 해제하시겠습니까?')){
		return false;
	}

	var uid = '<?php echo $data['uid'];?>';

	del_frm.uid.value = uid;
	del_frm.submit();
}
</script>
<?php }?>

<script type="text/javascript">
function frm_chk(f){
	if(f.it_id.value == ''){
		alert('상품코드를 입력해 주세요.');
		f.it_id.focus();
		return false;
	}

	if(f.st_dt.value == ''){
		alert('시작일을 입력해 주세요.');
		f.st_dt.focus();
		return false;
	}

	if(f.en_dt.value == ''){
		alert('종료일을 입력해 주세요.');
		f.en_dt.focus();
		return false;
	}

	if(f.img_link.value == ''){
		alert('이미지 링크를 입력해 주세요.');
		f.img_link.focus();
		return false;
	}

	return true;
}
</script>

<?php
include_once $g4['admin_path']."/admin.tail.php";