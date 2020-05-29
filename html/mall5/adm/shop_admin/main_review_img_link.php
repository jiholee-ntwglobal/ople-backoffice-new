<?php
/*
----------------------------------------------------------------------
file name	 : main_review_img_link.php
comment		 : 메인 노출 후기 이미지 등록
date		 : 2015-01-15
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/
$sub_menu = "600200";
include "_common.php";
auth_check($auth[$sub_menu], "w");


if($_POST['mode'] == 'update'){

	$sql = "
		update
			yc4_item_ps_main
		set
			img_link = '".mysql_real_escape_string(trim($_POST['img_link']))."'
		where
			is_id = '".mysql_real_escape_string(trim($_POST['is_id']))."'
	";
	if(!sql_query($sql)){
		alert('처리중 오류 발생! 관리자에게 문의해 주세요.');
		exit;
	}

	alert('저장이 완료되었습니다.',$g4['shop_admin_path'].'/main_review.php');
	exit;
}

# 후기 데이터 로드 #
$is = sql_fetch("
	select
		a.*,
		b.it_id,
		b.is_subject,
		b.is_content,
		b.mb_id,
		b.is_image0,
		b.is_image1,
		b.is_image2,
		b.is_image3,
		b.is_image4,
		c.it_maker,
		c.it_name

	from
		yc4_item_ps_main a,
		".$g4['yc4_item_ps_table']." b,
		".$g4['yc4_item_table']." c
	where
		a.is_id = b.is_id
		and
		b.it_id = c.it_id
		and
		a.is_id = '".mysql_real_escape_string($_GET['is_id'])."'
");

if($is['is_image0']){
	$is_img .= "<img class ='resizeing' src='".$g4['path']."/data/ituse/".$is['is_image0']."'/>";
}
if($is['is_image1']){
	$is_img .= "<img class ='resizeing' src='".$g4['path']."/data/ituse/".$is['is_image1']."'/>";
}
if($is['is_image2']){
	$is_img .= "<img class ='resizeing' src='".$g4['path']."/data/ituse/".$is['is_image2']."'/>";
}
if($is['is_image3']){
	$is_img .= "<img class ='resizeing' src='".$g4['path']."/data/ituse/".$is['is_image3']."'/>";
}
if($is['is_image4']){
	$is_img .= "<img class ='resizeing' src='".$g4['path']."/data/ituse/".$is['is_image4']."'/>";
}
include_once $g4['admin_path']."/admin.head.php";




?>
<style>
	.resizeing{
		width: 350px;
		float: left;
	}
</style>
<form action="<?php echo $_SERVER['PHP_SELF']?>" method='post'>
	<a href="<?php echo $g4['shop_admin_path'];?>/main_review.php">목록</a>
	<input type="hidden" name='mode' value='update' />
	<input type="hidden" name='is_id' value='<?php echo $_GET['is_id'];?>' />
	<table width='100%' border="1">

		<tr>
			<td>제품이미지</td>
			<td><?php echo get_it_image($is['it_id'].'_s',100,100,$it['it_id']);?></td>
		</tr>

		<tr>
			<td>상품코드</td>
			<td><?php echo $is['it_id']?></td>
		</tr>
		<tr>
			<td>제조사</td>
			<td><?php echo get_item_name($is['it_maker']);?></td>
		</tr>
		<tr>
			<td>제품명</td>
			<td><?php echo get_item_name($is['it_name']);?></td>
		</tr>
		<tr>
			<td>제목</td>
			<td><?php echo $is['is_subject'];?></td>
		</tr>
		<tr>
			<td>내용</td>
			<td><?php echo nl2br($is['is_content']);?></td>
		</tr>
		<tr>
			<td>회원 첨부 이미지</td>
			<td><?php echo $is_img;?></td>
		</tr>
		<tr>
			<td>보정이미지 링크</td>
			<td>
				<input type="text" name='img_link' value='<?php echo $is['img_link'];?>' />
				<?php
				if($is['img_link']){
					echo "<img src='".$is['img_link']."'/>";
				}
				?>
			</td>
		</tr>
	</table>
	<input type="submit" value='저장' />
	<a href="<?php echo $g4['shop_admin_path'];?>/main_review.php">목록</a>
</form>
<?php
include_once $g4['admin_path']."/admin.tail.php";
?>