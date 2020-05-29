<?php
include_once "_common.php";
if (!$is_member)
    goto_url("$g4[path]/bbs/login.php?url=".urlencode("$g4[path]/sjsjin/item_onrequest_write.php"));
$g4['title'] = "상품입고요청";
include_once("./_head.php");

//if(!$is_member) alert_close("회원만 신청이 가능합니다. 로그인후에 이용해 주십시오.");
?>
<form name="fwrite" method="post" action="item_onrequest_writeupdate.php" style="margin:0;">
<input type="hidden" name="mb_id" id="mb_id" value="<?=$member['mb_id']?>">

<table width="98%" align=center border="0" cellspacing="0" cellpadding="0">
<tr>
	<td width="110">상품명</td>
	<td><input type="text" name="it_name" id="it_name" size=70 required itemname="상품명" /></td>
</tr>
<tr>
	<td>제조사링크(URL)<BR/>상품정보링크(URL)</td>
	<td><input type="text" name="it_info" id="it_info" size=70 required itemname="상품정보 링크(URL)" /><br/>※ 예) http://www.aaa.com/itemfile.php?itemcode=12345</td>
</tr>
<tr>
	<td>신청자</td>
	<td><input type="text" name="mb_name" id="ts_name" size=30 value="<?=$member['mb_name']?>" required itemname='신청자이름'></td>
</tr>
<tr>
	<td>입고시 통보번호<br/>(휴대전화)</td>
	<td><input type="text" name="mb_hp" id="ts_hp" size=30 value="<?=$member['mb_hp']?>" required numeric itemname='휴대전화'></td>
</tr>
<tr align="center">
	<td colspan="2" height=30><font color="#FF0000"> 숫자만 입력하십시오. (예:01012341234)</font></td>
</tr>
<tr align="center">
	<td colspan=2><input type=submit value="확인" />&nbsp;&nbsp;<input type="button" value=" 창닫기 " onclick="window.close();" /></td>
</tr>
<tr>
	<td colspan=2 style=" padding:20px 20px 0px 20px; border-width:0;"><p>이곳에 없는 상품중 입고를 희망하는 상품을 적어주시면, 입고후에 입력한 휴대전화로 SMS 통보를 해드립니다.</p>
	</td>
</tr>
</table>
</form>


<? include_once "{$g4['path']}/tail.sub.php"; ?>