<?php
$sub_menu = "200500";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$g4[title] = "추천인리포트";
include_once ("$g4[admin_path]/admin.head.php");


$sql_common = " from {$g4['yc4_rc_table']} where 1 ";
if(trim($stx) != '')
{
	$stx = trim($stx);
	$sql_common .= " and instr($sfl, '$stx') ";
}

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt {$sql_common} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = "select * {$sql_common} order by rc_pid desc limit $from_record, $rows";
$result = sql_query($sql);

$qstr = "sfl=$sfl&stx=".urlencode($stx);
?>
<?=subtitle($g4[title])?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td valign=top>

	<table width=100% cellpadding=0 cellspacing=0>
	<tr>
		<td align=center><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
	</tr>
	</table>

	<form name=fhsearch style="margin:0;">
	<table cellpadding=0 cellspacing=0 width=100% align=center>
	<tr>
		<td height=28 width=50%>
			<a href="<?=$_SERVER['PHP_SELF']?>">처음으로</a> | 전체 : <? echo $total_count ?>
		</td>
		<td width=50% align=right>
		<select name=sfl>
			<option value='mb_id'>회원ID</option>
			<option value='od_id'>주문번호</option>
		</select>
		<? if ($sfl) echo "<script> document.fhsearch.sfl.value = '$sfl';</script>"; ?>
		<input type=text size=12 name=stx value='<?=get_text(stripslashes($stx))?>'>
		<input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
		</td>
	</tr>
	</table>
	</form>


	<table border=2 cellspacing=0 cellpadding=2 align="center" bordercolor='#95A3AC'  class="state_table">
	<tr>
		<td class=yalign_head>회원아이디</td>
		<td class=yalign_head>주문번호</td>
		<td class=yalign_head>할인금액</td>
		<td class=yalign_head>적립포인트</td>
		<td class=yalign_head>구분</td>
		<td class=yalign_head>처리일시</td>
	</tr>
	<?
	for($k=0; $row=sql_fetch_array($result); $k++)
	{
		$mb_nick = '';
		if($row['mb_id']){
			$mb = get_member($row['mb_id'], 'mb_nick,mb_email,mb_homepage');
			$mb_nick = get_sideview($row[mb_id], $mb[mb_nick], $mb[mb_email], $mb[mb_homepage]);
		}
	?>
	<tr bgcolor="#FFFFFF" onmouseover="this.style.backgroundColor='#c9c9c9';" onmouseout="this.style.backgroundColor='#FFFFFF';" align=center>
		<td>(<?=$row['mb_id']?>)<br/><?=$mb_nick?></td>
		<td><a href="orderform.php?od_id=<?=$row['od_id']?>" title="새창으로 주문서보기" target="_blank"><?=$row['od_id']?></a></td>
		<td><?=($row['rc_off_sale'] ? nf($row['rc_off_sale']) : '-');?></td>
		<td><?=($row['rc_save_point'] ? nf($row['rc_save_point']) : '-');?></td>
		<td><?=($row['rc_part'] == 'order' ? '할인' : '적립');?></td>
		<td><?=$row['rc_datetime']?></td>
	</tr>
	<?}?>

	<?if(!$k) echo "<tr><td height=100 align=center colspan=10>자료가 없습니다.</td></tr>"; ?>

	</table><br>

	<table width=100% cellpadding=0 cellspacing=0>
	<tr>
		<td align=center><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
	</tr>
	</table>

	</td>
</tr>
</table><br/>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>