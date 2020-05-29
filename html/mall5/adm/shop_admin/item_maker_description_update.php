<?php
$sub_menu = "300500";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");


$qstr1 = "sel_field=$sel_field&search=$search";
$qstr  = "$qstr1&page=$page";

//print_r2($_POST); EXIT;
for ($i=0; $i<count($_POST['chk']); $i++)
{
	$a = $_POST['chk'][$i];

	sql_query("update {$g4['yc4_item_table']} set it_maker_description='{$_POST['it_maker_description'][$a]}' where it_maker='".mysql_real_escape_string(urldecode($_POST['it_maker'][$a]))."' ");
}

goto_url("item_maker_description.php?$qstr");
?>