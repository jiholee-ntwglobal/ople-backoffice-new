<?
$sub_menu = "500400";

include_once("./_common.php");
auth_check($auth[$sub_menu], "r");




# 신청자 내역 로드 #

$sql = sql_query("
	select 
		a.*,b.mb_name
	from 
		yc4_oneday_sms a
		left join
		".$g4['member_table']." b on a.mb_id = b.mb_id
");
$sms_member_cnt = mysql_num_rows($sql);

while( $result = sql_fetch_array($sql) ){
	$list_tr .= "
		<tr>
			<td>".$result['mb_id']."</td>
			<td>".$result['mb_name']."</td>
			<td>".$result['hp_no']."</td>
			<td>".$result['create_dt']."</td>
		</tr>
	";
}

if(!$list_tr){
	$list_tr = "
		<tr>
			<td colspan='4'>데이터가 존재하지 않습니다.</td>
		</tr>
	";
}



# sms 발송 리스트 로드 #
$sql2 = sql_query("
	select * from yc4_oneday_sms_contents
");

while($sms_contents = sql_fetch_array($sql2)){
	$list_tr2 .= "
		<tr>
			<td>".$sms_contents['title']."</td>
			<td>".$sms_contents['send_dt']."</td>
			<td>".$sms_contents['create_dt']."</td>
			<td>".icon('수정',$g4['shop_admin_path'].'/one_day_sms_write.php?uid='.$sms_contents['uid'])."</td>
		</tr>
	";
}

if(!$list_tr2){
	$list_tr2 = "
		<tr>
			<td colspan='4'>데이터가 존재하지 않습니다.</td>
		</tr>
	";
}

$g4['title'] = '원데이이벤트 SMS';
include_once ("$g4[admin_path]/admin.head.php");
?>

<table width='100%'>
	<tr>
		<td>제목</td>
		<td>발송일자</td>
		<td>생성일</td>
		<td><?=icon('입력',$g4['shop_admin_path'].'/one_day_sms_write.php')?></td>
	</tr>
	<?=$list_tr2;?>

</table>

<div>총 <?=$sms_member_cnt?>명</div>
<table width='100%'>
	<tr>
		<td>아이디</td>
		<td>이름</td>
		<td>휴대폰번호</td>
		<td>신청일</td>
	</tr>

	<?=$list_tr;?>
</table>


<?
include_once ("$g4[admin_path]/admin.tail.php");
?>