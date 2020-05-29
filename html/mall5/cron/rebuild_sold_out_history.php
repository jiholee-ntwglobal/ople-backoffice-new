<?php 
/*
----------------------------------------------------------------------
file name	 : rebuild_sold_out_history.php
comment		 : 품절해제 히스트로 데이터 리빌드
date		 : 2015-05-19
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/
include "db.config.php";

$ople_link = mysql_connect($ople_db['host'], $ople_db['id'], $ople_db['pw']);

$db_selected = mysql_select_db('okflex5');


/* 품절 상품 미처리건 상품정보 로드 쿼리 */
$sql = "SELECT i.it_id
		  FROM yc4_item i LEFT OUTER JOIN yc4_soldout_history h ON i.it_id = h.it_id
		 WHERE i.it_stock_qty < 1 AND (isnull(h.it_id) OR h.flag = 'i' AND h.current_fg='Y')";

$rs = mysql_query($sql);

while($data = mysql_fetch_assoc($rs)){

	mysql_query("update yc4_soldout_history set current_fg='N' where it_id='$data[it_id]'");

	mysql_query("insert into yc4_soldout_history (it_id, flag, mb_id, time, ip, current_fg) values('$data[it_id]', 'o', 'auto(system)', NOW(), 'SHELL', 'Y')");
}


/* 품절해제 상품 미처리건 상품정보 로드 쿼리 */
$sql = "SELECT i.it_id
		  FROM yc4_item i LEFT OUTER JOIN yc4_soldout_history h ON i.it_id = h.it_id
		 WHERE i.it_stock_qty > 0 AND h.flag = 'o' AND h.current_fg='Y'";

$rs = mysql_query($sql);

while($data = mysql_fetch_assoc($rs)){

	mysql_query("update yc4_soldout_history set current_fg='N' where it_id='$data[it_id]'");

	mysql_query("insert into yc4_soldout_history (it_id, flag, mb_id, time, ip, current_fg) values('$data[it_id]', 'i', 'auto(system)', NOW(), 'SHELL', 'Y')");
}
?>