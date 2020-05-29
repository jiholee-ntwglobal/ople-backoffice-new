<?php
$sub_menu = "400400";
include_once("./_common.php");

// 메세지
$html_title = "주문 내역 수정";
$alt_msg1 = "주문번호 오류입니다.";
$mb_guest = "비회원";
$prog_msg1 = "이 프로그램의 등록번호가 올바르지 않습니다.<br>정식구입자이신 경우는 프로그램 구입처에서 등록번호를 재발급 하시기 바랍니다.<br>등록번호 재발급 방법 : 구입처 홈페이지 > 마이페이지 > 등록번호 재발급요청";

auth_check($auth[$sub_menu], "w");

$g4['title'] = $html_title;
include_once("$g4[admin_path]/admin.head.php");
