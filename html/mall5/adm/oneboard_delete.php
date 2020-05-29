<?
$sub_menu = "300300";
include_once("./_common.php");

check_demo();

if ($is_admin != "super")
    alert("1:1 게시판 삭제는 최고관리자만 가능합니다.");

auth_check($auth[$sub_menu], "d");

// 1:1 게시판 테이블 DROP
sql_query(" drop table $g4[one_prefix]$ob_table ", FALSE);

// 1:1 게시판 폴더 전체 삭제
rm_rf("$g4[path]/data/one/$ob_table");

// 1:1 게시판 설정 삭제
sql_query(" delete from $g4[oneboard_table] where ob_table = '$ob_table' ");

goto_url("oneboard_list.php?$qstr&page=$page");
?>
