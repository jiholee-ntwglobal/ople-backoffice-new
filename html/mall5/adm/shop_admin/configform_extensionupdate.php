<?php
$sub_menu = "100600";
include_once("_common.php");
auth_check($auth[$sub_menu], "w");


for($k=3; $k<5; $k++){
	$off_arr[] = $k."=>".$_POST['de_mb_level_off'][$k];
	$post_arr[] = $k."=>".$_POST['de_mb_level_free_post'][$k];
}
$sql = "update $g4[yc4_default_table]
	set de_it_use_best_postpoint	= '$de_it_use_best_postpoint',
		de_item_sms_msg				= trim('$de_item_sms_msg'),
		de_item_sms_auto_use		= '$de_item_sms_auto_use',
		de_it_use_first_postpoint	= '$de_it_use_first_postpoint',
		de_mb_level_off				= '".implode('|', $off_arr)."',
		de_mb_level_free_post		= '".implode('|', $post_arr)."',
		de_recom_point				= trim('$de_recom_point'),
		de_recom_off_ca_id			= '$de_recom_off_ca_id',
		de_recom_off_amount			= trim('$de_recom_off_amount'),
		/*
		de_order_ship_multi_cost_amount	= '$de_order_ship_multi_cost_amount',
		de_order_ship_multi_cost_amount_add = '$de_order_ship_multi_cost_amount_add',
		*/
		de_order_ship_multi_default		= '$de_order_ship_multi_default',
		de_order_ship_multi_free_amount = '$de_order_ship_multi_free_amount',
		de_order_ship_multi_level	= '$de_order_ship_multi_level',
		de_cdn						= '$de_cdn',
		de_iherb_amount_ratio		= '$de_iherb_amount_ratio',
		de_srp_amount_ratio			= '$de_srp_amount_ratio'
	";
sql_query($sql);


goto_url("configform_extension.php");
/****
2=>|3=>5|4=>7|5=>
Array
(
    [de_mb_level_off] => Array
        (
            [2] =>
            [3] => 5
            [4] => 7
        )

    [de_mb_level_free_post] => Array
        (
            [4] => 1
        )
)
*****/
?>