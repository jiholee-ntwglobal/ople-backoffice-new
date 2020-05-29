<?php
include_once "_common.php";

// 김선용 200905 : 회원
if(!$is_member) alert_close("회원만 신청이 가능합니다. 로그인후에 이용해 주십시오.");


$chk = sql_fetch("select ts_id from {$g4['item_sms_table']} where it_id='{$_POST['it_id']}' and ts_hp='{$_POST['ts_hp']}' and ts_send=0 ");
if($chk['ts_id']) alert_close("이미 같은 상품, 같은 휴대전화로 알림신청이 되어 있습니다.");

$sql = "insert into {$g4['item_sms_table']}
	set it_id	= '{$_POST['it_id']}',
		mb_id	= '{$_POST['mb_id']}',
		ts_name	= '{$_POST['ts_name']}',
		ts_hp	= '{$_POST['ts_hp']}',
		ts_time	= '{$g4['time_ymdhis']}' ";
sql_query($sql);

alert_close("정상적으로 접수되었습니다.\\n\\n입고시 SMS로 통보해 드리겠습니다.");
?>