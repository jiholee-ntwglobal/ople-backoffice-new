<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가



// 김선용 201309 : 회원가입 프로모션 처리, 탈퇴, 차단회원 걸러내고
if($w == '' && $mp_event_id)
{
	$chk_mp = sql_fetch("select mb_id from {$g4['yc4_member_promo']} where mp_event_id='$mp_event_id' ");
	$chk_mb = sql_fetch("select mb_id from {$g4['member_table']} where mb_id='{$chk_mp['mb_id']}' and mb_leave_date='' and mb_intercept_date='' ");
	if($chk_mb['mb_id'])
	{
		sql_query("insert into {$g4['yc4_member_promor']}
			set mb_id			= '{$chk_mb['mb_id']}',
				mb_id2			= '$mb_id',
				ms_event_id		= '$mp_event_id',
				ms_datetime		= '{$g4['time_ymdhis']}',
				ms_ip			= '".getenv('REMOTE_ADDR')."' ");

		// glod lv(3) 업데이트
		sql_query("update {$g4['member_table']} set mb_level=3 where mb_id='$mb_id' ");

		// mp 테이블 가입자 카운팅
		sql_query("update {$g4['yc4_member_promo']} set mp_reg_count=mp_reg_count+1 where mp_event_id='$mp_event_id' ");
	}
}
//----------------------------------------------------------
// SMS 문자전송 시작
//----------------------------------------------------------

$sms_contents = $default[de_sms_cont1];
$sms_contents = preg_replace("/{이름}/", $mb_name, $sms_contents);
$sms_contents = preg_replace("/{회원아이디}/", $mb_id, $sms_contents);
$sms_contents = preg_replace("/{회사명}/", $default[de_admin_company_name], $sms_contents);

// 핸드폰번호에서 숫자만 취한다
$receive_number = preg_replace("/[^0-9]/", "", $mb_hp);  // 수신자번호 (회원님의 핸드폰번호)
$send_number = preg_replace("/[^0-9]/", "", $default[de_admin_company_tel]); // 발신자번호

if ($w == "" && $default[de_sms_use1] && $receive_number)
{
	if ($default[de_sms_use] == "xonda")
	{
		$usrdata1 = "회원가입";

		define("_SMS_", TRUE);
		include "$g4[shop_path]/sms.inc.php";
	}
	else if ($default[de_sms_use] == "icode")
	{
		include_once("$g4[path]/lib/icode.sms.lib.php");
		$SMS = new SMS;	// SMS 연결
		$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
		$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
		$SMS->Send();
	}
}
//----------------------------------------------------------
// SMS 문자전송 끝
//----------------------------------------------------------
?>
