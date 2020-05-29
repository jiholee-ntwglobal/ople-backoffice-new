<?
$sub_menu = "400800";

include_once("./_common.php");
auth_check($auth[$sub_menu], "r");


# insert 처리 #
if($_POST['mode'] == 'insert'){
	$_POST['title'] = mysql_real_escape_string($_POST['title']);
	$_POST['contents'] = mysql_real_escape_string($_POST['contents']);

	
	$sql = "
		insert into 
			yc4_oneday_sms_contents
		(
			title,contents,create_dt
		)values(
			'".$_POST['title']."','".$_POST['contents']."','".$g4['time_ymdhis']."'
		)
	";
	if(!sql_query($sql)){
		alert('저장중 오류 발생!');
	}
	alert('저장이 완료되었습니다.',$g4['shop_admin_path'].'/oneday_sms.php');
	
	exit;
}

# update 처리 #
if($_POST['mode'] == 'update'){

	$_POST['title'] = mysql_real_escape_string($_POST['title']);
	$_POST['contents'] = mysql_real_escape_string($_POST['contents']);

	$sql = "
		update
			yc4_oneday_sms_contents
		set
			title = '".$_POST['title']."',
			contents = '".$_POST['contents']."'
		where
			uid = '".$_POST['uid']."'
	";

	if(!sql_query($sql)){
		alert('저장중 오류 발생!');
	}
	alert('저장이 완료되었습니다.',$g4['shop_admin_path'].'/oneday_sms.php');
	exit;
}

# delete 처리 #
if($_POST['mode'] == 'delete'){
	$sql = "
		delete 
		from 
			yc4_oneday_sms_contents
		where uid = '".$_POST['uid']."'
	";
	if(!sql_query($sql)){
		alert('삭제중 오류 발생!');
	}

	alert('삭제가 완료되었습니다.',$g4['shop_admin_path'].'/oneday_sms.php');

	exit;
}

# 전송 처리 #
if($_POST['mode'] == 'send'){
	include_once("$g4[path]/lib/icode.sms.lib.php");
	
	$send_number = preg_replace("/[^0-9]/", "", $default[de_sms_hp]); // 발신자번호
	
	$sms_qry = sql_fetch("
		select contents from yc4_oneday_sms_contents where uid = '".$_POST['uid']."'
	");
	$sms_contents = $sms_qry['contents'];


	$sql = sql_query("
		select hp_no from yc4_oneday_sms
	");
	$i = 0;
	$SMS = new SMS; // SMS 연결
	$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);

	set_time_limit(0); // 타임아웃 임시 해제
	while($row = sql_fetch_array($sql)){
	
		$receive_number = preg_replace("/[^0-9]/", "", $row['hp_no']); // 수신자번호
		$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
		$i++;
	}
	$SMS->Send();

	if($i>0){
		$updateQ = "
			update 
				yc4_oneday_sms_contents
			set
				send_dt = '".$g4['time_ymdhis']."'
			where
				uid = '".$_POST['uid']."'
		";
		sql_query($updateQ);
		alert($i . '명에게 SMS를 발송했습니다.',$_SERVER['PHP_SELF'].'?uid='.$_POST['uid']);
	}else{
		alert('SMS를 발송할 회원이 없습니다.');
	}
	
	exit;
}

# 테스트 전송 처리 #
if($_POST['mode'] == 'send_test'){
	if(!preg_replace("/[^0-9]/", "", $_POST['test_hp'])){
		alert('테스트할 휴대폰 번호를 입력해 주세요');
		exit;
	}
	$send_number = preg_replace("/[^0-9]/", "", $default[de_sms_hp]); // 발신자번호
	
	$sms_qry = sql_fetch("
		select contents from yc4_oneday_sms_contents where uid = '".$_POST['uid']."'
	");
	$sms_contents = $sms_qry['contents'];
	
	include_once("$g4[path]/lib/icode.sms.lib.php");
	$SMS = new SMS; // SMS 연결
	$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
	$receive_number = preg_replace("/[^0-9]/", "", $_POST['test_hp']); // 수신자번호
	$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
	$SMS->Send();
	alert('SMS를 발송했습니다.');
	exit;

}



if($_GET['uid']){
	$data = sql_fetch("
		select * from yc4_oneday_sms_contents where uid = '".$_GET['uid']."'
	");
	$data['contents'] = stripcslashes($data['contents']);
	$data['title'] = stripcslashes($data['title']);


}

$g4['title'] = '원데이이벤트 작성';
include_once ("$g4[admin_path]/admin.head.php");
?>

<form action="<?=$_SERVER['PHP_SELF']?>" method='POST'>
	<input type="hidden" name='mode' value='<?=($data) ? 'update':'insert'?>'/>
	<?if($data){?>
	<input type="hidden" name='uid' value='<?=$data['uid']?>' />
	<?}?>
	
	<table width='100%'>
		<tr>
			<td>제목</td>
			<td><input type="text" name='title' value='<?=$data['title']?>'/></td>
		</tr>
		<tr>
			<td>내용</td>
			<td>
				<textarea name="contents" id="contents" onkeyup='smsByteChk(this)' onkeydown='smsByteChk(this)' rows='10' cols='30'><?=$data['contents']?></textarea>
				<br />
				<input type='text' id='sms_byte' size='1' readonly style='border:none; text-align:right'/>Byte 남음
			</td>
		</tr>
		<tr>
			<td>테스트전송 번호</td>
			<td><input type="text" name='test_hp' /></td>
		</tr>
	</table>
	<p align='center'>
		<input type="submit" value='저장'/>
		<?if($data){?>
		<input type="button" value='전송' onclick="if(!confirm('전송하시겠습니까?')) return false; this.form.mode.value='send';this.form.submit();"/>
		<input type="button" value='테스트 전송' onclick="if(!confirm('테스트 전송하시겠습니까?')) return false; this.form.mode.value='send_test';this.form.submit();"/>
		<input type="button" value='삭제' onclick="if(!confirm('삭제하시겠습니까?')) return false; this.form.mode.value='delete';this.form.submit();"/>
		<?}?>
		<input type="button" value='목록' onclick="location.href='<?=$g4['shop_admin_path']?>/oneday_sms.php'"; />
	</p>
</form>


<script type="text/javascript">
$(document).ready(function(){
	smsByteChk();
});
function smsByteChk(content)
    {
        var temp_str = $('#contents').val();
        var remain = document.getElementById("sms_byte");
         
        remain.value = 80 - getByte(temp_str);
        //남은 바이트수를 표시 하기
        if(remain.value < 0)
        {
            alert(80 + "Bytes를 초과할 수 없습니다.");
             
            while(remain.value < 0)
            {
                temp_str = temp_str.substring(0, temp_str.length-1);
                content.value = temp_str;
                remain.value = 80 - getByte(temp_str);
            }
             
            content.focus();
        }
  
    }
 
function getByte(str)
{
	var resultSize = 0;
	if(str == null)
	{
		return 0;
	}
	 
	for(var i=0; i<str.length; i++)
	{
		var c = escape(str.charAt(i));
		if(c.length == 1)//기본 아스키코드
		{
			resultSize ++;
		}
		else if(c.indexOf("%u") != -1)//한글 혹은 기타
		{
			resultSize += 2;
		}
		else
		{
			resultSize ++;
		}
	}
	 
	return resultSize;
} 
</script>
<?
include_once ("$g4[admin_path]/admin.tail.php");
?>