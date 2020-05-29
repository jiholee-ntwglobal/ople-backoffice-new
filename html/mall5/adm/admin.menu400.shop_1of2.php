<?
// 김선용 201107 : root 계정은 쇼핑몰관리 인덱스페이지 막음
if($member['mb_id'] == 'root')
    $index_href = "";
else
    $index_str = "$g4[shop_admin_path]/";

$menu["menu400"] = array (
    array("400000", "주문 관리", ""),   
    array("400400", "주문관리", "$g4[shop_admin_path]/orderlist.php"),
    array("400410", "주문개별관리", "$g4[shop_admin_path]/orderstatuslist.php"),
    array("400420", "주문통합관리", "$g4[shop_admin_path]/orderlist2.php"),
	array("400430", "전액포인트결제주문", "$g4[shop_admin_path]/orderlist_point.php"),
    array("400431", "전액포인트결제자동처리주문", "$g4[shop_admin_path]/orderlist_auto_point.php"),
    array("400500", "배송일괄처리", "$g4[shop_admin_path]/deliverylist.php"),	
	array("400510", "<span style='color:#FF6600;'>엑셀배송처리</span>", "$g4[shop_admin_path]/deliverylist_excel.php"),
	array("400910", "배송조회", "$g4[shop_admin_path]/delivery.php"),
	array("400995", "무통장입금자 일괄 입금확인처리", "$g4[shop_admin_path]/deposit_manager.php"),	
    array("400700", "주문내역출력", "$g4[shop_admin_path]/orderprint.php"),
    array("400800", "전자결제내역", "$g4[shop_admin_path]/ordercardhistory.php"),
    array("400820", "KCP-가상계좌확인 ", "$g4[shop_admin_path]/order-kcp-vbank-dif.php"),
    array("400900", "가상계좌입금통보", "$g4[shop_admin_path]/order_gsmpg_receive.php"),
    /*array("400910", "오픈마켓 주문처리", "$g4[shop_admin_path]/open_market_order_insert.php"),
    array("400920", "오픈마켓 배송처리", "$g4[shop_admin_path]/open_market_order_delivery.php"),*/
	array('400940', '출석체크 이벤트 참여정보 확인', $g4['shop_admin_path'].'/attendance_event.php'),
	array('400950', '이상 결제 시도 내역', $g4['shop_admin_path'].'/payment_request_cnt.php'),
	array('400901', '주문 배송 데이터 등록', $g4['shop_admin_path'].'/order_ship_data.php'),
    array('400124', '강자닷컴 주문서 관리', $g4['shop_admin_path'].'/gangja_order_list.php'),
    array('400123', '강자닷컴 상품 매핑', $g4['shop_admin_path'].'/gangja_mapping_list.php'),
    array('400960', '엑셀 주문일괄등록', $g4['shop_admin_path'].'/excel_order_upload.php'),
);
?>