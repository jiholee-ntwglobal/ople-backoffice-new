<?
$sub_menu = "700300";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

if($_POST['mode'] == 'confirm'){
	$updateQ = "
		update 
			yc4_outbound_call
		set
			confirm_comment = '".$_POST['confirm_comment']."',
			confirm_id = '".$member['mb_id']."',
			confirm_dt = now()
		where
			uid = '".$_POST['uid']."'
	";

	if(sql_query($updateQ)){
		echo 'ok';
	}
	
	exit;
}

$g4[title] = "전화요청 관리";
include_once ("$g4[admin_path]/admin.head.php");

# 전화요청 로드 #
if($_GET['confirm'] == 'y'){
	$where = (($where)? '' : ' where ')." confirm_dt is not null";
	$where2 = (($where2)? '' : ' where ')."  a.confirm_dt is not null";
}elseif($_GET['confirm'] == 'all'){


}else{
	$where = (($where)? '' : ' where ')." confirm_dt is null";
	$where2 = (($where2)? '' : ' where ')." a.confirm_dt is null";
}

// 20200101 이전것은 비노출
$where .= (($where)? ' and ' : ' where ')." left(create_dt, 10) >= '2020-01-01' ";
$where2 .= (($where2)? ' and ' : ' where ')." left(create_dt, 10) >= '2020-01-01' ";

$call_cnt = sql_fetch("select count(*) as cnt from yc4_outbound_call ".$where);
$call_cnt = $call_cnt['cnt'];
$call_qry = sql_query("
	select 
		a.* ,b.mb_name,c.mb_name as confirm_name
	from 
		yc4_outbound_call a
		left join
		g4_member b on a.mb_id = b.mb_id
		left join
		g4_member c on a.confirm_id = c.mb_id
	".$where2."
	order by a.dt desc, a.time desc
");

while($call = sql_fetch_array($call_qry)){

	$list_tr .= "
		<tr>
			<td rowspan='4' align='center'>".$call['uid']."</td>
			<td>".substr($call['dt'],0,4).'년 '.substr($call['dt'],4,2).'월 '.substr($call['dt'],6,2)."일</td>
			<td>".$call['mb_name']."</td>
			<td rowspan='2' align='center'>".$call['hp_no']."</td>
			<td align='center'>".$call['create_dt']."</td>
			<td rowspan='4' align='center'>".(($call['confirm_name'])?$call['confirm_name'] : "<a href='javascript:return false;' onclick=\"call_confirm('".$call['uid']."',this); return false;\">미처리</a>")."</td>
		</tr>
		<tr>
			<td>".$call['time']." ~ ".($call['time']+30)."</td>
			<td>".$call['mb_id']."</td>
			<td align='center'>".(($call['confirm_dt']) ? $call['confirm_dt'] : "미처리")."</td>
		</tr>
		<tr>
			<td colspan='4'>내용 : ".$call['comment']."</td>
		</tr>
		<tr>
			<td colspan='4'>관리자메모 : <textarea class='confirm_comment' style='width:620px; vertical-align:middle;'>".$call['confirm_comment']."</textarea></td>
		</tr>
	";

}
?>
<style type="text/css">
.call_tab{
	margin:0px;
	list-style:none;
	overflow:hidden;
}
.call_tab li{
	padding: 5px;
	float:left;
	text-align:center;
	border:1px solid #dddddd;
}
.call_tab li .small{
	padding:0px;
}
</style>
<?=subtitle("전화요청")?>
<ul class='call_tab'>
	<li><a href="<?=$_SERVER['PHP_SELF']?>"><span class='<?=(!$_GET['confirm'])?'small':''?>'>미처리</span></a></li>
	<li><a href="<?=$_SERVER['PHP_SELF']?>?confirm=y"><span class='<?=($_GET['confirm'] == 'y')?'small':''?>'>처리</span></a></li>
	<li><a href="<?=$_SERVER['PHP_SELF']?>?confirm=all"><span class='<?=($_GET['confirm'] == 'all')?'small':''?>'>전체</span></a></li>
</ul>
<table width='100%' border='1' style='border-collapse: collapse;'>
	<thead>
		<tr>
			<th rowspan='3'>번호</th>
			<th>요청날짜</th>
			<th>이름</th>
			<th rowspan='2'>전화번호</th>
			<th>등록일</th>
			<th rowspan='3'>처리자</th>
		</tr>
		<tr>
			<th>요청시간</th>
			<th>아이디</th>
			<th>처리일</th>
		</tr>
		<tr>
			<th colspan='4'>내용</th>
		</tr>
	</thead>
	<tbody>
		<?=$list_tr;?>
	</tbody>
</table>


<script type="text/javascript">
function call_confirm( uid, obj ){
	if(!confirm('해당 요청을 처리하시겠습니까?')){
		return false;
	}
	confirm_comment = $(obj).parent().parent().next().next().next().find('.confirm_comment').val();


	$.ajax({
		url : '<?=$_SERVER['PHP_SELF']?>',
		type : 'post',
		data : {
			'mode' : 'confirm',
			'uid' : uid,
			'confirm_comment' : confirm_comment
		},success : function( result ){
			if(result == 'ok'){
				alert('처리되었습니다.');
				location.reload();
			}else{
				alert('처리중 오류 발생! 다시 시도해 주세요.');
			}
		}
	});

	return false;
}
</script>
<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
