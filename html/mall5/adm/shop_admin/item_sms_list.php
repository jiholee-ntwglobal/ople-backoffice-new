<?php
$sub_menu = "300940";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$title_str = ($ts_send ? '통보자료' : '미통보자료');
$g4[title] = "상품입고 SMS통보 관리 : {$title_str}";
include_once ("$g4[admin_path]/admin.head.php");

$sql_common = " from {$g4['item_sms_table']} ";
if($ts_send)
	$sql_common .= " where ts_send=1 ";
else
	$sql_common .= " where ts_send=0 ";

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

$sql  = "select * {$sql_common} order by ts_id desc limit $from_record, $rows";
$result = sql_query($sql);

$qstr = "ts_send=$ts_send&sfl=$sfl&stx=".urlencode($stx);
?>
<?=subtitle($g4[title])?>
<fieldset style="margin:5px 10px 5px 0;">
	<legend>설명</legend>
	<div>※ 처음 보여지는 자료는 미통보 자료입니다. 통보한 자료는 '통보자료' 를 통해 조회 하십시오.<br/>※ SMS 통보한 자료는 자동으로 통보자료로 변경되고, 통보한자료를 '미통보' 자료로 변경하면 다시 '미통보자료' 에 넘겨집니다.
	</div>
</fieldset>


<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td valign=top>
	<table cellpadding=0 cellspacing=0 width=100% align=center>
	<tr>
		<td height=28 width=60%>
			<a href="<?=$_SERVER['PHP_SELF']?>?ts_send=<?=$ts_send?>">처음으로</a> |
			전체 : <? echo $total_count ?> |
			<input type=button class=btn1 value=" 미통보자료 " onclick="self.location.href='item_sms_list.php';">&nbsp;
			<input type=button class=btn1 value=" 통보자료 " onclick="self.location.href='item_sms_list.php?ts_send=1';">
		</td>
		<td width=40% align=right>
		<form name=fhsearch style="margin:0;">
		<select name=sfl>
			<option value='mb_id'>회원ID</option>
			<option value='ts_hp'>휴대전화</option>
			<option value='ts_name'>이름</option>
			<option value='it_id'>상품코드</option>
		</select>
		<? if ($sfl) echo "<script> document.fhsearch.sfl.value = '$sfl';</script>"; ?>
		<input type=text size=12 name=stx value='<?=get_text(stripslashes($stx))?>'>
		<input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
		</form>
		</td>
	</tr>
	</table>

	<form name="flist" method="post" action="item_sms_listupdate.php" onsubmit="return all_post(this);" style="margin:0;">
	<input type="hidden" name="ts_send" id="ts_send" value="<?=$ts_send?>">
	<input type="hidden" name="page" id="page" value="<?=$page?>">
	<input type="hidden" name="sfl" id="sfl" value="<?=$sfl?>">
	<input type="hidden" name="stx" id="stx" value="<?=$stx?>">

	<table border=2 cellspacing=0 cellpadding=2 align="center" bordercolor='#95A3AC'  class="state_table">
	<tr>
		<td class=yalign_head width=30><input type=checkbox name=chkall value='1' onclick='check_all(this.form)'></td>
		<td class=yalign_head width=>상품명</td>
		<td class=yalign_head width=80>회원아이디</td>
		<td class=yalign_head width=55>이름</td>
		<td class=yalign_head width=90>휴대전화</td>
		<td class=yalign_head width=55>통보여부</td>
		<td class=yalign_head width=70>통보일</td>
		<td class=yalign_head width=70>신청일</td>
		<td class=yalign_head width=55>처리</td>
	</tr>
	<?
	for($k=0; $row=sql_fetch_array($result); $k++)
	{
		$mb_nick = '';
		if($row['mb_id']){
			$mb = get_member($row['mb_id'], 'mb_nick,mb_email,mb_homepage');
			$mb_nick = get_sideview($row[mb_id], $mb[mb_nick], $mb[mb_email], $mb[mb_homepage]);
		}
		$it = sql_fetch("select it_name from {$g4['yc4_item_table']} where it_id='{$row['it_id']}'");
		$href = "{$g4[shop_path]}/item.php?it_id=$row[it_id]";
		$s_mod = icon("수정", "./itemform.php?w=u&it_id=$row[it_id]");
		$s_del = icon("삭제", "javascript:del('item_sms_listupdate.php?w=d&ts_id={$row['ts_id']}&{$qstr}&page=$page');");

		echo "<input type='hidden' name='ts_id[]' value='{$row['ts_id']}'>\n";
		echo "<input type='hidden' name='ts_hp[]' value='{$row['ts_hp']}'>\n";
		echo "<input type='hidden' name='ts_name[]' value='{$row['ts_name']}'>\n";
		echo "<input type='hidden' name='it_name[]' value='{$it['it_name']}'>\n";
	?>
	<tr align=center>
		<td align=center><input type=checkbox name=chk[] value='<?=$k?>'></td>
		<td style='padding:5px;' align=left>
		<table cellpadding=0 cellspacing=0 width=100%>
		<tr>
			<td width=60><a href='<?=$href?>' target=_blank title='새창으로 상품보기'><?=get_it_image("{$row[it_id]}_s", 60, 50)?></a></td>
			<td style="padding-left:4px;"><a href='<?=$href?>' target=_blank title='새창으로 상품보기'><?=stripslashes($it['it_name'])?></a></td>
		</tr>
		</table>
		</td>
		<td>(<?=$row['mb_id']?>)<br><?=$mb_nick?></td>
		<td><?=$row['ts_name']?></td>
		<td><?=$row['ts_hp']?></td>
		<td><?=($row['ts_send'] ? '통보' : '미통보')?></td>
		<td><?=str_replace(' ', '<br/>', $row['ts_send_time'])?></td>
		<td><?=str_replace(' ', '<br/>', $row['ts_time'])?></td>
		<td align=center><?=$s_mod?>&nbsp;<?=$s_del?></td>
	</tr>
	<?}?>

	<?if(!$k) echo "<tr><td height=100 align=center colspan=10>자료가 없습니다.</td></tr>"; ?>

	</table><br>

	<table width=100% cellpadding=0 cellspacing=0>
	<tr>
		<td><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
		<td align=right>
		<?if($ts_send){?>
			<input type="submit" value=" 미통보자료로 변경 " class=btn1>
		<?}else{?>
			<input type="submit" value=" SMS 발송 " class=btn1>
		<?}?>
		</td>
	</tr>
	</table>
	</form>

	</td>
</tr>
</table><br>

<script type="text/javascript">
<!--
function all_post(f)
{
    var a = false;
    for (var i=0; i<f.elements.length; i++)
    {
        if(f.elements[i].name == 'chk[]' && f.elements[i].checked)
        {
            a = true;
            break;
        }
    }
    if (a == false) {
        alert("처리할 자료를 1개 이상 선택하십시오.");
        return false;
    }
	return true;
}
//-->
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>