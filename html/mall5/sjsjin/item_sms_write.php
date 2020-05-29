<?php
include_once "_common.php";

$g4['title'] = "품절상품 입고시 SMS통보 신청";
include_once "{$g4['path']}/head.sub.php";

// 김선용 200905 : 회원
if(!$is_member) alert_close("회원만 신청이 가능합니다. 로그인후에 이용해 주십시오.");

$it_id = (int)$_GET['it_id'];
$it = sql_fetch("select it_id, it_name from {$g4['yc4_item_table']} where it_use=1 and it_id='$it_id'");
if($it['it_name']){
	$it['it_name'] = get_item_name($it['it_name']);
}
if(!$it['it_id']) alert_close("상품코드에 해당하는 상품정보가 없습니다.\\n\\n다시 시도해보시고 문제가 계속되면 고객센터를 통해 문의해 주십시오.");

// 품절상품인가
// 품절
$stock = get_it_stock_qty($it['it_id']);
$sms_chk = false;
if($stock <= 0){
	$sms_chk = true;
}
if(!$sms_chk) alert_close("이 상품은 품절상품이 아닙니다. 상품을 바로 구매하실 수 있습니다.");
?>
<style>
.PageTitle {
  border-bottom: none;
  font-size:15px;
  font-weight:bold;
  color:#000;
  letter-spacing:-1px;
}
table {border-top:solid 2px #000;}
table td {
  font-size: 12px;
  border-bottom: solid 1px #ececec;
  padding: 8px;
  text-align: left;
  line-height: 18px;
  color: #000;
}
table td input {padding:3px;}
table th {
  background-color:#f4f4f4;
  font-size: 12px;
  border-bottom: solid 1px #ececec;
  padding: 8px;
  text-align: left;
  line-height: 18px;
  color: #000;
  font-weight:normal;
}
</style>
<form name="fwrite" method="post" action="item_sms_writeupdate.php" style="margin:0;">
<input type="hidden" name="mb_id" id="mb_id" value="<?=$member['mb_id']?>">
<input type="hidden" name="it_id" id="it_id" value="<?=$it_id?>">

<div style="padding:5px;">
  <p class="PageTitle">SMS 통보 요청</p>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<th width="130px">상품명</th>
	<td><?=stripslashes($it['it_name'])?></td>
</tr>
<tr>
	<th>신청자</th>
	<td><input type="text" name="ts_name" id="ts_name" size="30" class="ed" value="<?=$member['mb_name']?>" required hangul itemname='신청자이름'></td>
</tr>
<tr>
	<th>통보번호(휴대전화)</th>
	<td><input type="text" name="ts_hp" id="ts_hp" size="30" class="ed" value="<?=$member['mb_hp']?>" required telnumber itemname='휴대전화'> <span style="color:#FF0000;letter-spacing:-0.08em;">휴대폰번호는 - 를 포함해야 합니다. (예:010-0000-0000)</span></td>
</tr>
<tr>
	<td colspan="2" style="text-align:center;"><input type=submit value=" 확 인 " /> <input type="button" value=" 창닫기 " onclick="window.close();" /></td>
</tr>
<tr>
	<td colspan="2" style="border:none;padding:20px;color:gray;line-height:18px;"><p>품절상품 SMS문자서비스를 신청하시면 재입고시 회원님에게 SMS문자로 입고소식을 가장 먼저 알려드립니다.<br/>신청하신 내역은 <font color="FF3300"><b>마이페이지</b></font>에서 확인가능합니다.</p>
	</td>
</tr>
</table>
</div>
</form>


<? include_once "{$g4['path']}/tail.sub.php"; ?>