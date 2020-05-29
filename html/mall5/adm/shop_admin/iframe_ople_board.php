<?php 
/*
----------------------------------------------------------------------
file name	 : iframe_ople_board.php
comment		 : 오플 고객센터 iframe
date		 : 2015-05-15
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/
$sub_menu = "700100";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "오플 고객센터";
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($g4[title])?>

<iframe src="/mall5/bbs/board2.php?bo_table=qa" width="1120" height="1600" frameborder="0"></iframe>
<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
