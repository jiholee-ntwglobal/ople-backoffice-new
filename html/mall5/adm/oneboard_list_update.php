<?
$sub_menu = "300300";
include_once("./_common.php");

check_demo();

auth_check($auth[$sub_menu], "w");

for ($i=0; $i<count($chk); $i++) {
    // 실제 번호를 넘김
    $k = $chk[$i];

    $sql = " update $g4[oneboard_table]
                set ob_subject      = '$ob_subject[$k]',
                    ob_skin         = '$ob_skin[$k]',
                    ob_admin        = '$ob_admin[$k]',
                    ob_write_level  = '$ob_write_level[$k]'
              where ob_table = '$oneboard_table[$k]' ";
    sql_query($sql);
}

goto_url("oneboard_list.php?$qstr");
?>
