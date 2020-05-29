<?php
$sub_menu = "400512";
include_once("./_common.php");
if ($w == "u" || $w == "d")
    check_demo();

if ($w == 'd')
    auth_check($auth[$sub_menu], "d");
else
    auth_check($auth[$sub_menu], "w");


$sql_common = "
	set gift_title = '$gift_title',
		gift_category = '$gift_category',
		gift_qty_all = '$gift_qty_all',
		gift_amount = '$gift_amount',
		gift_amount2 = '$gift_amount2',
		gift_st_time = '$gift_st_time',
		gift_ed_time = '$gift_ed_time' ";

if ($w == "")
{
    $gift_id = $g4[server_time];
    $sql = " insert $g4[yc4_gift_table] $sql_common, gift_id = '$gift_id' ";
    sql_query($sql);
}
else if ($w == "u")
{
    $sql = " update $g4[yc4_gift_table] $sql_common where gift_id = '$gift_id' ";
    sql_query($sql);
}
else if ($w == "d")
{
    $sql = " delete from $g4[yc4_gift_table] where gift_id = '$gift_id' ";
    sql_query($sql);
}

if ($w == "" || $w == "u")
{
     goto_url("./itemgiftform.php?w=u&gift_id=$gift_id&page=$page");
}
else
{
    goto_url("./itemgift.php");
}
?>
