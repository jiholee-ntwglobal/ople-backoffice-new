<?php
/*
----------------------------------------------------------------------
file name	 : make_naver_ep_brief.php
comment		 : 네이버 지식쇼핑 ep 생성(요약 ep)
date		 : 2015-01-19
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/
date_default_timezone_set ('Asia/Seoul');

header("Content-Type: text/html; charset=UTF-8");

$lt = '<<<';
$gt = '>>>';

$contexts = '';

$needle = array("\t",'[',']', '  ','||');
$target = array(' ', '',' ',' ',' ');

$ople_link = mysql_connect('localhost', 'sales', 'dhvmfghkdlxld123');

$db_selected1 = mysql_select_db('okflex5');


mysql_query("set names utf8");

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

$generate_time = date('Y-m-d H:i:s');

$rs = mysql_query("select max(generate_time) as cnt from naver_ep_brief");

$max_generate_time = mysql_result($rs,0,0);


$rs = mysql_query("select count(*) as cnt from naver_ep_brief where isnull(generate_time) or generate_time='$max_generate_time'");

$total_cnt = 0;

$rs = mysql_query("select * from naver_ep_brief where isnull(generate_time) or generate_time='$max_generate_time'");

while($data = mysql_fetch_assoc($rs)){

	if($data['generate_time']){
		$utime = $data['generate_time'];
	} else {
		$utime = $generate_time;
		mysql_query("update naver_ep_brief set generate_time='$utime' where uid='$data[uid]'");
	}


	if($data['update_yn'] == 'Y'){

		/* 상품 업데이트 시작 */

		$item_rs = mysql_query("select * from yc4_item where it_id='$data[it_id]'");
		$item_data = mysql_fetch_assoc($item_rs);

		$review_rs = mysql_query("select count(*) as cnt from yc4_item_ps where it_id='$data[it_id]' and is_confirm = '1'");

		$review_count = mysql_result($review_rs,0,0);

		$pgurl = 'http://www.ople.com/mall5/shop/item.php?it_id='.$item_data['it_id'].'&ep_code='.($item_data['it_id']*3);
		$igurl = "http://www.ople.com/mall5/data/item/$item_data[it_id]_s";

		$cate_rs = mysql_query("select ca_id from yc4_category_item where it_id='$data[it_id]' limit 0,1");

		$ca_id = mysql_result($cate_rs,0,0);

		$cate1 = $cate_nm[substr($ca_id,0,2)];
		$caid1 = substr($ca_id,0,2);

		$strpos = strpos($item_data['it_name'],'||');

		if($strpos === false){
			$it_name = str_replace($needle,$target,$item_data['it_name']);
		} else {
			$tmp = explode('||', $item_data['it_name']);
			array_walk($tmp,'trim');
			$item_data['it_name'] = implode(' ',$tmp);
			$it_name = str_replace($needle,$target,$item_data['it_name']);
		}
		$it_name = mb_substr($it_name,0,99,'utf-8');
		if(mb_strlen($it_name, 'utf-8') < 100){

		$contexts .= <<<EPALL
{$lt}begin{$gt}
{$lt}mapid{$gt}$data[it_id]
{$lt}pname{$gt}{$it_name}
{$lt}price{$gt}$item_data[it_amount]
{$lt}brand{$gt}$item_data[it_maker]
{$lt}maker{$gt}$item_data[it_maker]
{$lt}origi{$gt}$item_data[it_origin]
{$lt}point{$gt}$item_data[it_point]
{$lt}revct{$gt}$review_count
{$lt}class{$gt}U
{$lt}utime{$gt}$utime
{$lt}ftend{$gt}

EPALL;
			$total_cnt++;

		}

	} else if($data['pause_yn'] == 'Y'){

		/* 품절 시작 */

		$contexts .= <<<EPALL
{$lt}begin{$gt}
{$lt}mapid{$gt}$data[it_id]
{$lt}class{$gt}D
{$lt}utime{$gt}$utime
{$lt}ftend{$gt}

EPALL;
		$total_cnt++;

	} else if($data['resume_yn'] == 'Y'){

		/* 품절복구 시작 */

		$item_rs = mysql_query("select * from yc4_item where it_id='$data[it_id]'");
		$item_data = mysql_fetch_assoc($item_rs);

		$review_rs = mysql_query("select count(*) as cnt from yc4_item_ps where it_id='$data[it_id]' and is_confirm = '1'");

		$review_count = mysql_result($review_rs,0,0);

		$pgurl = 'http://www.ople.com/mall5/shop/item.php?it_id='.$item_data['it_id'].'&ep_code='.($item_data['it_id']*3);
		$igurl = "http://www.ople.com/mall5/data/item/$item_data[it_id]_s";

		$cate_rs = mysql_query("select ca_id from yc4_category_item where it_id='$data[it_id]' limit 0,1");

		$ca_id = mysql_result($cate_rs,0,0);

		$cate1 = $cate_nm[substr($ca_id,0,2)];
		$caid1 = substr($ca_id,0,2);

		$strpos = strpos($item_data['it_name'],'||');

		if($strpos === false){
			$it_name = str_replace($needle,$target,$item_data['it_name']);
		} else {
			$tmp = explode('||', $item_data['it_name']);
			$it_name = str_replace($needle,$target,$tmp[1]);
		}

		if(mb_strlen($it_name, 'utf-8') < 100){

			$contexts .= <<<EPALL
{$lt}begin{$gt}
{$lt}mapid{$gt}$data[it_id]
{$lt}pname{$gt}{$it_name}
{$lt}price{$gt}$item_data[it_amount]
{$lt}brand{$gt}$item_data[it_maker]
{$lt}maker{$gt}$item_data[it_maker]
{$lt}origi{$gt}$item_data[it_origin]
{$lt}point{$gt}$item_data[it_point]
{$lt}revct{$gt}$review_count
{$lt}class{$gt}U
{$lt}utime{$gt}$utime
{$lt}ftend{$gt}

EPALL;
			$total_cnt++;
		}

	}

}

echo <<<EPALL
{$lt}tocnt{$gt}$total_cnt

EPALL;

echo $contexts;
?>