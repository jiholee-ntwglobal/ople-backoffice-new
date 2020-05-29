<?php
/*
----------------------------------------------------------------------
file name	 : main_review_write.php
comment		 : 메인 노출 후기 등록
date		 : 2015-01-15
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/
$sub_menu = "600200";
include "_common.php";
auth_check($auth[$sub_menu], "w");


# 후기 메인 진열 처리 #
if($_POST['mode'] == 'insert'){

	$sql = "
		insert into
			yc4_item_ps_main
		(
			is_id,create_dt,mb_id
		)
		values (
			'".mysql_real_escape_string($_POST['is_id'])."',
			'".date('Y-m-d H:i:s')."',
			'".$member['mb_id']."'
		)
	";
	if(!sql_query($sql)){
		alert('처리중 오류 발생! 관리자에게 문의하세요.');
		exit;
	}
	echo "
		<script>
			if(confirm('메인 진열이 완료되었습니다. 계속 후기를 선정하시겠습니까?')){
				location.href='".$_SERVER['PHP_SELF']."?".$_POST['qstr']."';
			}else{
				location.href='".$g4['shop_admin_path']."/main_review.php';
			}
		</script>
	";
	exit;
}


include_once $g4['admin_path']."/admin.head.php";



$colspan = 2;


# 메인 후기 리스트 로드 #
$already_review_sql = sql_query("select is_id from yc4_item_ps_main");
$already_review = '';
while($row = sql_fetch_array($already_review_sql)){
	$already_review .= ($already_review ? ",":"") . "'".$row['is_id']."'";
}

if($already_review){
	$search_sql .= " and a.is_id not in (".$already_review.")";
}

if($_GET['it_id']){
	$_GET['it_id'] = trim($_GET['it_id']);
	$search_sql .= " and b.it_id = '".mysql_real_escape_string($_GET['it_id'])."'";
}

if($_GET['it_name']){
	$_GET['it_name'] = trim($_GET['it_name']);
	$search_sql .= " and b.it_name like '%".mysql_real_escape_string($_GET['it_name'])."%'";
}
if($_GET['it_maker']){
	$_GET['it_maker'] = trim($_GET['it_maker']);
	$search_sql .= " and b.it_maker like '%".mysql_real_escape_string($_GET['it_maker'])."%'";
}


$sql = "
	select
		count(*) as cnt
	from
		".$g4['yc4_item_ps_table']." a,
		".$g4['yc4_item_table']." b
	where
		a.it_id = b.it_id
		and
		a.is_best = 1
		and
		b.it_use = 1
		".$search_sql."
";

$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


# 베스트 후기 리스트 로드 #
$sql = sql_query("
	select
		a.*,
		b.it_name,
		if(a.is_image4 = '',1,0) as img4,
		if(a.is_image3 = '',1,0) as img3,
		if(a.is_image2 = '',1,0) as img2,
		if(a.is_image1 = '',1,0) as img1,
		if(a.is_image0 = '',1,0) as img0,
		date_format(a.is_time, '%Y') yyyy,
        date_format(a.is_time, '%m') mm,
        date_format(a.is_time, '%d') dd
	from
		".$g4['yc4_item_ps_table']." a,
		".$g4['yc4_item_table']." b
	where
		a.it_id = b.it_id
		and
		a.is_best = 1
		and
		b.it_use = 1
		".$search_sql."
	order by /*img4,img3,img2,img1,img0,*/a.is_time desc
	limit $from_record, $rows
");

while($row = sql_fetch_array($sql)){
	$list_tr .= "
		<tr>
			<td align='center'>".$row['it_id']."</td>
			<td>".$row['it_name']."</td>

		</tr>
		<tr>
			<td align='center'>".substr($row['is_time'],0,10)."</td>
			<td>".$row['is_subject']."</td>
		</tr>

		<tr>
			<td align='center'>".get_it_image($row['it_id'].'_s',100,100,$row['it_id'])."</td>
			<td style='padding:5px;'>
				".nl2br($row['is_content'])."
				".($row['is_image0'] ? '<img class ="resizeing" src="https://uvaxnqcpaepy770580.gcdn.ntruss.com/mall5/data/itemps_img/'.$row['yyyy'].'/'.$row['mm'].'/'.$row['dd'].'/'.$row['is_image0'].'" />':"")."
				".($row['is_image1'] ? '<img class ="resizeing" src="https://uvaxnqcpaepy770580.gcdn.ntruss.com/mall5/data/itemps_img/'.$row['yyyy'].'/'.$row['mm'].'/'.$row['dd'].'/'.$row['is_image1'].'" />':"")."
				".($row['is_image2'] ? '<img class ="resizeing" src="https://uvaxnqcpaepy770580.gcdn.ntruss.com/mall5/data/itemps_img/'.$row['yyyy'].'/'.$row['mm'].'/'.$row['dd'].'/'.$row['is_image2'].'" />':"")."
				".($row['is_image3'] ? '<img class ="resizeing" src="https://uvaxnqcpaepy770580.gcdn.ntruss.com/mall5/data/itemps_img/'.$row['yyyy'].'/'.$row['mm'].'/'.$row['dd'].'/'.$row['is_image3'].'" />':"")."
				".($row['is_image4'] ? '<img class ="resizeing" src="https://uvaxnqcpaepy770580.gcdn.ntruss.com/mall5/data/itemps_img/'.$row['yyyy'].'/'.$row['mm'].'/'.$row['dd'].'/'.$row['is_image4'].'" />':"")."
			</td>
		</tr>
		<tr>
			<td colspan='".$colspan."' align='center' style='padding:10px;'><b><a href='#' onclick=\"insert_main_review('".$row['is_id']."'); return false;\">후기 메인 등록(↑)</a></b><br/><br/><br/><hr/></td>
		</tr>
	";
}
// '<img class ="resizeing" src="http://ople.com/mall5/shop/itemuseimg.php?s=0&id='.$row['is_id'].'" />'
$qstr = $qstr2 = $_GET;
unset($qstr['page']);
$qstr = http_build_query($qstr);
$qstr2 = http_build_query($qstr2);

?>
	<style>
		.resizeing{
			width: 350px;
			float: left;
		}
	</style>
<form action="<?php echo $_SERVER['PHP_SELF']?>" method='get'>
	상품코드 <input type="text" name='it_id' value='<?php echo $_GET['it_id'];?>' />
	브랜드명 <input type="text" name='it_maker' value="<?php echo $_GET['it_maker'];?>" />
	상품명 <input type="text" name='it_name' value="<?php echo $_GET['it_name'];?>" />
	<input type="submit" value='검색' />
</form>
<p align='right'>
	<a href="<?php echo $g4['shop_admin_path'];?>/main_review.php">목록</a>
</p>

<table width='100%' >
	<tr align='center'>
		<td>상품코드</td>
		<td>상품명</td>
	</tr>
	<tr align='center'>
		<td>작성시간</td>
		<td>제목</td>
	</tr>
	<?php echo $list_tr;?>
</table>

<p align='center'>
<?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?>
</p>
<p>
	<a href="<?php echo $g4['shop_admin_path'];?>/main_review.php">목록</a>
</p>

<form action="<?php echo $_SERVER['PHP_SELF']?>" method='post' name='insert_frm'>
	<input type="hidden" name='mode' value='insert' />
	<input type="hidden" name='is_id' value='' />
	<input type="hidden" name='qstr' value='<?php echo $qstr2;?>' />
</form>

<script type="text/javascript">
function insert_main_review(is_id){
	if(!confirm('해당 후기를 메인에 진열하시겠습니까?\n 메인 진열 리스트에서 이미지 등록시 후기로 진열됩니다.')){
		return false;
	}

	insert_frm.is_id.value = is_id;
	insert_frm.submit();
}
</script>
<?php
include_once $g4['admin_path']."/admin.tail.php";
?>