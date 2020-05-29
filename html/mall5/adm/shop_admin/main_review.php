<?php
/*
----------------------------------------------------------------------
file name	 : main_review.php
comment		 : 메인 노출 후기 관리
date		 : 2015-01-15
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/
$sub_menu = "600200";
include "_common.php";
auth_check($auth[$sub_menu], "r");

if($_POST['mode'] == 'delete'){

	$sql = "
		delete from yc4_item_ps_main where is_id = '".mysql_real_escape_string($_POST['is_id'])."'
	";

	if(!sql_query($sql)){
		alert('처리중 오류 발생! 관리자에게 문의하세요.');
		exit;
	}

	alert('삭제가 완료되었습니다.',$_SERVER['PHP_SELF'].'?'.$_POST['qstr']);
	exit;
}

include_once $g4['admin_path']."/admin.head.php";

$colspan = 5;

# 메인 후기 리스트 로드 #
$sql = sql_query("
	select
		a.img_link,
		a.is_id,
		b.it_id,
		b.is_subject,
		b.is_content,
		b.is_time,
		c.it_name,
		c.it_maker
	from
		yc4_item_ps_main a,
		".$g4['yc4_item_ps_table']." b,
		".$g4['yc4_item_table']." c
	where
		a.is_id = b.is_id
		and
		b.it_id = c.it_id
	order by a.create_dt desc
");
$list_tr = '';
while($row = sql_fetch_array($sql)){
	if($row['img_link']){
		$img_link = "<img class ='asdf' src='".$row['img_link']."' onclick=\"location.href='".$g4['shop_admin_path']."/main_review_img_link.php?is_id=".$row['is_id']."'\" style='cursor:pointer;'/>";
	}else{
		$img_link = icon('수정',$g4['shop_admin_path'].'/main_review_img_link.php?is_id='.$row['is_id']);
	}

	$list_tr .= "
		<tr>

			<td>".$row['it_id']."</td>
			<td>".$row['it_name']."</td>
			<td>".$row['is_subject']."</td>
			<td>".$row['is_time']."</td>
			<td>".icon('삭제','#',"","onclick=\"main_item_ps_del('".$row['is_id']."'); return false;\"")."</td>
		</tr>
		<tr>
			<td align='center'>".$img_link."</td>
			<td colspan='".($colspan-1)."' style='padding:5px;'>".nl2br($row['is_content'])."<br/><br/><hr/></td>
		</tr>
	";
}
if(!$list_tr){
	$list_tr = "
		<tr>
			<td colspan='".$colspan."' align='center'>데이터가 존재하지 않습니다.</td>
		</tr>
	";
}

$qstr = $_GET;
$qstr = http_build_query($qstr);

?>
	<style>
		.asdf {
			width: 270px; height: 243.984px;
			object-fit: cover;
			object-position: right;
		}
	</style>

<table width='100%' border="1">
	<tr align='center'>
		<td width='100'>상품코드<br/>보정이미지</td>
		<td>상품명</td>
		<td>제목</td>
		<td width='100'>작성시간</td>
		<td><?php echo icon('입력',$g4['shop_admin_path'].'/main_review_write.php');?></td>
	</tr>
	<?php echo $list_tr;?>
</table>

<form action="<?=$_SERVER['PHP_SELF'];?>" method='post' name='del_frm'>
	<input type="hidden" name='mode' value='delete' />
	<input type="hidden" name='is_id' value='' />
	<input type="hidden" name='qstr' value='<?php echo $qstr;?>' />
</form>

<script type="text/javascript">
function main_item_ps_del(is_id){
	if(!confirm('해당 후기를 메인진열에서 삭제하시겠습니까?')){
		return false;
	}

	del_frm.is_id.value = is_id;
	del_frm.submit();
	return true;
}
</script>
<?php
include_once $g4['admin_path']."/admin.tail.php";
?>