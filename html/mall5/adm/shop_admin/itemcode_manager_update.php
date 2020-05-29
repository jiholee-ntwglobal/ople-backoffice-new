<?php
// 김선용 2010 :
$sub_menu = "300960";
include_once("./_common.php");
if(!$_POST['auto_fg']) {
	auth_check($auth[$sub_menu], "w");
}

include_once $g4['full_path'] . '/lib/ople_mapping.php';
$ople_mapping = new ople_mapping();

$search = urlencode(htmlspecialchars(stripslashes($search)));

$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=".stripslashes($search);
$qstr  = "$qstr1&page=$page";

$conn_id = @ftp_connect($g4['front_ftp_server']);
$login_result = @ftp_login($conn_id, $g4['front_ftp_user_name'], $g4['front_ftp_user_pass']);

//include_once $g4['full_path']."/lib/opk_db.php";
//$opk_db = new opk_db;

include_once $g4['full_path']."/lib/open_db.php";
$open_db = new open_db;

// new db library 2017-11-27 강경인
include_once $g4['full_path'] . '/lib/new_db.lib.php';
$new_db = new new_db;
if(!isset($new_opk_db)){
	$new_opk_db = $new_db->init_db('new_opk');
}
if(!isset($st11_db)){
	$st11_db = $new_db->init_db('st11');
}


$max = sql_fetch("select max(it_id) as max_id from {$g4['yc4_item_table']} ");
$max_id = $max['max_id'] + 1000;
for ($i=0; $i<count($_POST['chk']); $i++)
{

	$a = $_POST['chk'][$i];
	

	$sku = sql_fetch("select SKU from ".$g4['yc4_item_table']." where it_id = '".$_POST['it_id'][$a]."'");
	$sku = $sku['SKU'];

	// 오픈마켓 관리자 업데이트 처리 2015-09-18 홍민기
	$open_db->sql_query("update auction_mapping set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	$open_db->sql_query("update gmarket_mapping set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	$open_db->sql_query("update yc4_cart set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	$open_db->sql_query("update open_market_order_item set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	$open_db->sql_query("update yc4_item set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	$open_db->sql_query("update openmarket_no_item set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");

//	// 오플코리아 DATA Update -- 사용안함 -- 11번가 데이터로 변경
//	$opk_db->query("update {$g4['yc4_item_table']} set it_id='$max_id' where it_id='{$_POST[it_id][$a]}'");
//	$opk_db->query("update {$g4['yc4_cart_table']} set it_id='$max_id' where it_id='{$_POST[it_id][$a]}' ");
//	$opk_db->query("update {$g4['yc4_item_qa_table']} set it_id='$max_id' where it_id='{$_POST[it_id][$a]}' ");
//	$opk_db->query("update {$g4['yc4_item_ps_table']} set it_id='$max_id' where it_id='{$_POST[it_id][$a]}' ");
//	$opk_db->query("update {$g4['yc4_event_item_table']} set it_id='$max_id' where it_id='{$_POST[it_id][$a]}' ");
//	$opk_db->query("update {$g4['yc4_item_relation_table']} set it_id='$max_id', it_id2='$max_id' where it_id='{$_POST[it_id][$a]}' ");
//	$opk_db->query("update {$g4['yc4_wish_table']} set it_id='$max_id' where it_id='{$_POST[it_id][$a]}' ");
//	$opk_db->query("update yc4_category_item set it_id = '$max_id' where it_id='{$_POST[it_id][$a]}'");
//	$opk_db->query("update yc4_add_item_sms set it_id = '$max_id' where it_id='{$_POST[it_id][$a]}'");
//	$opk_db->query("update yc4_item_onrequest set it_id = '$max_id' where it_id='{$_POST[it_id][$a]}'");
//	$opk_db->query("update yc4_best_item set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
//	$opk_db->query("update yc4_best_item_no set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");

	// 11번가 상품 및 매핑 관련 처리 2017-11-27 강경인
	$st11_db->query("UPDATE yc4_item SET it_id = '".$max_id."' WHERE it_id = '".$_POST['it_id'][$a]."'");
	$st11_db->query("UPDATE yc4_category_item SET it_id = '".$max_id."' WHERE it_id = '".$_POST['it_id'][$a]."'");
	$st11_db->query("UPDATE ople_item_etc_info SET it_id = '".$max_id."' WHERE it_id = '".$_POST['it_id'][$a]."'");
	$st11_db->query("UPDATE ople_mapping SET it_id = '".$max_id."' WHERE it_id = '".$_POST['it_id'][$a]."'");
	$st11_db->query("UPDATE product_mapping_detail SET it_id = '".$max_id."' WHERE it_id = '".$_POST['it_id'][$a]."'");

	// 신규 오플코리아 상품 관련 처리 2017-11-27 강경인
	$new_opk_db->query("UPDATE oc_product SET sku = '".$max_id."' WHERE sku = '".$_POST['it_id'][$a]."'");

	// Ople data Update
	// 상품
	sql_query("update {$g4['yc4_item_table']} set it_id='$max_id' where it_id='{$_POST[it_id][$a]}' ");
	// 장바구니
	sql_query("update {$g4['yc4_cart_table']} set it_id='$max_id' where it_id='{$_POST[it_id][$a]}' ");
	// 상품문의
	sql_query("update {$g4['yc4_item_qa_table']} set it_id='$max_id' where it_id='{$_POST[it_id][$a]}' ");
	// 사용후기
	sql_query("update {$g4['yc4_item_ps_table']} set it_id='$max_id' where it_id='{$_POST[it_id][$a]}' ");
	// 이벤트상품
	sql_query("update {$g4['yc4_event_item_table']} set it_id='$max_id' where it_id='{$_POST[it_id][$a]}' ");
	// 관련상품
	sql_query("update {$g4['yc4_item_relation_table']} set it_id='$max_id', it_id2='$max_id' where it_id='{$_POST[it_id][$a]}' ");
	// 관심상품
	sql_query("update {$g4['yc4_wish_table']} set it_id='$max_id' where it_id='{$_POST[it_id][$a]}' ");
	// category_item
	sql_query("update yc4_category_item set it_id = '$max_id' where it_id='{$_POST[it_id][$a]}'");
	// master_card_no_item
	sql_query("update master_card_no_item set it_id = '$max_id' where it_id='{$_POST[it_id][$a]}'");
	// sms 알림신청
	sql_query("update yc4_add_item_sms set it_id = '$max_id' where it_id='{$_POST[it_id][$a]}'");
	sql_query("update yc4_item_onrequest set it_id = '$max_id' where it_id='{$_POST[it_id][$a]}'");
	// 비가공곡물
	sql_query("update yc4_item_weight set it_id = '$max_id' where it_id='{$_POST[it_id][$a]}'");
	// 굿데이 세일
	sql_query("update yc4_oneday_sale_item set it_id = '$max_id', l_it_id = '".$max_id."' where it_id='{$_POST[it_id][$a]}'");
	// 오버스탁 아이템
	sql_query("update yc4_over_stock_item set it_id = '$max_id' where it_id='{$_POST[it_id][$a]}'");
	sql_query("update yc4_over_stock_item_cart set it_id = '$max_id' where it_id='{$_POST[it_id][$a]}'");
	// 품절 히스토리
	sql_query("update yc4_soldout_history set it_id = '$max_id' where it_id='{$_POST[it_id][$a]}'");
	// 별도재고관리
	sql_query("update yc4_event_item_stock set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	// 네이버 지식쇼핑
	sql_query("update naver_ep_all set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	sql_query("update naver_ep_brief set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	// 상품 상세페이지 메세지
	sql_query("update yc4_item_tongwan set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	// 메인 진열상품
	sql_query("update yc4_main_item set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	// 베스트 상품
	sql_query("update yc4_best_item set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	sql_query("update `yc4_best_item_150` set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	sql_query("update `yc4_best_item_manual` set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	sql_query("update yc4_best_item_no set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	// 아이해피 상품문의
	sql_query("update g4_write_iqa set `wr_1` = '".$max_id."' where `wr_1` = '".$_POST['it_id'][$a]."'");
	// 아이해피 판매량
	sql_query("update ihappy_sales set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	// 만원의 행복 수동 설정
	sql_query("update manwon_event_manual set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	// 네이버 지식쇼핑 전체 ep
	sql_query("update naver_ep_all set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	// 네이버 지식쇼핑 요약 ep
	sql_query("update naver_ep_brief set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	// 논슨톱세일
	sql_query("update yc4_nontop_sale set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	# sales Data #
	sql_query("update sales.op_sales_item set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	sql_query("update sales.op_sales_report set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	# 클리어런스 #
	sql_query("update yc4_clearance_item set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	sql_query("update yc4_clearance_item set ori_it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	# 핫딜 #
	sql_query("update yc4_hotdeal_item set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	# 신상품 #
	sql_query("update yc4_item_new set type_value = '".$max_id."' where type_value = '".$_POST['it_id'][$a]."' and type='I'");
	# 메인 베스트 #
	sql_query("update yc4_station_main_best_item set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	# 관별 메인
	sql_query("update yc4_station_main_item set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	# 아이해피 사은품 #
	sql_query("update yc4_ihappy_event_gift_item set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	sql_query("update yc4_ihappy_event_gift_item set gift_it_id = '".$max_id."' where gift_it_id = '".$_POST['it_id'][$a]."'");
	# 아이해피 첫구매 사은품
	sql_query("update yc4_ihappy_first_buy_gift_item set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	# 아이해피 맥포머스 사은품
	sql_query("update yc4_ihappy_magformers_event set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	# 상품 가격 히스토리
	sql_query("update yc4_item_amount_history set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	# 달러 수동 가격 #
	sql_query("update yc4_item_amount_manual set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	# 달러가격 히스토리
	sql_query("update yc4_item_amount_usd_history set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	# 기타 가격
	sql_query("update yc4_item_etc_amount set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	# 세트 상품
	sql_query("update yc4_item_set set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	sql_query("update yc4_item_set set child_it_id = '".$max_id."' where child_it_id = '".$_POST['it_id'][$a]."'");
	# 기획전 상품
	sql_query("update yc4_promotion_item set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	sql_query("update yc4_promotion_item_dc set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");
	sql_query("update yc4_promotion_item_dc_cache set it_id = '".$max_id."' where it_id = '".$_POST['it_id'][$a]."'");

	// 오플코리아 제외 상품 테이블
	sql_query("update yc4_opk_no_item set it_id = '$max_id' where it_id='{$_POST[it_id][$a]}'");
	/*// 구매금액별 이벤트
	sql_query("update yc4_free_gift_event_item set it_id = '$max_id' where it_id='{$_POST[it_id][$a]}'");
	sql_query("update yc4_free_gift_event_order_history set it_id = '$max_id' where it_id='{$_POST[it_id][$a]}'");*/
	// 히스토리 저장
	sql_query("insert into yc4_it_id_change_history (it_id,bf_it_id,sku,dt) values( '".$max_id."','".$_POST['it_id'][$a]."','".$sku."',now() )");

//    // 인트라넷 상품별 판매 데이터 상품코드 업데이트 TODO 사용여부 확인 및 변경처리
//    file_get_contents("http://112.220.193.26/cron/ople_it_id_change.php?ori_it_id=".$_POST['it_id'][$a]."&new_it_id=".$max_id);

// 관리자 서버 이미지 동기화 중지로 로직 변경 2018-05-21 강경인
//	// 김선용 201205 : 상품이미지명 변경
//	$temp_dir = dir("{$g4['path']}/data/item");
//	while($entry = $temp_dir->read()){
//		if(preg_match("/^{$_POST['it_id'][$a]}*/", $entry)){
//			$rename = preg_replace("/^{$_POST['it_id'][$a]}*/", $max_id, $entry);
//			rename("{$g4['path']}/data/item/$entry", "{$g4['path']}/data/item/{$rename}");
//            @ftp_rename($conn_id,'/ssd/html/mall5/data/item/'.$entry,'/ssd/html/mall5/data/item/'.$rename); // 프론트 서버 이미지 파일명 변경
//			//echo "엔트리 : {$g4['path']}/data/item/$entry <br/>";
//			//echo "MAX ID : {$g4['path']}/data/item/$max_id <BR/>";
//			//echo "변환 : ".preg_replace("/^{$_POST['it_id'][$a]}*/", $max_id, $entry);
//			//echo "<BR/>";
//		}
//	}
	
	// 프론트 서버 이미지 파일명 변경 2018-05-21 강경인
	$img_files	= array(
		$_POST['it_id'][$a]."_l1" => $max_id."_l1"
	,	$_POST['it_id'][$a]."_m"  => $max_id."_m"
	,	$_POST['it_id'][$a]."_s"  => $max_id."_s"
	);
	foreach($img_files as $old_img => $new_img){
		if(ftp_size($conn_id, '/ssd/html/mall5/data/item/'.$old_img) != -1){
			@ftp_rename($conn_id,'/ssd/html/mall5/data/item/'.$old_img,'/ssd/html/mall5/data/item/'.$new_img);
		}
	}
	
	// 이미지 ftp 서버 이미지 복사(원본파일 삭제 안함) 2014-05-23 홍민기
	//file_get_contents("http://216.74.54.38/ftp/index.php?bf_file_name=".$_POST['it_id'][$a].".jpg&new_file_name=".$max_id.".jpg");

	# 매핑 변경 처리 2015-12-24 홍민기
	$ople_mapping->it_id_change($_POST['it_id'][$a],$max_id);
	$max_id++;
}

@ftp_close($conn_id);

//exit;
goto_url("itemcode_manager.php?$qstr");
