<?php
/*
----------------------------------------------------------------------
file name	 :
comment		 :
date		 : 2014-08-26
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/
include "db.config.php";
$ople_link = mysql_connect($ople_db['host'], $ople_db['id'], $ople_db['pw']);

$local_link = mysql_connect('59.17.43.129', 'natureware', 'elqlwjdrbghk*@*@');



$db_selected1 = mysql_select_db('okflex5',$ople_link);
$db_selected2 = mysql_select_db('203',$local_link);

$rs= mysql_query("	select
						date_format(o.od_time,'%Y%m%d') as sales_date, sum(if(o.od_receipt_bank>0,c.ct_amount * c.ct_qty,0)) as bank_ammount,sum(if(o.od_receipt_card>0,c.ct_amount * c.ct_qty,0)) as card_ammount
					from yc4_order o
						left outer join yc4_cart c on c.on_uid = o.on_uid
					where c.ct_status in ('준비','배송','완료')
						group by date_format(o.od_time,'%Y%m%d') order by o.od_time desc limit 0,90",$ople_link);


if (!$rs) {
    die('Invalid query: ' . mysql_error());
}

while($data = mysql_fetch_assoc($rs)){
	$static_rs = mysql_query("select count(*) as cnt from daily_sales_ammount_statics where channel='O' and sales_date='$data[sales_date]'",$local_link);

	if(mysql_result($static_rs,0,0) < 1){

		mysql_query($a="insert into daily_sales_ammount_statics (channel,sales_date,card_ammount,bank_ammount) values ('O','$data[sales_date]','$data[card_ammount]','$data[bank_ammount]')",$local_link);

	} else {
		mysql_query("update daily_sales_ammount_statics set card_ammount='$data[card_ammount]',bank_ammount='$data[bank_ammount]' where channel='O' and sales_date='$data[sales_date]'",$local_link);
	}
}
echo '????';

?>