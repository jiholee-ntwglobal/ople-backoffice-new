<?
$sub_menu = "100800";
include_once("./_common.php");

check_demo();

if ($is_admin != "super")
    alert("게시판 삭제는 최고관리자만 가능합니다.");

auth_check($auth[$sub_menu], "d");

// _BOARD_DELETE_ 상수를 선언해야 board_delete.inc.php 가 정상 작동함
define("_BOARD_DELETE_", TRUE);

for ($i=0; $i<count($chk); $i++) 
{
    // 실제 번호를 넘김
    $k = $chk[$i];

    $sql = " select count(*) as cnt from $g4[board_table] a, $g4[group_table] b
              where a.gr_id = '$gr_id[$k]' 
                and a.gr_id = b.gr_id ";
    $row = sql_fetch($sql);

    // include 전에 $bo_table 값을 반드시 넘겨야 함
    $tmp_bo_table = $board_table[$k];
    include ("./board_delete.inc.php");
}

goto_url("./board_list.php?$qstr");
?>
