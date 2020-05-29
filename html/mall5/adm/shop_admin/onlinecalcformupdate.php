<?
$sub_menu = "400600";
include_once("./_common.php");

@mkdir("$g4[path]/data/onlinecalc", 0707);
@chmod("$g4[path]/data/onlinecalc", 0707);

if ($w == "d") 
{
    check_demo();

    auth_check($auth[$sub_menu], "d");

    @unlink("$g4[path]/data/onlinecalc/{$oc_id}_m");
    @unlink("$g4[path]/data/onlinecalc/{$oc_id}_h");

    $sql = " delete from $g4[yc4_onlinecalc_table] where oc_id = '$oc_id' ";
    sql_query($sql);

    goto_url("./onlinecalclist.php");
}

auth_check($auth[$sub_menu], "w");

if ($oc_himg_del)  @unlink("$g4[path]/data/onlinecalc/{$oc_id}_h");
if ($oc_timg_del)  @unlink("$g4[path]/data/onlinecalc/{$oc_id}_t");


$oc_category = preg_replace("/,/", "\n", $ca_list);

$sql_common = " oc_subject = '$oc_subject',
                oc_category = '$oc_category',
                oc_head_html = '$oc_head_html',
                oc_tail_html = '$oc_tail_html' ";

if ($w == "")
{
    sql_query(" alter table $g4[yc4_onlinecalc_table] auto_increment=1 ");

    $sql = " insert $g4[yc4_onlinecalc_table] 
                set $sql_common ";
    sql_query($sql);

    $oc_id = mysql_insert_id();
}
else if ($w == "u")
{
    $sql = " update $g4[yc4_onlinecalc_table]
                set $sql_common
              where oc_id = '$oc_id' ";
    sql_query($sql);
}

if ($_FILES[oc_himg][name]) upload_file($_FILES[oc_himg][tmp_name], $oc_id . "_h", "$g4[path]/data/onlinecalc");
if ($_FILES[oc_timg][name]) upload_file($_FILES[oc_timg][tmp_name], $oc_id . "_t", "$g4[path]/data/onlinecalc");

goto_url("./onlinecalcform.php?w=u&oc_id=$oc_id");
?>
