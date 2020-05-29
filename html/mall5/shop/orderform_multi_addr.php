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

$g4['title'] = "주문서 작성";


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
include_once("./cartsub.inc.php");
?>
<form name=forderform id=forderform method=post action="./orderreceipt_multi_addr.php" onsubmit="return forderform_check(this);" autocomplete=off>
<input type=hidden name=od_amount    value='<?=$tot_sell_amount?>'>
<input type=hidden name=od_send_cost value='<?=$send_cost?>'>
<input type="hidden" name="od_ship" id="od_ship" value="" /> <? // 김선용 201210 : 복수배송 구분값(order) ?>


<!-- 주문하시는 분 -->
<table width=100% cellpadding=0 cellspacing=0 border=0 style='margin-bottom:30px;'>
<tr>
    <th class="table_title">주문하시는분</th>
</tr>
<tr>
    <td>
        <table cellpadding=0 cellspacing=0 width=100% class='list_order'>
        <colgroup>
			<col width='140'>
			<col />
		</colgroup>
		<tbody>
        <tr>
            <th>이름</th>
            <td style='text-align:left;'><input type=text id=od_name name=od_name value='<?=$member[mb_name]?>' maxlength=20 class=ed></td>
        </tr>

        <? if (!$is_member) { // 비회원이면 ?>
        <tr>
            <th>비밀번호</th>
            <td style='text-align:left;'><input type=password name=od_pwd class=ed maxlength=20>
                영,숫자 3~20자 (주문서 조회시 필요)</td>
        </tr>
        <? } ?>

        <tr>
            <th>전화번호</th>
            <td style='text-align:left;'><input type=text name=od_tel value='<?=$member[mb_tel]?>' maxlength=20 class=ed></td>
        </tr>
        <tr>
            <th>핸드폰</th>
            <td style='text-align:left;'><input type=text name=od_hp value='<?=$member[mb_hp]?>' maxlength=20 class=ed></td>
        </tr>
        <tr>
            <th>주소</th>
            <td style='text-align:left;'>
                <p>
					<input type=text name=od_zip1 size=3 maxlength=3 value='<?=$member[mb_zip1]?>' class=ed readonly>
					-
					<input type=text name=od_zip2 size=3 maxlength=3 value='<?=$member[mb_zip2]?>' class=ed readonly>
					<a href="javascript:;" onclick="win_zip('forderform', 'od_zip1', 'od_zip2', 'od_addr1', 'od_addr2' ,'od_addr_jibeon' ,'od_zonecode');"><img
						src="<?=$g4[shop_img_path]?>/btn_zip_find.gif" border="0" align=absmiddle></a>
				</p>
				<p>
					<input type=text name=od_addr1 size=35 maxlength=50 value='<?=$member[mb_addr1]?>' class=ed readonly style='width:705px;'>
				</p>
				<p>
					<input type=text name=od_addr2 size=15 maxlength=50 value='<?=$member[mb_addr2]?>' class=ed style='width:505px;'> (상세주소)
				</p>
				<p>
					<input type="hidden" name="od_addr_jibeon" value="<?=$member['mb_addr_jibeon']; ?>">
					<input type="hidden" name='od_zonecode' value="<?=$member['mb_zonecode'];?>" />
					<span id="od_addr_jibeon"><?=($member['mb_addr_jibeon'] && $member['mb_addr_jibeon'] != 'R' ? '지번주소 : '.$member['mb_addr_jibeon'] : ''); ?></span>
				</p>
            </td>
        </tr>
		<tr>
        <tr>
            <th>E-mail</th>
            <td style='text-align:left;'><input type=text name=od_email size=35 maxlength=100 value='<?=$member[mb_email]?>' class=ed></td>
        </tr>

        <? if ($default[de_hope_date_use]) { // 배송희망일 사용 ?>
        <tr>
            <th>희망배송일</th>
            <td style='text-align:left;'><select name=od_hope_date>
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
        </tbody></table>
    </td>
</tr>
</table>

<!-- 받으시는 분 -->
<table width=100% cellpadding=0 cellspacing=0 border=0 style='margin-bottom:30px;'>
<tr>
    <th class="table_title">받으시는 분</th>
</tr>
<tr>
<td>
<div class='notice_al'>
	<div style='width:470px;padding:15px 30px;float:left;border-right:solid 1px #fa6926;'>
		<p class='box_title'>복수배송지 배송정책 안내 (반드시 읽어보셔야 합니다.)</p>
		<div class='box_cont'>
		<ul>
			<li>※ 배송지1곳을 기준으로 배송상품의 <b><u>병수량이 기준수량(6병)을 초과했다면 6병씩 나누어 발송</u></b>합니다.(예외상품도 있습니다.)</li>
			<li>※ <strong><u><?=$default['de_order_ship_multi_default']?>곳 까지 기본배송비에 포함되고</u></strong>, 배송지가 <strong><u>추가되는 경우 1곳당 <?=nf($send_cost);?> 원이 추가</u></strong>됩니다.</li>
			<li>※ 단, 상품합계가 <b><u><?=nf($default['de_order_ship_multi_free_amount']);?>원 이상인 경우, 배송지수에 관계없이 무료로 배송</u></b>됩니다.</li>
			<li><span style="color:blue;">※ 배송지추가는 아래에서 선택해서 1군데 또는 복수배송지를 선택할 수 있고, 복수배송지 선택시에는 해당 배송지별 상품 및 상품수량 설정이 가능합니다.</span></li>
		</ul>
		</div>
	</div>
	<div style='width:410px;padding:15px 40px;margin-left:530px;'>
		<p class='box_title'>주의사항</p>
		<div class='box_cont'>
		<ul>
			<li>1. 복수배송지를 등록하고, 위의 '기본배송선택(1곳)' 버튼을 누르면 등록된 복수배송정보가 모두 삭제됩니다.</li>
			<li>2. 등록된 복수배송지정보는 주문을 완료하지 않으면 24시간후에 자동 삭제됩니다.(각각의 설정수량도 초기화 됩니다.)</li>
			<li>3. 복수배송을 선택한경우, <U>다음페이지로 이동했다가 <b>'뒤로'</b> 버튼</U>을 눌러서 여기로 돌아온 경우는 새로고침을 눌러주십시오. (F5 키)</li>
		</ul>
		</div>
	</div>
</div>

<?//=$tmp_on_uid?>

<p style="padding:10px 0;clear:both;">
<input type="button" value="기본배송선택(1곳)" title="기본배송선택(1곳)" onclick="get_ship_form('0');" style="height:30px;padding:0 15px;line-height:30px;" />
<input type="button" value="복수배송선택(2곳이상)" title="복수배송선택(2곳이상)" onclick="get_ship_form('1');" style="height:30px;padding:0 15px;line-height:30px;" />
</P>

<a id="dis_ship_item"></a>
<div id="_dis_ship_item_"><?if(get_fui_ship_item($tmp_on_uid, $member['mb_id']) != '#no_ship') echo get_fui_ship_item($tmp_on_uid, $member['mb_id'], $send_cost);?></div> <!-- 복수배송지별 배송상품정보 -->

<div id="_dis_ship_info_"></div> <!-- 배송지 입력폼 -->
</td>
</tr>
</table>

<!-- 결제 정보 -->
<table width=100% align=center cellpadding=0 cellspacing=0 border=0>
<tr>
    <th class="table_title">결제 방법</th>
</tr>
<tr>
    <td>
        <table cellpadding=0 cellspacing=0 width=100% class='list_order'>
		<colgroup>
			<col width='140'>
			<col />
		</colgroup>
		<tbody>
        <tr>
            <td style='text-align:left;'>
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
					//$chk_bank = true; // 무통장 입금 무조건 보이게
					$chk_bank = false; // 무통장 입금 5만원 미만 금액일 경우에만 보이도록
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
					<div style='line-height:190%; border:3px solid #fa5a00; padding:10px; font-size:12px;'>
						<div style='width: 50%;height: 375px;float: left;border-right: solid 1px orangered;'>
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
						<p style='text-align:right;'><img src='img/cart_banner_cardEvent.jpg' alt='카드이벤트정보'/></p>
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

<p style='padding:20px 0 30px 0;text-align:center;'>
	<a href='javascript:history.go(-1);'><img src="<?=$g4[shop_img_path]?>/btn_back1.gif" alt="뒤로" border=0></a>
    <input type="image" src="<?=$g4[shop_img_path]?>/btn_next2.gif" border=0 alt="다음">
</p>

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
				'left': (($('.contentsArea').width() - $(id).width())/2 + $(window).scrollLeft()) + 'px',
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
	f.od_b_addr_jibeon.value = f.od_addr_jibeon.value;
	f.od_b_zonecode.value = f.od_zonecode.value;
	od_b_addr_jibeon.textContent = od_addr_jibeon.textContent
}

// 통관 필수요건 선택
//$('input[name=customs_clearance_code]').change(function(){
function customs_clearance_code_change () {
	var value = $('input[name=customs_clearance_code]:checked').val();
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

	switch(value){
		case 'c_code' :
			in_div =
				"<div class='' style='background-color:#fff;line-height:16px;padding:10px;font-size:11px;'>"+
					"<a href='<?=$g4['shop_path']?>/persnoal_number_info.php' target='_blank' style='font-weight:bold;'>개인통관고유부호 발급 안내 바로 가기</a><br/><br/>"+
					"오플닷컴은 입력하신 개인통관고유부호를<br/>"+
					"오직 물품 통관에 관련된 목적으로 계약된 관세법인에게만 제공하며<br/>"+
					"다른 목적으로 이용 또는 제3자에게 판매, 양도하지 않습니다.<br/>"+
					"또한 입력하신 개인통관고유부호는 배송완료 후 자동 파기됩니다.<br/>"+
					"물품을 받으시는 분의 개인통관고유부호 오류, 또는 미입력시 통관이 지연되며,<br/>"+
					"이러한 경우에 관한 배송지연은 오플닷컴에서 책임지지 않습니다.<br/><br/>"+
					"<input type='button' value='자세히보기' onclick=\"$('.detail_clause').toggle(); return false;\">"+
				"</div>"+
				"<div class='detail_clause'>"+agree_detail+"</div>"+
				"<div>"+
					"<p style='border-top:solid 1px #ccc;padding-top:10px;'>위의 약관에 동의하십니까?(필수)</p>"+
					"<p style='border-bottom:solid 1px #ccc;padding-bottom:10px;'><input type='checkbox' name='detail_clause_agree' value='Y' onchange=\"detail_clause_agree_change();\">동의합니다.</p>"+
				"</div>"+

				"개인통관고유부호 : <input type='text' name='od_b_code' id='od_b_code' size=18 maxlength=13 class='ed' required itemname='개인통관고유부호' disabled>";
			break;
		case 'jumin' :
			in_div =
				"<div class='' style='background-color:#fff;line-height:16px;padding:10px;font-size:11px;'>"+

					"<a href='<?=$g4['shop_path']?>/persnoal_number_info.php' target='_blank' style='font-weight:bold;'>개인통관고유부호 발급 안내 바로 가기</a><br/><br/>"+
					"오플닷컴은 입력하신 주민등록번호를<br/>"+
					"오직 물품 통관에 관련된 목적으로 계약된 관세법인에게만 제공하며<br/>"+
					"다른 목적으로 이용 또는 제3자에게 판매, 양도하지 않습니다.<br/>"+
					"또한 입력하신 주민등록번호는 배송완료 후 자동 파기됩니다.<br/>"+
					"물품을 받으시는 분의 주민등록번호 오류, 또는 미입력시 통관이 지연되며,<br/>"+
					"이러한 경우에 관한 배송지연은 오플닷컴에서 책임지지 않습니다.<br/><br/>"+
					"<input type='button' value='자세히보기' onclick=\"$('.detail_clause').toggle(); return false;\">"+
				"</div>"+
				"<div class='detail_clause'>"+agree_detail+"</div>"+
				"<div>"+
					"<p style='border-top:solid 1px #ccc;padding-top:10px;'>위의 약관에 동의하십니까?(필수)</p>"+
					"<p style='border-bottom:solid 1px #ccc;padding-bottom:10px;'><input type='checkbox' name='detail_clause_agree' value='Y' onchange=\"detail_clause_agree_change();\">동의합니다.</p>"+
				"</div>"+
				"주민등록번호 : <input type='password' name='od_b_jumin' id='od_b_jumin' size=18 maxlength=13 class='ed' required jumin itemname='받는사람 주민등록번호' disabled>('-'없이 숫자만 입력하세요) <br>"+
				"<br/>외국인 : <input type='checkbox' class='foreigner' onclick=\"foreigner_check();\"/>";
			break;
	}
	$('.customs_clearance_code_wrap').html(in_div);
}

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