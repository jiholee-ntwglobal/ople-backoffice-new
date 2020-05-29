<?

include_once("./_common.php");

if(!$member['mb_id']){
	alert('회원만 이용하실 수 있습니다.');
	exit;
}

// 090710
if (substr_count($wr_content, "&#") > 50) {
    alert("내용에 올바르지 않은 코드가 다수 포함되어 있습니다.");
    exit;
}

$upload_max_filesize = ini_get('upload_max_filesize');

if (empty($_POST))
    alert("파일 또는 글내용의 크기가 서버에서 설정한 값을 넘어 오류가 발생하였습니다.\\n\\npost_max_size=".ini_get('post_max_size')." , upload_max_filesize=$upload_max_filesize\\n\\n게시판관리자 또는 서버관리자에게 문의 바랍니다.");

$w = $_POST["w"];


if ($w == "u" || $w == "d") {
	$uid = mysql_escape_string($_POST['uid']);
	if(!$wr = sql_fetch("select uid,mb_id from yc4_personal_qa where uid = '".$uid."'")){
		alert("글이 존재하지 않습니다.\\n\\n글이 삭제되었거나 이동하였을 수 있습니다.");
		exit;
	}
}


// 김선용 1.00 : 글쓰기 권한과 수정은 별도로 처리되어야 함
if($w =="u" && $member['mb_id'] && $wr['mb_id'] != $member['mb_id']){
	alert('본인이 작성한 글만 수정할 수 있습니다.');
	exit;
}elseif($w =="d" && $member['mb_id'] && $wr['mb_id'] != $member['mb_id']){
	alert('본인이 작성한 글만 삭제할 수 있습니다.');
	exit;
}

if ($w == "")
{
    if(!$is_admin){
		if(get_session('ss_datetime') >= ($g4['server_time'] - $config['cf_delay_sec']))
	        alert("너무 빠른 시간내에 게시물을 연속해서 올릴 수 없습니다.");
	}
    set_session('ss_datetime', $g4['server_time']);

    // 동일내용 연속 등록 불가
    $row = sql_fetch(" select MD5(CONCAT(subject, contents)) as prev_md5 from yc4_personal_qa order by uid desc limit 1 ");
    $curr_md5 = md5($_POST['wr_subject'].$_POST['wr_content']);
    if ($row['prev_md5'] == $curr_md5 && !$is_admin)
        alert("동일한 내용을 연속해서 등록할 수 없습니다.");
}

if($w != 'd'){
	if (!isset($_POST['wr_subject']) || !trim($_POST['wr_subject'])){
		alert("제목을 입력하여 주십시오.");
		exit;
	}
}

if ($w == ""){
	$mb_id = $member['mb_id'];
	$sql = "
		insert into
			yc4_personal_qa
		(
			parent_uid,depth,subject,contents,create_dt,mb_id
		)values(
			0,0,'".sql_safe_query($_POST['wr_subject'])."','".sql_safe_query($_POST['wr_content'])."',now(),'".$mb_id."'
		)
	";
	if(!sql_query($sql)){
		alert('저장중 오류 발생! 다시 시도해 주세요.');
		exit;
	}
	$w_uid = mysql_insert_id();
	alert('1:1문의 글 등록이 완료되었습니다.',$g4['bbs_path'].'/board_personnel_qa.php?mode=view&uid='.$w_uid);
	exit;

}elseif($w == 'u'){
	
	$sql = "
		update 
			yc4_personal_qa
		set
			subject = '".sql_safe_query($_POST['wr_subject'])."',
			contents = '".sql_safe_query($_POST['wr_content'])."'
		where
			uid = '".$uid."'
	";
	if(!sql_query($sql)){
		alert('저장중 오류 발생! 다시 시도해 주세요.');
		exit;
	}
	alert('1:1문의 수정이 완료되었습니다.',$g4['bbs_path'].'/board_personnel_qa.php?mode=view&uid='.$uid);
	exit;

}elseif($w == 'd'){
	
	$sql = "
		delete from yc4_personal_qa where uid = '".$uid."' or parent_uid = '".$uid."'
	";
	if(!sql_query($sql)){
		alert('삭제중 오류 발생! 다시 시도해 주세요.');
		exit;
	}

	alert('1:1문의 글 삭제가 완료되었습니다.',$g4['bbs_path'].'/board_personnel_qa.php');
	exit;

}
exit;
?>