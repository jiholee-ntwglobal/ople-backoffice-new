<?php
$sub_menu = "400501";
include_once("./_common.php");
auth_check($auth[$sub_menu], "d");

if($rs_pid == '') alert('레코드 고유id가 없습니다.');
sql_query("delete from {$g4['yc4_rs_table']} where rs_pid='$rs_pid' ");

goto_url("rs_iodine_list.php?sel_field=$sel_field&search=$search&page=$page");
?>