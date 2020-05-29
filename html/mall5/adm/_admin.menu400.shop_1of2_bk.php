<?
// 김선용 201107 : root 계정은 쇼핑몰관리 인덱스페이지 막음
if($member['mb_id'] == 'root')
    $index_href = "";
else
    $index_str = "$g4[shop_admin_path]/";

$menu["menu400"] = array (
    array("", "쇼핑몰관리", $index_str),
    array("400100", "쇼핑몰설정", "$g4[shop_admin_path]/configform.php"),
	array("400101", "확장설정", "$g4[shop_admin_path]/configform_extension.php"),
    array("-"),
    array("400200", "분류관리", "$g4[shop_admin_path]/categorylist.php"),
    array("400300", "상품관리", "$g4[shop_admin_path]/itemlist.php"),
	array("400301", "목록통관상품관리", "$g4[shop_admin_path]/list_clearance_item.php"),
	array("400310", "1회구매수량관리", "$g4[shop_admin_path]/item_order_onetime_limit_list.php"), // 김선용 201208 :
	array("400311", "상품병수량관리", "$g4[shop_admin_path]/item_bottle_list.php"), // 김선용 201210 :
	array("400312", "품절상품관리", "$g4[shop_admin_path]/item_soldout_list.php"), // 김선용 201211 :
	array("400315", "품절상품관리2014", "$g4[shop_admin_path]/item_stock_in.php"), // 홍민기 2014-08-19 :
	array("400317", "상품품절처리", "$g4[shop_admin_path]/item_stock_out.php"), // 홍민기 2015-01-13 :
	array("400316", "품절히스토리", "$g4[shop_admin_path]/item_stock_history.php"), // 홍민기 2014-08-19 :
	array("400313", "단종상품관리", "$g4[shop_admin_path]/item_discont_list.php"), // 김선용 201211 :
	array("400314", "상품입고요청관리", "$g4[shop_admin_path]/item_onrequest_list.php"), // 김선용 201211 :
	array("-"),
    array("400400", "주문관리", "$g4[shop_admin_path]/orderlist.php"),
    array("400410", "주문개별관리", "$g4[shop_admin_path]/orderstatuslist.php"),
    array("400420", "주문통합관리", "$g4[shop_admin_path]/orderlist2.php"),
	array("400430", "전액포인트결제주문", "$g4[shop_admin_path]/orderlist_point.php"),
    array("400500", "배송일괄처리", "$g4[shop_admin_path]/deliverylist.php"),
	array("-"),
	// 김선용 200805
    /*
	array("400501", "<span style='color:#FF6600;'>요오드 예약관리</span>", "$g4[shop_admin_path]/rs_iodine_list.php"),
	*/
	array("400510", "<span style='color:#FF6600;'>엑셀배송처리</span>", "$g4[shop_admin_path]/deliverylist_excel.php"),
	array("400511", "<span style='color:#FF6600;'>제조사 설명관리</span>", "$g4[shop_admin_path]/item_maker_description.php"),
	/*
	array("400512", "<span style='color:#FF6600;'>사은품관리</span>", "$g4[shop_admin_path]/itemgift.php"),
	array("-"),
    array("400600", "온라인견적", "$g4[shop_admin_path]/onlinecalclist.php"),
	*/
    array("400610", "상품유형관리", "$g4[shop_admin_path]/itemtypelist.php"),
    array("400620", "상품재고관리", "$g4[shop_admin_path]/itemstocklist.php"),
    array("400680", "이벤트일괄처리신)", "$g4[shop_admin_path]/itemeventlist_new.php"),
    array("400630", "이벤트관리", "$g4[shop_admin_path]/itemevent.php"),
    array("400640", "이벤트일괄처리", "$g4[shop_admin_path]/itemeventlist.php"),
    array("400650", "사용후기", "$g4[shop_admin_path]/itempslist.php"),
    array("400660", "상품문의", "$g4[shop_admin_path]/itemqalist.php"),
	array("-"),
	/*
	array("400670", "1:1문의", "$g4[shop_admin_path]/personal_qa.php"),

    array("400700", "내용관리", "$g4[shop_admin_path]/contentlist.php"),
	array("400710", "FAQ 관리", "$g4[shop_admin_path]/faqmasterlist.php"),
    array("400720", "새창관리", "$g4[shop_admin_path]/newwinlist.php"),
    array("400730", "배너관리", "$g4[shop_admin_path]/bannerlist.php"),

    array("400740", "배송회사관리", "$g4[shop_admin_path]/deliverycodelist.php"),
	*/
	array("-"),
	array("400800", "원데이이벤트설정", "$g4[shop_admin_path]/oneday.php"),
	array("400810", "원데이이벤트 SMS", "$g4[shop_admin_path]/oneday_sms.php"),
	array("400820", "구매금액별 이벤트관리", "$g4[shop_admin_path]/gift_event.php"),
	array("400830", "오버스탁 상품 이벤트", "$g4[shop_admin_path]/over_stock.php"),
	array("400920", "베스트 상품 수동 등록", "$g4[shop_admin_path]/best_item_reg.php"),
	array("400930", "별도 재고관리 상품", "$g4[shop_admin_path]/event_item_stock.php"),
	array("400970", "클리어런스 상품", "$g4[shop_admin_path]/clearance_list.php"),
	/*
	array("400950", "블랙프라이데이 판매량 확인", "$g4[shop_admin_path]/bl_item_stock.php"),
	*/
	array("400940", "논스톱이벤트관리", "$g4[shop_admin_path]/nonstop_event.php"),
	array("-"),
	array("400900", "전화요청 관리", "$g4[shop_admin_path]/call.php"),
	array("400910", "배송조회", "$g4[shop_admin_path]/delivery.php"),
	array("-"),


	array("400940", "오플코리아 미등록상품관리", "$g4[shop_admin_path]/opk_no_item.php"),

	array("400950", "만원의 행복 이벤트 순서 수동변경", "$g4[shop_admin_path]/manwon_event_manual_list.php"),
	array("400960", "브랜드 로고관리", "$g4[shop_admin_path]/brand_logo.php"),
	array("400990", "아이해피 상품 수동등록", "$g4[shop_admin_path]/ihappy_item_manual_reg.php"),

	array("-"),
	array("400991", "추천인 랭킹", "$g4[shop_admin_path]/recom_ranking.php"),
	array("400992", "아이허브 가격 수집 상품 관리", "$g4[shop_admin_path]/iherb_price_manager.php"),
	array("400995", "무통장입금자 일괄 입금확인처리", "$g4[shop_admin_path]/deposit_manager.php"),
);
?>