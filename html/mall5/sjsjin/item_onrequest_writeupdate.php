<?php
include_once "_common.php";


//if(!$is_member) alert_close("회원만 신청이 가능합니다. 로그인후에 이용해 주십시오.");


$chk = sql_fetch("select on_pid, on_sms_post from {$g4['yc4_onrequest_table']} where mb_id='{$_POST['mb_id']}' and on_hp='{$_POST['mb_hp']}' and on_it_name='{$_POST['it_name']}' and on_it_info='{$_POST['it_info']}' ");
if($chk['on_pid']){
	if($chk['on_sms_post'])
		alert_close("이미 같은 상품, 같은 휴대전화로 입고 SMS 통보를 발송했습니다.");
	else
		alert_close("이미 같은 상품, 같은 휴대전화로 입고요청 접수가 되어 있습니다.");
}

$sql = "insert into {$g4['yc4_onrequest_table']}
	set mb_id		= '{$_POST['mb_id']}',
		on_name		= '{$_POST['mb_name']}',
		on_hp		= '{$_POST['mb_hp']}',
		on_it_name	= '{$_POST['it_name']}',
		on_it_info	= '{$_POST['it_info']}',
		on_sms_post	= 0,
		on_datetime	= '{$g4['time_ymdhis']}',
		on_ip		= '".getenv('REMOTE_ADDR')."'
		";
sql_query($sql);

alert("정상적으로 접수되었습니다.\\n\\n입고시 SMS로 통보해 드리겠습니다.");
?>