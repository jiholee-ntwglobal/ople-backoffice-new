<?php
include_once "_common.php";
if(!$is_member) alert("회원이 아닙니다.");

$chk = sql_fetch("select ts_id from {$g4['item_sms_table']} where ts_id='".(int)$_GET['ts_id']."' and mb_id='{$member['mb_id']}'");
if(!$chk['ts_id']) alert("자신의 신청내역만 삭제할 수 있습니다.");

sql_query("delete from {$g4['item_sms_table']} where ts_id='{$chk['ts_id']}' and mb_id='{$member['mb_id']}'");
goto_url("item_sms_list.php?ts_send=".(int)$ts_send."&page=".(int)$page);
?>