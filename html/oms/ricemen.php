<?php
//$inDateTime = strtotime("2019-09-15 19:40:37");
//$dt = new DateTime($inDateTime, new DateTimeZone('KST'));
//echo $dt->format("Y-m-d H:i:s");

$inDateTime = strtotime("2019-09-16 11:40:37");

echo "<hr />";

echo(date_default_timezone_set("UTC") . "<br />");
echo(date_default_timezone_get() . "<br />");
echo(date("Y-d-mTG:i:sz",$inDateTime) . "<br />");
echo(date_default_timezone_set("Asia/Seoul") . "<br />");
echo(date("Y-d-mTG:i:sz", $inDateTime) . "<br />");