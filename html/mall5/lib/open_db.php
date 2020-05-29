<?php

/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-08-27
 * Time: 오전 10:43
 */
class open_db
{
    var $open_db;

    function __construct()
    {
//        $open_host = '209.216.56.102';
        $open_host = '127.0.0.1';
        $open_id = 'sales';
        $open_pw = 'dhvmfghkdlxld123';
        $open_db = 'openmarket';
        //$this->open_db = new PDO("mysql:host=$open_host;dbname=$open_db;charset=utf8", $open_id, $open_pw);
        $this->open_db = new mysqli($open_host, $open_id, $open_pw, $open_db);
        $this->sql_query("set names utf8");
    }

    function sql_query($sql)
    {
        return $this->open_db->query($sql);
    }
    function  sql_fetch($sql){
        return $this->sql_query($sql)->fetch_assoc();
    }

    function delevery($row)
    {
        if (trim($row) != '') {
            $fld = explode("\t", stripslashes(trim($row)));
            $od_id = preg_replace("/[^0-9]/", "", trim($fld[0]));


            // 단수배송
            if (count($fld) == 1) {
                $sql = "update yc4_order
					   set od_invoice_time = now(),
						   dl_id           = 7, /* 배송회사 코드 */
						   od_invoice      = '{$fld[0]}', /* 송장번호 */
						   od_shop_memo	   = concat(od_shop_memo, '\\n', '엑셀 배송일괄처리|',now()),
						   od_status_update_dt = NOW()
					 where od_id           = '$od_id' ";
                $this->sql_query($sql);


                // 장바구니 상태가 '주문', '준비' 일 경우 '배송' 으로 상태를 변경
                $od_row = $this->sql_fetch("select on_uid, od_name, od_hp, mb_id from yc4_order where od_id='$od_id' ");
                $sql = " update yc4_cart
						set ct_status = '배송',ct_status_update_dt = now()
					  where ct_status in ('주문', '준비')
						and on_uid = '{$od_row['on_uid']}' ";
                $this->sql_query($sql);


                // 재고 반영
                $sql2 = " select it_id, ct_id,opk_ct_id ct_stock_use, ct_qty from yc4_cart
					   where on_uid = '{$od_row['on_uid']}'
						 and ct_stock_use = '0' ";
                $result2 = $this->sql_query($sql2);
                while ($row2 = $result2->fetch_assoc()) {
                    //sql_query(" update $g4[yc4_item_table] set it_stock_qty = it_stock_qty - '$row2[ct_qty]' where it_id = '$row2[it_id]' ");
                    $sql4 = " update yc4_cart
							set ct_stock_use  = '1',
								ct_history    = CONCAT(ct_history, '\\n', '배송일괄|',now(),'|".$_SERVER['REMOTE_ADDR']."')
						  where on_uid = '{$od_row['on_uid']}'
							and ct_id  = '$row2[ct_id]' ";
                    $this->sql_query($sql4);
                }
            }
        }
    }

}