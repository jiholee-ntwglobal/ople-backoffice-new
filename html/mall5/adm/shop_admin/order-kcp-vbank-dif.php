<?php
$sub_menu = "400820";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$g4[title] = "KCP-가상계좌확인";
include_once ("$g4[admin_path]/admin.head.php");


############## // 김선용 2014.04 : 이 파일은 사용하지 않습니다.

$sql_search = "";
if ($search != "")
{
	if ($sel_field != "")
    {
		// 김선용 2014.04 : full-text 컬럼 리-인덱싱 처리
		// od_name, mb_id 컬럼만 해당
		if($sel_field == 'od_name' || $sel_field == 'mb_id')
		//	$sql_search .= " and match($sel_field) against('$search') "; // full-text 는 검색어=매치 이므로 불편함을 줄이고자 아래 b-tree 로 대체
			$sql_search .= " and $sel_field like '$search%' ";
		else
			$sql_search .= " and $sel_field like '%$search%' ";
    }
    if ($save_search != $search)
        $page = 1;
}
if ($sel_field == "")  $sel_field = "od_id";
if ($sort1 == "") $sort1 = "od_id";
if ($sort2 == "") $sort2 = "desc";

$sql_common = " from $g4[yc4_order_table] where od_settle_case='가상계좌' and kcp_vbank_dif=1 $sql_search ";
$sql = sql_query("select od_id {$sql_common} ");
$total_count = mysql_num_rows($sql);

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select *
           $sql_common
           group by od_id
           order by $sort1 $sort2
           limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = "sel_field=$sel_field&search=$search&save_search=$search";
$qstr = "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";
?>
<b> // 김선용 2014.04 : 이 파일은 사용되지 않습니다.</b>
<fieldset style="margin:0; padding:5px 5px 5px 5px; width:100%; border:4px solid #5A6973; line-height:150%;">
<legend style="color:blue;">안내</legend>
※ KCP 가상계좌 입금내역이 결제할 금액과 다른 주문서를 출력합니다.<BR/>
※ 주문번호를 클릭시, 새창으로 주문서 상세보기를 띄웁니다.<br/>
※ 확인이 끝난 주문서는 체크박스를 선택해서 '확인완료' 버튼을 눌러 주십시오. 그래야 이 목록에서 제외됩니다.
</fieldset>


<form name=frmorderlist style="margin:0;">
<input type=hidden name=sort1 value="<? echo $sort1 ?>">
<input type=hidden name=sort2 value="<? echo $sort2 ?>">
<input type=hidden name=page  value="<? echo $page ?>">

<table width=100% cellpadding=4 cellspacing=0>
<tr>
    <td width=20%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=60% align=center>
        <select name=sel_field>
            <option value='od_id'>주문번호
            <option value='mb_id'>회원 ID
            <option value='od_name'>주문자
        </select>
        <input type=hidden name=save_search value='<?=$search?>'>
        <input type=text name=search value='<? echo $search ?>' autocomplete="off">
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
    <td width=20% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>
</form>


<form name="vbank" method="post" action="order-kcp-vbank-dif-update.php" style="margin:0;" onsubmit="return fcheck(this);">
<input type=hidden name=sel_field  value="<? echo $sel_field ?>">
<input type=hidden name=search     value="<? echo $search ?>">
<input type=hidden name=page       value="<? echo $page ?>">

<table width=100% cellpadding=0 cellspacing=0 border=0>
<tr><td colspan=12 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center>
	<th align=left><input type=checkbox id=chk_on style="height:20px; width:20px;" /></th>
	<th height=30>주문번호</th>
	<th>주문자</th>
	<th>회원 ID</TH>
	<th>입금액</th>
	<th>결제할금액</th>
</tr>
<tr><td colspan=12 height=1 bgcolor=#CCCCCC></td></tr>

<? for($i=0; $row=sql_fetch_array($result); $i++)
{
	$mb_nick = get_sideview($row[mb_id], $row[od_name], $row[od_email], '');
?>
<tr>
	<td><input type=checkbox name="chk[]" value="<?=$row['od_id']?>" style="height:20px; width:20px;" /></td>
	<td><a href="orderform.php?od_id=$row[od_id]" target="_blank" title="새창으로 주문서보기"><?=$row['od_id']?></a></td>
	<td><?=$mb_nick?></td>
	<td><?=$row['mb_id']?></td>
	<td><?=nf($row['od_receipt_bank'])?></td>
	<td><?=nf($row['od_temp_bank'])?></td>
</tr>
<?
}
if ($i == 0)
    echo "<tr><td colspan=12 align=center height=100 bgcolor='#FFFFFF'><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
?>
<tr><td colspan=12 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<p align=right style="margin:10px 0 3px 0;"><input type=submit style="height:35px; width:150px; font-size:20px; font-weight:bold;" value='확인완료'></p>

</form>


<script type="text/javascript">

function fcheck(f)
{
	var chk = false;
	var a = document.getElementsByName('chk[]');
	for(k=0; k<a.length; k++){
		if(a[k].checked){
			chk = true;
		}
	}
	if(!chk){
		alert("변경할 자료를 하나이상 선택하십시오.");
		return false;
	}
    return true;
}

$(function()
{
	$('#chk_on').click(function() {
		if($('#chk_on').is(':checked')){
			$("input[name='chk[]']").prop('checked', true);
		}else{
			$("input[name='chk[]']").prop('checked', false);
		}
	});
});

</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>