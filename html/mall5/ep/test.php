<?php
//$ople_link = mysql_connect('209.216.56.102', 'sales', 'dhvmfghkdlxld123');

//$db_selected1 = mysql_select_db('okflex5');


//mysql_query("set names utf8");

//$sql = mysql_query("select char_length('가나다')");

$char = "123456789가";
echo PHP_EOL;

echo mb_strlen($char,'utf-8');
echo PHP_EOL;
echo mb_substr($char,0,9,'utf-8');

echo PHP_EOL;
echo PHP_EOL;
?>