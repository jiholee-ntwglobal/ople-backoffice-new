<?php
/*
----------------------------------------------------------------------
file name	 : make_naver_ep_all.php
comment		 : 네이버 지식쇼핑 ep 생성(전체 ep)
date		 : 2015-01-19
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/

header("Content-Type: text/html; charset=UTF-8");

$lt = '<<<';
$gt = '>>>';


$needle = array("\t",'[',']', '  ','||');
$target = array(' ', '',' ',' ',' ');

$ople_link = mysql_connect('localhost', 'sales', 'dhvmfghkdlxld123');

$db_selected1 = mysql_select_db('okflex5');


mysql_query("set names utf8");


# 등록 불가 상품 2015-02-05 홍민기 #
$no_it_id = array();
# 알파리포산 안됨 #
$sql = mysql_query("select distinct it_id from yc4_category_item where ca_id = '1159'");
while($row = mysql_fetch_assoc($sql)){
	$no_it_id[$row['it_id']] = true;
}

# 멜라토닌 안됨 #
$sql = mysql_query("select distinct it_id from yc4_item where it_name like '%멜라토닌%'");
while($row = mysql_fetch_assoc($sql)){
	$no_it_id[$row['it_id']] = true;
}


# 배송비 정책 로드 2015-01-22 홍민기 #
$send_cost_sql = mysql_query("
	select
		de_send_cost_limit, de_send_cost_list
	from
		yc4_default
");
$send_cost = mysql_fetch_assoc($send_cost_sql);
$send_cost_limit = explode(';',$send_cost['de_send_cost_limit']);
$send_cost_list = explode(';',$send_cost['de_send_cost_list']);

$send_cost_cnt = count($send_cost_list);

$send_cost_arr = array();
for($i=0; $i<$send_cost_cnt; $i++){
	$send_cost_arr[$send_cost_limit[$i]] = $send_cost_list[$i];
}


# 상품 가격당 배송비 로드 #
/*
	네이버 측에서 배송비를 상품 1개당 출력해 달라고 요청함 2015-01-22 홍민기
*/
function load_send_cost($amount){
	global $send_cost_limit,$send_cost_list;
    $result = 5000;
	for ($k=0; $k<count($send_cost_limit); $k++) {
		// 총판매금액이 배송비 상한가 보다 작다면
		if ($amount < $send_cost_limit[$k]) {
			$result = $send_cost_list[$k];
			break;
		}
	}

	return $result;
}



$rs = mysql_query("select * from shop_category");

while($data = mysql_fetch_assoc($rs)){
	switch($data['s_id']){
		case '1': $code_nm = '뷰티용품'; break;
		case '2': $code_nm = '식품'; break;
		case '3': $code_nm = '건강식품'; break;
		case '4': $code_nm = '생활'; break;
		case '5': $code_nm = '출산육아'; break;
	}
	$cate_nm[$data['ca_id']] = $code_nm;
}
mysql_query("delete from naver_ep_all");

$rs = mysql_query("select i.it_id from yc4_item i, yc4_category_item c where i.it_id=c.it_id and i.it_use='1' group by i.it_id");

while($data = mysql_fetch_assoc($rs)){
	if(array_key_exists($data['it_id'], $no_it_id)){ // 등록 불가 상품은 스킵
		continue;
	}
	mysql_query("insert into naver_ep_all (it_id,create_date) value ('$data[it_id]',now())");
}

$total_cnt = 0;



$rs = mysql_query("	SELECT
						c.ca_id,i.it_id,i.it_name,i.it_maker,i.it_origin,it_amount,i.it_point
					FROM
						naver_ep_all n, yc4_item i
						left outer join yc4_category_item c ON i.it_id= c.it_id
					WHERE
						i.it_id=n.it_id and isnull(generate_date)
					GROUP BY i.it_id");


$contexts = '';
while($data = mysql_fetch_assoc($rs)){

	$review_rs = mysql_query("select count(*) as cnt from yc4_item_ps where it_id='$data[it_id]' and is_confirm = '1'");

	$review_count = mysql_result($review_rs,0,0);

	$pgurl = 'http://www.ople.com/mall5/shop/item.php?it_id='.$data['it_id'].'&ep_code='.($data['it_id']*3);
	$igurl = "http://www.ople.com/mall5/data/item/$data[it_id]_s";
	$cate1 = $cate_nm[substr($data['ca_id'],0,2)];
	$caid1 = substr($data['ca_id'],0,2);

	$strpos = strpos($data['it_name'],'||');

	if($strpos === false){
		$it_name = str_replace($needle,$target,$data['it_name']);
	} else {
		/*
		$tmp = explode('||', $data['it_name']);
		# 브랜드명 한글만 출력 2015-01-22 홍민기 #
		$it_maker_kor_name = preg_replace("/\[([^{}]+)\]/i",'', $tmp[0]);
		# 브랜드명 추가 출력 2015-01-22 홍민기 #
		$it_name = str_replace($needle,$target,$it_maker_kor_name.' '.$tmp[1]);
		*/

		/*
			제품검색을 원활하게 하기 위하여 제품명을 최대한 많이 보여준다
			2015-02-05 홍민기
		*/
		$tmp = explode('||', $data['it_name']);
		array_walk($tmp,'trim');
		$data['it_name'] = implode(' ',$tmp);
		$it_name = str_replace($needle,$target,$data['it_name']);

	}
	$it_name = mb_substr($it_name,0,99,'utf-8');

	if(mb_strlen($it_name, 'utf-8') < 100){
		$send_cost = load_send_cost($data['it_amount']);



		$contexts .= <<<EPALL
{$lt}begin{$gt}
{$lt}mapid{$gt}$data[it_id]
{$lt}pname{$gt}{$it_name}
{$lt}price{$gt}$data[it_amount]
{$lt}pgurl{$gt}$pgurl
{$lt}igurl{$gt}$igurl
{$lt}cate1{$gt}$cate1
{$lt}cate2{$gt}
{$lt}cate3{$gt}
{$lt}cate4{$gt}
{$lt}caid1{$gt}$caid1
{$lt}caid2{$gt}
{$lt}caid3{$gt}
{$lt}caid4{$gt}
{$lt}model{$gt}
{$lt}brand{$gt}$data[it_maker]
{$lt}maker{$gt}$data[it_maker]
{$lt}origi{$gt}$data[it_origin]
{$lt}deliv{$gt}$send_cost
{$lt}event{$gt}6만원 이상 무료배송
{$lt}coupo{$gt}
{$lt}pcard{$gt}삼성3
{$lt}point{$gt}$data[it_point]
{$lt}mvurl{$gt}
{$lt}selid{$gt}
{$lt}barcode{$gt}
{$lt}cardn{$gt}
{$lt}cardp{$gt}
{$lt}mpric{$gt}
{$lt}revct{$gt}$review_count
{$lt}ecoyn{$gt}
{$lt}econm{$gt}
{$lt}gtype{$gt}FS
{$lt}branc{$gt}
{$lt}pcpdn{$gt}
{$lt}dlvga{$gt}
{$lt}dlvdt{$gt}
{$lt}insco{$gt}
{$lt}ftend{$gt}

EPALL;

		mysql_query("update naver_ep_all set generate_date=NOW() where it_id='$data[it_id]'");

		$total_cnt++;


	}
}

echo <<<EPALL
{$lt}tocnt{$gt}$total_cnt

EPALL;

echo $contexts;
?>