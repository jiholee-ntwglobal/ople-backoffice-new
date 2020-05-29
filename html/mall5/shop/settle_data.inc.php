<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-04-28
 * Time: 오전 9:14
 */

// 결제방법
$settle_case = $od['od_settle_case'];


// 총 결제금액
$receipt_amount = $od['od_receipt_bank']
    + $od['od_receipt_card']
    + $od['od_receipt_point']
    - $od['od_cancel_card']
    - $od['od_refund_amount'];

// 입금해야 할 금액이 있는지 여부
$misu = true;

if($s_page == "orderinquiryview.php") { // 주문 내역 조회 페이지에서만 미수금을 계산( 결제 페이지에서는 무조건 미수금이 있음)
    if ($_MASTER_CARD_EVENT) {
        $tot_amount_result = $od[od_temp_bank]
            + $od[od_temp_card]
            + $od[od_temp_point];

        if ($tot_amount_result - $tot_cancel_amount == $receipt_amount) {
            $wanbul = " (완불)";
            $misu = false; // 미수금 없음
        }
    } else {
        if ($tot_amount - $tot_cancel_amount == $receipt_amount) {
            $wanbul = " (완불)";
            $misu = false; // 미수금 없음
        }
    }

    $misu_amount = $tot_amount - $tot_cancel_amount - $receipt_amount;
}



$od_temp_amount = $settle_amount = $x_amount = 0;
// 미수금이 있을 경우에만 결제 할 금액을 로드
if($misu) {
    $od_temp_amount = $settle_case == '신용카드' ? $od['od_temp_card'] : $od['od_temp_bank'];
    $settle_amount = $od_temp_amount - $od['od_dc_amount']; // 결제 예정 금액
    $x_amount = usd_convert($settle_amount); // 달러 결제 금액

}