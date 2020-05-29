<?php
include_once("./_common.php");


// 김선용 201210 : 복수배송지 테이블 쓰레기 정리
$os_sql = sql_query("select os_pid from {$g4['yc4_os_table']} where os_status='쇼핑' and date_add(os_datetime, interval 24 hour) < now() ");
while($os_row=sql_fetch_array($os_sql)){
	sql_query("update {$g4['yc4_cart_table']} set ct_ship_os_pid='', ct_ship_ct_qty='' where ct_status='쇼핑' and ct_ship_os_pid like '%{$os_row['os_pid']}%' ", false);
}
sql_query("delete from {$g4['yc4_os_table']} where os_status='쇼핑' and date_add(os_datetime, interval 24 hour) < now() ", false);

// 장바구니가 비어있는가?
$tmp_on_uid = get_session('ss_on_uid');
if (get_cart_count($tmp_on_uid) == 0)
    alert("장바구니가 비어 있습니다.", "./cart.php");

$g4[title] = "주문서 작성";

include_once("./_head.php");
?>
<!--<img src="<?=$g4[shop_img_path]?>/top_orderform.gif" border="0"><p>-->
<div style="padding-top:20px;"></div>
<table width="755" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td width="319"><img src="<?=$g4['path']?>/images/category/category_title01_a.gif" width="320" height="40"></td>
	<td width="353" align="right" class="font11">HOME &gt; <span class="font11_orange">주문서 작성</span></td>
</tr>
<tr><td height="1" colspan="2" bgcolor="#fa5a00"></td></tr>
</table><p>

<?
$s_page = 'orderform.php';
$s_on_uid = $tmp_on_uid;
include_once("./cartsub.inc.php");
?>
<form name=forderform id=forderform method=post action="./orderreceipt_multi_addr.php" onsubmit="return forderform_check(this);" autocomplete=off>
<input type=hidden name=od_amount    value='<?=$tot_sell_amount?>'>
<input type=hidden name=od_send_cost value='<?=$send_cost?>'>
<input type="hidden" name="od_ship" id="od_ship" value="" /> <? // 김선용 201210 : 복수배송 구분값(order) ?>


<!-- 주문하시는 분 -->
<table width=97% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
    <td class="c3" align=center>주문하시는분</td>
    <td bgcolor=#FAFAFA style='padding-left:10px'>
        <table cellpadding=3>
        <colgroup width=100>
        <colgroup width=''>
        <tr>
            <td>이름</td>
            <td><input type=text id=od_name name=od_name value='<?=$member[mb_name]?>' maxlength=20 class=ed></td>
        </tr>

        <? if (!$is_member) { // 비회원이면 ?>
        <tr>
            <td>비밀번호</td>
            <td><input type=password name=od_pwd class=ed maxlength=20>
                영,숫자 3~20자 (주문서 조회시 필요)</td>
        </tr>
        <? } ?>

        <tr>
            <td>전화번호</td>
            <td><input type=text name=od_tel value='<?=$member[mb_tel]?>' maxlength=20 class=ed></td>
        </tr>
        <tr>
            <td>핸드폰</td>
            <td><input type=text name=od_hp value='<?=$member[mb_hp]?>' maxlength=20 class=ed></td>
        </tr>
        <tr>
            <td rowspan=2>주 소</td>
            <td>
                <input type=text name=od_zip1 size=3 maxlength=3 value='<?=$member[mb_zip1]?>' class=ed readonly>
                -
                <input type=text name=od_zip2 size=3 maxlength=3 value='<?=$member[mb_zip2]?>' class=ed readonly>
                <a href="javascript:;" onclick="win_zip('forderform', 'od_zip1', 'od_zip2', 'od_addr1', 'od_addr2');"><img
                    src="<?=$g4[shop_img_path]?>/btn_zip_find.gif" border="0" align=absmiddle></a>
            </td>
        </tr>
        <tr>
            <td>
                <input type=text name=od_addr1 size=35 maxlength=50 value='<?=$member[mb_addr1]?>' class=ed readonly>
                <input type=text name=od_addr2 size=15 maxlength=50 value='<?=$member[mb_addr2]?>' class=ed> (상세주소)
            </td>
        </tr>
        <tr>
            <td>E-mail</td>
            <td><input type=text name=od_email size=35 maxlength=100 value='<?=$member[mb_email]?>' class=ed></td>
        </tr>

        <? if ($default[de_hope_date_use]) { // 배송희망일 사용 ?>
        <tr>
            <td>희망배송일</td>
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


<fieldset style="padding:10px; line-height:150%; margin:0 20px 0 20px;">
	<legend>복수배송지 배송정책 안내 (반드시 읽어보셔야 합니다.)</legend>
	<div>
	※ 배송지1곳을 기준으로 배송상품의 <b><u>병수량이 기준수량(6병)을 초과했다면 6병씩 나누어 발송</u></b>합니다.<br/>(예외상품도 있습니다.)<br/>
	※ <b><u><?=$default['de_order_ship_multi_default']?>곳 까지 기본배송비에 포함되고</u></b>, 배송지가 <b><u>추가되는 경우 1곳당 <?=nf($send_cost);?> 원이 추가</u></b>됩니다.<br/>
	※ 단, 상품합계가 <b><u><?=nf($default['de_order_ship_multi_free_amount']);?>원 이상인 경우, 배송지수에 관계없이 무료로 배송</u></b>됩니다.<br/>
	<span style="color:blue;">※ 배송지추가는 아래에서 선택해서 1군데 또는 복수배송지를 선택할 수 있고, 복수배송지 선택시에는 해당 배송지별 상품 및 상품수량 설정이 가능합니다.</span>
	</div>
</fieldset>

<?//=$tmp_on_uid?>

<p style="margin:10px 0 0 0; text-align:center;">
<input type="button" value="기본배송선택(1곳)" title="기본배송선택(1곳)" onclick="get_ship_form('0');" style="height:30px;" />&nbsp;&nbsp;
<input type="button" value="복수배송선택(2곳이상)" title="복수배송선택(2곳이상)" onclick="get_ship_form('1');" style="height:30px;" />
</P>


<fieldset style="padding:10px; line-height:150%; margin:0 20px 0 20px;">
	<legend>주의사항</legend>
	<div>
	<span style="color:#cc0000;">
		1. 복수배송지를 등록하고, 위의 '기본배송선택(1곳)' 버튼을 누르면 등록된 복수배송정보가 모두 삭제됩니다.<br/>
		2. 등록된 복수배송지정보는 주문을 완료하지 않으면 24시간후에 자동 삭제됩니다.(각각의 설정수량도 초기화 됩니다.)<br/>
		3. 복수배송을 선택한경우, <U>다음페이지로 이동했다가 <b>'뒤로'</b> 버튼</U>을 눌러서 여기로 돌아온 경우는 새로고침을 눌러주십시오. (F5 키)
	</span>
	</div>
</fieldset>


<a id="dis_ship_item"></a>
<div id="_dis_ship_item_" style="margin:0 20px 0 20px;"><?if(get_fui_ship_item($tmp_on_uid, $member['mb_id']) != '#no_ship') echo get_fui_ship_item($tmp_on_uid, $member['mb_id'], $send_cost);?></div> <!-- 복수배송지별 배송상품정보 -->

<div id="_dis_ship_info_" style="margin:0 20px 0 20px;"></div> <!-- 배송지 입력폼 -->

<!-- 결제 정보 -->
<table width=97% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
    <td class="c3" align=center>결제 방법</td>
    <td bgcolor=#FAFAFA style='padding-left:10px'>
        <table cellpadding=3>
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


<!-- 주소록 레이어 -->
<div id="_dis_addr_" style="display:none; position:absolute; z-index:999; width:600px; height:500px; background-color:white; border:1px solid black; border-collapse:collapse; padding:10px; overflow:auto;"></div>


<script type="text/javascript">

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

// 김선용 201210 :
function print_bottle_sum()
{
	var a = $("input[name='sel_qty[]']"); // 상품수량
	var b = $("input[name='it_bottle_count[]']"); // 병수량정보
	var sum = parseInt(0);
	$("input[name='chk[]']:checkbox:checked").each(function() {
		sum += parseInt(b[$(this).val()].value * a[$(this).val()].value);
	});
	$('#_dis_bottle_str_').text(sum);
}
function check_all(f, fld_arr)
{
 	if(!fld_arr)
	    var chk = document.getElementsByName('chk[]');
	else
		var chk = document.getElementsByName(fld_arr);

    for (i=0; i<chk.length; i++){
		if(chk[i].disabled == false)
	        chk[i].checked = f.chkall.checked;
	}
}
function get_ship_addr() // 배송지 주소록
{
	if(!g4_is_member){
		alert("회원 서비스 입니다.");
		return;
	}
	if(get_id('_dis_addr_').style.display == 'block')
		return;

	$('#_dis_addr_').empty();
	$.ajax({
		type: 'POST',
		url: 'orderform.jquery.php',
		data: { 's_type' : 'addr' },
		cache: false,
		async: false,
		success: function(result) {
			var id = '#_dis_addr_';
			get_id('_dis_addr_').style.display = 'block';
			$(id).css({
				// window.width 사용불가. css에서 강제하기 때문에 가로크기가 다르게 잡힘. 콘테이너 개체로 가로크기를 기준함
				//'left': (($(window).width() - $(id).width())/2 + $(window).scrollLeft()) + 'px',
				'left': (($('#container').width() - $(id).width())/2 + $(window).scrollLeft()) + 'px',
				'top': (($(window).height() - $(id).height())/2 + $(window).scrollTop()) + 'px'
			});//.fadeIn();
			//$(id).show();
			$(id).html(result);
		}
	});
}
// args 순서 : {$row['ma_name']}', '{$row['ma_hp']}', '{$row['ma_tel']}', '{$row['ma_zip1']}', '{$row['ma_zip2']}', '{$row['ma_addr1']}', '{$row['ma_addr2']}
function insert_ship_addr() // 주소록에서 배송지입력폼에 셋팅
{

	var a = arguments;
	get_id('od_b_name').value = a[0];
	get_id('od_b_hp').value = a[1];
	get_id('od_b_tel').value = a[2];
	get_id('od_b_zip1').value = a[3];
	get_id('od_b_zip2').value = a[4];
	get_id('od_b_addr1').value = a[5];
	get_id('od_b_addr2').value = a[6];
	get_id('_dis_addr_').style.display = 'none';
}
function get_ship_save() // 복수배송정보 저장
{
	var f = document.forderform;
    errmsg = "";
    errfld = "";
	check_field(f.od_post_name, "보내는사람(업체명등) 을 입력하십시오.");
	check_field(f.od_b_name, "받으시는 분 이름을 입력하십시오.");
	check_field(f.od_b_tel, "받으시는 분 전화번호를 입력하십시오.");
	check_field(f.od_b_jumin, "받으시는 분 주민등록번호를 입력하십시오.");
	check_field(f.od_b_addr1, "우편번호 찾기를 이용하여 받으시는 분 주소를 입력하십시오.");
	check_field(f.od_b_addr2, "받으시는 분의 상세주소를 입력하십시오.");
	check_field(f.od_b_zip1, "");
	check_field(f.od_b_zip2, "");
    if (errmsg){
        alert(errmsg);
        errfld.focus();
        return;
    }

    var a = false;
	var b = document.getElementsByName('chk[]');
    for(var i=0; i<b.length; i++){
        if(b[i].checked){
            a = true;
            break;
        }
    }
    if (a == false) {
        alert("해당 배송지로 배송할 상품을 좌측 체크박스로 선택하고 수량을 입력해 주십시오.");
		document.getElementsByName('chk[]')[0].focus();
        return;
    }

	var post_data = $("#forderform").serialize()+"&s_type=save&tmp_on_uid=<?=$tmp_on_uid?>&send_cost=<?=$send_cost?>";
	//var post_data = $("#forderform").serializeArray();
	//$("#_dis_addr_").empty().append(post_data);
	//$("#_dis_addr_").show(); return;
	$.ajax({
		type: 'POST',
		url: 'orderform.jquery.php',
		data: post_data,
		cache: false,
		async: false,
		success: function(result) {
			if(result == '#save_error'){
				alert("장바구니에 자료가 없습니다.\n\n장바구니로 이동해서 다시 시도해 주십시오. 같은문제가 계속되면 관리자에게 문의 바랍니다.\n\n문의코드 : #order -multi-ship-save-error");
				$('#_dis_ship_item_').empty();
				return;
			}else{
				$('#_dis_ship_item_').html(result);
				get_ship_form('1'); // 복수배송지 입력폼 다시 뿌린다(지정할 상품정보가 바뀌므로)
			}
		}
	});
}
function del_ship(os_pid, on_uid, mb_id)
{
	$.ajax({
		type: 'POST',
		url: 'orderform.jquery.php',
		data: { 's_type' : 'del_os', 'tmp_on_uid' : on_uid, 'os_pid' : os_pid, 'send_cost' : '<?=$send_cost?>' },
		cache: false,
		async: false,
		success: function(result) {
			if(result == '#no_ship'){
				$('#_dis_ship_item_').empty();
			}else{
				$('#_dis_ship_item_').html(result);
			}
		}
	});
	get_ship_form('1'); // 복수배송지 입력폼 다시 뿌린다(지정할 상품정보가 바뀌므로)
}
/* // 복수배송 정보받을 때 같이 받음
function view_ship_item(os_pid)
{
	$.ajax({
		type: 'POST',
		url: 'orderform.jquery.php',
		data: { 's_type' : 'view', 'os_pid' : os_pid, 'tmp_on_uid' : '<?=$tmp_on_uid?>' },
		cache: false,
		async: false,
		success: function(result) {
			var id = '#_dis_view_item_';
			$(id).html(result);
			$(id).css({
				// window.width 사용불가. css에서 강제하기 때문에 가로크기가 다르게 잡힘. 콘테이너 개체로 가로크기를 기준함
				//'left': (($(window).width() - $(id).width())/2 + $(window).scrollLeft()) + 'px',
				'left': (($('#container').width() - $(id).width())/2 + $(window).scrollLeft()) + 'px',
				'top': (($(window).height() - $(id).height())/2 + $(window).scrollTop()) + 'px'
			});//.fadeIn();
			$(id).show();
		}
	});
}
*/
function dis_addr_hide()
{
	if(get_id('_dis_addr_').style.display == 'block')
		get_id('_dis_addr_').style.display = 'none';
}
function init_set()
{
	$.ajax({
		type: 'POST',
		url: 'orderform.jquery.php',
		data: { 's_type' : 'init', 'tmp_on_uid' : '<?=$tmp_on_uid?>' },
		cache: false,
		async: false,
		success: function(result) {
			if(result == '#no_ship'){
				$('#_dis_ship_item_').empty();
			}
		}
	});
	get_ship_form('1');
}
function get_ship_form(s_type) // 배송지 입력폼
{
	if(s_type == '')
	{
		alert("배송지구분값이 없습니다.\n\n기본배송지/복수배송지를 선택해 주십시오.");
		return;
	}
	// 등록한 배송지가 있다면 안내
	if(s_type == '0' && $('#chk_ship').length > 0) {
		if(confirm("복수배송으로 등록한 자료가 있습니다.\n\n기본배송선택(1곳)으로 바꾸면 등록한 복수배송정보가 모두 삭제됩니다.\n\n기본배송선택(1곳)으로 변경하시겠습니까?")){
			$.ajax({
				type: 'POST',
				url: 'orderform.jquery.php',
				data: { 's_type' : 'init', 'tmp_on_uid' : '<?=$tmp_on_uid?>' },
				cache: false,
				async: false,
				success: function(result) {
					if(result == '#no_ship'){
						$('#_dis_ship_item_').empty();
					}
				}
			});
		}
		else
			return;
	}

	dis_addr_hide();

	$('#od_ship').val(s_type);
	$.ajax({
		type: 'POST',
		url: 'orderform.jquery.php',
		data: { 's_type' : s_type, 'it_bottle_sum' : '<?=$it_bottle_sum?>', 'tmp_on_uid' : '<?=$tmp_on_uid?>' },
		cache: false,
		async: false,
		success: function(result) {
			if(result == '#error'){
				alert("배송지 구분값이 없습니다. 기본배송지/복수배송지를 선택해 주십시오. #return");
				$('#_dis_ship_info_').empty();
				return;
			}else{
				$('#_dis_ship_info_').html(result);
				if(s_type == '1'){
					print_bottle_sum();
					var Yy = $('#dis_ship_item').position(); // y 좌표 이동
					$("html,body").stop().animate({'scrollTop':Yy.top +5}, 500);
				}
			}
		}
	});
}


function forderform_check(f)
{
	if(f == '') f = document.forderform;
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
    check_field(f.od_tel, "주문하시는 분 전화번호를 입력하십시오.");
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

	// 김선용 201210 : 앞으로갔다가 뒤로온경우 처리. 기타 배송지가 1곳인경우 처리
	if($('#od_ship').val() == ''){
		if($('#chk_ship').length > 0)
			$('#od_ship').val('1');
		else
			$('#od_ship').val('1');
	}
	if($('#od_ship').val() == '0'){
		check_field(f.od_b_name, "받으시는 분 이름을 입력하십시오.");
		check_field(f.od_b_tel, "받으시는 분 전화번호를 입력하십시오.");
		check_field(f.od_b_jumin, "받으시는 분 주민등록번호를 입력하십시오.");
		check_field(f.od_b_addr1, "우편번호 찾기를 이용하여 받으시는 분 주소를 입력하십시오.");
		check_field(f.od_b_addr2, "받으시는 분의 상세주소를 입력하십시오.");
		check_field(f.od_b_zip1, "");
		check_field(f.od_b_zip2, "");
	}

	// 김선용 201211 :
    // 배송비를 받지 않거나 더 받는 경우 아래식에 + 또는 - 로 대입
    //f.od_send_cost.value = parseInt(f.od_send_cost.value);
	if(get_id('add_send_cost') != null){
		f.od_send_cost.value = '';
		f.od_send_cost.value = parseInt(<?=$send_cost?>) + parseInt(get_id('add_send_cost').value);
	}

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

	<?if($weight){ # 비가공 곡물제품 무게 처리 2014-07-17 홍민기 ?>
	var weight = Number('<?=$weight?>');
	if(weight >= 5000){
		var weight_kg = weight / 1000;
		if(!confirm('비가공 곡물 제품은 주문건당 5KG 이상일 경우 과세 대상에 포함됩니다.\n\n주문하시는 비가공 곡물제품은 '+weight_kg+'KG 이며 5KG을 초과합니다.\n주문하시겠습니까?')){
			return false;
		}
	}
	<?}?>

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
}
</script>

<?
include_once("./_tail.php");
?>