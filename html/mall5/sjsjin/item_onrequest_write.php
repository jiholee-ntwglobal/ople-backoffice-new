<?php
include_once "_common.php";
if (!$is_member)
    goto_url("$g4[path]/bbs/login.php?url=".urlencode("$g4[path]/sjsjin/item_onrequest_write.php"));
$g4['title'] = "상품입고요청";
include_once("./_head.php");

//if(!$is_member) alert_close("회원만 신청이 가능합니다. 로그인후에 이용해 주십시오.");
?>
<div class='PageTitle'>
  <img src="http://115.68.20.84/main/reqheader.gif" alt="상품입고요청" />
</div>
<div style='line-height:18px;padding-bottom:15px;'>
  <p style="color:#959595;">이곳에 없는 상품중 입고를 희망하는 상품을 적어주시면, 입고후에 입력한 휴대전화로 SMS통보를 해드립니다.</p>
</div>

<div id="onRequest-wrap">
<form name="fwrite" id="onReqeust" method="post" action="item_onrequest_writeupdate.php" style="margin:0;">
<input type="hidden" name="mb_id" id="mb_id" value="<?=$member['mb_id']?>">
<div class="title">상품명</div>
<div class="field"><input type="text" name="it_name" id="it_name" size=70 required placeholder="상품명" /></div>
<div class="title">제조사/상품정보 링크(URL)</div>
<div class="field withex">
  <input type="text" name="it_info" id="it_info" size=70 required placeholder="제조사/상품정보 링크(URL)" />
  <div class="ex"> (예:http://www.aaa.com/itemfile.php?itemcode=12345) </div>
</div>

<div class="title">신청자이름</div>
<div class="field"><input type="text" name="mb_name" id="ts_name" size=30 value="<?=$member['mb_name']?>" required placeholder='신청자이름'></div>
<div class="title">휴대전화</div>
<div class="field withex">
  <input type="text" name="mb_hp" id="ts_hp" size=30 value="<?=preg_replace("/[^0-9]/","",$member['mb_hp'])?>" required numeric placeholder='휴대전화'>
    <div class="ex">숫자만 입력하십시오. (예:01012341234)</div>
</div>

<div class="btn"><input type=submit value="확인" class="sbutton"/></div>
</form>
</div>

<?
include_once("./_tail.php");
?>
