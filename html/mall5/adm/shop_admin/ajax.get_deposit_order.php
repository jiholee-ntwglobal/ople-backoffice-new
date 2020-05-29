<?php 
/*
----------------------------------------------------------------------
file name	 : ajax.get_deposit_order.php
comment		 : 무통자 입금 오더 찾기 ajax
date		 : 2015-04-13
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "400992";

include_once("./_common.php");

//auth_check($auth[$sub_menu], "r");


?>
<form id="find_order_frm" onsubmit="return search_deposit_form();">
<input type="hidden" name="tr_num" value="<?php echo $_POST['tr_num']; ?>" />
<input type="hidden" name="deposit_name" value="<?php echo $_POST['deposit_name']; ?>" />
<input type="hidden" name="amount" value="<?php echo $_POST['amount']; ?>" />
<table>
	<tr>
		<td colspan="3"><strong>입금자명 : <?php echo $_POST['deposit_name']; ?> 입금액 : <?php echo number_format($_POST['amount']); ?>원</strong></td>
	</tr>
	<tr>
		<td>
			<select name="search_select">
				<option value="od_id" <?php if($_POST['search_select'] == 'od_id') echo 'selected'; ?>>주문번호</option>
				<option value="mb_id" <?php if($_POST['search_select'] == 'mb_id') echo 'selected'; ?>>주문자아이디</option>
				<option value="od_name" <?php if($_POST['search_select'] == 'od_name') echo 'selected'; ?>>주문자명</option>
				<option value="od_deposit_name" <?php if($_POST['search_select'] == 'od_deposit_name') echo 'selected'; ?>>입금자명</option>		
				<option value="remain_amount" <?php if($_POST['search_select'] == 'remain_amount') echo 'selected'; ?>>미수금</option>		
			</select></td>
		<td><input type="text" name="search_value" value="<?php echo $_POST['search_value']; ?>"/></td>
		<td><input type="button" value="검색" onclick="search_deposit_form()"/></td>
	</tr>
</table>
</form>
<?php 

if($_POST['search_select'] && $_POST['search_value']){ 

	$page = ($_POST['page']) ? $_POST['page'] : 1;

	if($_POST['search_select'] == 'remain_amount'){
		$add_where = "if(o.od_settle_case='신용카드',o.od_temp_card-o.od_dc_amount-o.od_receipt_card,o.od_temp_bank-o.od_dc_amount-o.od_receipt_bank) = '$_POST[search_value]'";
	} else {
		$add_where = "o.{$_POST[search_select]} = '$_POST[search_value]'";
	}


	// 테이블의 전체 레코드수만 얻음
	$row = sql_fetch("
				 SELECT count(distinct o.od_id) AS cnt
				  FROM yc4_order o LEFT OUTER JOIN yc4_cart c ON o.on_uid = c.on_uid
				 WHERE     (   (o.od_receipt_bank = 0 AND o.od_temp_bank > 0)
							OR (o.od_receipt_card = 0 AND o.od_temp_card > 0))
					   AND $add_where
					   AND date_format(o.od_time,'%Y%m%d')>='".date('Ymd',strtotime('-7day'))."'
					   AND c.ct_status = '주문'");
	$total_count = $row[cnt];

	$rows = $config[cf_page_rows];
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$rs = sql_query("
			 SELECT distinct o.od_id,
			   o.od_name,
			   o.mb_id,
			   o.od_hp,
			   o.od_b_hp,
			   o.od_temp_card,
			   o.od_temp_bank,
			   o.od_temp_point,
			   o.od_dc_amount,
			   o.od_receipt_bank,
			   o.od_receipt_card,
			   o.od_time,
			   o.od_settle_case
		  FROM yc4_order o LEFT OUTER JOIN yc4_cart c ON o.on_uid = c.on_uid
		 WHERE     (   (o.od_receipt_bank = 0 AND o.od_temp_bank > 0)
					OR (o.od_receipt_card = 0 AND o.od_temp_card > 0))				
			   AND $add_where
			   AND date_format(o.od_time,'%Y%m%d')>='".date('Ymd',strtotime('-7day'))."'
			   AND c.ct_status = '주문'
		ORDER BY o.od_id DESC
		 LIMIT $from_record, $rows");

	while($data = sql_fetch_array($rs)){

		$contents .= "
		<tr align='center' class='search_data_tr'>
			<td>$data[od_id]</td>
			<td>$data[od_name]</td>
			<td>$data[mb_id]</td>
			<td>".preg_replace("/\s+/","",$data['od_hp'])."</td>
			<td>".preg_replace("/\s+/","",$data['od_b_hp'])."</td>
			<td>$data[od_settle_case]</td>
			<td>".number_format(($data['od_settle_case'] == '신용카드' ? $data['od_temp_card'] + $data['od_temp_point'] + $data['od_dc_ammount'] : $data['od_temp_bank'] + $data['od_temp_point'] + $data['od_dc_ammount']))."</td>
			<td>".number_format($data['od_temp_point'])."</td>
			<td>".number_format($data['od_dc_amount'])."</td>
			<td>".number_format(($data['od_settle_case'] == '신용카드' ? $data['od_temp_card'] - $data['od_dc_amount'] : $data['od_temp_bank'] - $data['od_dc_amount']))."</td>
			<td>".number_format(($data['od_settle_case'] == '신용카드' ?  $data['od_temp_card'] - $data['od_dc_amount'] - $data['od_receipt_card'] : $data['od_temp_bank'] - $data['od_dc_amount'] - $data['od_receipt_bank']))."</td>
			<td>".($data['od_settle_case'] == '무통장' ? "<input type=\"button\" value=\"적용\" onclick=\"sel_order('$_POST[tr_num]', '$data[od_name]', '$data[od_id]', '$data[od_time]', '".preg_replace("/\s+/","",$data['od_hp'])."', '".preg_replace("/\s+/","",$data['od_b_hp'])."', '".number_format($data['od_temp_bank'])."');\">" : '')."</td>
		</tr>";
	}

?>
<form id="paging_form" >
<input type="hidden" name="tr_num" value="<?php echo $_POST['tr_num']; ?>" />
<input type="hidden" name="deposit_name" value="<?php echo $_POST['deposit_name']; ?>" />
<input type="hidden" name="amount" value="<?php echo $_POST['amount']; ?>" />
<input type="hidden" name="search_select" value="<?php echo $_POST['search_select']; ?>" />
<input type="hidden" name="search_value" value="<?php echo $_POST['search_value']; ?>" />
<input type="hidden" name="page" />
<table style='border:1px solid black;' width="850">
	<thead >
		<tr align='center' style='font-weight:bold;background-color:#EAEAEA;'>
			<td height="25">주문번호</td>
			<td>주문자</td>
			<td>회원ID</td>
			<td>주문자핸드폰</td>
			<td>받는분핸드폰</td>
			<td>결제방식</td>
			<td>주문합계</td>
			<td>사용포인트</td>
			<td>DC</td>
			<td>입금합계</td>
			<td>미수금</td>
			<td>처리</td>
		</tr>
	</thead>
	<tbody>
	<?php echo $contents; ?>
	</tbody>
</table>
</form>
<table width=100%>
<tr>
    <td width=50%></td>
    <td width=50% align=right>
	<?php
	if ($page > 1) {
        echo "<a href='#' onclick='go_paging(1)'>처음</a>";       
    }

    $start_page = ( ( (int)( ($page - 1 ) / $config[cf_write_pages] ) ) * $config[cf_write_pages] ) + 1;
    $end_page = $start_page + $config[cf_write_pages] - 1;

    if ($end_page >= $total_page) $end_page = $total_page;

    if ($start_page > 1) echo " &nbsp;<a href='#' onclick=\"go_paging(".($start_page-1).");\">이전</a>";

    if ($total_page > 1) {
        for ($k=$start_page;$k<=$end_page;$k++) {
            if ($page != $k)
                echo " &nbsp;<a href='#' onclick=\"go_paging($k);\"><span>$k</span></a>";
            else
                echo " &nbsp;<b>$k</b> ";
        }
    }

    if ($total_page > $end_page) $str .= " &nbsp;<a href='#' onclick=\"go_paging(".($end_page+1).");\">다음</a>";

    if ($page < $total_page) {
       
        echo " &nbsp;<a href='#' onclick=\"go_paging($total_page);\">맨끝</a>";
    }
	?>
	</td>
</tr>
</table>
<?php } else { ?>
<div>
검색 조건을 입력해주세요.
</div>
<?php } ?>
<script>
function go_paging(page){
	$(":hidden[name=page]").val(page);
	$.ajax({
		url:'./ajax.get_deposit_order.php',
		dataType:'html',
		type:'POST',
		data: $("#paging_form").serialize(),
		success:function(result){
			$('.b-close').click();
			
			$('.content').html(result);
			$('#popup').bPopup();
		}
	});
}
function search_deposit_form(){
	if($("input[name=search_value]").val() == ""){
		alert("검색어를 입력해주세요.");
		$("input[name=search_value]").focus()
		return false;
	} else {
		$.ajax({
			url:'./ajax.get_deposit_order.php',
			dataType:'html',
			type:'POST',
			data: $("#find_order_frm").serialize(),
			success:function(result){
				$('.b-close').click();
				
				$('.content').html(result);
				$('#popup').bPopup();
			}
		});
	}
	return false;
}
$(".search_data_tr").hover(function(){ $(this).addClass("hover"); }, function(){ $(this).removeClass("hover"); })
</script>