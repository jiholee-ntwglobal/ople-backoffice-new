<?php
include_once("./_common.php");

// 김선용 201006 : 전역변수 XSS/인젝션 보안강화 및 방어
include_once "{$g4['shop_path']}/sjsjin.shop_guard.php";

sql_query("insert into {$g4['yc4_rs_table']}
	set rs_name		= '{$_POST['rs_name']}',
		rs_hp		= '{$_POST['rs_hp']}',
		rs_email	= '{$_POST['rs_email']}',
		rs_datetime	= now(),
		rs_ip		= '{$_SERVER['REMOTE_ADDR']}',
		rs_agent	= '{$_SERVER['HTTP_USER_AGENT']}' ");

alert("예약신청이 정상적으로 접수되었습니다.");
?>