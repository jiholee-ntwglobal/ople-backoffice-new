<?
include_once("./_common.php");

/*
    NAVER 가격비교 "분유/기저귀 " DB  
    http://nshopping.naver.com/help/price_baby_index_info.php

    <<<begin>>>
    <<<pid>>> 해당 쇼핑몰 상품 ID 
    <<<category>>> 해당 쇼핑몰 카테고리
    <<<pname>>>매일 앱솔루트-명작 4단계(800g)×12캔
    <<<brand>>>앱솔루트
    <<<maker>>>매일유업
    <<<origin>>>한국
    <<<price>>>201200
    <<<purl>>>http://mall.shinsegae.com/public/front/product.asp?shopID= 4&displayID=66894&productID=848552&retDisUrl 
    <<<imgurl>>>http://mall.shinsegae.com/http://imgshopping.naver.com/ui/_help/app/product/500/8/848552.jpg 
    <<<event>>>상품에 대한 이벤트(사은품)
    <<<delivery>>>배송요금(무료시 '0'이나 공란으로 기재)
    <<<end>>>

    필요필드
    상품ID -> yc_item[it_id]
    분류 -> $category
    브랜드 -> yc_item[it_opt5]
    제조회사 -> yc_item[it_maker]
    원산지 -> yc_item[it_origin]
    가격 -> yc_item[it_amount]
    상품명 -> yc_item[it_name]
    상품URL -> $g4[url]/$g4[shop]/item.php&it_id=yc_item[it_id]
    이미지URL -> $g4[url]/data/item/{$row[it_id]}_l1
    이벤트 -> yc_item[it_6]
    배송료 -> 0

*/

$lt = "<<<";
$gt = ">>>";

// 배송비
if ($default[de_send_cost_case] == '없음')
    $delivery = 0;
else
{
    // 배송비 상한일 경우 제일 앞에 배송비
    $tmp = explode(';', $default[de_send_cost_list]);
    $delivery = (int)$tmp[0];
}

$sql =" select * from $g4[yc4_item_table] where it_use = '1' order by ca_id";
$result = sql_query($sql);

for ($i=0; $row=sql_fetch_array($result); $i++) 
{
	if($row['it_name']){
		$row['it_name'] = get_item_name($row['it_name']);
	}
    $row2 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,2)."' ");

    if (strlen($row[ca_id]) >= 4) 
    {
        $row3 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,4)."' ");
        $ca_name = $row2[ca_name]."@".$row3[ca_name];
    }
    else 
    {
        $row3[ca_name] = "&nbsp;";
        $ca_name = $row2[ca_name];
    }
    
    echo <<< HEREDOC
{$lt}begin{$gt} 
{$lt}pid{$gt}$row[it_id] 
{$lt}category{$gt}$ca_name 
{$lt}pname{$gt}$row[it_name] 
{$lt}brand{$gt}$row[it_opt5] 
{$lt}maker{$gt}$row[it_maker] 
{$lt}origin{$gt}$row[it_origin] 
{$lt}price{$gt}$row[it_amount] 
{$lt}purl{$gt}$g4[shop_url]/item.php?it_id=$row[it_id]
{$lt}imgurl{$gt}$g4[shop_url]/data/item/{$row[it_id]}_l1
{$lt}event{$gt}$row[it_6] 
{$lt}delivery{$gt}$delivery 
{$lt}end{$gt} 

HEREDOC;
}

?>