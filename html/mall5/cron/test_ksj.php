<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-09-17
* Time : 오후 2:52
*/

include "db.config.php";

$sms_link	= mysqli_connect($sms_db['host'], $sms_db['id'], $sms_db['pw'], $sms_db['dbname']);

$row = mysqli_fetch_array(mysqli_query($sms_link, "select count(*) as cnt from MMS_MSG where  PHONE = '234243243' and ETC3 = '33333'"));

echo "<pre>";
var_dump($row);
echo "</pre>";

echo $row['cnt']
?>