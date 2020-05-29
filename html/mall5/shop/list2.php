<?php
define('LIST_PAGE',true);
include_once("./_common.php");


$sql = " select *
           from ".$g4['yc4_category_table']."
          where ca_id = '".$ca_id."'
            and ca_use = '1'  ";
$ca = sql_fetch($sql);
if (!$ca['ca_id'])
    alert("등록된 분류가 없습니다.");

$g4['title'] = $ca['ca_name'] . " 상품리스트";

if ($ca['ca_include_head'])
    @include_once($ca['ca_include_head']);
else
    include_once("./_head.php");


// 스킨을 지정했다면 지정한 스킨을 사용함 (스킨의 다양화)
//if ($skin) $ca[ca_skin] = $skin;

$nav_ca_id = $ca_id;
include $g4[full_shop_path]."/navigation1.inc.php";


$himg = $g4['path']."/data/category/{$ca_id}_h";
if (file_exists($himg)) {
    echo "<img src='".$himg."' border=0><br>";
}

//if($ca_id == 'ck'){ // 김선용 201103 : 요오드 임시 예약 신청
////	echo "<br/><div style='line-height:150%; font-weight:bold; border:4px solid #eee;'>요오드 제품은 더이상 주문을 받지 않습니다. 주문 관리가 어려워 예약으로 만 접수를 합니다. 예약 정보를 입력해주시면 제품이 입고될때 고객님께 먼저 등록해주신 고객순으로 이메일 과 문자로 안내를 해드리겠습니다.<br/><a href='{$g4['path']}/sjsjin/reser_iodine_write.php' title='예약신청하기'><span style='font-size:12pt; font-weight:bold;'>예약신청</span></a> <<<= 클릭해주세요</div>";
//}
//
// 상단 HTML
echo stripslashes($ca[ca_head_html]);

if(file_exists($g4['full_shop_path'].'/category_header/ca_'.$_GET['ca_id'].'.html')){
	include $g4['full_shop_path'].'/category_header/ca_'.$_GET['ca_id'].'.html';

}


if ($is_admin)
    echo "<p align=center><a href='$g4[shop_admin_path]/categoryform.php?w=u&ca_id=$ca_id'><img src='$g4[shop_img_path]/btn_admin_modify.gif' border=0></a></p>";

include_once $g4['full_shop_path']."/listcategory3.inc.php";
?>
<div class="content_sub">
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td>

<?
// 상품 출력순서가 있다면
if ($sort != "") {
    $order_by = $sort . " , ";
}

// 상품 (하위 분류의 상품을 모두 포함한다.)
$sql_list1 = "
	select
	distinct b.it_id,
	b.it_name,
	b.it_gallery,
	b.it_maker,
	b.it_origin,
	b.it_type1,
	b.it_type2,
	b.it_type3,
	b.it_type4,
	b.it_type5,
	b.it_explan_html,
	b.it_cust_amount,
	b.it_amount,
	b.it_amount2,
	b.it_amount3,
	b.it_point,
	b.it_use,
	b.it_stock_qty,
	b.it_order,
	b.it_tel_inq,
	b.SKU,
	if(b.it_stock_qty <=0,0,1) as cnt ";
$sql_list2 = " order by cnt desc, $order_by b.it_order, b.it_id desc ";


/*
// 김선용 201207 :
// 하위분류 포함
// 판매가능한 상품만
$sql_common = " from $g4[yc4_item_table]
               where (ca_id like '{$ca_id}%'
                   or ca_id2 like '{$ca_id}%'
                   or ca_id3 like '{$ca_id}%')
                 and it_use = '1' ";
*/
/*
// 김선용 201211 : 단종 미출력
$sql_common = " from $g4[yc4_item_table] where match(ca_id, ca_id2, ca_id3, ca_id4, ca_id5) against('$ca_id*' in boolean mode) and it_use = '1' and it_discontinued=0 ";
*/

# co.kr 로 접속시 지정된 카테고리 상품 노출되지 않도록 2014-04-17 홍민기 #
//$sql_common = " from $g4[yc4_item_table] where match(ca_id, ca_id2, ca_id3, ca_id4, ca_id5) against('$ca_id*' in boolean mode) and it_use = '1' and it_discontinued=0 ".$hide_caQ4.$hide_makerQ.$hide_itemQ;

# 새로운 카테고리에 따른 목록 노출
$sql_common = "
	from
		".$g4['yc4_category_table']." a
		left join
		yc4_category_item c on a.ca_id = c.ca_id
		left join
		".$g4['yc4_item_table']." b on c.it_id = b.it_id
		where
		a.ca_id like '".$ca_id."%'
		and
		b.it_id is not null
		and
		b.it_use = '1' /* 판매 가능 상풍만 */
		and
		b.it_discontinued = 0
";

// 김선용 200804
if($it_maker)
	$sql_common .= " and it_maker='$it_maker' ";

$error = "<img src='$g4[shop_img_path]/no_item.gif' border=0>";

// 리스트 유형별로 출력
//$list_file = $g4['full_shop_path']."/".$ca['ca_skin'];
$list_file = $g4['full_shop_path']."/list.skin.10_test.php";
$qstr1 .= "items=$items&ca_id=$ca_id&ev_id=$ev_id&sort=$sort";
if (file_exists($list_file)) {

    //display_type(2, "maintype10.inc.php", 4, 2, 100, 100, $ca[ca_id]);

    $list_mod   = $ca[ca_list_mod];
    $list_row   = $ca[ca_list_row];
    $img_width  = $ca[ca_img_width];
    $img_height = $ca[ca_img_height];

    include "$g4[full_shop_path]/list.sub.product.php";
    include "$g4[full_shop_path]/list.sort.php";

    $sql = $sql_list1 . $sql_common . $sql_list2 . " limit $from_record, $items ";





    $result = sql_query($sql);
	echo $list_file;
    include $list_file;

}
else
{

    $i = 0;
    $error = "<p>$ca[ca_skin] 파일을 찾을 수 없습니다.<p>관리자에게 알려주시면 감사하겠습니다.";

}

if ($i==0)
{
    echo "<br/>";
    echo "<div align=center>$error</div>";
}
?>

        </td>
    </tr>
    <tr><td>
<div class='paging'>
<?

echo get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr1&page=");
?>
</div></tr></td>
</table>
</div>

<?
// 하단 HTML
echo stripslashes($ca[ca_tail_html]);

$timg = "$g4[full_path]/data/category/{$ca_id}_t";
if (file_exists($timg))
    echo "<br><img src='$timg' border=0>";

if ($ca[ca_include_tail])
    @include_once($ca[ca_include_tail]);
else
    include_once("./_tail.php");

echo "\n<!-- $ca[ca_skin] -->\n";
?>
