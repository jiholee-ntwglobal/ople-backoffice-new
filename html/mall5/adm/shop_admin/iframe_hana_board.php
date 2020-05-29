<?php
/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2016-12-28
 * Time: 오후 4:18
 */

$sub_menu = "700600";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "하나몰 고객센터";
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($g4[title])?>

<iframe src="/mall5/bbs/board2.php?bo_table=hana_qa" width="1120" height="1600" frameborder="0"></iframe>
<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
