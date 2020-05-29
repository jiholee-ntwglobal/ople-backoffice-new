<?php

include "./_common.php";

if($_SESSION['s_id'] == 6){
	$s_id = 3;
}else{
	$s_id = $_SESSION['s_id'];
}

$sql_list1 = " select b.`it_id`, b.`ca_id`, b.`ca_id2`, b.`ca_id3`, b.`ca_id4`, b.`ca_id5`, b.`it_name`, b.`it_gallery`, b.`it_maker`, b.`it_origin`, b.`it_opt1_subject`, b.`it_opt2_subject`, b.`it_opt3_subject`, b.`it_opt4_subject`, b.`it_opt5_subject`, b.`it_opt6_subject`, b.`it_opt1`, b.`it_opt2`, b.`it_opt3`, b.`it_opt4`, b.`it_opt5`, b.`it_opt6`, b.`it_type1`, b.`it_type2`, b.`it_type3`, b.`it_type4`, b.`it_type5`, b.`it_basic`, b.`it_explan`, b.`it_cust_amount`, b.`it_amount`, b.`it_amount2`, b.`it_amount3`,if(b.it_stock_qty <=0,0,1) as cnt ";
$sql_list2 = " order by a.sort,cnt desc, $order_by b.it_order, b.it_id desc ";

$sql_common = "
	from
		yc4_best_item a,
		yc4_item b
	where
		a.it_id = b.it_id
		and
		a.useyn = 'y'
		and
		a.s_id = '".$s_id."'
";
/*
$sql = "
	select
		b.*
	from
		yc4_best_item a,
		yc4_item b
	where
		a.it_id = b.it_id
		and
		a.s_id = '".$_SESSION['s_id']."'
";
*/
include $g4['full_path'].'/_head.php';
switch($s_id){
	case 1 : $img_no = 4; break;
	case 2 : $img_no = 5; break;
	case 3 : case 6 : $img_no = 1; break;
	case 4 : $img_no = 2; break;
	case 5 : $img_no = 3; break;
}
echo "<img src='http://115.68.20.84/mall6/page/Best100/best100_".$img_no.".jpg' usemap='#Best100'>";
$list_file = "$g4[full_shop_path]/list.skin.00 copy.php";
if (file_exists($list_file))
{
    $list_mod   = 4;
    $list_row   = 10;
    $img_width  = 200;
    $img_height = 200;

    include "$g4[full_shop_path]/list.sub.php";
/*     include "$g4[shop_path]/list.sort.php"; */

    $sql = $sql_list1 . $sql_common . $sql_list2 . " limit $from_record, $items ";



    $result = sql_query($sql);

    include $list_file;

}
?>
<map name="Best100" id="Best100">
	<area href="<?php echo $_SERVER['PHP_SELF'];?>?s_id=3" shape="rect" coords="0,260,205,326" target="_self">
    <area href="<?php echo $_SERVER['PHP_SELF'];?>?s_id=4" shape="rect" coords="205,260,412,326" target="_self">
    <area href="<?php echo $_SERVER['PHP_SELF'];?>?s_id=5" shape="rect" coords="412,260,618,326" target="_self">
      <area href="<?php echo $_SERVER['PHP_SELF'];?>?s_id=1" shape="rect" coords="618,260,823,326" target="_self">
        <area href="<?php echo $_SERVER['PHP_SELF'];?>?s_id=2" shape="rect" coords="823,260,1030,326" target="_self">
        </map>
<br>
<div align=center>
<?
$qstr = $_GET;
unset($qstr['page']);
$qstr = http_build_query($qstr);
echo get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");
?>
</div>
<?php
include $g4['full_path'].'/_tail.php';






?>