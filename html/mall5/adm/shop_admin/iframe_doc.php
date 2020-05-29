<?php 
/*
----------------------------------------------------------------------
file name	 : iframe_doc.php
comment		 : ntw data Analysis Center iframe
date		 : 2015-05-15
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/
$sub_menu = "800400";
include_once("./_common.php");

mail('rsmaker@ntwglobal.com','hello world','hi');

auth_check($auth[$sub_menu], "r");

$g4[title] = "data Analysis Center";
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($g4[title])?>

<iframe src="http://112.220.193.26/203/" width="1600" height="900" frameborder="0"></iframe>
<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
