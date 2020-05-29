<?php

/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-06-24
* Time : 오후 6:01
*/
$sub_menu = "500500";
include './_common.php';
auth_check($auth[$sub_menu], "w");
if($_POST['it_id']){
    $search_qry .= (($search_qry) ? " and ":' where ')."it_id like '%".$_POST['it_id']."%'";
}

if($_POST['SKU']){
    $search_qry .= (($search_qry) ? " and ":' where ')."SKU like '%".$_POST['SKU']."%'";
}

if($_POST['it_name']){
    $search_qry .= (($search_qry) ? " and ":' where ')."it_name like '%".$_POST['it_name']."%'";
}

if($_POST['it_maker']){
    $search_qry .= (($search_qry) ? " and ":' where ')."it_maker like '%".$_POST['it_maker']."%'";
}

$sql = sql_query("select it_id,it_name,it_maker,SKU,it_amount from ".$g4['yc4_item_table']." ".$search_qry." order by it_time desc");


while($row = sql_fetch_array($sql)){
    echo "
			<li class='ui-state-default'>
				<input type='hidden' name='it_id[".$i."]' value='".$row['it_id']."'/>
				<input type='hidden' name='sort[".$i."]' value='".$i."'>
				<p>".$row['it_maker']."</p>
				상품코드 : ".$row['it_id']." | SKU : ".$row['SKU']." | 가격 : ".number_format($row['it_amount'])."원<br/>
				
				".$row['it_name']."
			</li>
		";
}
?>