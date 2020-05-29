<?
include_once "./_common.php";

if($_POST['mode'] == 'insert'){
	$hp_no = trim($_POST['hp1']).'-'.trim($_POST['hp2']).'-'.trim($_POST['hp3']);
	$sql = "
		insert into yc4_oneday_sms (mb_id,hp_no,create_dt) values('".$member['mb_id']."','".$hp_no."','".$g4['time_ymdhis']."')
	";

	if(sql_query($sql)){
		echo 'ok';
	}

	exit;
}


if($_POST['mode'] == 'update'){
	$hp_no = trim($_POST['hp1']).'-'.trim($_POST['hp2']).'-'.trim($_POST['hp3']);
	$sql = "
		update 
			yc4_oneday_sms 
		set
			hp_no = '".$hp_no."',
			update_dt = '".$g4['time_ymdhis']."'
		where
			mb_id = '".$member['mb_id']."'
	";

	if(sql_query($sql)){
		echo 'ok';
	}

	exit;
}

if($_POST['mode'] == 'delete'){
	$sql = "
		delete from yc4_oneday_sms where mb_id = '".$member['mb_id']."'
	";

	if(sql_query($sql)){
		echo 'ok';
	}

	exit;
}




# 기존 신청 정보 로드 #
$oneday_sms = sql_fetch("
	select * from yc4_oneday_sms where mb_id = '".$member['mb_id']."'
");

if($oneday_sms){

	$hp = explode('-',$oneday_sms['hp_no']);

}else{

	# 휴대폰 번호 자르기 #
	$member_hp = str_replace('-','',$member['mb_hp']);


	if(strlen($member_hp) == 7){
		$hp[1] = substr($member_hp,3,3);
		$hp[2] = substr($member_hp,6,4);
	}else{
		$hp[1] = substr($member_hp,3,4);
		$hp[2] = substr($member_hp,7,4);

	}

	$hp[0] = substr($member_hp,0,3);
}

?>

<div style='width:426px;'>
	<div style="text-align:left; margin: 0 18px 18px 18px;" ><b>받으실 휴대폰 번호를 넣어주세요.</b> </div>
	<div style="text-align:left; margin: 0 18px 18px 18px;">
	<input type="text" class='one_day_sms_input' style="width:116px; height:30px;" name='hp_no1' value='<?=$hp[0];?>'/>
	<span style="margin: 0 4px; text-align:center; color:#bbbbbb">-</span>
	<input type="text" class='one_day_sms_input' style="width:116px; height:30px;" name='hp_no2' value='<?=$hp[1];?>'/>
	<span style="margin: 0 4px; text-align:center; color:#bbbbbb">-</span>
	<input type="text" class='one_day_sms_input' style="width:116px; height:30px;" name='hp_no3' value='<?=$hp[2];?>'/>
	</div>
	<div>
		<?if($oneday_sms){?>
		<p style="cursor: pointer; margin: 10px 8px 0px 80px; float:left;" onclick="oneday_sms_sbm();"><img src="http://115.68.20.84/main/smsch_bt.jpg"></p>
		<p style="cursor: pointer; margin: 10px 8px 0px 0px; float:left;" onclick="oneday_sms_sbm('d');"><img src="http://115.68.20.84/main/smscan_bt.jpg"></p>
		<?}else{?>
		<p style="cursor: pointer; margin: 29px 0;" onclick="oneday_sms_sbm();"><img src="http://115.68.20.84/main/smsok_bt.jpg"></p>
		<?}?>
	</div>
</div>



<script type="text/javascript">

function oneday_sms_sbm( mode ){
$('.layer_wrap').hide();
	
	var flag = (mode == 'd') ? 'delete' : '<?=($oneday_sms) ? 'update':'insert'?>'
	var msg = (mode == 'd') ? '삭제가' : '<?=($oneday_sms) ? '수정이':'신청이'?>'
	var msg2 = (mode == 'd') ? '굿데이 이벤트 SMS수신 신청을 취소하시겠습니까?' : '<?=($oneday_sms) ? '굿데이 이벤트 SMS 수신 번호를 변경하시겠습니까?':'굿데이 이벤트 SMS수신에 동의하십니까?'?>'
	if(!confirm(msg2)){
		return false;
	}

	


	$.ajax({
		url : '<?=$_SERVER['PHP_SELF']?>',
		type : 'post',
		data : {
			'mode' : flag,
			'hp1' : $('.one_day_sms_input[name=hp_no1]').val(),
			'hp2' : $('.one_day_sms_input[name=hp_no2]').val(),
			'hp3' : $('.one_day_sms_input[name=hp_no3]').val()
		},
		success : function ( result ) {
			if(result == 'ok'){
				alert(msg+' 완료되었습니다.');
				$('.layer_title , .layer_contents').empty();
				$('body > center').removeAttr('style');
			}
		}
	});

}

</script>