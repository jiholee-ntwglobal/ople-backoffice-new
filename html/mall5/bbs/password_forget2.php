<?php
include_once("./_common.php");

//header("Content-Type: text/html; charset=$g4[charset]");
//echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=$g4[charset]\">";

if ($_POST['pass_mb_id'])
    $sql = " select mb_id, mb_password_q,mb_leave_date from $g4[member_table] where mb_id = '$_POST[pass_mb_id]' ";
else if ($_POST['mb_name'] && $_POST['mb_jumin'])
    $sql = " select mb_id, mb_password_q,mb_leave_date from $g4[member_table] where mb_name = '$_POST[mb_name]' and mb_jumin = '".sql_password($_POST[mb_jumin])."' ";
else if ($_POST['mb_name'] && $_POST['mb_email'])
    $sql = " select mb_id, mb_password_q,mb_leave_date from $g4[member_table] where mb_name = '$_POST[mb_name]' and mb_email = '$_POST[mb_email]' ";
else
    alert("올바른 방법으로 접근하여 주십시오.");

$mb = sql_fetch($sql);
if (!$mb['mb_id'])
    alert("입력하신 내용으로는 회원정보가 존재하지 않습니다. #1");
elseif(is_admin($mb['mb_id'])){
    alert("관리자 아이디는 접근 불가합니다.");
}elseif($mb['mb_leave_date']){
	alert('탈퇴한 회원입니다.');
}

$g4[title] = "패스워드 찾기 2단계";
include_once($g4['full_path']."/head.sub.php");

$member_skin_path = $g4['path']."/skin/member/".$config['cf_member_skin'];
include_once($member_skin_path."/password_forget2.skin.php");

include_once($g4['full_path']."/tail.sub.php");
?>