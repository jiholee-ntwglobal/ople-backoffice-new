<?php
$sub_menu = "400312";
include_once("./_common.php");
alert('더 이상 사용하지 않는 기능입니다');
include_once "{$g4['path']}/lib/icode.sms.lib.php";
auth_check($auth[$sub_menu], "w");

//print_r2($_POST); EXIT;
$it_count = count($_POST['it_id']);
for ($i=0; $i<$it_count; $i++)
{
	// 판매선택이고, 단종설정이 아니고 재고가 있으면 sms 통보
	//if($_POST['it_use'][$i] && !$_POST['it_discontinued'][$i] && $_POST['it_stock_qty'][$i] > 0)
	//	it_sms_send($_POST['it_id'][$i], $_POST['it_stock_qty'][$i]);

	$sql = "update $g4[yc4_item_table]
               set it_use			= '{$_POST['it_use'][$i]}',
				   it_stock_qty = '{$_POST['it_stock_qty'][$i]}',
				   it_discontinued = '{$_POST['it_discontinued'][$i]}'
             where it_id = '{$_POST['it_id'][$i]}' ";
    sql_query($sql);
}

goto_url("./item_soldout_list.php?sort1=$sort&sort2=$sort2&sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=".urlencode($search)."&page=$page");
?>