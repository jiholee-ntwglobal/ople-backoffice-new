<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-06-15
 * Time: 오전 10:22
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

if($_SERVER['REMOTE_ADDR'] != '112.218.8.99'){
    exit;
}

$ch = curl_init();

$data = array(
		'channel'=>'opleback',
		'process_code'=>'test_107',
		'process_code_sub1'=>null,
		'process_code_sub2'=>null,
		'process_server_ip'=>'209.216.56.107',
		'auth_key'=>'87b5f92afe813417ea006a2b67431d70');

curl_setopt($ch, CURLOPT_URL,"http://209.216.56.104/web_service/scheduler.php");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_HEADER, 1);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec ($ch);

$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $header_size);
$body = substr($response, $header_size);

var_dump($header);

curl_close ($ch);


