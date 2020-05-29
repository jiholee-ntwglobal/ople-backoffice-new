<?php 
/*
----------------------------------------------------------------------
file name	 : iframe_ihappy_board.php
comment		 : 아이해피 고객센터 iframe
date		 : 2015-05-15
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/
$sub_menu = "700200";
include_once("./_common.php");

mail('rsmaker@ntwglobal.com','hello world','hi');

auth_check($auth[$sub_menu], "r");

$g4[title] = "아이해피 고객센터";
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($g4[title])?>

<iframe src="/mall5/bbs/board.php?bo_table=iqa" width="1120" height="1700" frameborder="0"></iframe>
<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
