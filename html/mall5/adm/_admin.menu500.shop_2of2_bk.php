<?
$menu["menu500"] = array (
    array("", "쇼핑몰현황/기타", ""),
    array("500100", "상품판매순위", "$g4[shop_admin_path]/itemsellrank.php"),
    array("500110", "매출현황", "$g4[shop_admin_path]/sale1.php"),
    array("500120", "주문내역출력", "$g4[shop_admin_path]/orderprint.php"),
    array("500130", "전자결제내역", "$g4[shop_admin_path]/ordercardhistory.php"),

	// 김선용 2014.04 :
    array("500131", "KCP-가상계좌확인 ", "$g4[shop_admin_path]/order-kcp-vbank-dif.php"),

	// 김선용 201107 :
	array("500135", "가상계좌입금통보", "$g4[shop_admin_path]/order_gsmpg_receive.php"),
	// 김선용 201107 :
	array("500137", "상품코드관리", "$g4[shop_admin_path]/itemcode_manager.php"),
    array("500140", "보관함현황", "$g4[shop_admin_path]/wishlist.php"),
	// 김선용 200908 :
	array("500150", "서버정보", "{$g4['shop_admin_path']}/state_server_information.php"),
	array("500190", "상품입고 SMS관리", "$g4[shop_admin_path]/item_sms_list.php"),
	// 김선용 201210 :
	array("500191", "추천인리포트", "$g4[shop_admin_path]/order_recommend_list.php"),
	array("-"),
	// 김선용 201309 :
	array("500300", "회원 프로모션관리", "$g4[shop_admin_path]/member_promo_manage.php"),

	array("-"),
    //array("500200", "SMS 문자전송", "$g4[shop_admin_path]/smssend.php"),
    array("500210", "SMS 문자전송", "$g4[shop_admin_path]/smssend_new.php"),
    array("500210", "가격비교사이트", "$g4[shop_admin_path]/price.php")
);
?>