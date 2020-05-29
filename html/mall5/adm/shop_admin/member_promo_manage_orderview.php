<?php
$sub_menu = "500300";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

if(!$_GET['mb_id']) alert_close("이벤트회원 ID가 없습니다. 다시 확인해 주십시오.");


$g4[title] = "회원 프로모션관리 : 주문내역조회";
include_once ("$g4[path]/head.sub.php");

$sql_search = "";
if($search != ""){
    if ($sfl != "")
        $sql_search .= " and $sfl like '$search%' ";
}
// 차단, 탈퇴회원은 제외
$sql_common = " from $g4[yc4_member_promor] a left join $g4[yc4_member_promo_order] b on a.mb_id2=b.mb_id2 where a.mb_id='$mb_id' and b.od_id<>'' {$sql_search} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(b.od_id) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

if(!$items) $items = $config['cf_page_rows'];
$rows = $items;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$sql  = " select *, b.mb_id2 as od_mb_id2
           $sql_common
           order by b.mo_pid desc
           limit $from_record, $rows ";
$result = sql_query($sql);


$qstr1 = "mb_id=$mb_id&sfl=$sfl&search=".urlencode($search);
$qstr  = "$qstr1&page=$page";
?>
<script type="text/javascript" src="<?=$g4[path]?>/js/sideview.js"></script>

<div style="padding:4px; border:4px #eaeaea solid; text-align:center; margin-top:5px;">
※ 이벤트회원 : <?=$mb_id?><br/>

<form name=fhsearch METHOD=GET style="margin:0;">
<input type="hidden" name="mb_id" value="<?=$mb_id?>" />

<select name="items" id="items">
	<option value="">목록수</option>
	<?for($k=2; $k<21; $k++){?>
	<option value="<?=($k*5)?>" <?if($items==($k*5)) echo 'selected';?>><?=($k*5)?></option>
	<?}?>
</select>
<select name=sfl>
	<option value='a.mb_id2'>회원ID</option>
</select>
<? if ($sfl) echo "<script> document.fhsearch.sfl.value = '$search';</script>"; ?>
<input type=text size=30 name=search value='<?=stripslashes(get_text($_GET['search']));?>' title="첫자리부터 일치순으로 검색">
<input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle><br/>
</form>
</div>

<table width=100% cellpadding=4 cellspacing=0 border=0>
<tr>
    <td><a href='<?=$_SERVER[PHP_SELF]?>?mb_id=<?=$mb_id?>'><b>처음으로</b></a></td>
    <td align=right>전체 : <?=nf($total_count) ?>&nbsp;</td>
</tr>
</table>

<table border=2 cellspacing=0 cellpadding=2 align="center" bordercolor='#95A3AC'  class="state_table">
<tr align=center>
	<td class=yalign_head width="*">ID<BR/>성명</TD>
	<td class=yalign_head width=85>주문번호</td>
	<td class=yalign_head width=90>주문합계</td>
	<td class=yalign_head width=90>결제금액</td>
	<td class=yalign_head width=110>휴대전화</td>
    <td class=yalign_head width=70>등급</td>
	<td class=yalign_head width=80>주문일</td>
</tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
	$mb = get_member($row['mb_id2'], "mb_name,mb_hp,mb_level,mb_email");
	$mb_nick = get_sideview($row[mb_id2], $mb[mb_name], $mb[mb_email], $mb[mb_homepage]);

	$od = sql_fetch("select * from {$g4['yc4_order_table']} where od_id='{$row['od_id']}' and mb_id='{$row['mb_id2']}' ");

?>
	<tr bgcolor="#FFFFFF" onmouseover="this.style.backgroundColor='#c9c9c9';" onmouseout="this.style.backgroundColor='#FFFFFF';" align=center>
		<TD><?=$row['mb_id2']?><br/><?=$mb_nick?></td>
		<td><a href="./orderform.php?od_id=<?=$row['od_id']?>" target="_blank" title="새창으로 주문서보기"><?=$row['od_id']?></a></td>
		<td><?=nf($od['od_temp_bank']+$od['od_temp_card'])?></td>
		<td><?=nf($od['od_receipt_bank']+$od['od_receipt_card']+$od['od_receipt_point'])?></td>
		<td><?=$od['od_hp']?></td>
		<td><?=$mb_level_str[$mb['mb_level']]?> (<?=$mb['mb_level']?>)</td>
		<td title="주문일시:<?=$od['od_time']?>"><?=substr($od['od_time'],0,10);?></td>
    </tr>
<?}?>
<?if ($i == 0)
    echo "<tr><td colspan=20 align=center height=100 bgcolor=#FFFFFF><span class=point>자료가 없습니다.</span></td></tr>";
?>
</table>
<br/>
<p style="margin-left:20px; float:left;"><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?items=$items&$qstr1&page=");?></p>
<p style="margin:0; float:right;"><input type="button" value=" 닫/기 " onclick="window.close();" title="닫기"></p>


<? include_once ("$g4[path]/tail.sub.php"); ?>