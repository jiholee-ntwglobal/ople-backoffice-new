<?php
include_once("./_common.php");

// 김선용 201210 : 복수배송작업관련 임시 리디렉션
if($default['de_order_ship_multi_level'] <= $member['mb_level']) goto_url("orderform_multi_addr.php");
//if($is_admin === 'super' || $member['mb_id'] === 'sucjin') goto_url("orderform_multi_addr.php");

// 장바구니가 비어있는가?
$tmp_on_uid = get_session('ss_on_uid');
if (get_cart_count($tmp_on_uid) == 0)
    alert("장바구니가 비어 있습니다.", "./cart.php");

// 포인트 결제 대기 필드 추가
//sql_query(" ALTER TABLE `$g4[yc4_order_table]` ADD `od_temp_point` INT NOT NULL AFTER `od_temp_card` ", false);

$g4[title] = "주문서 작성";

include_once("./_head.php");

?>



<style type="text/css">
.detail_clause{
	display:none;
}
</style>
<!--<img src="<?=$g4[shop_img_path]?>/top_orderform.gif" border="0"><p>-->
<div class='PageTitle'>
<img src="<?=$g4['path']?>/images/category/category_title01_a.gif" alt="주문서작성" />
</div>

<?
$s_page = 'orderform.php';
$s_on_uid = $tmp_on_uid;
include_once("./cartsub.inc_test.php");
?>

<form name='forderform' method=post action="./orderreceipt.php" onsubmit="return forderform_check(this);" autocomplete=off>
	<input type=hidden name=od_amount    value='<?=$tot_sell_amount?>'>
	<input type=hidden name=od_send_cost value='<?=$send_cost?>'>

	<!-- 주문하시는 분 -->
	<table width=100% align=center cellpadding=0 cellspacing=10 border=0>
	<tr>
		<th class="table_title">주문하시는 분</th>
	</tr>
	<tr>
		<td>
			<table cellpadding=0 cellspacing=0 width=100% class='list_order'>
			<colgroup>
				<col width='130'>
				<col />
			</colgroup>
			<tr>
				<th>이름</th>
				<td><input type=text id=od_name name=od_name value='<?=$member[mb_name]?>' maxlength=20 class=ed></td>
			</tr>

			<? if (!$is_member) { // 비회원이면 ?>
			<tr>
				<th>비밀번호</th>
				<td><input type=password name=od_pwd class=ed maxlength=20>
					영,숫자 3~20자 (주문서 조회시 필요)</td>
			</tr>
			<? } ?>

			<tr>
				<th>전화번호</th>
				<td><input type=text name=od_tel value='<?=$member[mb_tel]?>' maxlength=20 class=ed></td>
			</tr>
			<tr>
				<th>핸드폰</th>
				<td><input type=text name=od_hp value='<?=$member[mb_hp]?>' maxlength=20 class=ed></td>
			</tr>
			<tr>
				<th rowspan='3'>주 소</th>
				<td style='border-bottom:none;'>
					<input type=text name=od_zip1 size=3 maxlength=3 value='<?=$member[mb_zip1]?>' class=ed readonly>
					-
					<input type=text name=od_zip2 size=3 maxlength=3 value='<?=$member[mb_zip2]?>' class=ed readonly>
					<a href="javascript:;" onclick="win_zip('forderform', 'od_zip1', 'od_zip2', 'od_addr1', 'od_addr2', 'od_addr_jibeon');"><img
						src="<?=$g4[shop_img_path]?>/btn_zip_find.gif" border="0" align=absmiddle></a>
				</td>
			</tr>
			<tr>
				<td style='border-bottom:none;'>
					<p><input type=text name=od_addr1 size=35 maxlength=50 value='<?=$member[mb_addr1]?>' class=ed readonly></p>
					<p><input type=text name=od_addr2 size=35 maxlength=50 value='<?=$member[mb_addr2]?>' class=ed> (상세주소)</p>
				</td>
			</tr>
			<tr>
				<td>
					<input type="hidden" name="od_addr_jibeon" value="<?=$member['mb_addr_jibeon']; ?>">
					<span id="od_addr_jibeon"><?=($member['mb_addr_jibeon'] && $member['mb_addr_jibeon'] != 'R' ? '지번주소 : '.$member['mb_addr_jibeon'] : ''); ?></span>
				</td>
			</tr>
			<tr>
				<th>E-mail</th>
				<td><input type=text name=od_email size=35 maxlength=100 value='<?=$member[mb_email]?>' class=ed></td>
			</tr>

			<? if ($default[de_hope_date_use]) { // 배송희망일 사용 ?>
			<tr>
				<th>희망배송일</th>
				<td><select name=od_hope_date>
					<option value=''>선택하십시오.
					<?
					for ($i=0; $i<7; $i++) {
						$sdate = date("Y-m-d", time()+86400*($default[de_hope_date_after]+$i));
						echo "<option value='$sdate'>$sdate (".get_yoil($sdate).")\n";
					}
					?>
					</select></td>
			</tr>
			<? } ?>
			</table>
		</td>
	</tr>
	</table>

	<!-- 받으시는 분 -->
	<table width=100% align=center cellpadding=0 cellspacing=10 border=0>
	<tr>
		<th class="table_title">받으시는 분</th>
	</tr>
	<tr>
		<td>
			<table cellpadding=0 cellspacing=0 width=100% class='list_order'>
			<colgroup>
				<col width='130'>
				<col />
			</colgroup>
			<tr>
				<td colspan=2>
					<input type=checkbox id=same name=same onclick="javascript:gumae2baesong(document.forderform);">
					<label for='same'><b>주문하시는 분과 받으시는 분의 정보가 동일한 경우 체크하세요.</b></label></td>
			</tr>
			<tr>
			<tr>
				<th>이름</th>
				<td><input type=text name=od_b_name class=ed maxlength=20></td>
			</tr>
			<!-- // 김선용 200908 : -->
			<tr>
				<th><u>통관고유식별정보</u></th>
				<td>
					<input type="radio" name='customs_clearance_code' value='c_code'/> 개인통관고유부호
					<input type="radio" name='customs_clearance_code' value='jumin' /> 주민등록번호
					<div class='customs_clearance_code_wrap' style='margin-top:10px;'>
						<b style='color:#ff0000'>통관필수요건을 선택해 주세요.</b>
					</div>
				</td>
			</tr>

			<tr>
				<th>전화번호</th>
				<td><input type=text name=od_b_tel class=ed maxlength=20></td>
			</tr>
			<tr>
				<th>핸드폰</th>
				<td><input type=text name=od_b_hp class=ed maxlength=20></td>
			</tr>
			<tr>
				<th rowspan='3'>주 소</th>
				<td style='border-bottom:none;'>
					<input type=text name=od_b_zip1 size=3 maxlength=3 class=ed readonly>
					-
					<input type=text name=od_b_zip2 size=3 maxlength=3 class=ed readonly>
					<a href="javascript:;" onclick="win_zip('forderform', 'od_b_zip1', 'od_b_zip2', 'od_b_addr1', 'od_b_addr2' , 'od_b_addr_jibeon');"><img
						src="<?=$g4[shop_img_path]?>/btn_zip_find.gif" border="0" align=absmiddle></a>
					</a>
				</td>
			</tr>
			<tr>
				<td style='border-bottom:none;'>
					<p><input type=text name=od_b_addr1 size=35 maxlength=50 class=ed readonly></p>
					<p><input type=text name=od_b_addr2 size=35 maxlength=50 class=ed> (상세주소)</p>
				</td>
			</tr>
			<tr>
				<td>
					<input type="hidden" name="od_b_addr_jibeon" value="<?=$member['mb_addr_jibeon']; ?>">
					<span id="od_b_addr_jibeon"></span>
				</td>
			</tr>
			<tr>
				<td>전하실말씀</td>
				<td><textarea name=od_memo rows=4 cols=60 class=ed></textarea></td>
			</tr>
			</table>
		</td>
	</tr>
	</table>

	<!-- 결제 정보 -->
	<table width=100% align=center cellpadding=0 cellspacing=10 border=0>
	<tr>
		<th class="table_title">결제 정보</th>
	</tr>
	<tr>
		<td>
			<table cellpadding=0 cellspacing=0 width=100% class='list_order'>
			<colgroup>
				<col width='100'>
				<col />
			</colgroup>
			<tr>
				<td height=50>
					<?
					$multi_settle == 0;
					$checked = "";


					/********* 김선용 2014.03 : KCP PG 처리.
					1. 가상계좌 사용시 무통장 입금 미출력.
					2. 가상계좌 사용시 한국시간 07~23시 사이만 출력.
					3. 구매상품 총합이 5만원 이상일 경우만 출력.
					**********/

				// 구매총합 ( 배송료는 상품금액이 아님)
				$kcp_point_str = "";
				if($default['de_kcp_escrow_use']) // 가상계좌사용
				{
					//if($tot_sell_amount >= 50000) { // 상품총합 5만원 이상
					if($tot_amount >= 50000) // 배송료포함 총합 5만원 이상
					{
						// 김선용 2014.04 : 고정계좌. 미사용. 1명이 여러건 주문시 kcp에서 주문번호별로 입금액 통보못함.
						/*
						if($member['mb_id'])
						{
							// 고정가상계좌 존재
							if($member['mb_kcp_vcnt_code'] != '' && $member['mb_kcp_vcnt_account'] != '') {
								$multi_settle++;
								echo "<input type='radio' id=od_settle_vbank_fix name=od_settle_case value='가상계좌' $checked><label for='od_settle_vbank_fix'>회원전용 고정계좌(무통장)</label> &nbsp;&nbsp;";
								echo "<input type=hidden name=vbank_fix value='1' />";
								$checked = "";
							}
						}
						*/
						$hour = (int)(date("H"));
						if($hour >= 7 && $hour < 23){ // 07~22:59:59
						//if($hour >= 1 && $hour < 24){
							$multi_settle++;
							echo "<input type='radio' id=od_settle_vbank name=od_settle_case value='가상계좌' $checked><label for='od_settle_vbank'>가상계좌(무통장)</label> &nbsp;&nbsp;";
							$checked = "";

							// kcp 가상계좌 추가 적립금 안내
							if($default['de_kcp_escrow_point']){
								$kcp_point_str = "<br/><span style='color:blue; font-weight:bold;'>※ 가상계좌(무통장) 결제 이벤트!! 총 구매 금액의 {$default['de_kcp_escrow_point']} % 포인트로 추가 적립!<br/>상품 수령후 '마이페이지-주문내역보기에서 수령확인시 자동적립' 됩니다. <b>단, 실 입금액 기준으로 적립됩니다. (포인트로 구매한경우등은 실제 포인트제외한 실제 입금액 기준입니다.)</span>";
							}
						}
					}else{ // 상품총합 5만원 미만
						/*
						// 무통장입금 사용
						if ($default[de_bank_use]) {
							$multi_settle++;
							echo "<input type='radio' id=od_settle_bank name='od_settle_case' value='무통장' $checked><label for='od_settle_bank'>무통장입금</label> &nbsp;&nbsp;";
							$checked = "";
						}
						*/
					}


				}
				else
				{
					/*
					// 무통장입금 사용
					if ($default[de_bank_use]) {
						$multi_settle++;
						echo "<input type='radio' id=od_settle_bank name='od_settle_case' value='무통장' $checked><label for='od_settle_bank'>무통장입금</label> &nbsp;&nbsp;";
						$checked = "";
					}
					*/
				} // if($default['de_kcp_escrow_use']) // 가상계좌사용


					// 계좌이체 사용
					if ($default[de_iche_use]) {
						$multi_settle++;
						echo "<input type='radio' id=od_settle_iche name=od_settle_case value='계좌이체' $checked><label for='od_settle_iche'>계좌이체</label> &nbsp;&nbsp;";
						$checked = "";
					}


					// 신용카드 사용
					if ($default[de_card_use])
					{
						// kcp 복합처리
						if($default['de_kcp_card_use']) { // kcp
							$multi_settle++;
							echo "<input type='radio' id=od_settle_card1 name=od_settle_case value='kcp' $checked><label for='od_settle_card1'>신용카드(국내 카드사 결제)</label> &nbsp;&nbsp;";
						}
						if($default['de_card_pg'] == 'authorize'){
							$multi_settle++;
							echo "<input type='radio' id=od_settle_card2 name=od_settle_case value='authorize' $checked><label for='od_settle_card2'>신용카드(해외 카드사 결제)</label> &nbsp;&nbsp;";
						}
						$checked = "";
					}


					// 김선용 2014.04
					// 무통장입금 사용
					if ($default[de_bank_use]) {
						$chk_bank = false;
						//$chk_bank = true;
						if($tot_amount < 50000) // 배송료포함 총합 5만원 미만
							$chk_bank = true;

						## 무통장입금 시간,금액 제한 해제 2014-06-02 홍민기
						$hour = (int)(date("H"));
						if($hour < 7 || $hour > 22) // 23~06:59:59. 하루 0~23
							$chk_bank = true;


						if($chk_bank){
							$multi_settle++;
							echo "<input type='radio' id=od_settle_bank name='od_settle_case' value='무통장' $checked><label for='od_settle_bank'>무통장입금</label> &nbsp;&nbsp;";
							$checked = "";
						}
					}

					# co.kr로 접속시에만 에스크로 결제 적용 2014-04-18 홍민기 #
					if($domain_flag == 'kr'){
						echo "
							<input type='radio' id='od_settle_escro' name='od_settle_case' value='에스크로'>
							<label for='od_settle_card2'>에스크로</label>
						";
						$multi_settle++;
					}

					// kcp 추가 포인트
					if($kcp_point_str != '')
						echo "<br/>{$kcp_point_str}";

					// 해외 카드결제 안내 메시지
					echo "
					<div id='_dis_authorize_info_' style='display:none;'>
						".($_MASTER_CARD_EVENT ? "
						<div><img src='http://115.68.20.84/event/master_card/master-card_event_order.jpg'/></div>
						":"")."
						<div style='padding-top:10px;'></div>
						<div style='line-height:190%; border:3px solid #fa5a00; padding:10px; font-size:14px;'>
						<div class='cardinfo'></div><br>
							<span style='color:#ff0000; font-weight:bold;'>※ 해외 카드사 결제 안내 (국내 카드사 결제인 경우는 해당없음)</span><br/>
							<b>1.</b> 해외 사용이 가능한 <u>Visa, Master, Amex, BC글로벌</u> 로고가 있는 카드만 가능합니다.<br>
							<b>2.</b> 해외 결제를 지원하는 체크카드의 경우 가능합니다.<br>
							<b>3.</b> 일시불 결제만 가능합니다. (할부 전환을 원할 경우 결제 후 해당 카드사에 문의바랍니다)<br>
							<b>4.</b> 카드 결제정보는 미국 최대 PG사인 Authorize의 최고 보안모듈 AIM을 통해 보호됩니다.<br>
							국내에서 사용되는 카드결제는 지금까지 많은 문제가 되고 있는 ActiveX 기반의
							웹링크 방식으로 보안 문제가 많아 공인인증서까지 사용해야 되는 불편함이 있습니다.
							저희 사이트는 ActiveX 를 사용하지 않고, 고객님께서 입력하신 카드 정보를 저희 서버를 거치지 않고 바로 카드사로 전송하는 고급 보안 기술을 사용하여 해킹 또는 크랙킹이 불가능하기 때문에 매우 안전한 카드 결제 시스템입니다.
							카드종류에 따라 해외사용 수수료가 발생 될 수 있습니다.<br>
							(총 결제하신 금액의 1.5% 이상이 수수료로 발생할 경우 초과 금액 분에 대하여는 포인트로 적립해 드립니다. 고객센터나 콜센터로 문의를 하시면 처리가 가능합니다.)

						</div>
					</div>
					";

					// 김선용 2014.04 : kcp vcnt (고정가상계좌) 안내
					echo "
					<div id='_dis_kcpvcnt_info_' style='display:none;'>
						<div style='padding-top:10px;'></div>
						<div style='line-height:190%; border:3px solid #fa5a00; padding:10px; font-size:14px;'>
							<span style='color:#ff0000; font-weight:bold;'>※ KCP 가상계좌 안내</span><br/>
							<b>※ 저희 ".getenv("HTTP_HOST")." 에서 사용하는 KCP 가상계좌는 별도의 액티브X 를 설치하거나 사용하지 않습니다.</B>
						</div>
					</div>
					";

					// 김선용 2014.04 : 고정가상계좌 - 회원 전용계좌 안내
					/*
					echo "
					<div id='_dis_kcpvcnt_info2_' style='display:none;'>
						<div style='padding-top:10px;'></div>
						<div style='line-height:190%; border:3px solid #fa5a00; padding:10px; font-size:14px;'>
							<span style='color:#ff0000; font-weight:bold;'>※ KCP 고유 가상계좌 안내</span><br/>
							<b>※ 회원님의 고유 가상계좌는 아래와 같습니다.<BR/>
							<span style='color:blue;'>{$member['mb_kcp_vcnt_account']}</span></br>
							만약 가상계좌를 바꾸고 싶다면, 전용가상계좌말고 일반 가상계좌를 선택해서 새로 발급이 가능합니다.<BR/>
							단, 같은 은행의 경우는 새로 발급되지 않습니다.</B>
						</div>
					</div>
					";
					*/




					// 회원이면서 포인트사용이면
					# 선결제 포인트 쿠폰이 장바구니에 있다면 포인트 결제 불가 2014-05-15 홍민기 #
					if ($is_member && $config[cf_use_point] && !$no_point)
					{
						// 포인트 결제 사용 포인트보다 회원의 포인트가 크다면
						if ($member[mb_point] >= $default[de_point_settle])
						{
							// 김선용 201304 : 포인트결제 1점단위로 변경처리
							$temp_point = $tot_amount * ($default['de_point_per'] / 100); // 포인트 결제 % 적용
							//$temp_point = (int)(($temp_point / 100) * 100); // 100점 단위
							$temp_point = (int)$temp_point;

							//$member_point = (int)(($member[mb_point] / 100) * 100); // 100점 단위
							if ($temp_point > $member['mb_point'])
								$temp_point = $member['mb_point'];

							echo "<br><br><input type=checkbox id=od_temp_point name=od_temp_point value='$temp_point' checked>";
							echo "<label for='od_temp_point'>보유포인트 ".display_point($temp_point)." 사용 : 주문금액의 {$default[de_point_per]}% 내에서 포인트 결제가 가능합니다.</label> &nbsp;";
							$multi_settle++;
						}
					}

					if ($multi_settle == 0)
						echo "<br><span class=point>결제할 방법이 없습니다.<br>운영자에게 알려주시면 감사하겠습니다.</span>";

					if (!$default[de_card_point])
						echo "<br><br>· '무통장입금' 이외의 결제 수단으로 결제하시는 경우 포인트를 적립해드리지 않습니다.";
					?>

				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>

	<p align=center>
	<a href='javascript:history.go(-1);'><img src="<?=$g4[shop_img_path]?>/btn_back1.gif" alt="뒤로" border=0></a>&nbsp;
		<input type="image" src="<?=$g4[shop_img_path]?>/btn_next2.gif" border=0 alt="다음">

</form>


<script type='text/javascript'>

// 김선용 2014.03 :
$(function() {

    $("#od_settle_card2").bind("click", function() {
		$("#_dis_authorize_info_").show();
    });
	$("#od_settle_vbank").bind("click", function() {
		$("#_dis_kcpvcnt_info_").show();
    });
	//$("#od_settle_vbank_fix").bind("click", function() {
	//	$("#_dis_kcpvcnt_info2_").show();
    //});

	// 결제 수단이 많거나 고정적이지 않은경우, 셀렉터를 일일이 지정해서 처리할 순 없다. 해외카드결제 셀렉터만 제외하고 처리.
	$("input:radio:not(#od_settle_card2)").bind("click", function() {
	//$("input:radio").not("#od_settle_card2").bind("click", function() { // 위와 같다.
        $("#_dis_authorize_info_").hide();
    });
	$("input:radio:not(#od_settle_vbank)").bind("click", function() {
	//$("input:radio").not("#od_settle_card2").bind("click", function() { // 위와 같다.
        $("#_dis_kcpvcnt_info_").hide();
    });
	//$("input:radio:not(#od_settle_vbank_fix)").bind("click", function() {
	//$("input:radio").not("#od_settle_card2").bind("click", function() { // 위와 같다.
    //    $("#_dis_kcpvcnt_info2_").hide();
    //});
});

function forderform_check(f)
{
    errmsg = "";
    errfld = "";
    var deffld = "";

    check_field(f.od_name, "주문하시는 분 이름을 입력하십시오.");
    if (typeof(f.od_pwd) != 'undefined')
    {
        clear_field(f.od_pwd);
        if( (f.od_pwd.value.length<3) || (f.od_pwd.value.search(/([^A-Za-z0-9]+)/)!=-1) )
            error_field(f.od_pwd, "회원이 아니신 경우 주문서 조회시 필요한 비밀번호를 3자리 이상 입력해 주십시오.");
    }
    check_field(f.od_hp, "주문하시는 분 전화번호를 입력하십시오.");
    check_field(f.od_addr1, "우편번호 찾기를 이용하여 주문하시는 분 주소를 입력하십시오.");
    check_field(f.od_addr2, " 주문하시는 분의 상세주소를 입력하십시오.");
    check_field(f.od_zip1, "");
    check_field(f.od_zip2, "");

    clear_field(f.od_email);
    if(f.od_email.value=='' || f.od_email.value.search(/(\S+)@(\S+)\.(\S+)/) == -1)
        error_field(f.od_email, "E-mail을 바르게 입력해 주십시오.");

    if (typeof(f.od_hope_date) != "undefined")
    {
        clear_field(f.od_hope_date);
        if (!f.od_hope_date.value)
            error_field(f.od_hope_date, "희망배송일을 선택하여 주십시오.");
    }

    check_field(f.od_b_name, "받으시는 분 이름을 입력하십시오.");

	if($('input[name=customs_clearance_code]:checked').length == 0){
		alert('통관고유식별정보을 선택해 주세요.');
		$('input[name=customs_clearance_code]:eq(0)').focus();
		return false;
	}
	if($('input[name=detail_clause_agree]').is(':checked') == false){
		alert('통관고유식별정보에 대한 동의에 체크해 주세요');
		$('input[name=detail_clause_agree]').focus();
		return false;
	}


	if (typeof(f.od_b_code) != "undefined"){
		if(f.od_b_code.value.length == 0){
			alert('개인통관고유부호를 입력해 주세요');
			f.od_b_code.focus();
			return false;

		}else if(f.od_b_code.value.replace(/[^0-9a-zA-Z]/g,'').length == 0){
			alert('올바른 개인통관고유부호를 입력해 주세요');
			f.od_b_code.focus();
			return false;
		}else if(f.od_b_code.value.replace(/[^0-9a-zA-Z]/g,'').length != 13){
			alert('올바른 개인통관고유부호를 입력해 주세요');
			f.od_b_code.focus();
			return false;
		}else if(f.od_b_code.value.length != 13){
			alert('개인통관고유부호 13자리를 입력해 주세요');
			f.od_b_code.focus();
			return false;
		}
	}
	if( typeof(f.od_b_jumin) != 'undefined'){
		if($('.foreigner').is(':checked') == false){
			if(f.od_b_jumin.value.replace(/[^0-9]/g,'').length != 13){
				alert('올바른 주민등록번호를 입력해 주세요.');
				f.od_b_jumin.focus();
				return false;
			}
			wrestJumin(f.od_b_jumin);
			if(wrestMsg != ''){
				alert(wrestMsg);
				f.od_b_jumin.focus();
				return false;
			}
		}else{
			if(f.od_b_jumin.value.replace(/[^0-9]/g,'').length != 13){
				alert('올바른 외국인등록번호를 입력해 주세요.');
				f.od_b_jumin.focus();
				return false;
			}
		}

	}

    check_field(f.od_b_hp, "받으시는 분 핸드폰번호를 입력하십시오.");
	check_field(f.od_b_tel, "받으시는 분 전화번호를 입력하십시오.");
    check_field(f.od_b_addr1, "우편번호 찾기를 이용하여 받으시는 분 주소를 입력하십시오.");
    check_field(f.od_b_addr2, "받으시는 분의 상세주소를 입력하십시오.");
    check_field(f.od_b_zip1, "");
    check_field(f.od_b_zip2, "");

    // 배송비를 받지 않거나 더 받는 경우 아래식에 + 또는 - 로 대입
    f.od_send_cost.value = parseInt(f.od_send_cost.value);

    if (errmsg)
    {
        alert(errmsg);
        errfld.focus();
        return false;
    }

    var settle_case = document.getElementsByName("od_settle_case");
    var settle_check = false;
    for (i=0; i<settle_case.length; i++)
    {
        if (settle_case[i].checked)
        {
            settle_check = true;
            break;
        }
    }
    if (!settle_check)
    {
        alert("결제방식을 선택하십시오.");
        return false;
    }

    return true;
}

// 구매자 정보와 동일합니다.
function gumae2baesong(f)
{
    f.od_b_name.value = f.od_name.value;
    f.od_b_tel.value  = f.od_tel.value;
    f.od_b_hp.value   = f.od_hp.value;
    f.od_b_zip1.value = f.od_zip1.value;
    f.od_b_zip2.value = f.od_zip2.value;
    f.od_b_addr1.value = f.od_addr1.value;
    f.od_b_addr2.value = f.od_addr2.value;
	f.od_b_addr_jibeon.value = f.od_addr_jibeon.value;
	od_b_addr_jibeon.textContent = od_addr_jibeon.textContent
}

// 통관 필수요건 선택
$('input[name=customs_clearance_code]').change(function(){
	var in_div = "";
	var agree_detail = // 관련법규
		"<b>개인정보 보호법</b><br/>"+
		"일부개정 2014.03.24 [법률 제12504호, 시행 2014.03.24] 안전행정부<br/><br/>"+
		"제15조(개인정보의 수집ㆍ이용)<br/>"+
		"① 개인정보처리자는 다음 각 호의 어느 하나에 해당하는 경우에는 개인정보를 수집할 수 있으며 그 수집 목적의 범위에서 이용할 수 있다.<br/>"+
		"1. 정보주체의 동의를 받은 경우<br/>"+
		"2. 법률에 특별한 규정이 있거나 법령상 의무를 준수하기 위하여 불가피한 경우<br/>"+
		"3. 공공기관이 법령 등에서 정하는 소관 업무의 수행을 위하여 불가피한 경우<br/>"+
		"4. 정보주체와의 계약의 체결 및 이행을 위하여 불가피하게 필요한 경우<br/>"+
		"5. 정보주체 또는 그 법정대리인이 의사표시를 할 수 없는 상태에 있거나 주소불명 등으로 사전 동의를 받을 수 없는 경우로서 명백히 정보주체 또는 제3자의 급박한 생명, 신체, 재산의 이익을 위하여 필요하다고 인정되는 경우<br/>"+
		"6. 개인정보처리자의 정당한 이익을 달성하기 위하여 필요한 경우로서 명백하게 정보주체의 권리보다 우선하는 경우. 이 경우 개인정보처리자의 정당한 이익과 상당한 관련이 있고 합리적인 범위를 초과하지 아니하는 경우에 한한다.<br/>"+
		"② 개인정보처리자는 제1항제1호에 따른 동의를 받을 때에는 다음 각 호의 사항을 정보주체에게 알려야 한다. 다음 각 호의 어느 하나의 사항을 변경하는 경우에도 이를 알리고 동의를 받아야 한다.<br/>"+
		"1. 개인정보의 수집ㆍ이용 목적<br/>"+
		"2. 수집하려는 개인정보의 항목<br/>"+
		"3. 개인정보의 보유 및 이용 기간<br/>"+
		"4. 동의를 거부할 권리가 있다는 사실 및 동의 거부에 따른 불이익이 있는 경우에는 그 불이익의 내용<br/><br/>"+

		"제17조(개인정보의 제공)<br/>"+
		"① 개인정보처리자는 다음 각 호의 어느 하나에 해당되는 경우에는 정보주체의 개인정보를 제3자에게 제공(공유를 포함한다. 이하 같다)할 수 있다.<br/>"+
		"1. 정보주체의 동의를 받은 경우<br/>"+
		"2. 제15조제1항제2호ㆍ제3호 및 제5호에 따라 개인정보를 수집한 목적 범위에서 개인정보를 제공하는 경우<br/>"+
		"② 개인정보처리자는 제1항제1호에 따른 동의를 받을 때에는 다음 각 호의 사항을 정보주체에게 알려야 한다. 다음 각 호의 어느 하나의 사항을 변경하는 경우에도 이를 알리고 동의를 받아야 한다.<br/>"+
		"1. 개인정보를 제공받는 자<br/>"+
		"2. 개인정보를 제공받는 자의 개인정보 이용 목적<br/>"+
		"3. 제공하는 개인정보의 항목<br/>"+
		"4. 개인정보를 제공받는 자의 개인정보 보유 및 이용 기간<br/>"+
		"5. 동의를 거부할 권리가 있다는 사실 및 동의 거부에 따른 불이익이 있는 경우에는 그 불이익의 내용<br/>"+
		"③ 개인정보처리자가 개인정보를 국외의 제3자에게 제공할 때에는 제2항 각 호에 따른 사항을 정보주체에게 알리고 동의를 받아야 하며, 이 법을 위반하는 내용으로 개인정보의 국외 이전에 관한 계약을 체결하여서는 아니 된다.<br/><br/>"+

		"제24조의2(주민등록번호 처리의 제한)연혁<br/>"+
		"① 제24조제1항에도 불구하고 개인정보처리자는 다음 각 호의 어느 하나에 해당하는 경우를 제외하고는 주민등록번호를 처리할 수 없다.<br/>"+
		"1. 법령에서 구체적으로 주민등록번호의 처리를 요구하거나 허용한 경우<br/>"+
		"2. 정보주체 또는 제3자의 급박한 생명, 신체, 재산의 이익을 위하여 명백히 필요하다고 인정되는 경우<br/>"+
		"3. 제1호 및 제2호에 준하여 주민등록번호 처리가 불가피한 경우로서 안전행정부령으로 정하는 경우<br/>"+
		"② 개인정보처리자는 제24조제3항에도 불구하고 주민등록번호가 분실ㆍ도난ㆍ유출ㆍ변조 또는 훼손되지 아니하도록 암호화 조치를 통하여 안전하게 보관하여야 한다. 이 경우 암호화 적용 대상 및 대상별 적용 시기 등에 관하여 필요한 사항은 개인정보의 처리 규모와 유출 시 영향 등을 고려하여 대통령령으로 정한다. <신설 2014.3.24><br/>"+
		"③ 개인정보처리자는 제1항 각 호에 따라 주민등록번호를 처리하는 경우에도 정보주체가 인터넷 홈페이지를 통하여 회원으로 가입하는 단계에서는 주민등록번호를 사용하지 아니하고도 회원으로 가입할 수 있는 방법을 제공하여야 한다. <개정 2014.3.24><br/>"+
		"④ 안전행정부장관은 개인정보처리자가 제3항에 따른 방법을 제공할 수 있도록 관계 법령의 정비, 계획의 수립, 필요한 시설 및 시스템의 구축 등 제반 조치를 마련ㆍ지원할 수 있다. <개정 2014.3.24><br/><br/>";

	switch($(this).val()){
		case 'c_code' :
			in_div =
				"<div class='' style='background-color:#fff;line-height:16px;padding:10px;font-size:11px;'>"+
					"<a href='<?=$g4['shop_path']?>/persnoal_number_info.php' target='_blank' style='font-weight:bold;'>개인통관고유부호 발급 안내 바로 가기</a><br/><br/>"+
					"오플닷컴은 입력하신 개인통관고유부호를<br/>"+
					"오직 물품 통관에 관련된 목적으로 계약된 관세법인에게만 제공하며<br/>"+
					"다른 목적으로 이용 또는 제3자에게 판매, 양도하지 않습니다.<br/>"+
					"또한 입력하신 개인통관고유부호는 배송완료 후 자동 파기됩니다.<br/>"+
					"물품을 받으시는 분의 개인통관고유부호 오류, 또는 미입력시 통관이 지연될 수 있으며,<br/>"+
					"이러한 경우에 관한 배송지연은 오플닷컴에서 책임지지 않습니다.<br/><br/>"+
					"<input type='button' value='자세히보기' onclick=\"$('.detail_clause').toggle(); return false;\">"+
				"</div>"+
				"<div class='detail_clause'>"+agree_detail+"</div>"+
				"<div style='margin:10px 0;'>"+
					"<p style='border-top:solid 1px #ccc;padding-top:10px;'>위의 약관에 동의하십니까?(필수)</p>"+
					"<p style='border-bottom:solid 1px #ccc;padding-bottom:10px;'><input type='checkbox' name='detail_clause_agree' value='Y' onchange=\"detail_clause_agree_change();\">동의합니다.</p>"+
				"</div>"+

				"개인통관고유부호 : <input type='text' name='od_b_code' id='od_b_code' size=18 maxlength=13 class='ed' itemname='개인통관고유부호' disabled><br><span class='warning'>";
			break;
		case 'jumin' :
			in_div =
				"<div class='' style='background-color:#fff;line-height:16px;padding:10px;font-size:11px;'>"+
					"<a href='<?=$g4['shop_path']?>/persnoal_number_info.php' target='_blank' style='font-weight:bold;'>개인통관고유부호 발급 안내 바로 가기</a><br/><br/>"+
					"오플닷컴은 입력하신 주민등록번호를<br/>"+
					"오직 물품 통관에 관련된 목적으로 계약된 관세법인에게만 제공하며<br/>"+
					"다른 목적으로 이용 또는 제3자에게 판매, 양도하지 않습니다.<br/>"+
					"또한 입력하신 주민등록번호는 배송완료 후 자동 파기됩니다.<br/>"+
					"물품을 받으시는 분의 주민등록번호 오류, 또는 미입력시 통관이 지연될 수 있으며,<br/>"+
					"이러한 경우에 관한 배송지연은 오플닷컴에서 책임지지 않습니다.<br/><br/>"+
					"<input type='button' value='자세히보기' onclick=\"$('.detail_clause').toggle(); return false;\">"+
				"</div>"+
				"<div class='detail_clause'>"+agree_detail+"</div>"+
				"<div style='margin:10px 0;'>"+
					"<p style='border-top:solid 1px #ccc;padding-top:10px;'>위의 약관에 동의하십니까?(필수)</p>"+
					"<p style='border-bottom:solid 1px #ccc;padding-bottom:10px;'><input type='checkbox' name='detail_clause_agree' value='Y' onchange=\"detail_clause_agree_change();\">동의합니다.</p>"+
				"</div>"+
				"주민등록번호 : <input type='password' name='od_b_jumin' id='od_b_jumin' size=18 maxlength=13 class='ed' jumin itemname='받는사람 주민등록번호' disabled>('-'없이 숫자만 입력하세요)"+
				"<br/>외국인 : <input type='checkbox' class='foreigner' onclick=\"foreigner_check();\"/>";
			break;
	}
	$('.customs_clearance_code_wrap').html(in_div);
});

function detail_clause_agree_change(){
	if(typeof($('input[name=detail_clause_agree]').val()) != 'undefined'){
		if($('input[name=detail_clause_agree][value=Y]:checked').length > 0){
			$('#od_b_jumin, #od_b_code').removeAttr('disabled');
		}else{
			$('#od_b_jumin, #od_b_code').attr('disabled',true);
		}

	}else{
		return false;
	}
}


function foreigner_check(){
	if($('.foreigner').is(':checked') == true){
		$('input[name=od_b_jumin]').removeAttr('jumin');
	}else{
		$('input[name=od_b_jumin]').attr('jumin',true);
	}
}
</script>

<?
include_once("./_tail.php");
?>