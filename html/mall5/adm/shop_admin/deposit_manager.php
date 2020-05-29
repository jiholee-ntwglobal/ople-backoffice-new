<?php 
/*
----------------------------------------------------------------------
file name	 : deposit_manager.php
comment		 : 무통장입금자 일괄 입금확인처리
date		 : 2015-04-10
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "400995";
include_once("./_common.php");

//auth_check($auth[$sub_menu], "r");


if($_POST['mode'] == 'excel_upload'){

	if(isset($_FILES['upfile'])){		

		$file_nm_split = explode('.',$_FILES['upfile']['name']);

		$file_ext = strtolower($file_nm_split[count($file_nm_split)-1]);

		 

		if($file_ext == 'xlsx' || $file_ext == 'xls'){

			$uploaded_file = $g4['full_path'].'/adm/shop_admin/admin_upload/'.date('YmdHis').'.'.$file_ext;

			if(move_uploaded_file($_FILES['upfile']['tmp_name'], $uploaded_file)){				

				require_once $g4['full_path']. '/classes/PHPExcel.php';

				$objPHPExcel = PHPExcel_IOFactory::load($uploaded_file);

				$iterator = $objPHPExcel->getWorksheetIterator();

				if($iterator->valid()){

					$today_upload_info = sql_fetch("select max(seq) as max_seq from yc4_deposit_upload_history where upload_date='".date('Ymd')."'");
					
					$max_seq = ($today_upload_info['max_seq'] > 0) ? $today_upload_info['max_seq']+1 : 1;

					$_SESSION['max_seq'] = $max_seq;

					$num = 0;

					$objWorksheet = $iterator->current();
					foreach ($objWorksheet->getRowIterator() as $row) {

						$cellIterator = $row->getCellIterator();
						$cellIterator->setIterateOnlyExistingCells(FALSE);

						$data = array();

						foreach ($cellIterator as $cell) {

							$column_index = $cell->getColumn();

							switch($column_index){
								case 'A': $data['name']		 =  str_replace("\n", '', trim($cell->getCalculatedValue())); break;
								case 'B': $data['ammout']	 =  str_replace("\n", '', trim($cell->getCalculatedValue())); break;
							}							
                
						}

						if($data['name'] == '' && $data['ammout'] == '') break;

						unset($matching_data);

						$order_cnt = sql_fetch("
							SELECT count(distinct o.od_id) AS cnt
							  FROM yc4_order o
							  left outer join yc4_cart c
								on o.on_uid=c.on_uid
							 WHERE     o.od_temp_bank > 0
								   AND o.od_receipt_bank = 0
								   AND o.od_deposit_name = '$data[name]'
								   AND o.od_temp_bank - od_dc_amount = '$data[ammout]'
								   AND date_format(o.od_time,'%Y%m%d')>='".date('Ymd',strtotime('-7day'))."'
								   AND c.ct_status='주문'");	
								   
						if($order_cnt['cnt'] > 0){
							$tr_class = 'match_tr';
							$chk_disabled = '';

							$matching_data = sql_fetch("
												SELECT o.od_id,
													   o.od_name,
													   o.on_uid,
													   o.od_hp,
													   o.od_b_hp,
													   o.od_temp_bank,
													   o.od_time
												  FROM yc4_order o LEFT OUTER JOIN yc4_cart c ON o.on_uid = c.on_uid
												 WHERE     o.od_temp_bank > 0
													   AND o.od_receipt_bank = 0
													   AND o.od_deposit_name = '$data[name]'
													   AND o.od_temp_bank - od_dc_amount = '$data[ammout]'
													   AND date_format(o.od_time, '%Y%m%d') >= '".date('Ymd',strtotime('-7 day'))."'
													   AND c.ct_status = '주문'
													ORDER BY o.od_id DESC
												 LIMIT 1");

							$matching_name = $matching_data['od_name'];
							$matching_id = "<a href=\"/mall5/adm/shop_admin/orderform.php?od_id=$matching_data[od_id]\" target=\"_blank\">$matching_data[od_id]</a>";
							$matching_ordertime = $matching_data['od_time'];


							 $matching_hp = $matching_data['od_hp'];
							 $matching_rhp = $matching_data['od_b_hp'];
							 $matching_amount = number_format($matching_data['od_temp_bank']);



						} else {
							$tr_class = '';
							$chk_disabled = 'disabled';
							$matching_name = $matching_id = $matching_ordertime = $matching_hp = $matching_rhp = $matching_amount = '';
						}
						
						sql_query("insert into yc4_deposit_upload_history (name, price, upload_date, seq, create_date) values ('$data[name]', '$data[ammout]', '".date('Ymd')."', '$max_seq', NOW())");
						
						if($order_cnt['cnt'] > 1) $matching_id .= " 외 ".($order_cnt['cnt']-1)."건";

						$upload_info = sql_fetch("select seq from yc4_deposit_upload_history where name='$data[name]' and price='$data[ammout]' and not isnull(match_od_id) order by seq desc limit 1");

						$seq_history = ($upload_info['seq'] > 0) ? "<a href=\"./deposit_upload_history.php?yyyy=".date('Y')."&mm=".date('m')."&dd=".date('d')."&seq=$upload_info[seq]\" target=\"_blank\">{$upload_info[seq]}회차</a>" : '';

						$CONTENTS .= "
						<tr class=\"$tr_class\" id=\"parsing_tr_${num}\">							
							<td><input type=\"checkbox\" name=\"matching_order_${num}\" id=\"checkbox_num_${num}\" value=\"$matching_data[od_id]\" $chk_disabled /><input type=\"hidden\" name=\"deposit_name_${num}\" value=\"$data[name]\"/><input type=\"hidden\" name=\"deposit_amount_${num}\" value=\"$data[ammout]\"/><input type=\"hidden\" name=\"parce_num[]\" value=\"${num}\"/></td>
							<td>$seq_history</td>
							<td><a href='#' onclick=\"load_pop_order_find_by_name('$num','$data[name]','$data[ammout]');return false;\"/>$data[name]</a></td>
							<td><a href='#' onclick=\"load_pop_order_find_by_amount('$num','$data[name]','$data[ammout]');return false;\">".number_format($data['ammout'])."</a></td>
							<td id='match_nm_${num}'>$matching_name</td>
							<td id='match_id_${num}'>$matching_id</td>
							<td id='match_ot_${num}'>$matching_ordertime</td>
							<td id='match_hp_${num}'>$matching_hp</td>
							<td id='match_rh_${num}'>$matching_rhp</td>
							<td id='match_am_${num}'>$matching_amount</td>
							<td><input type=\"button\" value=\"주문서찾기\" onclick=\"load_pop_order('$num','$data[name]','$data[ammout]');return false;\"/>&nbsp;<input type=\"button\" value=\"입금확인\" onclick=\"single_check_deposit('$num');\"/></td>
						</tr>
						";

						$num++;

					}
				}
			} else {
				alert("네트워크 장애가 있습니다. 잠시후 다시 시도해주세요.");
				exit;
			} 

		} else {
			alert("Excel파일을 업로드하세요.");
			exit;
		}

		
	} else {
		alert("Excel파일을 업로드하세요.");
		exit;
	}
	
} else {
	$CONTENTS = "
	<tr>
		<td colspan='9' height='50'>일괄 처리할 대상을 업로드해 주세요.</td>
	</tr>";
}


//$g4['full_shop_path']

$g4['title'] = "무통장입금자 일괄 입금확인처리";
include_once ($g4['admin_path']."/admin.head.php");
?>
<script src="/mall5/js/jquery.bpopup.min.js" type="text/javascript"></script>
<style>
.match_tr {background-color:yellow;}
.Pstyle {
	opacity: 0;
	display: none;
	position: relative;
	width: auto;
	border: 5px solid #fff;
	padding: 20px;
	background-color: #fff;
}

.b-close {
	position: absolute;
	right: 5px;
	top: 5px;
	padding: 5px;
	display: inline-block;
	cursor: pointer;
}
.hover {
	background-color:#FFA7A7;
}
.hover2 {
	background-color:#B2CCFF
}
</style>
<h2>무통장입금자 일괄 입금확인처리&nbsp;<input type="button" value="입금확인 일괄처리 내역 확인페이지로 이동" onclick="location.href='./deposit_upload_history.php'"/></h2>
<br>
<div style="border:1px solid black;padding:10px 20px;">
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="mode" value="excel_upload"/>
<table width="100%">
	<tr>
		<td align="left" width="140"><a href="/mall5/adm/shop_admin/admin_upload/sample.xlsx">Download Sample File</a></td>
		<td align="left" width="300"><input type="file" name="upfile"/>&nbsp;<input type="submit" value="Upload File"/></td>
		<td align="right"><input type="button" value="선택 입금자 일괄 입금 확인 처리" onclick="all_check_deposit();"/></td>
	</tr>
</table>
</form>
</div>
<br />
<div>
<form id="bulk_proc_form">
<input type="hidden" name="mode" value="bulk" />
<table width="140%">
	<thead align="center" style="font-weight:bold;">
		<tr>
			<td height="30"><input type="checkbox" class="allchk"/></td>
			<td>금일중복데이터</td>
			<td>입금자명</td>
			<td>입금액</td>
			<td>매칭주문자</td>
			<td>매칭주문서ID</td>
			<td>매칭주문일자</td>
			<td>매칭주문자 휴대전화</td>
			<td>매칭주문 받는사람 휴대전화</td>
			<td>매칭주문 미수금</td>
			<td>관리</td>
		</tr>
	</thead>
	<tbody align="center" class="data_tbody">
	<?php echo $CONTENTS; ?>
	</tbody>
</table>
</form>
</div>
<span style="font-weight:bold;color:red;">매칭주문서ID에 추가건수표시 ( ex. 150101123456 외1건) 가 있는 주문건은 동일조건으로 매칭되는 주문건이 다수 존재하는 뜻이므로 반드시 주문건 직접 확인 후 처리하시기 바랍니다.</span><br>
<span style="font-weight:bold;color:red;">금일중복데이터는 금일업로드 되었던 자료 중 입금자명,입금액이 동일하고 입금확인처리가 된 내역이 있는 데이터입니다.</span>

<div id="popup" class="Pstyle">
	<span class="b-close">X</span>
	<div class="content" style="height: auto; width: auto;"></div>
</div>

<form id="single_process_frm">
<input type="hidden" name="od_id" />
<input type="hidden" name="od_deposit_nm" />
</form>
<script>
function single_check_deposit(num){
	if($("#checkbox_num_" + num).val() == ""){
		alert("주문서가 매칭되지 않았습니다. 주문서찾기를 통해 주문서 매칭하시기 바랍니다.");
		return false;
	}

	if(confirm("주문번호가 " + $("#checkbox_num_" + num).val() + "인 주문에 대해\n입금자명 " + $(":hidden[name=deposit_name_" + num + "]").val() + "으로 " + $(":hidden[name=deposit_amount_" + num + "]").val() + "원을 입금처리하시겠습니까?")){
		$.ajax({
			url:'./ajax.proc_deposit_order.php',
			dataType:'xml',
			type:'POST',
			data:"mode=single&tr_num=" + num + "&od_id=" + $("#checkbox_num_" + num).val() + "&deposit_nm=" + $(":hidden[name=deposit_name_" + num + "]").val() + "&deposit_amount=" + $(":hidden[name=deposit_amount_" + num + "]").val(),
			success:function(xml){
				alert($(xml).find("msg").text());
				$("#parsing_tr_" + num).remove();				
			}
		});
	}
}

function all_check_deposit(){

	var check_order_cnt = 0;

	$(":checkbox[name^=matching_order_]").each(function(){
		if($(this).is(":checked") && $(this).val() != ""){
			check_order_cnt++;
		}
	});

	if(check_order_cnt < 1){
		alert("입금확인 처리할 대상을 선택하세요.");
		return false;
	}

	if(confirm("선택 입금주문건(총 " + check_order_cnt + "건)에 대해 일괄 입금화인 처리하시겠습니까?")){
		$.ajax({
			url:'./ajax.proc_deposit_order.php',
			dataType:'xml',
			type:'POST',
			data:$("#bulk_proc_form").serialize(),
			success:function(xml){
				alert($(xml).find("msg").text());
				
				var proc_num = $(xml).find("proc_num").text();

				var split_txt = proc_num.split("|"); 

				for(var k=0;k<split_txt.length;k++){
					$("#parsing_tr_" + split_txt[k]).remove();
				}
			}
		});
	}
}

function load_pop_order(tr_num, deposit_name, amount){
	$.ajax({
        url:'./ajax.get_deposit_order.php',
        dataType:'html',
        type:'POST',
        data:"tr_num=" + tr_num + "&deposit_name=" + deposit_name + "&amount=" + amount,
        success:function(result){
            $('.content').html(result);
			$('#popup').bPopup();
        }
    });
}


function load_pop_order_find_by_name(tr_num, deposit_name, amount){
	$.ajax({
        url:'./ajax.get_deposit_order.php',
        dataType:'html',
        type:'POST',
        data:"tr_num=" + tr_num + "&deposit_name=" + deposit_name + "&amount=" + amount + "&search_select=od_deposit_name&search_value=" + deposit_name,
        success:function(result){
            $('.content').html(result);
			$('#popup').bPopup();
        }
    });
}

function load_pop_order_find_by_amount(tr_num, deposit_name, amount){
	$.ajax({
        url:'./ajax.get_deposit_order.php',
        dataType:'html',
        type:'POST',
        data:"tr_num=" + tr_num + "&deposit_name=" + deposit_name + "&amount=" + amount + "&search_select=remain_amount&search_value=" + amount,
        success:function(result){
            $('.content').html(result);
			$('#popup').bPopup();
        }
    });
}

function sel_order(tr_num, match_nm, match_id, match_ot, match_hp, match_rh, match_am){

	$("#parsing_tr_" + tr_num).addClass("match_tr");
	$("#checkbox_num_" + tr_num).val(match_id);
	$("#checkbox_num_" + tr_num).attr("disabled", false);
	$("#match_nm_" + tr_num).text(match_nm);
	$("#match_id_" + tr_num).html("<a href=\"/mall5/adm/shop_admin/orderform.php?od_id=" + match_id + "\" target=\"_blank\">"+ match_id + "</a>");
	$("#match_ot_" + tr_num).text(match_ot);
	$("#match_hp_" + tr_num).text(match_hp);
	$("#match_rh_" + tr_num).text(match_rh);
	$("#match_am_" + tr_num).text(match_am);
	$('.b-close').click();	
}

$(document).ready(function(){
	$(".allchk").click(function(){
		if($(this).is(":checked")){
			$(":checkbox[name^=matching_order_]:not([value=''])").each(function(){
				$(this).prop("checked","checked");
			});
		} else {
			$(":checkbox[name^=matching_order_]:not([value=''])").each(function(){
				$(this).prop("checked",false);
			});
		}
	});

	$(".data_tbody > tr").hover(function(){ $(this).addClass("hover2");  }, function(){ $(this).removeClass("hover2");  });
});
</script>