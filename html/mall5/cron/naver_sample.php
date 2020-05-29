<?php 
/*
----------------------------------------------------------------------
file name	 : naver_sample.php
comment		 : 
date		 : 2014-08-26
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/
$ople_link = mysql_connect('209.216.56.102', 'sales', 'dhvmfghkdlxld123');

$db_selected1 = mysql_select_db('okflex5');

$rs = mysql_query("select i.it_id from yc4_item i, yc4_category_item c where i.it_id=c.it_id and i.it_use='1' group by i.it_id");

while($data = mysql_fetch_assoc($rs)){
	mysql_query("insert into naver_ep_all (it_id,create_date) value ('$data[it_id]',now())");
}

?>