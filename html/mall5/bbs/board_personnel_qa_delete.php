<?
$uid = mysql_escape_string($_GET['uid']);
$uid_chk = sql_fetch("select mb_id,uid from yc4_personal_qa where uid = '".$uid."'");
if(!$uid_chk){
	alert('잘못된 경로로 접근하셨습니다.');
	exit;
}
if(!$member['mb_id']){
	alert('회원만 이용하실 수 있습니다.');
	exit;
}

if($uid_chk['mb_id'] != $member['mb_id']){
	alert('본인의 글만 삭제하실 수 있습니다.');
	exit;
}



?>
<form action="board_personnel_qa_update.php" method='post' name='del_frm'>
	<input type="hidden" name='w' value='d' />
	<input type="hidden" name='uid' value='<?=$uid?>' />
</form>
<script type="text/javascript">
del_frm.submit();
</script>