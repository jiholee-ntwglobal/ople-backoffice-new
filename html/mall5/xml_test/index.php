<?php
exit;
include "../dbconfig.php";

/*
	주문서(준비,취소) XML로 출력 
	Inventroy Module
	2014-04-30 홍민기

	yc4_order
		od_settle_case 결제방법
		od_receipt_bank 입금액
		od_receipt_card 카드결제금액
		od_receipt_point 포인트 결제 금액

		od_b_name 받는사람 이름
		od_b_hp 배송지 휴대폰번호
		od_b_addr1
		od_b_addr2
			= > 주소
	
		

*/



mysql_connect($mysql_host,$mysql_user,$mysql_password);
mysql_select_db($mysql_db);


$order_qry = "
	select 
		a.*,
		b.it_id,
		b.ct_amount,
		b.ct_qty,
		b.ct_status
	from 
		yc4_order a
		left join
		yc4_cart b on a.on_uid = b.on_uid
	where 
		(b.ct_status = '준비' or b.ct_status = '취소')
		and
		a.od_id not in (select o_code from order_matching)
		-- limit 500

";

# 주문서 정보 로드 시작 #
$order_infoQ = mysql_query($order_qry);


$xml = new SimpleXMLElement('<xml/>');

$data = $xml->addChild('data');
$order_cnt = 0;
$cancel_cnt = 0;
while($order_info = mysql_fetch_assoc($order_infoQ)){


	if($order_info['od_id'] != $bf_od['od_id'] && $bf_od['od_id']){
		

		if($bf_od['ct_status'] == '준비'){
			$item_wrap->addAttribute('cnt', $item_cnt);
		}else{
			$item_wrap->addAttribute('cnt', $cancel_item_cnt);
		}


		

	}

	if(!$od_id[$order_info['od_id']]){
		
		## $_GET['metching']이 존재해야 모든 코드 실행되게 수정되야함 ##
		if($_GET['metching']){

			# 매칭 테이블에 저장 안되면 다음으로 #
			if(!mysql_query("insert into order_matching (o_code,m_dt) values('".$order_info['od_id']."', '".date('Y-m-d h:i:s')."')")){
				continue;
			}
		}
		
		if($order_info['ct_status'] == '준비'){
			$row = $data->addChild('row');
			$order_cnt++;
		}else{
			$row = $data->addChild('cancel_row');
			$cancel_cnt++;
		}
		$od_id[$order_info['od_id']] = $order_info['od_id'];
		$bf_od = $order_info;

		$row->addAttribute('id', $order_info['od_id']); // 주문서 id
		$receipt = $order_info['od_receipt_bank'] + $order_info['od_receipt_card']; // 총 결제금액
		
		# 주문서 상세정보 #
		$od_info = $row->addChild('od_info'); 
		$case = $od_info->addChild('case', $order_info['od_settle_case']); // 결제방법
		$receipt = $od_info->addChild('receipt', $receipt);  // 결제금액
		$point = $od_info->addChild('point', $order_info['od_receipt_point']); // 포인트 결제금액
		$od_dt = $od_info->addChild('od_dt', $order_info['od_time']); // 주문일자
		$name = $od_info->addChild('name', $order_info['od_b_name']); // 주문자 성함
		$tel = $od_info->addChild('tel', preg_replace("[^0-9]",'',$order_info['od_b_hp'])); // 배송 휴대폰번호
		$addr = $od_info->addChild('addr', $order_info['od_b_addr1'].' '.$order_info['od_b_addr2']); // 배송 주소
		$memo = $od_info->addChild('memo', $order_info['od_memo']); // 배송 메세지


		$item_cnt = 0;
		$cancel_item_cnt = 0;

		$item_wrap = $row->addChild('item_wrap');
	}

	
	

	if($order_info['ct_status'] == '준비'){

		$item_cnt += $order_info['ct_qty'];
	}else{
		$cancel_item_cnt += $order_info['ct_qty'];

	}

	$item_row = $item_wrap->addChild('item_row');
	$it_id = $item_row->addChild('it_id',$order_info['it_id']); // 상품 코드
	$it_price = $item_row->addChild('it_price',$order_info['ct_amount'] * $order_info['ct_qty'] ); // 상품 가격
	$it_qty = $item_row->addChild('it_qty',$order_info['ct_qty']); // 상품 수량



	
	


	/*
	# 주문서 상품 로드 
	$od_item_qry = "
		select 
			it_id,
			ct_amount,
			ct_qty,
			ct_status
		from 
			yc4_cart 
		where on_uid = '".$order_info['on_uid']."'
	";
	$od_itemQ = mysql_query($od_item_qry);

//	print_r($order_info);
	$item_wrap = $row->addChild('item_wrap');
	
	while($od_item = mysql_fetch_assoc($od_itemQ)){

		$item_cnt += $od_item['ct_qty'];
		
		
		$item_row = $item_wrap->addChild('item_row');
		$it_id = $item_row->addChild('it_id',$od_item['it_id']); // 상품 코드
		$it_price = $item_row->addChild('it_price',$od_item['ct_amount'] * $od_item['ct_qty'] ); // 상품 가격
		$it_qty = $item_row->addChild('it_qty',$od_item['ct_qty']); // 상품 수량
//		print_r($od_item);
	}
	
	$item_wrap->addAttribute('cnt', $item_cnt);
	*/


	

}
$data->addAttribute('total_order_cnt', $order_cnt); // 총 주문서 갯수
$data->addAttribute('cancel_cnt', $cancel_cnt); // 총 주문취소건 갯수
# 주문서 정보 로드 끝 #


Header('Content-type: text/xml');
print($xml->asXML());

?>