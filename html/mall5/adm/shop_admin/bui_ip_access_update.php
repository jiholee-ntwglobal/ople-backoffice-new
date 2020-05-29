<?php
$sub_menu = "100100";
include_once("./_common.php");

$qstr1 = "sel_field=$sel_field&search=$search";
$qstr  = "$qstr1&page=$page";
$w = (isset($_POST['w']) ? $_POST['w'] : $_GET['w']);

if($w == 'cf_sw') // ip제어 사용/미사용 액션
{
	auth_check($auth[$sub_menu], "w");
	sql_query("update {$g4['config_table']} set cf_bui_ip_access_sw='{$_POST['cf_bui_ip_access_sw']}' ");
	goto_url("bui_ip_access.php");
}
else if($w == 'd')
{
	auth_check($auth[$sub_menu], "d");
	sql_query("delete from {$g4['yc4_bui_ip_table']} where bi_pid='{$_GET['bi_pid']}' ");
	goto_url("bui_ip_access.php?$qstr");
}
else if($w == 'w')
{
	auth_check($auth[$sub_menu], "w");
	$temp_arr = explode("\n", trim($_POST['bi_access_ip_arr']));
	for($k=0; $k<count($temp_arr); $k++){
		sql_query("insert into {$g4['yc4_bui_ip_table']}
			set bi_access_ip	= trim('{$temp_arr[$k]}'),
				bi_datetime		= now(),
				bi_ip			= '{$_SERVER['REMOTE_ADDR']}' ");
	}
	goto_url("bui_ip_access.php?$qstr");
}
else
	goto_url("bui_ip_access.php");
?>