<?php
/**
 * Created by PhpStorm.
 * User: Developers
 * Date: 2018-04-26
 * Time: 오후 2:43
 */
$sub_menu = "300120";
include_once "./_common.php";
include_once $g4['full_path'] . '/lib/nfo.php';

$nfo = new nfo();

$tmp_uid = $nfo->get_temp_uid();

var_dump($tmp_uid);