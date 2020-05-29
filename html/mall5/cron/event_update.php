<?php
/*
----------------------------------------------------------------------
file name	 : event_update.php
comment		 :
date		 : 2014-08-26
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/
exit;
include "db.config.php";
$ople_link = mysql_connect($ople_db['host'], $ople_db['id'], $ople_db['pw']);

$db_selected1 = mysql_select_db('okflex5');

$rs = mysql_query("select * from yc4_bl_event_item_stock");

while($data = mysql_fetch_assoc($rs)){

	$item_rs = mysql_query("select it_amount,it_stock_qty,it_maker from yc4_item where it_id='$data[it_id]'");

	$row = mysql_fetch_assoc($item_rs);

	$it_stock_qty = (strtolower($row['it_maker']) == 'solgar') ? 0 : 99999;

	$update_qry = "update yc4_item set it_amount='$data[ch_amount]',it_stock_qty='$it_stock_qty' where it_id='$data[it_id]'";


	//echo "$update_qry<br>";


	mysql_query($update_qry);
}
echo 'complete';
?>