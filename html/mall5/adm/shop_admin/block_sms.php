<?php
/**
 * Created by Eclipse
 * User: kyung-in
 * Date: 2015.09.25
 * file: block_sms.php
 */

$sub_menu = "200720";
include "_common.php";
include_once $g4['full_path'].'/lib/icode.sms.lib.php';
include_once $g4['admin_path']."/admin.head.php";

$sql1	="";
$sql2	="";
$sql3	="";
if(isset($_POST['mob_num'])){
	$mo		= $_POST['mob_num'];
	$mob_num = str_replace("-","",mysql_real_escape_string(trim($_POST['mob_num'])));
	$mob_num = str_replace(",","",$mob_num);
	$mob_num = str_replace(".","",$mob_num);
	if(!preg_match("/^[0-9]{10}/", $mob_num)){
		echo "
			<script type=text/JavaScript>
			alert('휴대폰 번호를 정확하게 입력해 주세요.');
			history.back();
			</script>
		";
		exit;
	}
	$sms = new SMS();
	$sms->send_cancel($mob_num);
	
	$sql1	= "UPDATE g4_member SET mb_sms='0' WHERE replace(mb_hp,'-','')='".$mob_num."'";
	$sql2	= "DELETE FROM yc4_oneday_sms WHERE replace(hp_no,'-','')='".$mob_num."'";
	$sql3	= "DELETE FROM yc4_add_item_sms WHERE ts_send='0' AND replace(ts_hp,'-','')='".$mob_num."'";
	$sql4	= "INSERT INTO ople_sms_block_history (mb_hp,dt,update_id) VALUES ('".$mob_num."', now(),'".$member['mb_id']."')";
	
	$res1	= mysql_query($sql1);
	if(!$res1){
		echo "
			<script type=text/JavaScript>
			alert('멤버테이블 업데이트에 실패하였습니다.');
			history.back();
			</script>
		";
		exit;
	}
	$res2	= mysql_query($sql2);
	if(!$res2){
		echo "
			<script type=text/JavaScript>
			alert('onday 테이블 변경에 실패하였습니다.');
			history.back();
			</script>
		";
		exit;
	}
	$res3	= mysql_query($sql3);
	if(!$res3){
		echo "
			<script type=text/JavaScript>
			alert('item 테이블 변경에 실패하였습니다.');
			history.back();
			</script>
		";
		exit;
	}
	mysql_query($sql4);
	echo "
		<script type=text/JavaScript>
		alert('정상 처리되었습니다.');
		</script>
	";
}
?>

<h1>SMS 수신 거부 처리</h1>
<h3>휴대폰 번호를 입력해 주세요.</h3>
<form method="POST" action="block_sms.php">
<input type="text" name="mob_num" value="<?php echo $mo;?>" />
<input type="submit" value="SMS 수신거부처리" />
	<p>현재 발송 대기중인 SMS 및 LMS도 발송 취소 됩니다.</p>
</form>

<?php
//echo $sql1;
//echo "</br>";
//echo $sql2;
//echo "</br>";
//echo $sql3;
//echo "</br>";


include_once $g4['admin_path']."/admin.tail.php";
