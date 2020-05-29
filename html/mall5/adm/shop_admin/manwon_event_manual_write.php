<?php
$sub_menu = "500600";
include "./_common.php";

auth_check($auth[$sub_menu], "r");
$g4['title'] = '만원의 행복 이벤트 순서 등록';



if($_POST['mode'] == 'insert'){
	$it_id = (int)trim($_POST['it_id']);	

	$it_id_chk = sql_fetch("select count(*) as cnt from ".$g4['yc4_item_table']." where it_id = '".$it_id."'");
	if($it_id_chk['cnt'] < 1){
		alert('존재하지 않는 상품입니다.');
		exit;
	}

	$du_chk = sql_fetch("select count(*) as cnt from manwon_event_manual where it_id = '".$it_id."' and category='".$_POST['category']."'");

	if($du_chk['cnt'] > 0){
		alert('중복된 상품이 존재합니다.');
		exit;
	}

	$insert_qry = "
		insert into
			manwon_event_manual
		(
			sort, category, it_id
		)values(
			'".$_POST['sort']."','".$_POST['category']."','".$it_id."'
		)
	";
	if(!sql_query($insert_qry)){
		alert('저장중 오류 발생! 다시 시도해 주세요.');
		exit;
	}

	$uid = mysql_insert_id();


	alert('저장이 완료되었습니다.',$_SERVER['PHP_SELF']."?uid=".$uid."&category=".$_POST['category']);

	exit;
}

if($_POST['mode'] == 'update'){
	$it_id = (int)trim($_POST['it_id']);
	if(!$_POST['use_yn']){
		$_POST['use_yn'] = 'n';
	}

	$it_id_chk = sql_fetch("select count(*) as cnt from ".$g4['yc4_item_table']." where it_id = '".$it_id."'");
	if($it_id_chk['cnt'] < 1){
		alert('존재하지 않는 상품입니다.');
		exit;
	}

	$du_chk = sql_fetch("select count(*) as cnt from manwon_event_manual where it_id = '".$it_id."' and category='".$_POST['category']."' and uid != '".$_POST['uid']."'");

	if($du_chk['cnt'] > 0){
		alert('중복된 상품이 존재합니다.');
		exit;
	}

	$update_qry = "
		update
			manwon_event_manual
		set
			sort = '".$_POST['sort']."',
			it_id = '".$_POST['it_id']."'
		where
			uid = '".$_POST['uid']."'
	";

	if(!sql_query($update_qry)){
		alert('수정중 오류 발생! 다시 시도해 주세요.');
		exit;
	}
	


	alert('수정이 완료되었습니다.',$_SERVER['PHP_SELF']."?uid=".$uid."&category=".$_POST['category']);
	exit;
}

if($_POST['mode'] == 'delete'){
	$uid = (int)trim($_POST['uid']);
	
	$del_qry = "delete from manwon_event_manual where uid = '".$uid."'";

	if(!sql_query($del_qry)){
		alert('삭제중 오류 발생! 다시 시도해 주세요.');
		exit;
	}
	


	alert('삭제가 완료되었습니다.','manwon_event_manual_list.php?category='.$_POST['category']);

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

if($_GET['uid']){
	$it = sql_fetch("select m.*,i.it_name from manwon_event_manual m, ".$g4['yc4_item_table']." i where m.it_id=i.it_id and m.uid = '".$_GET['uid']."'");
	$input_hidden = "
		<input type='hidden' name='uid' value='".$_GET['uid']."'/>
		<input type='hidden' name='it_id' value='".$it['it_id']."'/>
		<input type='hidden' name='mode' value='update'/>
	";
}else{
	$input_hidden = "<input type='hidden' name='mode' value='insert'/>";
}
include $g4['full_path']."/adm/admin.head.php";

switch($_GET['category']){
	case '0': $category_txt = '전체 베스트'; break;
	case '1': $category_txt = '뷰티용품'; break;
	case '2': $category_txt = '식품'; break;
	case '3': $category_txt = '건강식품'; break;
	case '4': $category_txt = '생활'; break;
	case '5': $category_txt = '출산/육아'; break;
}

?>

<form action="<?php echo $_SERVER['PHP_SELF'];?>" method='post' onsubmit='return frm_sbm_fnc();'>
	<?php echo $input_hidden;?>
	<input type="hidden" name="category" value="<?php echo $_GET['category']; ?>"/>
	<table>
		<tr>
			<td>선택 카테고리</td>
			<td><?php echo $category_txt; ?></td>
		</tr>
		<tr>
			<td>상품코드</td>
			<td><input type="text" name='it_id' onchange='it_id_search(this.value);' value='<?php echo $it['it_id'];?>' <?php echo $it ? "readonly":"";?>/></td>
		</tr>
		<tr>
			<td>상품명</td>
			<td class='it_name'><?php if($it) {echo get_it_image($it['it_id'].'_s',100,100,$it['it_id']); echo $it['it_name']; }?></td>
		</tr>
		<tr>
			<td>정렬순서</td>
			<td>
				<input type="text" name='sort' value='<?php echo $it['sort'];?>'/>
			</td>
		</tr>		
	</table>
	<input type="submit" value='저장' />
	<?php if($it){?>
	<input type="button" value='삭제' onclick='ov_item_del(this.form);'/>
	<?php }?>
	<input type="button" value='목록' onclick="location.href='manwon_event_manual_list.php?category=<?php echo $_GET['category']; ?>'" />
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

	if($('input[name=sort]').val() == ''){
		alert('정렬순서를 입력해 주세요.');
		$('input[name=sort]').focus();
		return false;
	}

	return true;
	
}

</script>
<?php
include $g4['full_path']."/adm/admin.tail.php";
?>