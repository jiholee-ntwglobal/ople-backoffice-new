<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-04-27
 * Time: 오후 3:03
 */
$sub_menu = "400124";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");
include $g4['full_path'] . '/classes/PHPExcel.php';
// 엑셀 날짜 관련 function
function excel_date_format($time)
{
    return round((($time - 25569) * 86400 - 60 * 60 * 9) * 10) / 10;
}

//강자 닷컴 엑셀파일 업로드
$UpFile     = $_FILES['excel_file'];
$UpFileName = $UpFile['name'];

$UpFilePathInfo = pathinfo($UpFileName);
$UpFileExt      = strtolower($UpFilePathInfo['extension']);

//엑셀파일 확장자
if ($UpFileExt != 'xls' && $UpFileExt != 'xlsx') {
    alert('엑셀파일만 업로드 가능합니다. (xls, xlsx 확장자의 파일포멧)');
}

//업로드된 엑셀파일을 서버의 지정된 곳에 옮기기 위해 경로 적절히 설정
$upload_path = $g4['full_path'] . '/data/tmp';
$upfile_path = $upload_path . '/' . date('Ymd_His') . '_gangja';

//엑셀파일 확인
if (is_uploaded_file($UpFile['tmp_name'])) {
    // 엑셀파일 업로드
    if (!move_uploaded_file($UpFile["tmp_name"], $upfile_path)) {
        alert("업로드된 파일을 옮기는 중 에러가 발생했습니다.");
    }

//파일 타입 설정 (확자자에 따른 구분)
    $inputFileType = 'Excel2007';
    if ($UpFileExt == 'xls') {
        $inputFileType = 'Excel5';
    }

//엑셀리더 초기화
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);

//데이터만 읽기(서식을 모두 무시해서 속도 증가 시킴)
    $objReader->setReadDataOnly(true);

//업로드된 엑셀 파일 읽기
    $objPHPExcel = $objReader->load($upfile_path);

//첫번째 시트로 고정
    $objPHPExcel->setActiveSheetIndex(0);

//고정된 시트 로드
    $objWorksheet = $objPHPExcel->getActiveSheet();

//시트의 지정된 범위 데이터를 모두 읽어 배열로 저장
    $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
    $total_rows = count($sheetData);

    /*  엑셀 데이터
        [A]	=> SerialNum
        [B] => 주문번호
        [C] => 상품명
        [D] => 상품코드
        [E] => 수량
        [F] => 주문가격
        [G] => 배송비용
        [H] => 택배업체
        [I] => 송장번호
        [J] => 배송일시
        [K] => 주문자
        [L] => 이메일
        [M] => 전화번호
        [N] => 핸드폰번호
        [O] => 수령인
        [P] => 수령인전화
        [Q] => 수령인핸드폰
        [R] => 수령인메일
        [S] => 새우편번호
        [T] => 우편번호
        [U] => 주소
        [V] => 도로명주소
        [W] => 개인통관 고유번호
        [X] => 배송메세지
        [Y] => 주문일시
        [Z] => 결제일시
    */
//변수지정
    $i = 1; //첫번째 row
    $gangja_arr = array(); //데이버 저장
    $gangja_arr_it_amont = array(); // 데이터저장(결제금액)
    $excel_escape_str = array('"', ",", '.', ";",); //replace

    //엑셀파일 데이터 foreach
    foreach ($sheetData as $rows) {

        //첫번째 row 넘기기
        if ($i < 2) {
            $i++;
            continue;
        }
        //Seria Num
        if (!$rows['A']) {
            alert('Seria Num이 존재하지않는 주문 내역이있습니다');
        }
        //주문번호
        if (!$rows['B']) {
            alert('주문번호가 존재하지않는 주문 내역이있습니다');
        }
        //강자닷컴 상품코드
        if (!$rows['D']) {
            alert('강자닷컴 상품 코드가 존재하지않는 주문 내역이있습니다');
        }
        //수량
        if (!$rows['E']) {
            alert('수량이 존재하지않는 주문 내역이있습니다');
        }
        //가격
        if (!$rows['F']) {
            alert('주문가격이 존재하지않는 주문 내역이있습니다');
        }
        //배송비
        if (!$rows['G']) {
            alert('배송비용이 존재하지않는 주문 내역이있습니다 예) 5000원,0원');
        }
        //수령인
        if (!$rows['O']) {
            alert('수령인이 존재하지않는 주문 내역이있습니다');
        }
        //수령인 핸드폰
        if (!$rows['R']) {
            alert('수령인 핸드폰이 존재하지않는 주문 내역이있습니다');
        }
        //우편번호
        if (!$rows['S']) {
            alert('새 우편번호가 존재하지않는 주문 내역이있습니다');
        }
        //주소
        if (!$rows['V']) {
            alert('도로명주소가 존재하지않는 주문 내역이있습니다');
        }

        //gangja_mapping 매핑확인
        $gangja_it_id   =   trim(str_replace($excel_escape_str, '', $rows['D']));
        $gangja         =   sql_fetch("select it_id from gangja_mapping where gangja_it_id ='{$gangja_it_id}'");
        // gangja_mapping
        $it_id = trim($gangja['it_id']);
        if (!$it_id) {
            alert('오플 상품 코드와 맵핑이 되어있지않는 주문 내역이있습니다');
        }

        //yc4_item
        $it_id_fg = sql_fetch("select count(*) cnt from yc4_item where it_id ='{$it_id}'");
        //yc4_item에 데이터가 없으면 튕기기
        if ($it_id_fg['cnt'] <= 0) {
            alert('오플 상품 코드와 맵핑이 되어있지만 오플에서 존재하지않는 오플상품코드입니다 .', '');
        }

        //생성된 seria_num 인지 확인
        $seria_num = $rows['A'];
        //만들어진 주문 내역인지 확인
        $seria_num_fg = sql_fetch("select count(*) cnt from gangja_uid where uid ='{$seria_num}'");
        if ($seria_num_fg['cnt'] > 0) {
            continue;
        }

        //데이터생성
        $gangja_arr[$seria_num]['on_uid']        = $rows['B'];    // on_uid
        $gangja_arr[$seria_num]['gangja_it_id'] = $gangja_it_id; //강자닷컴 상품코드
        $gangja_arr[$seria_num]['it_id']          = $it_id;       //오플상품코드

        $gangja_arr[$seria_num]['qty']               = preg_replace("/[^0-9]*/s", "", $rows['E']);                                   //수량
        $gangja_arr[$seria_num]['it_amount']        = preg_replace("/[^0-9]*/s", "", $rows['F']) / $gangja_arr[$seria_num]['qty']; //상품 하나가격(원)
        $gangja_arr[$seria_num]['ct_amount_usd']   = usd_convert($gangja_arr[$seria_num]['it_amount']);                             //상품 하나 가격(달러)

        $gangja_arr[$seria_num]['od_send_cost']      = preg_replace("/[^0-9]*/s", "", $rows['G'])==''?'0': preg_replace("/[^0-9]*/s", "", $rows['G']);                //배송비
        $gangja_arr[$seria_num]['od_b_name']          = sql_safe_query(str_replace($excel_escape_str, "", $rows['O'])); //수령인
        $gangja_arr[$seria_num]['od_b_tel']           = sql_safe_query($rows['Q']);  //전화번호
        $gangja_arr[$seria_num]['od_b_hp']            = $gangja_arr[$seria_num]['od_b_tel']; //전화번호

        $od_zip = str_replace('-', '', $rows['S']); //새우편번호
        if (strlen($od_zip) == 4) { //4자리 아니면 0
            $od_zip = '0' . $od_zip;
        }
        $gangja_arr[$seria_num]['od_zonecode'] = $od_zip;
        $gangja_arr[$seria_num]['od_zip1']     = substr($od_zip, 0, 3);
        $gangja_arr[$seria_num]['od_zip2']     = substr($od_zip, 3, 3);

        $gangja_arr[$seria_num]['od_b_addr1']    = sql_safe_query(str_replace($excel_escape_str, "", $rows['V'])); //주소
        $gangja_arr[$seria_num]['od_memo']        = sql_safe_query(str_replace($excel_escape_str, "", $rows['X'])); //배송메모
        $gangja_arr[$seria_num]['od_b_jumin']    = preg_replace("/[^0-9a-zA-Z]/s", "", $rows['W']); //통관부호
        if (strlen($gangja_arr[$seria_num]['od_b_jumin']) != 13) {
            $gangja_arr[$seria_num]['od_b_jumin'] = 'M000000000000';
        }

        $gangja_arr[$seria_num]['od_pay_time']      = date('Y-m-d H:i:s', excel_date_format($rows['Y'])); //결제일시
        $gangja_arr[$seria_num]['od_bank_time']     = date('Y-m-d H:i:s', excel_date_format($rows['Y'])); // 주문일시

        if (!isset($gangja_arr_it_amont[$rows['B']])) { //해당 주문서에 상품금액 합산
            $gangja_arr_it_amont[$rows['B']] = preg_replace("/[^0-9]*/s", "", $rows['F']);
        } else {
            $gangja_arr_it_amont[$rows['B']] += preg_replace("/[^0-9]*/s", "", $rows['F']);
        }
    }

    //만든 데이터로 yc4_order, yc4_cart, gangja_uid 작업
    foreach ($gangja_arr as $seria_num => $rows) {

        //오플 주문번호 있는지 확인
        $od_id_fg = sql_fetch("select od_receipt_bank +od_send_cost as od_receipt_bank,od_id from yc4_order where on_uid ='".trim($rows['on_uid'])."'");

        // insert yc4_order , gangja_uid
        $od_id = !$od_id_fg['od_id'] ? get_new_od_id() : $od_id_fg['od_id'];

        //결제금액 + 배송비
        $bank = $gangja_arr_it_amont[$rows['on_uid']] + $rows['od_send_cost'];

        if (!$od_id_fg['od_id'] || trim($od_id_fg['od_id'])=='') {
            //오플 주문번호 생성
            $sql1 = "
            INSERT INTO
                yc4_order
            SET 
                od_id             = '$od_id',
                on_uid            = '{$rows['on_uid']}',
                mb_id             = 'kangja',
                od_pwd            = password('OPEN_MARKET_ORDER'),
                od_name           = '강자닷컴',
                od_email          = 'info@ople.com',
                od_tel            = '000-000-0000',
                od_hp             = '000-0000-0000',
                od_zonecode		  = '{$rows['od_zonecode']}',
                od_zip1           = '{$rows['od_zip1']}',
                od_zip2           = '{$rows['od_zip2']}',
                od_addr1          = '{$rows['od_b_addr1']}',
                od_addr2          = '',
                od_addr_jibeon	  = '',
                od_b_name         = '{$rows['od_b_name']}',
                od_b_tel          = '{$rows['od_b_tel']}',
                od_b_hp           = '{$rows['od_b_hp']}',
                od_b_zonecode	  = '{$rows['od_zonecode']}',
                od_b_zip1         = '{$rows['od_zip1']}',
                od_b_zip2         = '{$rows['od_zip2']}',
                od_b_addr1        = '{$rows['od_b_addr1']}',
                od_b_addr2        = '',
                od_b_addr_jibeon  = '',
                od_deposit_name   = '강자닷컴',
                od_memo           = '{$rows['od_memo']}',
                od_send_cost      = '{$rows['od_send_cost']}',
                od_temp_bank      = '{$bank}',
                od_temp_card      = '0',
                od_temp_point     = '',
                od_receipt_bank   = '{$bank}',
                od_receipt_card   = '0',
                od_receipt_point  = '0',
                od_bank_account   = '가상계좌',
                od_shop_memo      = '강자닷컴 주문건 입니다.',
                od_hope_date      = '',
                od_time           = '$g4[time_ymdhis]',
                od_ip             = '" . getenv('REMOTE_ADDR') . "',
                od_settle_case    = '가상계좌',
                od_b_jumin		= '{$rows['od_b_jumin']}',
                od_recommend_off_sale = '',
                card_settle_case ='',
                kcp_escrow_point = '',
                od_pay_time = '{$gangja_arr[$seria_num]['od_pay_time']}',
                od_bank_time = '{$gangja_arr[$seria_num]['od_bank_time']}',
                od_status_update_dt  = now()
        ";
            sql_query($sql1);
        } else {

            if ($od_id_fg['od_receipt_bank'] != $bank) {
                //오플 주문서가있을경우 결제 금액업데이트
                $sql1="
                        UPDATE yc4_order
                        SET od_temp_bank = '{$gangja_arr_it_amont[$rows['on_uid']]}'+od_temp_bank,
                               od_receipt_bank = '{$gangja_arr_it_amont[$rows['on_uid']]}'+od_receipt_bank,
                               od_status_update_dt = now()
                         WHERE on_uid = '{$rows['on_uid']}'
                        ";
                sql_query($sql1);
            }
        }


        //cart 생성
        $sql2 = "
                INSERT INTO
                    yc4_cart
                SET
                    on_uid = '{$rows['on_uid']}',
                    it_id = '{$rows['it_id']}',
                    it_opt1 = '',
                    it_opt2 = '',
                    it_opt3 = '',
                    it_opt4 = '',
                    it_opt5 = '',
                    it_opt6 = '',
                    ct_status = '준비',
                    ct_history = '강자닷컴',
                    ct_amount = '{$rows['it_amount']}',
                    ct_amount_usd ='{$rows['ct_amount_usd']}',
                    ct_point = 0,
                    ct_point_use = 0,
                    ct_stock_use = 0,
                    ct_qty = '{$rows['qty']}',
                    ct_time = '" . date('Y-m-d H:i:s') . "',
                    ct_ip = '{$_SERVER['REMOTE_ADDR']}',
                    ct_send_cost = '',
                    ct_mb_id = '',
                    ct_ship_os_pid = '',
                    ct_ship_ct_qty = '',
                    ct_ship_stock_use = '',
                    ct_status_update_dt = now()
            ";
        sql_query($sql2);

        //강자 uid 데이터 저장
        sql_query("
                INSERT INTO 
                    gangja_uid (uid,od_id,id,ip,dt) 
                VALUES 
                (
                  '{$seria_num}',
                  '{$od_id}',
                  '{$member['mb_id']}',
                  '{$_SERVER['REMOTE_ADDR']}',
                   now()
                )"
        );
    }

    alert('주문서가 생성 되었습니다 ', 'gangja_order_list.php');
}
$g4[title] = "프로모션 통계";
define('bootstrap', true);
include $g4['full_path'] . "/adm/admin.head.php";
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<?
include_once("$g4[admin_path]/admin.tail.php");
?>
