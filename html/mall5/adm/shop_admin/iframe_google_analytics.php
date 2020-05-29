<?php 
/*
----------------------------------------------------------------------
file name	 : iframe_google_analytics.php
comment		 : Google Analytics iframe
date		 : 2015-05-15
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/
$sub_menu = "800300";
include_once("./_common.php");

mail('rsmaker@ntwglobal.com','hello world','hi');

auth_check($auth[$sub_menu], "r");

$g4[title] = "Google Analytics";
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($g4[title])?>

<iframe src="http://www.google.co.kr/intl/ko/analytics/" width="1600" height="1000" frameborder="0"></iframe>
<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
