<?
$sub_menu = "200100";
include_once("./_common.php");

check_demo();

auth_check($auth[$sub_menu], "w");

for ($i=0; $i<count($chk); $i++) 
{
    // 실제 번호를 넘김
    $k = $chk[$i];

    $mb = get_member($mb_id[$k]);

    if (!$mb[mb_id]) {
        $msg .= "$mb[mb_id] : 회원자료가 존재하지 않습니다.\\n";
    } else if ($is_admin != "super" && $mb[mb_level] >= $member[mb_level]) {
        $msg .= "$mb[mb_id] : 자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.\\n";
    } else {
        $sql = " update $g4[member_table]
                    set mb_level = '$mb_level[$k]',
                        mb_intercept_date = '$mb_intercept_date[$k]'
                  where mb_id = '$mb_id[$k]' ";
        sql_query($sql);
    }
}

if ($msg)
    echo "<script language='JavaScript'> alert('$msg'); </script>";

goto_url("./member_list.php?$qstr");
?>
