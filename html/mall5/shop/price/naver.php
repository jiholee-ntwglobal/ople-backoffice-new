<?
include_once("./_common.php");

/*
Field	Status	Notes
<<<begin>>>	필수	상품의 시작을 알리는 필드
<<<mapid>>>		    판매하는 상품의 유니크한 상품ID
<<<pname>>>		    실제 서비스에 반영될 상품명(Title)
<<<price>>>		    해당 상품의 판매가격
<<<pgurl>>>		    해당 상품을 구매할 수 있는 상품URL
<<<igurl>>>		    해당 상품의 이미지URL
<<<cate1>>>		    판매하는 상품의 카테고리명(대분류)
<<<cate2>>>	옵션	판매하는 상품의 카테고리명(중분류)
<<<cate3>>>		    판매하는 상품의 카테고리명(소분류)
<<<cate4>>>		    판매하는 상품의 카테고리명(세분류)
<<<model>>>		    모델명
<<<brand>>>		    브랜드
<<<maker>>>		    제조사
<<<origi>>>		    원산지
<<<pdate>>>		    출시일
<<<deliv>>>		    배송료
<<<event>>>		    이벤트
<<<coupo>>>		    쿠폰
<<<pcard>>>		    무이자
<<<point>>>		    포인트
<<<modig>>>		    이미지 재생성요청
<<<ftend>>>	필수	상품의 마지막을 알리는 필드
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
    $cate1 = $cate2 = $cate3 = $cate4 = "";

    $row2 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,2)."' ");
    $cate1 = $row2[ca_name];

    if (strlen($row[ca_id]) >= 8) {
        $row2 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,8)."' ");
        $cate4 = $row2[ca_name];
    }

    if (strlen($row[ca_id]) >= 6) {
        $row2 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,6)."' ");
        $cate3 = $row2[ca_name];
    }

    if (strlen($row[ca_id]) >= 4) {
        $row2 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,4)."' ");
        $cate2 = $row2[ca_name];
    }

    echo <<< HEREDOC
{$lt}begin{$gt}
{$lt}mapid{$gt}$row[it_id]
{$lt}pname{$gt}$row[it_name]
{$lt}price{$gt}$row[it_amount]
{$lt}pgurl{$gt}$g4[shop_url]/item.php?it_id={$row[it_id]}
{$lt}igurl{$gt}$g4[url]/data/item/{$row[it_id]}_m
{$lt}cate1{$gt}$cate1
{$lt}cate2{$gt}$cate2
{$lt}cate3{$gt}$cate3
{$lt}cate4{$gt}$cate4
{$lt}model{$gt}
{$lt}brand{$gt}
{$lt}maker{$gt}$row[it_maker]
{$lt}origi{$gt}$row[it_origin]
{$lt}pdate{$gt}
{$lt}deliv{$gt}$delivery
{$lt}event{$gt}
{$lt}coupo{$gt}
{$lt}pcard{$gt}
{$lt}point{$gt}$row[it_point]
{$lt}modig{$gt}Y
{$lt}ftend{$gt}

HEREDOC;
}
?>