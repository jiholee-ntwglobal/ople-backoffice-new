<?php 
/*
----------------------------------------------------------------------
file name	 : update_manwon_event_item.php
comment		 : 만원의 행복 이벤트 상품 업데이트 처리 파일
date		 : 2014-12-09
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/

include 'dbconfig.php';

$conn = mysql_connect($mysql_host, $mysql_user, $mysql_password);

mysql_select_db($mysql_db);

$ev_id = '1418090541'; // 전체 베스트 상품 이벤트 코드


/* 전체 만원의 행복 베스트 상품 처리 시작 */

// 기존 이벤트 아이템 데이터 삭제
$del_qry = "DELETE FROM yc4_event_item WHERE ev_id='$ev_id'";
mysql_query($del_qry);

// 수동 입력 데이터 정보 획득
$manual_rs = mysql_query("SELECT * FROM manwon_event_manual WHERE category='0' ORDER BY sort ASC");
$manual_data = array();
while($manual_info = mysql_fetch_assoc($manual_rs)){
	$manual_data['s'.$manual_info['sort']] = $manual_info['it_id'];
}


$rs = mysql_query("SELECT * FROM yc4_item WHERE it_amount<10000 AND it_use = '1' AND it_discontinued=0 ORDER BY IF(it_stock_qty<=0,0,1) DESC, it_order, it_id DESC LIMIT 0,100");

$sort = 1;

$manual_process_it_id = array();

while($data = mysql_fetch_assoc($rs)){

	while(array_key_exists('s'.$sort, $manual_data)){

		// 수동입력한 데이터가 있는 경우

		$insert_qry = "INSERT INTO yc4_event_item (ev_id, it_id, sort) VALUES ('$ev_id', '".$manual_data['s'.$sort]."', '$sort')";
		mysql_query($insert_qry);

		array_push($manual_process_it_id,$manual_data['s'.$sort]);

		$sort++;
	}

	if(!in_array($data['it_id'],$manual_process_it_id)){

		$insert_qry = "INSERT INTO yc4_event_item (ev_id, it_id, sort) VALUES ('$ev_id', '$data[it_id]', '$sort')";
		mysql_query($insert_qry);

		$sort++;

	}

}

$manual_data = array();


/* 전체 만원의 행복 베스트 상품 처리 끝 */



/* 관별 만원의 행복 베스트 상품 처리 시작 */

$ev_id1 = '1418090626'; // 뷰티용품관별 베스트 상품 이벤트 코드
$ev_id2 = '1418090641'; // 식품관별 베스트 상품 이벤트 코드
$ev_id3 = '1418090574'; // 건강식품관관별 베스트 상품 이벤트 코드
$ev_id4 = '1418090588'; // 생활관별 베스트 상품 이벤트 코드
$ev_id5 = '1418090605'; // 출산/육아관별 베스트 상품 이벤트 코드
$ev_id7 = '1470712111'; // 헬스/다이어트 베스트 상품 이벤트 코드

$s_cate_rs = mysql_query("SELECT s_id FROM shop_category GROUP BY s_id ORDER BY s_id ASC");

while($s_cate = mysql_fetch_assoc($s_cate_rs)){

	// 기존 이벤트 아이템 데이터 삭제
	$del_qry = "DELETE FROM yc4_event_item WHERE ev_id='".${'ev_id'.$s_cate['s_id']}."'";
	mysql_query($del_qry);

	// 수동 입력 데이터 정보 획득
	$manual_data = array();
	$manual_rs = mysql_query("SELECT * FROM manwon_event_manual WHERE category='$s_cate[s_id]' ORDER BY sort ASC");

	while($manual_info = mysql_fetch_assoc($manual_rs)){
		$manual_data['s'.$manual_info['sort']] = $manual_info['it_id'];
	}


	// 관 하위의 카테고리 정보 획득
	$cate_info = mysql_fetch_assoc(mysql_query("SELECT GROUP_CONCAT(ca_id,'') AS ca_ids FROM shop_category WHERE s_id='$s_cate[s_id]'"));

	// 관 하위의 카테고리에 있는 아이템 로드
	$rs = mysql_query("
		SELECT 
			i.* 
		FROM 
			yc4_item i,
			(SELECT it_id FROM yc4_category_item WHERE LEFT(ca_id,2) IN ($cate_info[ca_ids]) GROUP BY it_id) c
		WHERE 
		i.it_id = c.it_id AND i.it_amount<10000 and i.it_use = '1' AND i.it_discontinued=0 
		ORDER BY IF(i.it_stock_qty<=0,0,1) DESC, i.it_order, i.it_id DESC");

	$sort = 1;

	$manual_process_it_id = array();

	while($data = mysql_fetch_assoc($rs)){

		while(array_key_exists('s'.$sort, $manual_data)){

			// 수동입력한 데이터가 있는 경우

			$insert_qry = "INSERT INTO yc4_event_item (ev_id, it_id, sort) VALUES ('".${'ev_id'.$s_cate['s_id']}."', '".$manual_data['s'.$sort]."', '$sort')"; 
			mysql_query($insert_qry);

			array_push($manual_process_it_id,$manual_data['s'.$sort]);

			$sort++;
		}

		if(!in_array($data['it_id'],$manual_process_it_id)){

			$insert_qry = "INSERT INTO yc4_event_item (ev_id, it_id, sort) VALUES ('".${'ev_id'.$s_cate['s_id']}."', '$data[it_id]', '$sort')";
			mysql_query($insert_qry);

			$sort++;

		}
	}
}

/* 관별 만원의 행복 베스트 상품 처리 끝 */

echo "<script>alert('업데이트 되었습니다.')</script>";
