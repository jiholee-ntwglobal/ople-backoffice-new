<?
$sub_menu = "200300";
include_once("./_common.php");

if ($w == 'u' || $w == 'd')
    check_demo();

auth_check($auth[$sub_menu], "w");

if ($w == "") 
{
    $sql = " insert $g4[mail_table]
                set ma_id = '$ma_id',
                    ma_subject = '$ma_subject',
                    ma_content = '$ma_content',
                    ma_time = '$g4[time_ymdhis]',
                    ma_ip = '$REMOTE_ADDR' ";
    sql_query($sql);
} 
else if ($w == "u") 
{
    $sql = " update $g4[mail_table]
                set ma_subject = '$ma_subject',
                    ma_content = '$ma_content',
                    ma_time = '$g4[time_ymdhis]',
                    ma_ip = '$REMOTE_ADDR'
              where ma_id = '$ma_id' ";
    sql_query($sql);
} 
else if ($w == "d") 
{
	$sql = " delete from $g4[mail_table] where ma_id = '$ma_id' ";
    sql_query($sql);
}

goto_url("./mail_list.php");
?>
