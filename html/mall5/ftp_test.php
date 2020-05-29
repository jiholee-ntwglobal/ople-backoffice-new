<?php
/**
 * Created by PhpStorm.
 * User: Developers
 * Date: 2018-04-25
 * Time: 오후 3:59
 */
$g4['front_ftp_server']	 = "66.209.90.19";
$g4['front_ftp_user_name'] = "ntwglobal";
$g4['front_ftp_user_pass'] = "qwe123qwe!@#";
$conn_id = ftp_connect($g4['front_ftp_server']);var_dump($conn_id);
$login_result = ftp_login($conn_id, $g4['front_ftp_user_name'], $g4['front_ftp_user_pass']);

var_dump($login_result);