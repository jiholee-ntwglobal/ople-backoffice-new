<?php
$sub_menu = "300777";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$g4[title] = "상품입고요청관리";

if ($search != "") {
	if ($sel_field != "") {
    	$sql_search .= " and $sel_field like '%$search%' ";
    }
}
if ($sel_field == "")  $sel_field = "mb_id";

$sql_common = " from {$g4['yc4_onrequest_table']} ";
if($on_sms_post)
	$sql_common .= " where on_sms_post=1 ";
else
	$sql_common .= " where on_sms_post=0 ";

$sql_common .= $sql_search;

if($mode == 'excel'){

    include $g4['full_path'] . '/classes/PHPExcel.php';
    $objPHPExcel = new PHPExcel();
    $excel_title = '상품입고요청_'.date('Ymd_His');
    $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
        ->setTitle($excel_title)
        ->setSubject($excel_title)
        ->setDescription($excel_title);

    $active_sheet = $objPHPExcel->getActiveSheet();


    $active_sheet->getColumnDimension('A')->setWidth(15);
    $active_sheet->getColumnDimension('B')->setWidth(15);
    $active_sheet->getColumnDimension('C')->setWidth(15);
    $active_sheet->getColumnDimension('D')->setWidth(35);
    $active_sheet->getColumnDimension('E')->setWidth(40);
    $active_sheet->getColumnDimension('F')->setWidth(15);

    $active_sheet->getCell('A1')->setValueExplicit('ID', PHPExcel_Cell_DataType::TYPE_STRING);
    $active_sheet->getCell('B1')->setValueExplicit('이름', PHPExcel_Cell_DataType::TYPE_STRING);
    $active_sheet->getCell('C1')->setValueExplicit('휴대전화', PHPExcel_Cell_DataType::TYPE_STRING);
    $active_sheet->getCell('D1')->setValueExplicit('요청상품명', PHPExcel_Cell_DataType::TYPE_STRING);
    $active_sheet->getCell('E1')->setValueExplicit('요청상품부가설명', PHPExcel_Cell_DataType::TYPE_STRING);
    $active_sheet->getCell('F1')->setValueExplicit('등록일자', PHPExcel_Cell_DataType::TYPE_STRING);

    $sql  = " select * $sql_common order by on_pid desc ";

    $result = sql_query($sql);

    for($k=0; $row=sql_fetch_array($result); $k++){

        $line = $k+2;

        $active_sheet->getCell('A'.$line)->setValueExplicit($row['mb_id'], PHPExcel_Cell_DataType::TYPE_STRING);
        $active_sheet->getCell('B'.$line)->setValueExplicit($row['on_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $active_sheet->getCell('C'.$line)->setValueExplicit($row['on_hp'], PHPExcel_Cell_DataType::TYPE_STRING);
        $active_sheet->getCell('D'.$line)->setValueExplicit($row['on_it_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $active_sheet->getCell('E'.$line)->setValueExplicit($row['on_it_info'], PHPExcel_Cell_DataType::TYPE_STRING);
        $active_sheet->getCell('F'.$line)->setValueExplicit($row['on_datetime'], PHPExcel_Cell_DataType::TYPE_STRING);

    }

    $objPHPExcel->getActiveSheet()->setTitle($excel_title);
    $filename = $excel_title . '.xls';
    header('Content-Type: application/vnd.ms-excel'); //mime type
    header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
    header('Cache-Control: max-age=0'); //no cache

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    //force user to download the Excel file without writing it to server's HD
    $objWriter->save('php://output');
    exit;
}

// 테이블의 전체 레코드수만 얻음
$sql = " select count(on_pid) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select * $sql_common order by on_pid desc limit $from_record, $rows ";
$result = sql_query($sql);

$qstr = "on_sms_post=$on_sms_post&sel_field=$sel_field&search=".urlencode($search);
$qstr1 = "$qstr&page=$page";


include_once ("$g4[admin_path]/admin.head.php");
?>
<?=subtitle($g4[title])?>
<form name=flist method=get style="margin:0px;">
<table width=100% cellpadding=4 cellspacing=0>
<tr>
    <td width=50%>
		<a href="<?=$_SERVER['PHP_SELF']?>?on_sms_post=<?=$on_sms_post?>">처음으로</a>&nbsp;&nbsp;|&nbsp;&nbsp;전체 : <? echo $total_count ?>&nbsp;&nbsp;|&nbsp;&nbsp;
		<input type=button class=btn1 value=" 미등록자료 " onclick="self.location.replace('item_onrequest_list.php');">&nbsp;
		<input type=button class=btn1 value=" 등록자료 " onclick="self.location.replace('item_onrequest_list.php?on_sms_post=1');">
        <a class=btn1 href="item_onrequest_list.php?mode=excel&search=<?php echo $search; ?>&sel_field=<?php echo $sel_field;?>&on_sms_post=<?php echo $on_sms_post; ?>" taget="_blank">Excel Dowload</a>
	</td>
    <td width=50% align=right>
        <select name=sel_field>
			<option value='mb_id'>회원ID</option>
            <option value='on_name'>신청자이름</option>
            <option value='on_it_name'>신청상품명</option>
        </select>
        <? if ($sel_field) echo "<script> document.flist.sel_field.value = '$sel_field';</script>"; ?>
        <input type=text name=search value='<? echo get_text(stripslashes($search)) ?>'>
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
</tr>
</table>
</form>

<?if(get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=")){?><div style="width:100%; margin:5px 0 0 0; text-align:center;"><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></div><?}?>

<form name=fitemcardonly method=post action="./item_onrequest_listupdate.php" style="margin:0;">
<input type=hidden name=sel_field  value="<? echo $sel_field ?>">
<input type=hidden name=search     value="<? echo $search ?>">
<input type=hidden name=page       value="<? echo $page ?>">
<input type=hidden name=on_sms_post value="<? echo $on_sms_post?>">

<?if(!$on_sms_post){?>
<? ob_start(); ?>
<p align=right style="margin:3px 0 3px 0;"><input type=submit class=btn1 value='SMS 발송'></p>
<?
$all_update_button = ob_get_contents();
ob_end_flush();
?>
<?}?>

<table border=2 cellspacing=0 cellpadding=2 align="center" bordercolor='#95A3AC'  class="state_table">
<tr>
	<?if(!$on_sms_post){?><td class=yalign_head width=5%><input type=checkbox id=chk_on /></td><?}?>
	<td class=yalign_head width=''>신청상품명<br/>상품정보</td>
	<td class=yalign_head width=12%>회원아이디<br/>신청자이름</td>
	<td class=yalign_head width=12%>휴대전화</td>
	<td class=yalign_head width=5%>통보</td>
	<td class=yalign_head width=9%>통보일</td>
	<td class=yalign_head width=9%>신청일</td>
	<td class=yalign_head width=8%>삭제</td>
</tr>
<?
for($k=0; $row=sql_fetch_array($result); $k++)
{
	$mb_nick = $row['on_name'];
	if($row['mb_id']){
		$mb = get_member($row['mb_id'], 'mb_nick,mb_email,mb_homepage');
		$mb_nick = get_sideview($row[mb_id], $mb[mb_nick], $mb[mb_email], $mb[mb_homepage]);
	}
	$s_del = icon("삭제", "javascript:del('item_onrequest_listupdate.php?w=d&on_pid={$row['on_pid']}&{$qstr1}');");

	echo "<input type='hidden' name='on_pid[]' value='{$row['on_pid']}'>\n";
	echo "<input type='hidden' name='on_hp[]' value='{$row['on_hp']}'>\n";
	echo "<input type='hidden' name='on_name[]' value='{$row['on_name']}'>\n";
	echo "<input type='hidden' name='on_it_name[]' value='{$row['on_it_name']}'>\n";
?>
<tr align=center>
	<?if(!$on_sms_post){?><td align=center><input type=checkbox name='chk[]' value='<?=$k?>' /></td><?}?>
	<td style='padding:5px;' align=left>
	<table cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td colspan=2>
			<?=stripslashes($row['on_it_name'])?><hr><?=url_auto_link(stripslashes($row['on_it_info']))?></hr>
			<?if($row['it_id'] == ''){?>
			<hr>상품코드입력 : <input type="text" name="it_id[]" id="it_id[<?=$k?>]" size=10 maxlength=10 value="" /> <input type="button" value="상품찾기" class="jq_sel" /></hr>
			<?}?>
		</td>
	</tr>
	<?
	$s_mod = "";
	if($row['it_id']){ // 등록된상품
		$it = sql_fetch("select it_name from {$g4['yc4_item_table']} where it_id='{$row['it_id']}' ");
		$href = "{$g4[shop_path]}/item.php?it_id=$row[it_id]";
		$s_mod = icon("수정", "./itemform.php?w=u&it_id=$row[it_id]");
	?>
	<tr><td height=3 colspan=10></td></tr>
	<tr><td height=1 bgcolor=gray colspan=10></td></tr>
	<tr><td height=3 colspan=10></td></tr>
	<tr>
		<td width=60><a href='<?=$href?>' target=_blank title='새창으로 상품보기'><?=get_it_image("{$row[it_id]}_s", 60, 50)?></a></td>
		<td style="padding-left:4px;"><a href='<?=$href?>' target=_blank title='새창으로 상품보기'><?=stripslashes($it['it_name'])?></a></td>
	</tr>
	<?}?>

	</tr>
	</table>
	</td>
	<td><?=($row['mb_id'] != '' ? "({$row['mb_id']})<br/>" : "");?><?=$mb_nick?></td>
	<td><?=$row['on_hp']?></td>
	<td><?=($row['on_sms_post'] ? '통보' : '-')?></td>
	<td><?=(is_null_time($row['on_sms_post_datetime']) ? '-' : str_replace(' ', '<br/>', $row['on_sms_post_datetime']))?></td>
	<td><?=str_replace(' ', '<br/>', $row['on_datetime'])?></td>
	<td align=center><?=$s_mod?>&nbsp;<?=$s_del?></td>
</tr>
<?}?>
<?if(!$k) echo "<tr><td align=center height=100 colspan=10>자료가 없습니다.</td></tr>"; ?>
</table>

<?=$all_update_button?>

</form>
<br/>

<div style="width:100%; margin:0 0 5px 0; text-align:center;"><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></div>


<div id="_jq_it_search_" style="width:600px; height:600px;"></div> <!-- // 김선용 201211 : 상품서치용 -->

<script type="text/javascript">
$(document).ready(function()
{
	$('#chk_on').click(function() {
		if($('#chk_on').is(':checked')){
			$("input[name='chk[]']").prop('checked', true);
		}else{
			$("input[name='chk[]']").prop('checked', false);
		}
	});

	$('.jq_sel').click(function()
	{
		var i = $('.jq_sel').index(this);
		window.open('item_onrequest_it_search.php?class_index='+i, '_it_search_', 'width=700,height=500,scrollbars=1,left=550,top=100');
	});
});
</script>


<? include_once ("$g4[admin_path]/admin.tail.php"); ?>