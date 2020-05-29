<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 김선용 2014.04 :


$site_cd   = trim($default['de_kcp_mid']);
$ordr_idxx = trim($od['od_id']);
$good_mny  = (int)$settle_amount;
$timestamp = $g4['server_time'];

?>
<style type="text/css">
.kcpwin{font-size:9pt; line-height:160%}
.bblack1 {FONT-WEIGHT: bold; FONT-SIZE: 9pt; COLOR: #000000; LINE-HEIGHT: 12pt; FONT-STYLE: normal; FONT-FAMILY: "돋움"; TEXT-DECORATION: none
}
</style>

<script type="text/javascript">

$(function() {
	<?if($s_page != "orderinquiryview.php"){?>
		alert("아래의 \'가상계좌 발급신청\' 버튼을 꼭 눌러주셔야 주문이 완료되고, 가상계좌가 발급됩니다.\n\n회원전용 고정계좌의 경우도 \'가상계좌 발급신청\' 버튼을 눌러주십시오.\n\n그래야 주문이 완료처리 됩니다.");
	<?}?>
});

function jsf__pay()
{
	if(document.getElementById('ipgm_bank').value == ''){
		alert("입금할 은행을 선택해 주십시오.");
		document.getElementById('ipgm_bank').select();
		document.getElementById('ipgm_bank').focus();
		return ;
	}
	if(document.getElementById('ipgm_name').value == ''){
		alert("입금자 이름을 입력해 주십시오.");
		document.getElementById('ipgm_name').select();
		document.getElementById('ipgm_name').focus();
		return ;
	}
	/*
	if(document.getElementById('ipgm_date').value == ''){
		alert("입금예정일을 입력해 주십시오.");
		document.getElementById('ipgm_date').select();
		document.getElementById('ipgm_date').focus();
		return ;
	}
	if(/([^0-9]+$)/.test(document.getElementById('ipgm_date').value)){
		alert("입금예정일은 년월일 숫자만 입력해 주십시오.");
		document.getElementById('ipgm_date').select();
		document.getElementById('ipgm_date').focus();
		return ;
	}
	*/
	$('#_dis_kcp_progress_').show();
	document.order_info.submit();
}
</script>

<div id="_dis_kcp_progress_" class=kcpwin style="display:none; width:420px; height:300px; top:400px; left:300px; position:absolute; index:-1;">
<table width="400" border="0" cellspacing="1" cellpadding="0" align="center" bgcolor="#E0D6AD">
  <tr>
    <td align="center" class="bblack1" height="60" bgcolor="#FBFAF4">
      가상계좌 발급처리중 입니다. 잠시만 기다려 주십시오.<br/>
      <img src="./kcp-vcnt/processing.gif" name="pro" width="295" height="10">
    </td>
  </tr>
</table>
</div>

<?
switch ($settle_case)
{
    case '계좌이체' :
        $settle_method = "010000000000";
        break;
    case '가상계좌' :
        //$settle_method = "001000000000";
		$settle_method = "VCNT"; // 김선용 2014.04 : 고정가상계좌
        break;
    default : // 신용카드
        $settle_method = "100000000000";
        break;
}
?>
<br/>


<form name="order_info" method="post" action='./kcp-vcnt/pp_cli_hub.php'>
<!-- 사용자 변수 -->
<input type=hidden name='timestamp'     value='<?=$timestamp?>'>
<input type=hidden name='d_url'         value='<?=$g4['url']?>'>
<input type=hidden name='shop_dir'      value='<?=$g4['shop']?>'>
<input type=hidden name='on_uid'        value='<?=$_SESSION['ss_temp_on_uid']?>'>


<fieldset style="margin:0 0 0 20px; padding:0px; width:94%; border:4px solid #ff9900;" align=center>
<legend style="color:blue; padding-left:10px;">가상계좌 필수정보를 입력해 주십시오.</legend>
<table width=97% cellpadding=0 cellspacing=10 border=0 align=center>
<tr>
	<td><B>입금은행</B></td>
	<td>
		<select name="ipgm_bank" id="ipgm_bank">
			<option value="" selected>선택</option>
			<option value="BK03">기업은행</option>
			<option value="BK04">국민은행</option>
			<option value="BK05">외환은행</option>
			<option value="BK07">수협은행</option>
			<option value="BK11">농협중앙회</option>
			<option value="BK20">우리은행</option>
			<option value="BK23">SC제일은행</option>
			<option value="BK32">부산은행</option>
			<option value="BK34">광주은행</option>
			<option value="BK71">우체국</option>
			<option value="BK81">하나은행</option>
			<option value="BK26">신한은행</option>
		</select>
	</td>
</tr>
<tr>
	<td><b>입금자명</b></td>
	<td><input type='text' name='ipgm_name' id="ipgm_name" value="<?=$member['mb_name']?>" maxlength='10'> ※ 최대 10글자</td>
</tr>
<!--
<tr>
	<td><B>입금 예정일</B></td>
	<td><input type='text' name='ipgm_date' id="ipgm_date" maxlength='8' value=""> ※ 숫자만 (예: 2014년 3월 5일의 경우 : 20140305 와 같이 입력)</td>
</tr>
-->
</table>
</fieldset>

<input type="hidden" name="ipgm_date"  value="<?=date("Ymd", strtotime("+3 day"));?>"> <? // 입금예정일. 현재+3일 ?>
<input type="hidden" name="pay_method" value="<?=$settle_method?>">
<input type="hidden" name="ordr_idxx"  value="<?=$ordr_idxx?>">
<input type="hidden" name="good_name"  value="<?=$goods?>">
<input type="hidden" name="good_mny"   value="<?=$good_mny?>">
<input type="hidden" name='currency'      value='WON'>
<input type="hidden" name='buyr_name'     value='<?=addslashes($od['od_name'])?>' >
<input type="hidden" name='buyr_mail'     value='<?=$od['od_email']?>'>
<input type="hidden" name='buyr_tel1'     value='<?=$od['od_tel']?>'>
<input type="hidden" name='buyr_tel2'     value='<?=$od['od_hp']?>'>
<input type="hidden" name="soc_no"     value=""> <? //-- 김선용 2014.04 : 주민번호. 사용안함 ?>
<input type="hidden" name="req_tx"     value="pay"> <? // 요청종류 승인(pay)/취소,매입(mod) 요청시 사용 ?>
<input type="hidden" name="currency"   value="WON">
<input type="hidden" name="va_uniq_key" value="<?=get_microtime();?>"> <? // 고정계좌 고유키. 20자. 고정모듈은 반드시 값 필수. 중복되면 같은 계좌, ?>
</form>
<br/>

<p align="center"><input type="button" value="가상계좌 발급신청" onclick="jsf__pay();" style="height:50px; width:300px; font-size:20pt; color:black; font-weight:bold;" title="가상계좌 발급신청" /></p>
