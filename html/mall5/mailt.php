<?php 
/*
----------------------------------------------------------------------
file name	 : 
comment		 : 
date		 : 2014-08-26
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/


ini_set('display_errors','On');
	error_reporting(E_ALL ^ E_NOTICE);


include_once("./_common.php");
include_once("./lib/mailer.lib.php");


$fname='����';
$fmail='info@ople.com';
$to='rsmaker@naver.com';
$subject='���Ϲ߼��׽�Ʈ';
$content='���Ϲ߼��׽�Ʈ';

mailer($fname, $fmail, $to, $subject, $content);echo '111';exit;
?>