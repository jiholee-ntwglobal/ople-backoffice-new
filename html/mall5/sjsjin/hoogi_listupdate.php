<?php
include_once("./_common.php");


// 김선용 201208 : 권한처리 : [400650] 사용후기
// [au_menu] => 400650
// [au_auth] => r,w
$is_auth = false;
$au_sql = sql_query("select au_menu, au_auth from $g4[auth_table] where mb_id='{$member['mb_id']}' ");
while($au=sql_fetch_array($au_sql)){
	if($au['au_menu'] == '400650' && strpos($au['au_auth'], 'w') !== false) {
		$is_auth = true;
		break;
	}
}
if($is_admin === 'super') $is_auth = true;
if(!$is_auth) alert("권한이 없습니다.");

for($k=0; $k<count($_POST['chk']); $k++)
{
    $photo_review = false;
	$a = $_POST['chk'][$k];
	if($_POST['is_id'][$a]){
		sql_query("update {$g4['yc4_item_ps_table']} set is_best=1 where is_id='{$_POST['is_id'][$a]}' ");

		# 해당 상품의 제품코드,제품명 로드 2014-05-19 홍민기 #
		$item_info = sql_fetch("
			select 
				a.it_id,a.it_name,
				b.od_id,
				b.is_image0,b.is_image1,b.is_image2,b.is_image3,b.is_image4,b.is_point
			from 
				".$g4['yc4_item_table']." a
				left join
				".$g4['yc4_item_ps_table']." b on a.it_id = b.it_id
				where 
					b.is_id = '".$_POST['is_id'][$a]."'
		");
		if($item_info['it_name']){
			$item_info['it_name'] = get_item_name($item_info['it_name']);
		}


		if($item_info['od_id']){// 주문서 정보가 있으면 주문서 구매금액에 따라 적립 포인트가 달라진다 2014-07-23 홍민기
			# 주문서 정보
			$order_info = sql_fetch("select od_receipt_bank + od_receipt_card + od_receipt_point as amount, on_uid from ".$g4['yc4_order_table']." where od_id = '".$item_info['od_id']."'");
			
			# 포토 후기일 경우 포토후기 작성으로 인한 포인트 반환
			if(
				$item_info['is_image0'] ||
				$item_info['is_image1'] ||
				$item_info['is_image2'] ||
				$item_info['is_image3'] ||
				$item_info['is_image4'] 
			){
				$photo_review = true;	
			}

			// 해당 주문서의 총 결제 금액이 만원 이하일 경우에는 상품 구매가의 5%만 적립
			if($order_info['amount'] <= 10000){
				# 해당 주문서의 장바구니속 해당 상품 정보
				$cart_info = sql_fetch("select ct_amount from ".$g4['yc4_cart_table']." where on_uid = '".$order_info['on_uid']."'");

				if($photo_review){
					$point = $default['de_it_use_best_postpoint'] - ($cart_info['amount'] * 0.05 ); // 베스트 후기 포인트 - 상품가의 0.5% (포토후기 작성시 적립받은 금액은 제외)
				}else{
					$point = $default['de_it_use_best_postpoint'] - 200;
				}

			}else{ // 만원 이상일 경우 2000포인트 적립(관리자설정값에 따름)
				$point = $default['de_it_use_best_postpoint'];

				if($photo_review){
					$point = $default['de_it_use_best_postpoint'] - 500; // 포토 후기로 이미 적립받은 금액은 제외
				}else{
					$point = $default['de_it_use_best_postpoint'] - 200;
				}
			}
		}else{// 주문서 데이터가 없는 후기는 2000포인트 적립(관리자설정값에 따름)
			$point = $default['de_it_use_best_postpoint'];
		}

        if($item_info['is_point'] > 0){ // 지급된 포인트가 저장되었다면 2000-지급포인트를 적립
            if($default['de_it_use_best_postpoint'] - $item_info['is_point'] > 0) {
                $point = $default['de_it_use_best_postpoint'] - $item_info['is_point'];
            }
        }
		
		/*
		if($default['de_it_use_best_postpoint'] && $_POST['mb_id'][$a]) // 회원, 지급포인트가 있으면 지급
			insert_point($_POST['mb_id'][$a], $default['de_it_use_best_postpoint'], "구매후기 베스트선정 포인트지급 - (".$item_info['it_id']."-".$item_info['it_name'].")", "@ituse_best", $_POST['mb_id'][$a], time());
		*/
		if($default['de_it_use_best_postpoint'] && $_POST['mb_id'][$a]) // 회원, 지급포인트가 있으면 지급
			
			insert_point2($_POST['mb_id'][$a], $point, "구매후기 베스트선정 포인트지급 - (".$item_info['it_id']."-".$item_info['it_name'].")", "@ituse_best", $_POST['mb_id'][$a], time());
	}
}

$qstr = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=".urlencode($search)."&page=$page";
goto_url("hoogi_list.php?$qstr");
?>