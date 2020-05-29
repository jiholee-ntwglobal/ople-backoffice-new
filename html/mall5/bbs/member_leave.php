<?
include_once("./_common.php");

if (!$member[mb_id])
    alert("회원만 접근하실 수 있습니다.");

if ($is_admin == "super")
    alert("최고 관리자는 탈퇴할 수 없습니다");

if (!($_POST[mb_password] && $member[mb_password] == sql_password($_POST[mb_password])))
    alert("패스워드가 틀립니다.");

// 회원탈퇴일을 저장
$date = date("Ymd");
$sql = " update $g4[member_table] set mb_leave_date = '$date' where mb_id = '$member[mb_id]' ";
sql_query($sql);

// 김선용 201301 : php v5.3+ 부터 session_unregister 등의 세션관련 함수가 배제됨. 세션 전역변수를 처리할때 반드시 unset 권장(php 메뉴얼참조)
// 3.09 수정 (로그아웃)
//session_unregister("ss_mb_id");
unset($_SESSION['ss_mb_id']);

if (!$url)
    $url = $g4[path];

alert("그동안 이용해 주셔서 감사드립니다.\\n\\n{$member[mb_nick]}님께서는 " . date("Y년 m월 d일") . "에 회원에서 탈퇴 하셨습니다.", $url);
?>