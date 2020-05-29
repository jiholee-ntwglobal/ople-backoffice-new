<?
include_once("./_common.php");

/*
    NAVER 가격비교 "가전/PC 및 주변기기/핸드폰,통신" DB
    http://nshopping.naver.com/help/price_price_index_info.php

    <<<begin>>>
    <<<상품ID>>>51
    <<<분류>>>컴퓨터.주변기기@기본제품@데스크탑
    <<<상품명>>>PARA 종합선물세트 메카닉 4266B-TV
    <<<모델명>>>01-0170
    <<<출시일자>>>
    <<<제조회사>>>컴파라
    <<<가격>>>1450000
    <<<상품URL>>>http://www.shopping.co.kr/v1/shop/product_cart.php?id=50&product_category =PARA_데스크탑
    <<<포인트>>>
    <<<배송료>>>0
    <<<이벤트>>>
    <<<end>>>

    필요필드
    상품ID -> yc_item[it_id]
    분류 -> yc_category[ca_name]@
    상품명 -> yc_item[it_name]
    모델명 -> yc_item[it_opt5]
    제조회사 -> yc_item[it_maker]
    가격 -> yc_item[it_amount]
    상품URL -> $url?doc=$cart_dir/item.php&it_id=yc_item[it_id]
    배송료 -> 0
    이벤트 -> yc_item[it_6]
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
{$lt}상품ID{$gt}$row[it_id]
{$lt}분류{$gt}$ca_name
{$lt}상품명{$gt}$row[it_name]
{$lt}모델명{$gt}$row[it_opt5]
{$lt}출시일자{$gt}
{$lt}제조회사{$gt}$row[it_maker]
{$lt}가격{$gt}$row[it_amount]
{$lt}상품URL{$gt}$g4[shop_url]/item.php?it_id=$row[it_id]
{$lt}포인트{$gt}
{$lt}배송료{$gt}$delivery
{$lt}이벤트{$gt}
{$lt}end{$gt}

HEREDOC;
}
?>