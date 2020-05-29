<?php
$sub_menu = "400510";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$g4[title] = "엑셀배송처리";
include_once ("$g4[admin_path]/admin.head.php");
?>
<fieldset style="margin:0; padding:10px; width:100%;">
	<legend>안내</legend>
	※ 한 번에 등록가능한 자료는 가능한 500 건 이하로 해주십시오.(많은자료의 이메일, SMS발송시 부하발생.)&nbsp;&nbsp;반드시 항목순서를 지켜 주십시오.<br/>
	※ 항목 순서 <u>(엑셀파일에서 해당 셀만 복사하여 붙여넣기 하십시오.)</u><br/>
	1) 1행 기준 : <b>1번째 셀 = 송장번호</b>, 복수배송의 경우 <b>2번째 셀 = os_pid</b><br/>
	2) 단수배송이라면 2번째 셀은 공란으로 두십시오.<br/>
	3) SMS는 '쇼핑몰설정'의 SMS설정에서 배송시 SMS발송이 설정되어 있어야 합니다.
</fieldset>
<p style="margin:2px 0 2px 0;"></p>


<form name="fexcel" method="post" action="deliverylist_excelupdate2.php" style="margin:0;">
<table width="100%" cellpadding=0 cellspacing=0 align="center">
<tr>
	<td>
		<label><input type=checkbox name='od_send_mail' value='1' />메일발송</label>&nbsp;
		<label><input type=checkbox name='send_sms' value='1' />SMS</label>
		<?=textarea_size("excel_data", 20)?>
		 <textarea name="excel_data" id="excel_data" rows=20 style="width:100%;" required itemname="엑셀 자료를 복사하여 붙여넣으십시오."></textarea>
	</td>
</tr>
</table><br>

<div align="center">
	<input type='submit' value=" 등록하기 ">
</div>
</form>

<? include_once ("$g4[admin_path]/admin.tail.php"); ?>