<?php
$sub_menu = "400670";
include_once "./_common.php";
auth_check($auth[$sub_menu], "r");
$g4[title] = "1:1 문의관리";


if($_POST['mode'] == 'insert'){
	foreach($_POST as $key => $val){
		$val = mysql_escape_string($val);
	}
	$insert_qry = "
		insert into
			yc4_personal_qa
		(
			parent_uid, depth, subject, contents, create_dt, mb_id
		)values(
			".$_POST['uid'].",'1','".$_POST['subject']."','".$_POST['contents']."',now(),'".$member['mb_id']."'
		)
	";
	if(!sql_query($insert_qry)){
		alert('저장중 오류가 발생했습니다. 다시 시도해 주세요.');
		exit;
	}else{
		alert('저장이 완료되었습니다.',$_SERVER['PHP_SELF']);
	}
	exit;
}

if($_POST['mode'] == 'update'){
	foreach($_POST as $key => $val){
		$val = mysql_escape_string($val);
	}

	$update_qry = "
		update
			yc4_personal_qa
		set
			subject = '".$_POST['subject']."',
			contents = '".$_POST['contents']."'
		where
			uid = '".$_POST['reply_uid']."'
	";
	if(!sql_query($update_qry)){
		alert('수정중 오류가 발생했습니다. 다시 시도해 주세요.');
		exit;
	}else{
		alert('수정이 완료되었습니다.',$_SERVER['PHP_SELF']);
	}
	exit;
}

if($_POST['mode'] == 'delete'){
	$del_qry = "
		delete from yc4_personal_qa where uid = '".$_POST['reply_uid']."'
	";
	if(!sql_query($del_qry)){
		alert('삭제중 오류가 발생했습니다. 다시 시도해 주세요.');
		exit;
	}else{
		alert('삭제가 완료되었습니다.',$_SERVER['PHP_SELF']);
	}
}


include_once $g4['admin_path'].'/admin.head.php';

# 일대일 문의 로드 #
if($_GET['flag']){
	$common = "
		left outer join
			yc4_personal_qa c on a.uid = c.parent_uid
	";
}

if($_GET['flag'] == 'y'){
	$where .= " and c.uid is not null";
}elseif($_GET['flag'] == 'n'){
	$where .= " and c.uid is null";
}

$cnt = sql_fetch("
	select
		count(*) as cnt
	from
		yc4_personal_qa a
		left join
		".$g4['member_table']." b on a.mb_id = b.mb_id
		".$common."
	where
		a.depth = 0
		".$where."
	order by a.create_dt desc
");
$total_count = $cnt['cnt'];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$sql = sql_query("
	select
		a.uid,a.subject,a.mb_id,a.create_dt,a.contents,
		b.mb_name
	from
		yc4_personal_qa a
		left join
		".$g4['member_table']." b on a.mb_id = b.mb_id
		".$common."
	where
		a.depth = 0
		".$where."
	order by a.create_dt desc
	limit $from_record, $rows
");

$qstr = "flag=".$_GET['flag'];

$no = $total_count;
while($row = sql_fetch_array($sql)){

	# 답변글이 있는지 체크 #
	$reply_chk = sql_fetch("select count(*) as cnt from yc4_personal_qa where parent_uid = '".$row['uid']."'");


	if($reply_chk['cnt']>0){ // 답변글이 존재할 경우 답변글을 보여주고 수정 및 삭제 가능하도록
		$reply_contents = sql_fetch("select * from yc4_personal_qa where parent_uid = '".$row['uid']."'");
		$reply_contents_mode = "
			<input type='hidden' name='mode' value='update'/>
			<input type='hidden' name='reply_uid' value='".$reply_contents['uid']."'/>
		";
		$reply_contents_del_btn = "<input type='button' value='삭제' onclick=\"this.form.mode.value='delete';this.form.submit();\"/>";
		$replay_flg = true;
	}else{
		$reply_contents_mode = "<input type='hidden' name='mode' value='insert'/>";
		$replay_flg = false;
	}

	$reply_contents['subject'] = $reply_contents['subject'] ? $reply_contents['subject'] : "답변) ".$row['subject']."";

	$reply_contents = "
		".$reply_contents_mode."
		<table width='100%'>
			<tr>
				<td>제목</td>
				<td><input type='text' name='subject' style='width:100%;' value='".$reply_contents['subject']."'/></td>
			</tr>
			<tr>
				<td>내용</td>
				<td><textarea name='contents' style='width:100%; height:200px;'>".strip_tags($reply_contents['contents'])."</textarea></td>
			</tr>
		</table>
		<input type='submit' value='저장'/>
		".$reply_contents_del_btn."
		<input type='button' value='닫기' onclick=\"$(this).parent().parent().parent().parent().hide(); return false;\"/>
	";



	$data .= "
		<tr>
			<td align='center'>".$no."</td>
			<td>".$row['subject']."</td>
			<td>".$row['mb_name']."</td>
			<td align='center'>".$row['create_dt']."</td>
			<td align='center'>".($replay_flg ? "답변완료":"미답변")."</td>
			<td><span onclick=\"$(this).parent().parent().next().toggle(); return false;\" style='cursor:pointer;'>".icon('수정')."</span></td>
		</tr>
		<tr class='qa_contents' uid='".$row['uid']."'>
			<td></td>
			<td colspan='5'>
				<div class='qa_contents_div'>
					".conv_content($row['contents'],1)."
				</div>
				<div>
					<form method='post' action='".$_SERVER['PHP_SELF']."'>
						<input type='hidden' name='uid' value='".$row['uid']."'/>
						".$reply_contents."
					</form>
				</div>
			</td>
		</tr>
		";
	unset($reply_contents_del_btn,$reply_contents,$row,$reply_chk);
	$no--;
}
?>

<style type="text/css">
	.qa_contents_div{
		border:1px solid #dddddd;
		padding:10px;
		margin-bottom:15px;
	}
	.qa_contents{
		display:none;
	}
</style>

<table width=100% cellpadding=0 cellspacing=0>
	<tr>
        <td><?=subtitle("1:1문의 리스트")?></td>
    </tr>
</table>


<table width='100%'>
	<col width='50'/>
	<col />
	<col width='100'/>
	<col width='100'/>
	<col width='100'/>
	<col width='30'/>
	<thead>
		<tr>
			<td colspan='6'>
				<a href="<?=$_SERVER['PHP_SELF']?>" class='tab_btn' style='<?=!$_GET['flag'] ? "font-weight:bold;":""?>'>전체</a>
				<a href="<?=$_SERVER['PHP_SELF']?>?flag=y" class='tab_btn' style='<?=$_GET['flag'] == 'y' ? "font-weight:bold;":""?>'>답변</a>
				<a href="<?=$_SERVER['PHP_SELF']?>?flag=n" class='tab_btn' style='<?=$_GET['flag'] == 'n' ? "font-weight:bold;":""?>'>미답변</a>
			</td>
		</tr>
		<tr>
			<th>번호</th>
			<th>제목</th>
			<th>글쓴이</th>
			<th>등록일</th>
			<th>답변여부</th>
			<th></th>
		</tr>
	</thead>
	<?=$data;?>
</table>
<?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?>
<?
include_once $g4['admin_path'].'/admin.tail.php';
?>