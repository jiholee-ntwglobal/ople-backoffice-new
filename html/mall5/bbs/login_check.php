<?php
include_once("./_common.php");

$mb_id       = $_POST[mb_id];
$mb_password = $_POST[mb_password];

if (!trim($mb_id) || !trim($mb_password))
    alert("회원아이디나 패스워드가 공백이면 안됩니다.");

$mb = get_member($mb_id);

// 가입된 회원이 아니다. 패스워드가 틀리다. 라는 메세지를 따로 보여주지 않는 이유는
// 회원아이디를 입력해 보고 맞으면 또 패스워드를 입력해보는 경우를 방지하기 위해서입니다.
// 불법사용자의 경우 회원아이디가 틀린지, 패스워드가 틀린지를 알기까지는 많은 시간이 소요되기 때문입니다.
if (!$mb[mb_id] || (sql_password($mb_password) != $mb[mb_password]))
    alert("가입된 회원이 아니거나 패스워드가 틀립니다.\\n\\n패스워드는 대소문자를 구분합니다.");

// 차단된 아이디인가?
if ($mb[mb_intercept_date] && $mb[mb_intercept_date] <= date("Ymd", $g4[server_time])) {
    $date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년 \\2월 \\3일", $mb[mb_intercept_date]);
    alert("회원님의 아이디는 접근이 금지되어 있습니다.\\n\\n처리일 : $date");
}

// 탈퇴한 아이디인가?
if ($mb[mb_leave_date] && $mb[mb_leave_date] <= date("Ymd", $g4[server_time])) {
    $date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년 \\2월 \\3일", $mb[mb_leave_date]);
    alert("탈퇴한 아이디이므로 접근하실 수 없습니다.\\n\\n탈퇴일 : $date");
}

if ($config[cf_use_email_certify] && !preg_match("/[1-9]/", $mb[mb_email_certify]))
    alert("메일인증을 받으셔야 로그인 하실 수 있습니다.\\n\\n회원님의 메일주소는 $mb[mb_email] 입니다.");

$member_skin_path = "$g4[path]/skin/member/$config[cf_member_skin]";
@include_once("$member_skin_path/login_check.skin.php");

$is_auth = false;

if(is_admin($mb_id) == ''){
	
	$sql = " select count(*) as cnt from ".$g4['auth_table']." where mb_id = '".$mb_id."' ";
	$row = sql_fetch($sql);
	if ($row['cnt'] > 0){
		$is_auth = true;
	}	
} else {
	$is_auth = true;
}

if (!$is_auth)
    alert("가입된 회원이 아니거나 패스워드가 틀립니다.\\n\\n패스워드는 대소문자를 구분합니다.!");

// 회원아이디 세션 생성
set_session('ss_mb_id', $mb[mb_id]);

// 김선용 201111 :
// FLASH XSS 공격에 대응하기 위하여 회원의 고유키를 생성해 놓는다. 관리자에서 검사함 - 110106
set_session('ss_mb_key', md5($mb[mb_datetime] . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']));


###############// 김선용 2014.03 : 속도향상을 위한 리-인덱싱 처리완료. 속도 향상확인
// 김선용 201206 :
$tmp_on_uid = get_session('ss_on_uid');
if(get_cart_count($tmp_on_uid))
{
	$sql = "select a.ct_id, b.it_point from {$g4['yc4_cart_table']} a
		left join {$g4['yc4_item_table']} b on a.it_id=b.it_id
		where a.ct_status='쇼핑' and a.on_uid = '$tmp_on_uid' ";
	$result = sql_query($sql);
	for($k=0; $row=sql_fetch_array($result); $k++){
		if(!$config['cf_use_point']) $row['it_point'] = 0;
		sql_query("update {$g4['yc4_cart_table']} set ct_point='{$row['it_point']}', ct_mb_id='{$mb['mb_id']}' where ct_id='{$row['ct_id']}' ");
	}
}
// 김선용 201210 : 복수배송지 설정사항 초기화(설정하다가 주문완료를 안한경우)
// 김선용 201206 : 회원인경우 on_uid 재처리(담은상품 보관기간 유효처리)
sql_query("update {$g4['yc4_cart_table']} set on_uid='$tmp_on_uid' where ct_mb_id='{$mb['mb_id']}' and ct_status='쇼핑' ", false);
###############// 김선용 2014.03 : 속도향상을 위한 리-인덱싱 처리완료. 속도 향상확인


// 3.26
// 아이디 쿠키에 한달간 저장
if ($auto_login) {
    // 3.27
    // 자동로그인 ---------------------------
    // 쿠키 한달간 저장
    $key = md5($_SERVER[SERVER_ADDR] . $_SERVER[REMOTE_ADDR] . $_SERVER[HTTP_USER_AGENT] . $mb[mb_password]);
    set_cookie('ck_mb_id', $mb[mb_id], 86400 * 31);
    set_cookie('ck_auto', $key, 86400 * 31);
    // 자동로그인 end ---------------------------
} else {
    set_cookie('ck_mb_id', '', 0);
    set_cookie('ck_auto', '', 0);
}


if ($url)
{
    $link = urldecode($url);
    // 2003-06-14 추가 (다른 변수들을 넘겨주기 위함)
    if (preg_match("/\?/", $link))
        $split= "&";
    else
        $split= "?";

    // $_POST 배열변수에서 아래의 이름을 가지지 않은 것만 넘김
    foreach($_POST as $key=>$value)
    {
        if ($key != "mb_id" && $key != "mb_password" && $key != "x" && $key != "y" && $key != "url")
        {
            $link .= "$split$key=$value";
            $split = "&";
        }
    }
}
else
    $link = $g4[path];


$link = '/mall5/adm/';

goto_url($link);
?>
