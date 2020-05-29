<?php
//==============================================================================
// 쇼핑몰 상수, 변수
//==============================================================================


//------------------------------------------------------------------------------
// 쇼핑몰 상수 모음 시작
//------------------------------------------------------------------------------
// 미수금에 대한 QUERY 문
// 테이블 a 는 주문서 ($g4[yc4_order_table])
// 테이블 b 는 장바구니 ($g4[yc4_cart_table])
define(_MISU_QUERY_, "
    count(distinct a.od_id) as ordercount, /* 주문서건수 */
    count(b.ct_id) as itemcount, /* 상품건수 */
    (SUM(b.ct_amount * b.ct_qty) + a.od_send_cost) as orderamount , /* 주문합계 */
    (SUM(IF(b.ct_status = '취소' OR b.ct_status = '반품' OR b.ct_status = '품절', b.ct_amount * b.ct_qty, 0))) as ordercancel, /* 주문취소 */
    (a.od_receipt_bank + a.od_receipt_card + a.od_receipt_point + a.od_recommend_off_sale) as receiptamount, /* 입금합계 : // 김선용 201210 : 추천인할인포함 */
    (a.od_refund_amount + a.od_cancel_card) as receiptcancel, /* 입금취소 */
    (
        (SUM(b.ct_amount * b.ct_qty) + a.od_send_cost) -
        (SUM(IF(b.ct_status = '취소' OR b.ct_status = '반품' OR b.ct_status = '품절', b.ct_amount * b.ct_qty, 0))) -
        a.od_dc_amount -
        (a.od_receipt_bank + a.od_receipt_card + a.od_receipt_point + a.od_recommend_off_sale) +
        (a.od_refund_amount + a.od_cancel_card)
    ) as misu /* 미수금 = 주문합계 - 주문취소 - DC - 입금합계 + 입금취소 */");
//------------------------------------------------------------------------------
// 쇼핑몰 상수 모음 끝
//------------------------------------------------------------------------------



//------------------------------------------------------------------------------
// 쇼핑몰 변수 모음 시작
//------------------------------------------------------------------------------
// 쇼핑몰 디렉토리
$g4['shop']           = 'shop';
$g4['shop_path']      = $g4['path'].'/'.$g4['shop'];
$g4['shop_url']       = $g4['url'].'/'.$g4['shop'];

$g4['shop_admin']     = 'shop_admin';
$g4['shop_admin_path']= $g4['path'].'/'.$g4['admin'].'/'.$g4['shop_admin'];
$g4['shop_admin_url'] = $g4['url'].'/'.$g4['admin'].'/'.$g4['shop_admin'];

$g4['shop_img']       = 'img';
$g4['shop_img_path']  = $g4['path'].'/'.$g4['shop'].'/'.$g4['shop_img'];
$g4['shop_img_url']   = $g4['url'].'/'.$g4['shop'].'/'.$g4['shop_img'];


// 쇼핑몰 테이블명
$g4['yc4_default_table']       = 'yc4_default';
$g4['yc4_banner_table']        = 'yc4_banner';
$g4['yc4_card_history_table']  = 'yc4_card_history';
$g4['yc4_cart_table']          = 'yc4_cart';
$g4['yc4_category_table']      = 'yc4_category_new';
$g4['yc4_content_table']       = 'yc4_content';
$g4['yc4_delivery_table']      = 'yc4_delivery';
$g4['yc4_event_table']         = 'yc4_event';
$g4['yc4_event_item_table']    = 'yc4_event_item';
$g4['yc4_faq_table']           = 'yc4_faq';
$g4['yc4_faq_master_table']    = 'yc4_faq_master';
$g4['yc4_item_table']          = 'yc4_item';
$g4['yc4_item_ps_table']       = 'yc4_item_ps';
$g4['yc4_item_qa_table']       = 'yc4_item_qa';
$g4['yc4_item_relation_table'] = 'yc4_item_relation';
$g4['yc4_new_win_table']       = 'yc4_new_win';
$g4['yc4_onlinecalc_table']    = 'yc4_onlinecalc';
$g4['yc4_order_table']         = 'yc4_order';
$g4['yc4_wish_table']          = 'yc4_wish';
$g4['yc4_on_uid_table']        = 'yc4_on_uid';

// 김선용 200909 :
$g4['yc4_order_ship']	= 'yc4_order_ship';
$g4['yc4_cart_ship']	= 'yc4_cart_ship';
// 김선용 201103 :
$g4['yc4_rs_table'] = 'yc4_reser_iodine';

// 신용카드결제대행사 URL
$g4['yc4_cardpg']['kcp']        = 'http://admin.kcp.co.kr';
$g4['yc4_cardpg']['banktown']   = 'http://ebiz.banktown.com/index.cs';
$g4['yc4_cardpg']['telec']      = 'http://www.ebizpro.co.kr';
$g4['yc4_cardpg']['inicis']     = 'https://iniweb.inicis.com/DefaultWebApp/index.html';
$g4['yc4_cardpg']['dacom']      = 'https://pgweb.dacom.net';
$g4['yc4_cardpg']['allthegate'] = 'http://www.allthegate.com/login/r_login.jsp';
$g4['yc4_cardpg']['allat']      = 'http://www.allatbiz.net/servlet/AllatBizSrvX/bizcon/jspx/login/login.jsp?next=/servlet/AllatBizSrvX/bizable/jspx/login/login.jsp';
$g4['yc4_cardpg']['tgcorp']     = 'https://npg.tgcorp.com/mdbop/login.jsp';
$g4['yc4_cardpg']['kspay']      = 'http://nims.ksnet.co.kr:7001/pg_infoc/src/login.jsp'; // ksnet
//------------------------------------------------------------------------------
// 쇼핑몰 변수 모음 끝
//------------------------------------------------------------------------------
?>