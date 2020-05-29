<?
$sub_menu = "100900";
include_once("./_common.php");

check_demo();

auth_check($auth[$sub_menu], "w");

for ($i=0; $i<count($chk); $i++) 
{
    // 실제 번호를 넘김
    $k = $chk[$i];

    $sql = " update $g4[group_table]
                set gr_subject = '$gr_subject[$k]',
                    gr_admin = '$gr_admin[$k]',
                    gr_use_access = '$gr_use_access[$k]'
              where gr_id = '$gr_id[$k]' ";
    if ($is_admin != "super")
        $sql .= " and gr_admin = '$gr_admin[$k]' ";
    sql_query($sql);
}

goto_url("./boardgroup_list.php?$qstr");
?>
