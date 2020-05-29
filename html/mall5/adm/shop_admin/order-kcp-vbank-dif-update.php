<?php
$sub_menu = "400820";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");

exit;
for ($i=0; $i<count($_POST['chk']); $i++)
{
    sql_query("update {$g4['yc4_order_table']} set kcp_vbank_dif=0 where od_id='{$_POST['chk'][$i]}' ");
}

goto_url("./order-kcp-vbank-dif.php?sel_field=$sel_field&search=".urlencode($search)."&page=$page");
?>