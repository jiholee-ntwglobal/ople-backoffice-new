<?php 
/*
----------------------------------------------------------------------
file name	 : iframe_rolling_banner_config.php
comment		 : rolling_banner_config iframe
date		 : 2015-05-15
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/
$sub_menu = "600700";
include_once("./_common.php");

mail('rsmaker@ntwglobal.com','hello world','hi');

auth_check($auth[$sub_menu], "r");

$g4[title] = "롤링배너 관리";
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($g4[title])?>

<iframe src="/mall5/banner_config_new.php" width="1600" height="2500" frameborder="0"></iframe>
<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
