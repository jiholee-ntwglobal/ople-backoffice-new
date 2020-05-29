<?php
include_once $g4['full_path']."/shop.config.php";
include_once $g4['full_path']."/lib/shop.lib.php";

//==============================================================================
// 쇼핑몰 필수 실행코드 모음 시작
//==============================================================================
// 쇼핑몰 설정값 배열변수
$default = sql_fetch(" select * from ".$g4['yc4_default_table']);
$default['no_send_cost'] = 60000; // 무료배송금액
$default['no_send_cost_health_cnt'] = 6; // 무료배송 최대건기식수량
// 프로그램 전반에 걸쳐 사용하는 유일한 키 (장바구니 키)
if (!get_session('ss_on_uid'))
{
    set_session('ss_on_uid', get_unique_id());
}

// 일반 숫자형으로 출력
function nf($value, $n=0)
{
    return number_format($value, $n);
}
//==============================================================================
// 쇼핑몰 필수 실행코드 모음 끝
//==============================================================================
?>