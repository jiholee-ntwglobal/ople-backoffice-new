<?php
$sub_menu = "400920";
include "_common.php";
auth_check($auth[$sub_menu], "w");

$fg = $_GET['fg'] == 'it_id' ? "it_id":"it_maker";

if($_POST['mode'] == 'it_maker_chk'){
	$chk = sql_fetch("select count(*) as cnt from ".$g4['yc4_item_table']." where it_maker = '".mysql_real_escape_string($_POST['it_maker'])."'");
	if($chk['cnt'] == '0'){
		echo 'err1';
	}else{
		// 이미 오플코리아 제외 브랜드에 등록되어있는지 체크
		$chk2 = sql_fetch("select count(*) as cnt from yc4_opk_no_item_maker where it_maker = '".mysql_real_escape_string($_POST['it_maker'])."'");
		if($chk2['cnt'] < 1){
			echo 'ok';
		}else{
			echo 'err2';
		}
	}
	exit;
}

if($_POST['mode'] == 'insert'){
	if($fg == 'it_maker'){
		$it_maker = mysql_real_escape_string($_POST['it_maker']);
		$chk = sql_fetch("select count(*) as cnt from yc4_opk_no_item_maker where it_maker = '".$it_maker."'");
		if($chk['cnt']>0){
			alert('이미 등록된 브랜드 입니다.');
			exit;
		}
		$chk2 = sql_fetch("select count(*) as cnt from ".$g4['yc4_item_table']." where it_maker = '".$it_maker."'");
		if($chk['cnt']<1){
			alert('존재하지 않는 브랜드 입니다.');
			exit;
		}
		$sql = "insert into yc4_opk_no_item_maker (it_maker) values('".$it_maker."')";

		if(!sql_query($sql)){
			alert('처리중 오류 발생 관리자에게 문의하세요.');
			exit;
		}else{
			alert('저장되었습니다.','opk_no_item.php?fg='.$fg);
			exit;
		}

	}else{
		$chk = sql_fetch("select count(*) as cnt from ".$g4['yc4_item_table']." where it_id = '".$_POST['it_id']."'");
		if($chk['cnt']<1){
			alert('존재하지 않는 상품입니다.');
			exit;
		}
		$chk2 = sql_fetch("select count(*) as cnt from yc4_opk_no_item where it_id = '".$_POST['it_id']."'");
		if($chk['cnt']>0){
			alert('이미 등록된 상품입니다.');
			exit;
		}
		$sql = "insert into yc4_opk_no_item (it_id) values('".$_POST['it_id']."')";

		if(!mysql_query($sql)){
			alert('처리중 오류 발생 관리자에게 문의하세요.');
			exit;
		}else{
			alert('저장되었습니다.','opk_no_item.php?fg='.$fg);
		}
	}
	exit;
}

if($_POST['mode'] == 'update'){
	if($fg == 'it_maker'){
		$it_maker = mysql_real_escape_string($_POST['it_maker']);
		$bf_it_maker = mysql_real_escape_string($_POST['bf_it_maker']);
		$sql = "update yc4_opk_no_item_maker set it_maker = '".$it_maker."' where it_maker = '".$bf_it_maker."'";
		if(!sql_query($sql)){
			alert("처리중 오류 발생! 관리자에게 문의하세요.");
		}else{
			alert("수정이 완료되었습니다.",'opk_no_item.php?fg='.$fg);
		}
	}else{
		$sql = "update yc4_opk_no_item set it_it = '".$_POST['it_id']."' where it_id = '".$_POST['bf_it_id']."'";
		if(!sql_query($sql)){
			alert("처리중 오류 발생! 관리자에게 문의하세요.");
		}else{
			alert("수정이 완료되었습니다.",'opk_no_item.php?fg='.$fg);
		}
	}

	exit;
}

if($_POST['mode'] == 'delete'){
	if($fg == 'it_maker'){
		$bf_it_maker = mysql_real_escape_string($_POST['bf_it_maker']);
		$sql = "delete from yc4_opk_no_item_maker where it_maker = '".$bf_it_maker."'";
	}else{
		$sql = "delete from yc4_opk_no_item where it_id = '".$_POST['bf_it_id']."'";
	}

	if(!sql_query($sql)){
		alert("처리중 오류 발생! 관리자에게 문의하세요.");
	}else{
		alert("수정이 완료되었습니다.",'opk_no_item.php?fg='.$fg);
	}
	exit;
}


$g4[title] = "오플코리아 미등록상품관리";
include $g4['full_path']."/adm/admin.head.php";


/*
echo 'here';
echo '<br/>';
$conv =  htmlentities("as\"d'f");
echo $conv;
echo '<br/>';
$conv2 = html_entity_decode($conv);
echo $conv2;



*/

?>

<form action="<?=$_SERVER['PHP_SELF'];?>" method='post' name='frm' onsubmit='return frm_chk();'>
	<input type="hidden" name='fg' value='<?=$fg;?>'/>
	<input type="hidden" name='mode' value='insert'/>
	<table width='100%'>
		<?if($fg == 'it_maker'){?>
		<tr>
			<td>브랜드명</td>
			<td><input type="text" name="it_maker" /></td>
		</tr>
		<?}else{?>
		<tr>
			<td>상품코드</td>
			<td><input type="text" name='it_id' /></td>
		</tr>
		<?}?>
		<tr>
			<td colspan='2'><input type="submit" value=' 저 장 ' /></td>
		</tr>
	</table>
</form>

<script type="text/javascript">
function frm_chk(){
	if(typeof(frm.it_maker) != 'undefined'){
		var it_maker = frm.it_maker.value;
		var submit_chk = true;
		$.ajax({
			url : '<?=$_SERVER['PHP_SELF']?>',
			type : 'post',
			data : {
				'mode' : 'it_maker_chk',
				'it_maker' : it_maker
			},success : function (result){
				if(result != 'ok'){
					if(result == 'err1'){
						alert('존재하지 않는 브랜드명입니다.');
					}else if(result == 'err2'){
						alert('이미 등록된 브랜드 입니다.');
					}
					submit_chk = false;
					return false;
				}else{
					submit_chk = true;
					return true;
				}
			}
		});
		if(submit_chk == true) {
			return true;
		}else{
			return false;
		}
	}
}
</script>
<?php
include $g4['full_path']."/adm/admin.tail.php";
?>