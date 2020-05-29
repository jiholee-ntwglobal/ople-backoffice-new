<?php
$sub_menu = "500300";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");

$g4['title'] = "회원 프로모션관리 > ".($w == 'u' ? '수정' : '등록');
include_once ("$g4[admin_path]/admin.head.php");

if($w == 'u'){
	$mp = sql_fetch("select * from {$g4['yc4_member_promo']} where mp_pid='$mp_pid' ");
	if(!$mp['mp_pid']) alert("해당하는 회원 프로모션 자료가 없습니다.");
}
?>
<?=subtitle($g4[title])?>

<form name=fconfig method=post action='member_promo_manage_writeupdate.php' onsubmit="return fcheck(this)" style="margin:0px;">
<input type=hidden name=w value='<? echo $w ?>' />
<input type=hidden name=mp_pid value='<?=$mp_pid?>' />
<input type=hidden name=sel_field value='<?=$sel_field?>' />
<input type=hidden name=search value='<?=$search?>' />
<input type=hidden name=page value='<?=$page?>' />

<table border=2 cellspacing=0 cellpadding=2 align="center" bordercolor='#95A3AC'  class="state_table">
<tr>
	<td class=yalign_head width=130>회원ID</td>
	<td style="padding-left:10px;"><input type="text" name="mb_id" id="mb_id" size=30 class="ed" value="<?=$mp['mb_id']?>" />&nbsp;&nbsp;<input type=button value=" 찾 기 " onclick="_jq_open();" /><br/>※ 직접입력도 가능하고, 찾기를 통해 찾아서 선택입력도 가능.</td>
</tr>
<tr>
	<td class=yalign_head>이벤트ID</td>
	<td style="padding-left:10px; line-height:150%;"><input type="text" name="mp_event_id" id="mp_event_id" size=30 class="ed" value="<?=$mp['mp_event_id']?>" /> ※ 영문, 숫자, 언더바(_)<br/>이벤트 URL : http://www.ople.com/mall5/bbs/register_form.php?mp_event=이벤트ID</td>
</tr>
<tr>
	<td class=yalign_head >설정인원</td>
	<td style="padding-left:10px; line-height:150%;"><input type="text" name="mp_mb_count" id="mp_mb_count" size=30 class="ed" value="<?=$mp['mp_mb_count']?>" /> <br/>※ 설정인원이상 누적가입, 주문완료(배송)의 경우, 위의 회원이 VIP LV 로 승급.<BR/>※ 이벤트로 가입한 회원의 주문건은 별도로 누적관리.<BR/>※ 예) 20명이 설정인원이면 20명이 모두 구매내역이 존재해야 한다.</td>
</tr>
<tr>
	<td class=yalign_head>등록일</td>
	<td style="padding-left:10px; line-height:150%;"><input type="text" name="mp_datetime" id="mp_datetime" size=30 class="ed" value="<?=($w == '' ? $g4['time_ymdhis'] : $mp['mp_datetime']);?>" /></td>
</tr>
</table>

<p align=center>
    <input type=submit class=btn1 value='  확  인  ' />&nbsp;
    <input type=button class=btn1 value='  목  록  ' onclick="document.location.replace('./member_promo_manage.php?sel_field=<?=$sel_field?>&search=<?=$search?>&page=<?=$page?>');" />
</p>
</form>


<script type="text/javascript">
function _jq_open()
{
	window.open('member_promo_manage_write_mbsearch.php', '_mp_search_', 'width=500,height=600,scrollbars=1,left=600,top=50');
}

function fcheck(f)
{
	return true;
}
</script>


<?
include_once ("$g4[admin_path]/admin.tail.php");
?>