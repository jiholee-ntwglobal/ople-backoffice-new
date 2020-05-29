<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-08-17
 * Time: 오전 11:22
 */
$sub_menu = "500530";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$g4[title] = "오플러 관리";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

