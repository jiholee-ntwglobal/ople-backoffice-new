<?php
$sub_menu = "300300";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");

//print_r2($_POST); exit;
$it_count = count($_POST['it_id']);
for ($i=0; $i<$it_count; $i++)
{
    $sql = "update $g4[yc4_item_table]
               set it_use			= '{$_POST['it_use'][$i]}',
				   it_order_onetime_limit_cnt = '{$_POST['it_order_onetime_limit_cnt'][$i]}'
             where it_id = '{$_POST['it_id'][$i]}' ";
    sql_query($sql);
}

goto_url("./item_order_onetime_limit_list.php?sort1=$sort&sort2=$sort2&sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=".urlencode($search)."&page=$page");
?>