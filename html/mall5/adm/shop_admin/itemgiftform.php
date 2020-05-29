<?php
$sub_menu = "400512";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");

$html_title = "사은품관리 ";

if ($w == "u")
{
    $html_title .= "(수정)";
    $readonly = " readonly";

    $sql = " select * from $g4[yc4_gift_table] where gift_id = '$gift_id' ";
    $gift = sql_fetch($sql);
    if (!$gift[gift_id])
        alert("등록된 자료가 없습니다.");
}
else
{
    $html_title .= "(입력)";

}

$g4[title] = $html_title;
include_once ("$g4[admin_path]/admin.head.php");
?>
<?=subtitle($html_title);?><p>

<form name=fgiftform method=post action="./itemgiftformupdate.php" enctype="MULTIPART/FORM-DATA" style="margin:0;" onsubmit="return fcheck(this);">
<input type=hidden name=w     value='<? echo $w ?>'>
<input type=hidden name=gift_id value='<? echo $gift_id ?>'>

<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=10%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>

<? if ($w == "u") { ?>
<tr class=ht>
    <td>ID</td>
    <td><?echo $gift_id; ?></td>
</tr>
<? } ?>

<tr class=ht>
    <td>제목</td>
    <td colspan=3><input type=text class=ed name=gift_title size=100 value='<? echo stripslashes($gift[gift_title]) ?>' required itemname='제목'></td>
</tr>
<tr class=ht>
    <td>적용분류</td>
    <td colspan=3>
		<select name="gift_category" id="gift_category">
			<option value="">미사용</option>
			<?
			$sql1 = " select ca_id, ca_name from $g4[yc4_category_table] order by ca_id ";
			$result1 = sql_query($sql1);
			for ($i=0; $row1=sql_fetch_array($result1); $i++)
			{
				$len = strlen($row1[ca_id]) / 2 - 1;
				$nbsp = "";
				for ($i=0; $i<$len; $i++) $nbsp .= "&nbsp;&nbsp;&nbsp;";
				echo "<option value='$row1[ca_id]'>$nbsp$row1[ca_name] ($row1[ca_id])</option>";
			}
			?>
		</select><br/>※ 예) 60 선택시 60xxxx 포함. 미선택시 가격조건만 충족시 적용
		<script type="text/javascript">
		if("<?=$gift['gift_category']?>" != '')
			document.getElementById ('gift_category').value = "<?=$gift['gift_category']?>";
		</script>
	</td>
</tr>
<tr class=ht>
    <td>총수량</td>
    <td colspan=3><input type=text class=ed name=gift_qty_all size=10 value='<?=$gift[gift_qty_all]?>' itemname='총수량'> 개</td>
</tr>
<? if ($w == "u") { ?>
<tr class=ht>
    <td>현재(지급,주문)수량</td>
    <td colspan=3><input type=text class=ed name=gift_qty_now size=10 value='<?=$gift['gift_qty_now'];?>' itemname='현재(지급,주문)수량'> 개</td>
</tr>
<? } ?>
<tr class=ht>
    <td>가격조건</td>
    <td colspan=3><input type=text class=ed name=gift_amount size=10 value='<?=$gift[gift_amount]?>' itemname='가격1'>~
	<input type=text class=ed name=gift_amount2 size=10 value='<?=$gift[gift_amount2]?>' itemname='가격2'><br/>※ 둘다 입력시 이상~이하. 하나만 입력시 이상 또는 이하</td>
</tr>
<tr class=ht>
    <td>시작시간</td>
    <td colspan=3><input type=text class=ed name=gift_st_time size=30 value='<?=($gift[gift_st_time] != '' ? $gift[gift_st_time] : $g4['time_ymdhis']);?>' itemname='시작시간'> ※ 한국시간</td>
</tr>
<tr class=ht>
    <td>마감시간</td>
    <td colspan=3><input type=text class=ed name=gift_ed_time size=30 value='<?=($gift[gift_ed_time] != '' ? $gift[gift_ed_time] : date("Y-m-d H:i:s", strtotime("+1 month", strtotime($g4['time_ymdhis']))));?>' itemname='마감시간'> ※ 한국시간</td>
</tr>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<p align=center>
    <input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type=button class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.replace('./itemgift.php?page=<?=$page?>');">
</form>

<script type="text/javascript">
function fcheck(f)
{
	return true;
}
</script>
<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
