<?php
include_once("./_common.php");

if (!$is_member){
	goto_url("$g4[bbs_path]/login.php?url=".urlencode("$g4[shop_path]/review.php"));
}


switch($_GET['complete']){
	case 1 : // 주문
		$complete = '1';
		$ct_status = " and b.ct_status = '주문'";
		break;
	case 2 : // 준비
		$complete = '2';
		$ct_status = " and b.ct_status = '준비'";
		break;
	case 3 : // 배송
		$complete = '3';
		$ct_status = " and b.ct_status = '배송'";
		break;
	case 4 : // 완료
		$complete = '4';
		$ct_status = " and b.ct_status = '완료'";
		break;
	case 5 : // 취소,반품,품절
		$complete = '5';
		$ct_status = " and b.ct_status in ('취소','반품','품절')";
		break;
	default : 
		break;

}

if($_GET['od_id']){
	$search_qry .= " and a.od_id = '".trim($_GET['od_id'])."'";
}

if($_GET['st_dt'] || $_GET['en_dt']){
	if($_GET['st_dt'] && $_GET['en_dt']){
		$search_qry .= " and left(a.od_time,10) between '".$_GET['st_dt']."' and '".$_GET['en_dt']."'";
	}elseif($_GET['st_dt']){
		$search_qry .= " and left(a.od_time,10) between '".$_GET['st_dt']."' and '".date('Y-m-d')."'";
	}elseif($_GET['en_dt']){
		$search_qry .= " and left(a.od_time,10) <= '".$_GET['en_dt']."'";
	}
}

if($_GET['st_price'] || $_GET['en_price']){
	if($_GET['st_price'] && $_GET['en_price']){
		$search_qry .= " and a.od_receipt_bank+	a.od_receipt_card+	a.od_receipt_point between '".$_GET['st_price']."' and '".$_GET['en_price']."'";
	}
}

$sql1 = "
	a.od_id,			a.on_uid,
	a.od_receipt_bank+	a.od_receipt_card+	a.od_receipt_point as total_amount,
	a.od_send_cost,
	a.od_time,
	a.od_name,			a.od_tel,			a.od_hp,			a.od_zip1,			a.od_zip2,			a.od_addr1,		a.od_addr2,		a.od_email,
	a.od_b_name,		a.od_b_tel,			a.od_b_hp,			a.od_b_zip1,		a.od_b_zip2,		a.od_b_addr1,	a.od_b_addr2,	a.od_memo,
	a.dl_id,			a.od_invoice,		a.od_invoice_time,
	a.od_receipt_bank,	a.od_receipt_card,	a.od_receipt_point,	a.od_cancel_card,	a.od_refund_amount,	a.od_dc_amount,	a.od_bank_time,	a.od_bank_account,
	a.od_deposit_name,	a.od_temp_bank,
	a.od_settle_case,	a.card_settle_case,	a.od_temp_card,		a.od_temp_point,
	a.od_hope_date
";

$sql2 = "
	yc4_order a
		left join
		yc4_cart b on a.on_uid = b.on_uid
	where 
		a.mb_id = '".$member['mb_id']."'
		".$ct_status."
		".$search_qry."
	group by a.od_id
";
$sql2_no_search = "
	yc4_order a
		left join
		yc4_cart b on a.on_uid = b.on_uid
	where 
		a.mb_id = '".$member['mb_id']."'
		".$ct_status."


";
$sql_order_by = "order by a.od_time desc";

# 최소,최대 결제금액 #
$amount_arr = sql_fetch("
	select 
		min(a.od_receipt_bank+	a.od_receipt_card+	a.od_receipt_point) as min_amount,
		max(a.od_receipt_bank+	a.od_receipt_card+	a.od_receipt_point) as max_amount 
	from ".$sql2_no_search."
");

$amount_min = $amount_arr['min_amount'];
$amount_max = $amount_arr['max_amount'];


$st_price = ($_GET['st_price']) ? $_GET['st_price'] : $amount_min;
$en_price = ($_GET['en_price']) ? $_GET['en_price'] : $amount_max;

# 주문내역 카운트 # 

$order_cnt = sql_fetch("
	select count(*) as cnt from (
		select count(*) from ".$sql2." 
	) t
");
$order_cnt = $order_cnt['cnt'];

$rows =  $config['cf_page_rows']; //$config['cf_page_rows'];
$total_page  = ceil($order_cnt / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$qstr = "complete=".$complete;

# 주문내역 로드 #
$sql = sql_query("
	select 
		".$sql1."
	from 
		".$sql2."
	".$sql_order_by."
	limit ".$from_record.", ".$rows."

");

$s_page = "orderinquiryview.php";
while($row = sql_fetch_array($sql)){
	# 주문 상품 정보 로드 #

	$tot_point = 0;
	$tot_sell_amount = 0;
	$tot_cancel_amount = 0;

	$item_sql = sql_query("
		select 
			a.it_id,a.ct_status,a.ct_amount,a.ct_point,a.ct_qty,
			b.it_name
		from 
			yc4_cart a
			left join
			yc4_item b on a.it_id = b.it_id
		where 
			on_uid = '".$row['on_uid']."'
	");
	$item_info = '';
	while($row2 = sql_fetch_array($item_sql)){
		if($row2['it_name']){
			$row2['it_name'] = get_item_name($row2['it_name']);
		}
		$point       = $row2['ct_point'] * $row2['ct_qty'];
		$sell_amount = $row2['ct_amount'] * $row2['ct_qty'];

	
		$tot_point       += $point;
		$tot_sell_amount += $sell_amount;

		if ($row2['ct_status'] == '취소' || $row2['ct_status'] == '반품' || $row2['ct_status'] == '품절') {
			$tot_cancel_amount += $sell_amount;
		}

		switch($row2['ct_status']){
			case '배송': 
				$action_btn = "<button onclick=\"order_confirm('".$row['od_id']."','".$row['on_uid']."')\">수령확인</button>"; 
				$deliverying = true;
				break;
			case '완료': 
				$review_chk = sql_fetch("
					select 
						count(*) as cnt
					from 
						yc4_item_ps a
						left join
						yc4_order b on a.od_id = b.od_id
						left join 
						yc4_cart c on b.on_uid = c.on_uid
					where 
						a.od_id = '".$row['od_id']."'
						and
						a.it_id = '".$row2['it_id']."'
						and
						a.it_id is not null
						and
						a.it_id != ''
				");
				$review_chk = $review_chk[0];
				if($review_chk == 0){
					$action_btn = "<button onclick=\"review_write('".$row['od_id']."','".$row2['it_id']."')\" style='width:65px;'>후기작성</button>"; 
				}else{
					$action_btn = '후기작성완료'; 
				}
				
				break;
		}

		switch($row2['ct_status'])
        {
            case '주문' : $icon = "<img src='$g4[shop_img_path]/status01.gif'>"; break;
            case '준비' : $icon = "<img src='$g4[shop_img_path]/status02.gif'>"; break;
            case '배송' : $icon = "<img src='$g4[shop_img_path]/status03.gif'>"; break;
            case '완료' : $icon = "<img src='$g4[shop_img_path]/status04.gif'>"; break;
            default     : $icon = $row2['ct_status']; break;
        }
		$item_info .= "
			
			
			<tr class='item_info_row' od_id='".$row['od_id']."' it_id='".$row2['it_id']."'>
				<td class='od_it_img'>".get_it_image($row2['it_id'].'_s',50,50,$row2['it_id'])."</td>
				<td class='od_it_name'>".$row2['it_name']."</td>
				<td align='right'>".$row2['ct_qty']."</td>
				<td align='right'>".display_amount($row2['ct_amount'])."</td>
				<td align='right'>".display_amount($row2['ct_amount'] * $row2['ct_qty'])."</td>
				<td align='right'>".number_format($row2['ct_point'])."</td>
				<td align='center'>".$icon."</td>
				
				<td class='od_it_info'>
					".((!$deliverying) ? $action_btn:'')."
				</td>
			</tr>
			<tr><td class='c1' colspan='8' height='2'></td></tr>

			
		";

		
	}

	



	
	# 배송정보 처리 #
	if($row['dl_id']){
		// 배송회사 정보
		$dl = sql_fetch($a=" select * from $g4[yc4_delivery_table] where dl_id = '".$row['dl_id']."' ");
		// get 으로 날리는 경우 운송장번호를 넘김
		if (strpos($dl['dl_url'], "=")) {
			$invoice = $row['od_invoice'];
		}
		$delivery_tr = "
			<tr>
				<td class='c3' align='center'>배송정보</td>
				<td style='padding:20px;'>
					<table cellpadding='4' cellspacing='0'>
						<colgroup>
							<col width='120' />
							<col width='' />
						</colgroup>
						<tr>
							<td>· 배송회사</td>
							<td>: $dl[dl_company] &nbsp;&nbsp;[<a href='$dl[dl_url]{$invoice}' target='itracking'>배송조회하기</a>]</td>
						</tr>
						<tr>
							<td>· 운송장번호</td>
							<td>: ".$row['od_invoice']."</td>
						</tr>
						<tr>
							<td>· 배송일시</td>
							<td>: ".$row['od_invoice_time']."</td>
						</tr>
					</table>
					<iframe name='itracking' style='width:100%;height:130px;font-size:10px'></iframe>
					<div style='text-align:center;color:red'>
						국내등기/택배조회는 통관이 완료되고 택배사 전산에 업데이트 된 이후부터 조회가 가능합니다.<br>
						항공배송 및 통관과정 중에는 국내 택배 정보는 나오지 않습니다.
					</div>
				</td>
			</tr>
			<tr><td colspan=2 height=1 bgcolor='#cccccc'></td></tr>
		";
	}

	
	# 결제정보 처리 시작 #
	$pay_case_info = '';

	// 총 결제금액
	$receipt_amount = $row['od_receipt_bank']
					+ $row['od_receipt_card']
					+ $row['od_receipt_point']
					- $row['od_cancel_card']
					- $row['od_refund_amount'];

	// 배송비
	if ($default['de_send_cost_case'] == "없음")
		$send_cost = 0;
	else {
		// 배송비 상한 : 여러단계의 배송비 적용 가능
		$send_cost_limit = explode(";", $default['de_send_cost_limit']);
		$send_cost_list  = explode(";", $default['de_send_cost_list']);
		$send_cost = 0;
		for ($k=0; $k<count($send_cost_limit); $k++) {
			// 총판매금액이 배송비 상한가 보다 작다면
			if ($tot_sell_amount < $send_cost_limit[$k]) {
				$send_cost = $send_cost_list[$k];
				break;
			}
		}
	}
	if ($row['od_send_cost'] > 0){
		$send_cost = $row['od_send_cost'];
	}
	// 합계금액
	$tot_amount = $tot_sell_amount + $send_cost;

	$misu = true;
	$wanbul = '';
	if ($tot_amount - $tot_cancel_amount == $receipt_amount) {
		$wanbul = " (완불)".$tot_cancel_amount;
		$misu = false; // 미수금 없음

	}
	$misu_amount = $tot_amount - $tot_cancel_amount - $receipt_amount - $row['od_dc_amount'];

	if($row['od_settle_case'] == '신용카드'){

		if ($row[od_receipt_card] > 0){
			$sql_pay_info = " select * from $g4[yc4_card_history_table] where od_id = '".$row['od_id']."' order by cd_id desc ";
			$result_pay_info = sql_query($sql_pay_info);
			$cd = mysql_fetch_array($result_pay_info);
		}

		// 김선용 2014.03 : kcp/authorize 구분
		$card_str = ($row['card_settle_case'] == 'kcp' ? ' [ 국내카드결제(KCP) ]' : '[ 해외카드결제(authorize.net) ]');
		$pay_case_info .= "
			<tr>
				<td>· 결제방식</td>
				<td>: 신용카드 결제 <b>{$card_str}</b></td>
			</tr>
		";
		if ($row['od_receipt_card']){
			$usd_str = "";
			if($row['card_settle_case'] == 'authorize')
				$usd_str = " (\$".number_format($cd['cd_amount_usd'],2).")";

			$pay_case_info .=  "<tr><td>· 결제금액</td><td class=amount>: " . display_amount($cd[cd_amount]) . "{$usd_str}</td></tr>";
			$pay_case_info .=  "<tr><td>· 승인일시</td><td>: $cd[cd_trade_ymd] $cd[cd_trade_hms]</td>";
			$pay_case_info .=  "<tr><td>· 승인번호</td><td>: $cd[cd_app_no]</td></tr>";
		}else if ($default['de_card_use']){
			$settle_amount = $row['od_temp_card'];
			$pay_case_info .=  "<tr><td>· 결제정보</td><td>: 아직 승인되지 않았거나 승인을 확인하지 못하였습니다.</td></tr>";

			// 김선용 200801 : PG 사가 authorize 인 경우 USD 금액 표기, 소수점 3자리에서 무조건 올림
			$pay_case_info .=  "<tr><td>· 결제금액</td><td>: ".display_amount($row['od_temp_card'])."&nbsp;";
			if($row['card_settle_case'] == 'authorize'){
				$temp_pay = ($row['od_temp_card'] / $default['de_conv_pay']);
				$x_amount = ceil($temp_pay * 100)/100;
				$pay_case_info .=  "(\$".number_format($x_amount,2).")&nbsp;";
			}
			$pay_case_info .=  "(결제하실 금액)</td></tr>";

			$pay_case_info .=  "</td></tr>";
		}
	}else{
		$pay_case_info .= "
			<tr>
				<td>· 결제방식</td>
				<td>: ".$row['od_settle_case']."</td>
			</tr>
		";
		if ($row['od_receipt_bank']){

			$pay_case_info .= "<tr><td>· 입금액</td><td>: " . display_amount($row['od_receipt_bank']) . "</td></tr>";
			$pay_case_info .= "<tr><td>· 입금확인일시</td><td>: ".$row['od_bank_time']."</td></tr>";
		
		}else{
			$pay_case_info .= "<tr><td>· 입금액</td><td>: 아직 입금되지 않았거나 입금정보를 입력하지 못하였습니다.</td></tr>";
		}

		if ($row['od_settle_case'] != '계좌이체'){
			$pay_case_info .= "<tr><td>· 계좌번호</td><td>: ".$row['od_bank_account']."</td></tr>";
		}
		$pay_case_info .= "<tr><td>· 입금자명</td><td>: ".$row['od_deposit_name']."</td></tr>";
	}

	if ($row['od_receipt_point'] > 0){
		$pay_case_info .= "<tr><td>· 포인트결제</td><td>: " . display_point($row['od_receipt_point']) . "</td></tr>";
	}else if ($row['od_temp_point'] > 0 && $member['mb_point'] >= $row['od_temp_point']) {
		$pay_case_info .= "<tr><td>· 포인트결제</td><td>: " . display_point($row['od_temp_point']) . "</td></tr>";
	}

	if ($row['od_cancel_card'] > 0){
		$pay_case_info .= "<tr><td><b>· 승인취소 금액</td><td>: " . display_amount($row[od_cancel_card]) . "</td></tr>";
	}

	if ($row['od_refund_amount'] > 0){
		$pay_case_info .= "<tr><td>· 환불 금액</td><td>: " . display_amount($row[od_refund_amount]) . "</td></tr>";
	}

	// 취소한 내역이 없다면
	if ($tot_cancel_amount == 0) {

		if (
			($row['od_temp_bank'] > 0 && $row['od_receipt_bank'] == 0) ||
			($row['od_temp_card'] > 0 && $row['od_receipt_card'] == 0) 
		) {
			$pay_case_info .= "
				<tr class='order_cancel_tr'><td>· 주문취소</td><td>: <button onclick=\"order_cancel_open('".$row['od_id']."');\" style='padding: 0;border: none;margin: 0;vertical-align: middle;'><img src='$g4[shop_img_path]/ordercancel.gif' style='cursor:pointer;'/></button</td></tr>
				<tr class='order_cancel_tr cancel_form' style='display:none;'><td>· 취소사유</td><td>: <input type='text' name='cancel_memo' size='40' maxlength='100' required itemname='취소사유' style='background-image: url($g4[path]/js/wrest.gif); background-position: 100% 0%; background-repeat: no-repeat;'><button onclick=\"order_cancel('".$row['od_id']."','".$row['on_uid']."',this)\">확인</button></td></tr>
			";
		
		}else if ($row['od_invoice'] == "") {
		
		}
		$pay_case_info .= "<tr><td class='amount_comment' style='color:red;' colspan='2'></td></tr>";
	}else{
		$misu_amount = $misu_amount - $send_cost;
		$pay_case_info .= "<tr><td class='amount_comment' style='color:red;' colspan='2'>· 주문 취소, 반품, 품절된 내역이 있습니다.</td></tr>";
	}
		


	$amount_info = "
		<tr>
			<td class='c3' align='center'>결제정보</td>
			<td style='padding:20px;'>
				<table cellpadding='4' cellspacing='0'>
					<colgroup>
						<col width='120' />
						<col width='' />
					</colgroup>
					".$pay_case_info."
					
					
				</table>
			</td>
		</tr>
		<tr><td colspan=2 height=1 bgcolor='#cccccc'></td></tr>
		<tr>
			<td colspan='2' align='right' bgcolor='#ffffff' height='70' style='font-size:15px'>
				<b>결제합계</b> ".$wanbul." : <b>".display_amount($receipt_amount)."</b>
				".(($misu_amount > 0) ? 
					"<br><font color=crimson><b>아직 결제하지 않으신 금액 : ".display_amount($misu_amount)."</b></font>&nbsp;&nbsp;":
					''	
				)."
			</td>
		</tr>
	";
	# 결제정보 처리 끝 #



	if($item_info){
		$item_info = "
			<table width='97%' cellpadding='0' cellspacing='0' align='center' border='0' style='margin:10px 0;'>
				<colgroup>
					<col width='60' />
					<col width='' />
					<col width='30' />
					<col width='80' />
					<col width='80' />
					<col width='70' />
					<col width='80' />
					<col width='' />
				</colgroup>
				<thead>
					<tr><td class='c1' colspan='8' height='2'></td></tr>
					<tr align='center' height='28' class='c2'>
						<td colspan='2'>상품명</td>
						<td>수량</td>
						<td>판매가</td>
						<td>소계</td>
						<td>포인트</td>
						<td>상태</td>
						<td></td>
					</tr>
				</thead>
				<tbody>
					".$item_info."
					<tr>
						<td colspan='4' align='right' height='28'><b>배송비 : </b></td>
						<td align='right'>".display_amount($send_cost)."</td>
					</tr>
					<tr>
						<td colspan='4' align='right' height='28'><b>총계 : </b></td>
						<td align='right' class='font11_orange'><b>".display_amount($tot_amount)."</b></td>
						<td align='right' class='font11_orange'><b>".number_format($tot_point)."</b></td>
					</tr>
					<tr><td height='2' colspan='8' bgcolor='#fd7c00'></td></tr>
				</tbody>
			</table>
			<div align='center'>
				<img src='".$g4['shop_img_path']."/status01.gif' align='absmiddle'> : 주문대기, 
				<img src='".$g4['shop_img_path']."/status02.gif' align='absmiddle'> : 상품준비중, 
				<img src='".$g4['shop_img_path']."/status03.gif' align='absmiddle'> : 배송중, 
				<img src='".$g4['shop_img_path']."/status04.gif' align='absmiddle'> : 배송완료
			</div>
		";
	}

	
	$list_tr .= "
		<tr class='order_row'>
			<td class='order_no'><a href='".$g4['shop_path']."/orderinquiryview.php?od_id=".$row['od_id']."&on_uid=".$row['on_uid']."'>".$row['od_id']."</a></td>
			<td class='order_item_amount'>". display_amount($tot_sell_amount) ."</td>
			<td class='order_delivery_amount'>".display_amount($send_cost)."</td>
			<td class='order_amount'>".display_amount($tot_amount)."</td>
			<td class='order_recipt'>".display_amount($receipt_amount)."</td>
			<td align='right'>".display_amount($misu_amount)."</td>
			<td class='order_time'>".$row['od_time']." (".get_yoil($row['od_time']).")</td>
			<td class='order_detail_btn'>
				<button onclick=\"order_detail_view('".$row['od_id']."');\">상세보기</button>
				".(($deliverying) ? $action_btn:'')."
			</td>
		</tr>

		<tr class='item_info_wrap' rel='".$row['od_id']."' ".(($_GET['od_id'] == $row['od_id']) ? "style='display:table-row;'":"").">
			<td colspan='8'>
				<!-- item_info -->
				".$item_info."
				<!-- item_info -->
				<div class='od_detail_info'>
					<table width='95%'>
						<colgroup>
							<col width='90'/>
							<col />
						</colgroup>
						<tbody>
							<tr>
								<td class='c3' align='center'>주문하시는분</td>
								<td style='padding:20px;'>
									<table cellpadding='4' cellspacing='0'>
										<colgroup>
											<col width='120' />
											<col width='' />
										</colgroup>
										<tr>
											<td>· 주문일시</td>
											<td>: ".$row['od_time']." (".get_yoil($row['od_time']).")</td>
										</tr>
										<tr>
											<td>· 이름</td>
											<td>: ".$row['od_name']."</td>
										</tr>
										<tr>
											<td>· 전화번호</td>
											<td>: ".$row['od_tel']."</td>
										</tr>
										<tr>
											<td>· 핸드폰</td>
											<td>: ".$row['od_hp']."</td>
										</tr>
										<tr>
											<td>· 주소</td>
											<td>: (".$row['od_zip1']."-".$row['od_zip1'].") ".$row['od_addr1']." ".$row['od_addr2']."</td>
										</tr>
										<tr>
											<td>· E-mail</td>
											<td>: ".$row['od_email']."</td>
										</tr>
									</table>
								</td>
							</tr>
		
							<tr><td colspan=2 height=1 bgcolor='#cccccc'></td></tr>

							<tr>
								<td class='c3' align='center'>받으시는분</td>
								<td style='padding:20px;'>
									".(($row['od_ship']) ?
										get_fui_ship_item($row['on_uid'], $member['mb_id'], 0, false) : // 복수 배송지 처리 
										"
										<table cellpadding='4' cellspacing='0'>
											<colgroup>
												<col width='120' />
												<col width='' />
											</colgroup>
											<tr>
												<td>· 이름</td>
												<td>: ".$row['od_b_name']."</td>
											</tr>
											<tr>
												<td>· 전화번호</td>
												<td>: ".$row['od_b_tel']."</td>
											</tr>
											<tr>
												<td>· 핸드폰</td>
												<td>: ".$row['od_b_hp']."</td>
											</tr>
											<tr>
												<td>· 주소</td>
												<td>: (".$row['od_b_zip1']."-".$row['od_b_zip1'].") ".$row['od_b_addr1']." ".$row['od_b_addr2']."</td>
											</tr>

											".(($default['de_hope_date_use']) ? 
												"<tr>
													<td>· 희망배송일</td>
													<td>: ".substr($row['od_hope_date'],0,10)." (".get_yoil($row['od_hope_date']).")</td>
												</tr>":
												""
											)."

											".(($row['od_memo']) ? 
												"":
												"
													<tr>
														<td>· 전하실 말씀</td>
														<td>: ".nl2br($row['od_memo'])."</td>
													</tr>
												"
											)."
											
										</table>
										")."
								</td>
							</tr>
							<tr><td colspan=2 height=1 bgcolor='#cccccc'></td></tr>
							".$delivery_tr."
							".$amount_info."

						</tbody>
					</table>
				</div>
			</td>
		</tr>
		<tr><td height='1' colspan='8' background='".$g4['shop_img_path']."/dot_line.gif'></td></tr>
		
	";
}

if(is_array($_GET)){
	$i = 0;
	foreach($_GET as $key => $val){
		$param .= (($i > 0) ? '&':'').$key.'='.$val;
	}
}

include_once "./_head.php";
?>


<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/themes/smoothness/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/jquery-ui.min.js"></script>

<div>
  <table width="755" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td width="319">
        <img src="../images/menu/menu_title02_a.gif">
        </td>
      <td width="353" align="right" class="font11">
        HOME &gt; 마이페이지 &gt; <span class="font11_orange">주문내역조회</span>
      </td>
    </tr>
    <tr>
      <td height="2" colspan="2" bgcolor="#fa5a00"></td>
    </tr>
  </table>
</div>

<ul class='tab_btn_wrap'>
  <li class='tab_btn
    <?=(!$complete) ? ' active':''?>' onclick="location.href='<?=$_SERVER['PHP_SELF']?>'">전체
  </li>
  <li class='tab_btn
    <?=($complete == 1) ? ' active':''?>' onclick="location.href='<?=$_SERVER['PHP_SELF']?>?complete=1'">주문
  </li>
  <li class='tab_btn
    <?=($complete == 2) ? ' active':''?>' onclick="location.href='<?=$_SERVER['PHP_SELF']?>?complete=2'">준비
  </li>
  <li class='tab_btn
    <?=($complete == 3) ? ' active':''?>' onclick="location.href='<?=$_SERVER['PHP_SELF']?>?complete=3'">배송
  </li>
  <li class='tab_btn
    <?=($complete == 4) ? ' active':''?>' onclick="location.href='<?=$_SERVER['PHP_SELF']?>?complete=4'">완료
  </li>
  <li class='tab_btn
    <?=($complete == 5) ? ' active':''?>' onclick="location.href='<?=$_SERVER['PHP_SELF']?>?complete=5'">취소,반품,품절
  </li>
  <li style='clear:both;'></li>
</ul>


<div class='order_surch_form'>
  <div class='price_serch_form'>
    <div class='price_search_title'>주문가격 : </div>
    <div class='price_search_view'></div>
    <div style='width: 500px;' class='price_search'></div>
  </div>
  <div style="clear: both;"></div>
<form action=""
  <?=$_SERVER['PHP_SELF']?>" style='width:100%; margin:0;'>
  <input type="hidden" name='complete' value=''<?=$_GET['complete']?>' />
  <input type="hidden" name='st_price' value=''<?=$_GET['st_price']?>'/>
  <input type="hidden" name='en_price' value=''<?=$_GET['en_price']?>'/>
  주문번호 : <input style='margin-right:24px;' type="text" name='od_id' placeholder='주문번호' value=''<?=$_GET['od_id']?>' />

  기간 : <input type="text" name='st_dt' value=''<?=$_GET['st_dt']?>' readonly placeholder='조회 시작일'/> ~ <input type="text" name='en_dt' value=''<?=$_GET['en_dt']?>' readonly placeholder='조회 종료일'/>
  <input type="button" value='전체' onclick="this.form.st_dt.value='';this.form.en_dt.value='';"; />
  <input style="margin-left:60px;" type="submit" value='조회하기' />
  <?if($search_qry){?>
  <input type="button" value='검색초기화' onclick="location.href='"<?=$_SERVER['PHP_SELF']?>'"/>
  <?}?>
</form>
</div>
<div style="clear: both;"></div>
<div>
  총 주문건 <?=number_format($order_cnt);?>
</div>
<div class='order_list_wrap'>
	<table>
		<colgroup>
			<col width='11%'>
			<col width='10%'>
			<col width='10%'>
			<col width='10%'>
			<col width='10%'>
			<col width='10%'>
			<col width=''>
			<col width='20%'>
		</colgroup>
		<thead>
			<tr><td height="2" colspan="8" class="c1"></td></tr>
			<tr class='c2' height='28' align='center'>
				<td>주문서번호</td>
				<td>상품</td>
				<td>배송비</td>
				<td>소계(금액)</td>
				<td>결제금액</td>
				<td>미결제금액</td>
				<td>주문일</td>
				<td></td>
			</tr>
			<tr><td height="1" colspan="8" class="c1"></td></tr>
		</thead>
		<tbody>
			<?=$list_tr;?>
		</tbody>

	</table>
</div>
<p align='center'><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></p>



<form method='post' name='review_frm'></form>


<script type="text/javascript">
token = '<?=get_session('ss_token');?>';
function review_write(od_id,it_id){
	var ss_token = token;
	var refer = '<?=$_SERVER['PHP_SELF']?>?<?=$param;?>';
	$('.review_write_from_wrap').remove();
	$('.item_info_row[od_id='+od_id+'][it_id='+it_id+']').after(
		"<tr class='review_write_from_wrap'>"+
			"<td colspan='8'>"+
				"<form method='post' name='review_frm' enctype='multipart/form-data'>"+
					"<input type='hidden' name='ss_token' value='"+ss_token+"'/>"+
					"<input type='hidden' name='refer' value='"+refer+"'/>"+
					"<input type='hidden' name='od_id' value='"+od_id+"'/>"+
					"<input type='hidden' name='it_id' value='"+it_id+"'/>"+
					"<table width='100%'>"+
						"<tr>"+
							"<td width='100'>제목</td>"+
							"<td><input type='text' name='is_subject' style='width:90%;'></input></td>"+
						"</tr>"+
						"<tr>"+
							"<td>내용</td>"+
							"<td><textarea name='is_content' style='width: 90%; background-image: url("+g4_path+"/js/wrest.gif); background-position: 100% 0%; background-repeat: no-repeat;' rows='10'></textarea></td>"+
						"</tr>"+
						"<tr>"+
							"<td>평가</td>"+
							"<td>"+
								"<input type='radio' name='is_score' value='10' checked / >"+
								"<img src='<?=$g4['path']?>/shop/img/star5.gif' align='absmiddle'>"+
								"<input type='radio' name='is_score' value='8' / >"+
								"<img src='<?=$g4['path']?>/shop/img/star4.gif' align='absmiddle'>"+
								"<input type='radio' name='is_score' value='6' / >"+
								"<img src='<?=$g4['path']?>/shop/img/star3.gif' align='absmiddle'>"+
								"<input type='radio' name='is_score' value='4' / >"+
								"<img src='<?=$g4['path']?>/shop/img/star2.gif' align='absmiddle'>"+
								"<input type='radio' name='is_score' value='2' / >"+
								"<img src='<?=$g4['path']?>/shop/img/star1.gif' align='absmiddle'>"+
							"</td>"+
						"</tr>"+
						"<tr>"+
							"<td>이미지1</td>"+
							"<td><input type='file' name='is_image[0]' size='60'></td>"+
						"</tr>"+
						"<tr>"+
							"<td>이미지2</td>"+
							"<td><input type='file' name='is_image[1]' size='60'></td>"+
						"</tr>"+
							"<tr>"+
							"<td>이미지3</td>"+
							"<td><input type='file' name='is_image[2]' size='60'></td>"+
						"</tr>"+
							"<tr>"+
							"<td>이미지4</td>"+
							"<td><input type='file' name='is_image[3]' size='60'></td>"+
						"</tr>"+
							"<tr>"+
							"<td>이미지5</td>"+
							"<td><input type='file' name='is_image[4]' size='60'></td>"+
						"</tr>"+
						"<tr>"+
							"<td><img id='kcaptcha_image_use' onclick=\"kcaptcha_image_on();\" /></td>"+
							"<td><input type='text' name='is_key' style='background-image: url("+g4_path+"/js/wrest.gif); background-position: 100% 0%; background-repeat: no-repeat;'>&nbsp;* 왼쪽의 자동등록방지 코드를 입력하세요.</td>"+
						"</tr>"+
					"</table>"+
				"</form>"+
			"</td>"+
		"</tr>"
	);
	kcaptcha_image_on();
}

function kcaptcha_image_on(){

	$.ajax({
		type: 'POST',
		url: g4_path+'/'+g4_bbs+'/kcaptcha_session.php',
		cache: false,
		async: false,
		success: function(text) {
			$("#kcaptcha_image_use, #kcaptcha_image_qa").attr('src', g4_path+'/'+g4_bbs+'/kcaptcha_image.php?t=' + (new Date).getTime())
				.css('cursor', 'pointer')
				.attr('title', '글자가 잘 안보이시는 경우 클릭하시면 새로운 글자가 나옵니다.')
				.attr('width', '120')
				.attr('height', '60');
		}
	});
}

function order_confirm(od_id,on_uid){
	var ss_token = '<?=get_session('ss_token');?>';
	var refer = '<?=$_SERVER['PHP_SELF']?>?<?=$param;?>'.replace('complete=N','complete=Y');

	review_frm.action = "member_delivery_confirm.php";
	$('form[name=review_frm]').html(
		"<input type='hidden' name='ss_token' value='"+ss_token+"'/>"+
		"<input type='hidden' name='od_id' value='"+od_id+"'/>"+
		"<input type='hidden' name='on_uid' value='"+on_uid+"'/>"+
		"<input type='hidden' name='refer' value='"+refer+"'/>"
	);
	review_frm.submit();
}

function order_detail_view(od_id){
	$('.item_info_wrap[rel!='+od_id+']').hide();
	if($('.item_info_wrap[rel='+od_id+']').css('display') == 'none'){
		$('.item_info_wrap[rel='+od_id+']').slideDown(150);
	}
}

function order_cancel_open(od_id){
	$('.item_info_wrap[rel='+od_id+']').find('.cancel_form').show();
}
function order_cancel(od_id,on_uid,obj){
	var cancel_memo = $(obj).parent().find('input[name=cancel_memo]').val();
	if(cancel_memo == ''){
		alert('취소사유를 입력해 주세요.');
		return false;
	}
	if(!confirm('주문을 취소하시겠습니까?')){
		return false;
	}

	$.ajax({
		url : 'orderinquirycancel.php',
		type : 'post',
		cache : false,
		headers : { "cache-control" : 'no-cache', 'pragma' : 'no-cache'},
		data : {
			'mode' : 'ajax',
			'od_id' : od_id,
			'on_uid' : on_uid,
			'token' : token,
			'cancel_memo' : cancel_memo
		},
		success : function ( result ) {
			if( result == ''){
				alert('주문이 취소되었습니다.');
				$('.item_info_wrap[rel='+od_id+']').find('.amount_comment').text('· 주문 취소, 반품, 품절된 내역이 있습니다.');
				$('.item_info_wrap[rel='+od_id+']').find('.order_cancel_tr').remove();
				get_token();
				
			}else{
				alert( result );
				return false;
			}
		},error : function ( result ){
			alert('처리중 요류가 발생하였습니다.');
			return false;
		}
	});
}
function get_token(){
	$.ajax({
		url : 'get_token.php',
		cache : false,
		headers : { "cache-control" : 'no-cache', 'pragma' : 'no-cache'},
		success : function ( result ){
			token = result;
			return false;
		}
	});

}

$('input[name=st_dt] , input[name=en_dt]').datepicker({
	dateFormat : "yy-mm-dd",
	firstDay : 0, // 일요일 부터 시작
	maxDate : '+0',
	changeMoth : true,
	changeYear : true,
	dayNamesMin : ['일','월','화','수','목','금','토']
		
});

$(function() {
    $( ".price_search" ).slider({
      range: true,
      min: Number('<?=$amount_min;?>'),
      max: Number('<?=$amount_max;?>'),
      values: [ Number('<?=$st_price;?>'), Number('<?=$en_price;?>') ],
      step : 10,
      animate : 'fast',
      slide: function( event, ui ) {

        $( "input[name=st_price]" ).val( ui.values[ 0 ] ) ;
		$( "input[name=en_price]" ).val( ui.values[ 1 ] );
		$('.price_search_view').text( 
			number_format(String(ui.values[ 0 ])) + '원 ~ ' + 
			number_format(String(ui.values[ 1 ])) + '원'
		);
      }
    });

	$( "input[name=st_price]" ).val( $( ".price_search" ).slider( "values", 0 ) ) ;
	$( "input[name=en_price]" ).val( $( ".price_search" ).slider( "values", 1 ) );

	$('.price_search_view').text( 
		number_format(String($( ".price_search" ).slider( "values", 0 ))) + '원 ~ ' + 
		number_format(String($( ".price_search" ).slider( "values", 1 ))) + '원'
	);

	

	
  });
</script>
<?php
include_once("./_tail.php");
?>