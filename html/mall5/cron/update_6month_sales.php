<?php
/*
----------------------------------------------------------------------
file name	 : update_6month_sales.php
comment		 : 상품별 6개월치 판매 데이터 업데이트
date		 : 2014-12-31
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/
date_default_timezone_set('Asia/Seoul');
include "db.config.php";
$ople_link = mysql_connect($ople_db['host'], $ople_db['id'], $ople_db['pw']);

$db_selected = mysql_select_db('okflex5');

$start_dt	 = date('Y-m-d', strtotime('-6 month'));
$end_dt		 = date('Y-m-d');

$rs = mysql_query("	SELECT
						i.it_id,SUM(s.total_sold) AS total_sold
					FROM
						okflex5.yc4_category_item i,
						sales.op_sales_report s
					WHERE
						i.it_id=s.it_id AND s.dt BETWEEN '$start_dt' AND '$end_dt'
					GROUP BY i.it_id");

while($data = mysql_fetch_assoc($rs)){

	$update_qry = "update yc4_item set it_sales_6month='$data[total_sold]' where it_id='$data[it_id]'";

	mysql_query($update_qry);
}
echo 'complete!!!';
?>