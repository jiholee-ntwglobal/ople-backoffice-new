<?
include_once("./_common.php");

/*
    NAVER 가격비교 "화장품"상품군 DB 
    http://nshopping.naver.com/help/price_cosmetic_index_info.php

    <<<begin>>>
    <<<pid>>>51
    <<<category>>>화장품.미용@스킨케어@스킨
    <<<pname>>>여심(女心) 청명수 한방5종 기획세트
    <<<brand>>>청명수
    <<<maker>>>(주)태양화장품
    <<<price>>>29000
    <<<purl>>>http://www.ma.co.kr/html/product3.html?b_code=1301011002&dc_code=NN-1301011002
    <<<imgurl>>>http://www.ma.co.kr/manager/image/1301011002_F.jpg
    <<<spec>>>
    <<<delivery>>>30000/2500
    <<<end>>> 

    필요필드
    상품ID -> yc_item[it_id]
    분류 -> $category
    브랜드 -> yc_item[it_opt5]
    제조회사 -> yc_item[it_maker]
    가격 -> yc_item[it_amount]
    상품명 -> yc_item[it_name]
    상품URL -> $url?doc=$cart_dir/item.php&it_id=$row[it_id]
    이미지URL -> $url/data/item/{$row[it_id]}_l1
    사양 -> yc_item[it_explan]
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
    $spec = strip_tags($row[it_explan]);

    echo <<< HEREDOC
{$lt}begin{$gt}
{$lt}pid{$gt}$row[it_id]
{$lt}category{$gt}$ca_name
{$lt}pname{$gt}$row[it_name]
{$lt}brand{$gt}$row[it_opt5]
{$lt}maker{$gt}$row[it_maker]
{$lt}price{$gt}$row[it_amount]
{$lt}purl{$gt}$g4[shop_url]/item.php?it_id=$row[it_id]
{$lt}imgurl{$gt}$g4[shop_url]/data/item/{$row[it_id]}_l1
{$lt}spec{$gt}$spec
{$lt}delivery{$gt}$delivery
{$lt}end{$gt}

HEREDOC;
}
?>