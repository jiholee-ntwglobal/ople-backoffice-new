<?php
$sub_menu = "500300";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");
/*
sql_query("create table {$g4['yc4_member_promo']} (
	mp_pid int not null auto_increment primary key,
	mb_id varchar(30) not null,
	mp_mb_count int not null default '0',
	mp_event_id varchar(20) not null,
	mp_datetime datetime not null,
	key index1(mb_id),
	key index2(mp_event_id)
)", false);

sql_query("create table {$g4['yc4_member_promor']} (
	ms_pid int not null auto_increment primary key,
	mb_id varchar(30) not null,
	mb_id2 varchar(30) not null,
	ms_reg_count int not null default '0',
	ms_order_count int not null default '0',
	ms_event_id varchar(20) not null,
	ms_datetime datetime not null,
	ms_ip varchar(20) not null,
	key index1(mb_id, mb_id2),
	key index2(ms_event_id)
)" );

sql_query("create table {$g4['yc4_member_promo_order']} (
	mo_pid int not null auto_increment primary key,
	mb_id2 varchar(30) not null,
	od_id varchar(10) not null,
	mo_datetime datetime not null,
	key index1(mb_id2, od_id)
)" );
*/


$g4[title] = "회원 프로모션관리";
include_once ("$g4[admin_path]/admin.head.php");


$sql_search = "";
$where = " where ";
if ($search != "") {
	if ($sel_field != "") $sql_search .= " $where $sel_field like '$search%' ";
	$where = " and ";
}
if ($sel_field == "") $sel_field = "mp_event_id";

$sql_common = " from {$g4['yc4_member_promo']} ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(mp_pid) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select *
          $sql_common
          order by mp_datetime desc
          limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = "sel_field=$sel_field&search=$search";
$qstr  = "$qstr1&page=$page";
?>
<script type="text/javascript" src="<?=$g4[path]?>/js/sideview.js"></script>

<?=subtitle($g4[title])?>

<fieldset style="margin:5px 10px 5px 0;">
	<legend>안내</legend>
	<div>
		※ 등록자료를 삭제하는 경우, 해당 프로모션에 누적된 내역이 있는경우(구매내역등)는 삭제할 수 없습니다.<br/>
		※ 가입/주문의 숫자를 클릭하면 해당 내역을 조회할 수 있습니다. (주문내역은 배송/완료인 자료만 등록됩니다.)
	</div>
</fieldset>

<form name=flist method=get style="margin:0px;">
<table width=100% cellpadding=4 cellspacing=0>
<tr>
    <td width=50%>
		<a href="<?=$_SERVER['PHP_SELF']?>">처음으로</a> | 전체 : <? echo $total_count ?>
	</td>
    <td width=50% align=right>
        <select name=sel_field>
            <option value='mp_event_id'>이벤트ID</option>
			<option value='mb_id'>회원ID</option>
        </select>
        <? if ($sel_field) echo "<script> document.flist.sel_field.value = '$sel_field';</script>"; ?>
        <input type=text name=search value='<? echo get_text(stripslashes($search)) ?>' title="첫자리부터 일치순으로 검색">
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
</tr>
</table>
</form>

<?if(get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=")){?><div style="width:100%; margin:5px 0 0 0; text-align:center;"><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></div><?}?>


<table border=2 cellspacing=0 cellpadding=2 align="center" bordercolor='#95A3AC'  class="state_table">
<tr>
	<!--<td class=yalign_head width=5%><input type=checkbox id=chk_on /></td>-->
	<td class=yalign_head width=22%>이벤트ID</td>
	<td class=yalign_head width="*">회원아이디<br/>이름</td>
	<td class=yalign_head width=10%>설정인원</td>
	<td class=yalign_head width=8%>가입</td>
	<td class=yalign_head width=8%>주문</td>
	<td class=yalign_head width=12%>등록일</td>
	<td class=yalign_head width=8%><a href='./member_promo_manage_write.php?return_url=member_promo_manage'><img src='<?=$g4[admin_path]?>/img/icon_insert.gif' border=0 title='등록'></a></td>
</tr>
<?
for($k=0; $row=sql_fetch_array($result); $k++)
{
	if($row['mb_id']){
		$mb = get_member($row['mb_id'], 'mb_name,mb_level,mb_nick,mb_email,mb_homepage');
		$mb_nick = get_sideview($row[mb_id], $mb[mb_name], $mb[mb_email], $mb[mb_homepage]);
	}
	$s_mod = icon("수정", "./member_promo_manage_write.php?w=u&mp_pid={$row['mp_pid']}&$qstr");
	$s_del = icon("삭제", "javascript:del('member_promo_manage_writeupdate.php?w=d&mp_pid={$row['mp_pid']}&{$qstr}');");
?>
<tr bgcolor="#FFFFFF" onmouseover="this.style.backgroundColor='#c9c9c9';" onmouseout="this.style.backgroundColor='#FFFFFF';" align=center>
	<!--<td align=center><input type=checkbox name='chk[]' value='<?=$k?>' /></td>-->
	<td><?=$row['mp_event_id']?></td>
	<td align=left>&nbsp;&nbsp;<?=$row['mb_id']?> &nbsp;&nbsp;[ <?=$mb_level_str[$mb['mb_level']]?> (<?=$mb['mb_level']?>) ]<br/>&nbsp;&nbsp;<?=$mb_nick?></td>
	<td><?=nf($row['mp_mb_count'])?></td>
	<td align=right onclick="_js_view('reg', '<?=$row['mb_id']?>');" style="cursor:pointer;" title="가입자 조회"><?=nf($row['mp_reg_count'])?>&nbsp;&nbsp;</td>
	<td align=right onclick="_js_view('order', '<?=$row['mb_id']?>');" style="cursor:pointer;" title="주문내역 조회"><?=nf($row['mp_order_count'])?>&nbsp;&nbsp; </td>
	<td><?=substr($row['mp_datetime'],0,10)?></td>
	<td><?=$s_mod?>&nbsp;<?=$s_del?></td>
</tr>
<?}?>
<?if(!$k) echo "<tr><td align=center height=100 colspan=10>자료가 없습니다.</td></tr>"; ?>
</table>

<div style="width:100%; margin:0 0 5px 0; text-align:center;"><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr1&page=");?></div>



<script type="text/javascript">
function _js_view(str, mb_id)
{
	if(str == 'reg')
		window.open('member_promo_manage_regview.php?mb_id='+mb_id, '_mp_regview_', 'width=500,height=600,scrollbars=1,left=640,top=50');
	else if(str == 'order')
		window.open('member_promo_manage_orderview.php?mb_id='+mb_id, '_mp_orderview_', 'width=700,height=600,scrollbars=1,left=400,top=50');
}
/*
$(document).ready(function()
{
	$('#chk_on').click(function() {
		if($('#chk_on').is(':checked')){
			$("input[name='chk[]']").prop('checked', true);
		}else{
			$("input[name='chk[]']").prop('checked', false);
		}
	});
});
*/

</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>