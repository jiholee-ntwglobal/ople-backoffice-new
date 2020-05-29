<?
include_once("./_common.php");

if($_POST['login_email']){
	$_SESSION['login_email'] = $_POST['login_email'];
	$_SESSION['wr_password'] = $_POST['wr_password'];
	goto_url($g4['bbs_path'].'/board2.php?bo_table=qa');
	exit;
}

$g4[title] = "메일주소 확인";
include_once("./_head.php");

// 이미 로그인 중이라면
if ($member[mb_id])
{
    if ($url)
        goto_url($url);
    else
        goto_url($g4[path]);
}

if ($url)
    $urlencode = urlencode($url);
else
    $urlencode = urlencode($_SERVER[REQUEST_URI]);

$member_skin_path = "$g4[path]/skin/member/$config[cf_member_skin]";

include_once("$member_skin_path/email_confirm.skin.php");

include_once("./_tail.php");
?>
