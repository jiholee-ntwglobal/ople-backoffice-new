<?php
$sub_menu = "500300";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");


$chk_mb = get_member($_POST['mb_id'], "mb_id");
if($w == '')
{
	if(!$chk_mb['mb_id']) alert("존재하지 않는 회원ID 입니다.");
	if(preg_match("/[^\w]/", $_POST['mp_event_id'])) alert("이벤트ID는 숫자, 영문, 언더바(_) 만 가능합니다.");

	sql_query("insert into {$g4['yc4_member_promo']}
		set mb_id		= trim('{$_POST['mb_id']}'),
			mp_mb_count	= trim('{$_POST['mp_mb_count']}'),
			mp_event_id	= trim('{$_POST['mp_event_id']}'),
			mp_datetime	= trim('{$_POST['mp_datetime']}') ");

	$mp_pid = mysql_insert_id();
	goto_url("./member_promo_manage_write.php?w=u&mp_pid=$mp_pid");
}
else if($w == 'u')
{
	if(preg_match("/[^\w]/", $_POST['mp_event_id'])) alert("이벤트ID는 숫자, 영문, 언더바(_) 만 가능합니다.");
	if(!$chk_mb['mb_id']) alert("존재하지 않는 회원ID 입니다.");
	if($_POST['mp_pid'] == '') alert("수정하려는 자료가 존재하지 않습니다. #mp_pid value error");

	sql_query("update {$g4['yc4_member_promo']}
		set mb_id		= trim('{$_POST['mb_id']}'),
			mp_mb_count	= trim('{$_POST['mp_mb_count']}'),
			mp_event_id	= trim('{$_POST['mp_event_id']}'),
			mp_datetime	= trim('{$_POST['mp_datetime']}')
		where mp_pid='{$_POST['mp_pid']}' ");

	goto_url("./member_promo_manage_write.php?w=u&mp_pid=$mp_pid&sel_field=$sel_field&search=".urlencode($search)."&page=$page");
}
else if($w == 'd')
{
	// 프로모션에 누적내역이 존재하면 삭제불가.
	$chk_mpr = sql_fetch("select ms_pid from {$g4['yc4_member_promor']} where mb_id='{$_POST['mb_id']}' ");
	//$chk_mpo = sql_fetch("select mo_pid from {$g4['yc4_member_promo_order']} where mb_id2='{$od_row['mb_id']}' and od_id='$od_id' ");
	if($chk_mpr['ms_pid'])
		alert("해당 프로모션자료는 누적내역이 존재하므로 삭제할 수 없습니다.");

	sql_query("delete from {$g4['yc4_member_promo']} where mp_pid='$mp_pid' ");
	goto_url("./member_promo_manage.php?sel_field=$sel_field&search=".urlencode($search)."&page=$page");
}
else
	alert("자료처리 구분값이 없습니다.");
?>