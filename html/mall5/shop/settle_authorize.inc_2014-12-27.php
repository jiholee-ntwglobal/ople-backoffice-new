<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if($_MASTER_CARD_EVENT){
	//kb vcn 사용 여부 체크
	$kb_vcn = true;
	$kb_vcn_chk = sql_fetch("select count(*) as cnt from master_card_event where mb_id = '".$member['mb_id']."' and point > 0 ");
	if($kb_vcn_chk['cnt']>0){
		$kb_vcn = false;
	}

	$it_id_in = sql_fetch("select group_concat('''',it_id,'''') as it_id from master_card_no_item where it_id is not null");
	$it_id_in = $it_id_in['it_id'];
	//$it_maker_in = sql_fetch("select group_concat('''',it_maker,'''') as it_maker from master_card_no_item where it_maker is not null");
	$it_maker_sql = sql_query("select it_maker from master_card_no_item where it_maker is not null");
	while($row = sql_fetch_array($it_maker_sql)){
		$it_maker_in .= ($it_maker_in ? ",":""). "'".mysql_real_escape_string($it_maker_in['it_maker'])."'";
	}
	//$it_maker_in = $it_maker_in['it_maker'];


	# 마스터카드 프로모션에 해당하지 않는 상품 총 가격 로드
	$no_master_card_tot_amount = sql_fetch("
		select
			sum(a.ct_amount * a.ct_qty) as amount
		from
			yc4_cart a,
			yc4_item b
		where
			a.on_uid = '".$tmp_on_uid."'
			and
			a.it_id = b.it_id
			and (
				a.it_id in (".$it_id_in.")
				or
				b.it_maker in (".$it_maker_in.")
			)
	");

	# 마스터카드 이벤트에 해당되는 상품의 총 카드결제 가격
	$result_tot_amount = $od['od_temp_card'] - $no_master_card_tot_amount['amount'];

}






// 김선용 200801
//if(!$is_member)	alert("카드결제 보안에 의해 카드결제는 회원만 사용할 수 있습니다.");
if($default['de_card_pg'] != 'authorize')
	alert("PG 사가 Authorize.net 이 아닙니다.\\n\\n관리자에게 문의 바랍니다.");
if(trim($default['de_authorize_id']) == '' || trim($default['de_authorize_key']) == '')
	alert("API, tranjaction key 정보가 없습니다.\\n\\n관리자에게 문의 바랍니다.");

if($default['de_card_test'])
	$x_test = "true";
else
	$x_test = "false";



?>
<style type="text/css">
.card_dc_comment{
	display:none;
}
</style>

<iframe width="0" height="0" name="_authorize_"></iframe>
<form name="AIMform" method="post" onsubmit="return card_post(this);" autocomplete="off" style="margin:0;">
<input type=hidden name="x_type" value="AUTH_CAPTURE">
<input type=hidden name="x_version" value="3.1">
<input type=hidden name="x_delim_data" value="true">
<input type=hidden name="x_delim_char" value="|">
<input type=hidden name="x_relay_response" value="false">
<input type=hidden name="x_method" value="CC">
<input type=hidden name="x_test_request" value="<?=$x_test?>">
<input type=hidden name="x_cust_id" value="<?=$od['od_id']?>">
<input type=hidden name="x_amount" value="<?=$x_amount?>">
<input type=hidden name="x_customer_ip" value="<?=$_SERVER['REMOTE_ADDR']?>">
<input type=hidden name="x_currency_code" value="USD">
<input type=hidden name="x_first_name" value="<?=mb_substr($member['mb_name'],0,1,'utf-8')?>">
<input type=hidden name="x_last_name" value="<?=mb_substr($member['mb_name'],1,(strlen($member['mb_name'])-1),'utf-8')?>">
<input type=hidden name="x_phone" value="<?=$member['mb_tel']?>">
<input type=hidden name="x_email" value="<?=$member['mb_email']?>">
<input type=hidden name="x_email_customer" value="true">
<input type=hidden name="on_uid" value="<?=get_session('ss_temp_on_uid')?>">
<input type=hidden name="u_amount" value="<?=$od['od_temp_card']?>">
<input type="hidden" name='pay_ing' value='0' />
<input type="hidden" name='od_id' value='<?=$od_id;?>' />

<table width='100%' cellpadding='0' cellspacing='0' border='0' class='list_order'>
  <colgroup>
    <col width='140'>
      <col />
    </colgroup>
<tr>
	<td colspan='2' style='padding:0;'>
	<div style='line-height:150%; background-color:#f3f3f3; padding:15px;'>
		<font color='red'><u>※ 카드결제 안내</u></font><br>
		<b>1)</b> 카드사용시 할부는 사용할 수 없고, 일시불로만 결제가 됩니다.<br>사용하신 일시불은 한국의 해당 카드사에 문의하시면 10만원 이상건에 대해 할부로 전환이 됩니다.(해당 카드사별로 상이함)<br>
		<b>2)</b> 한국에서 사용하는 <u>할부/공인인증서</u>와 같은 부분은 한국에서만 사용하고 있는 기능입니다.&nbsp;&nbsp;미국등과 같은 한국이외 국가에서는 할부/공인인증서와 같은 기능은 사용하지 않습니다.<br>저희 <u>http://<?=$_SERVER['HTTP_HOST']?></u> 에서는 카드결제 보안을 위해서 미국내 최고 카드결제사인 authorize 의 최고 보안 모듈인 AIM 모듈방식을 사용하고 있으며, 회원에게만 카드결제가 가능토록 하고 있습니다.&nbsp;&nbsp;또한 <u>카드결제시 회원의 어떠한 카드정보도 저장하거나 남지 않으며</u> 결제정보 전송은 <u>최고 보안방식인 256비트 SSL 로 암호화하여 전송하는 방식을 이용하며, 한국의 웹링크방식이 아닌 다른 방식으로 전송하므로 중간에서 해킹이나 크랙킹이 전혀 불가능</u>합니다.<br>따라서 회원들께서는 안심하고 카드결제를 이용하실 수 있습니다.<br>
		<b>3)</b> 카드결제는 해외 사용이 가능한 비자/마스터/아멕스(American express) 카드만 가능합니다.&nbsp;&nbsp;(카드에 비자/마스터/아멕스 로고가 있어야 합니다.)<br>
		<b>4)</b> 한국 국내전용 카드는 결제하실 수 없습니다.(Local Card)<br>
		<b>5)</b> 비자/마스터 카드라해도 해외사용 신청을 하지 않았으면 결제가 불가능합니다.<br>
		(카드사에 해외 사용신청이 되어있는지 확인하시기 바랍니다.)<br>
		<b>6)</b> 한국에서 사용하는 <u>체크카드</u>의 경우, 해외사용이 가능한 체크카드라면 결제가 가능합니다.(visa/master/jcb/discover 로고가 있는 체크카드)

	</div>
	<?if($_MASTER_CARD_EVENT){?>
	<div><img src="http://115.68.20.84/event/master_card/master-card_event_order.jpg" alt="" /></div>
	<?}?>
	</td>
</tr>
<tr>
	<th>카드 번호</th>
	<td><input class=ed type='text' name="x_card_num" size="30" numeric required itemname='카드번호' maxlength=16 onblur='master_card_price_view(this.value)'> (숫자만 입력하십시오.)</td>
</tr>
<tr class='card_dc_comment'>
	<th>할인금액</th>
	<td class='card_dc_comment_contents'></td>
</tr>
<tr>
	<th>카드 유효기간</th>
	<td>
		<select name='x_exp_m'>
		<?for($i=1;$i<13;$i++){?>
			<option value='<?=$i?>'><?=$i?> 월</option>
		<?}?>
		</select>
		<select name='x_exp_y'>
		<?for($i=date("Y");$i<(date("Y") + 15);$i++){?>
			<option value='<?=$i?>'><?=$i?> 년</option>
		<?}?>
		</select>
	</td>
</tr>
<tr>
	<th>CVV2 Code</th>
	<td><input class=ed type='password' name="x_card_code" maxlength="4" size="10" required itemname='CVV2 Code 값'></td>
</tr>
<!--
<tr>
	<td colspan=2 align=center><input type=checkbox name=x_card_brand id=x_card_brand value=1><label for=x_card_brand><font color="#ff0000">※ 아멕스카드<u>(American Express Card)</u>의 경우 반드시 체크하십시오.</font></label></td>
</tr>
-->
<tr><td colspan='2'><font color='red'>※ CVV2 Code 란</font><br>- 일반 카드의 경우 카드뒷면 카드번호 끝에 있는 3자리의 숫자를 말합니다.<br>- 아멕스카드(American Express Card)의 경우 카드 전면 카드번호 끝에 있는 4자리의 숫자를 말합니다.<br/><!--<p><font color='red'>긴급 공지! 사이트 리뉴얼 후 카드 결제가 승인 되었지만 승인 처리 통보가 지연되는 현상이 있습니다. 7월29일 일요일 저녁시간 복구 예정입니다. 결제하기 버튼을 눌렀는데 15초 이상 반응이 없으면, 사이트 상단 메뉴인 마이페이지(MY PAGE) 로 가시면 카드 결제가 승인되었는지 확인하실수 있습니다. </font> </p>--></td></tr>
</table>

  <p style='padding:20px 0 30px 0;text-align:center;'><input type='image' src='<?=$g4[shop_img_path]?>/btn_settle.gif' border=0>
</form>

<script type="Text/JavaScript">
<!--
// 카드결제
function card_post(f)
{

	if(confirm("카드결제는 보안을 위해 진행과정이 사용자에게 보여지지 않습니다. 결제하시겠습니까?"))
	{
		/*
		if(f.x_card_brand.checked)
		{
			var url  = "<?="settle_{$default['de_card_pg']}_amex_result.php";?>";
			f.action = "<?="settle_{$default['de_card_pg']}_amex_result.php";?>";
		}
		else
		{
			*/
			var url  = "<?="settle_{$default['de_card_pg']}_result.php";?>";
			f.action = "<?="settle_{$default['de_card_pg']}_result.php";?>";
		//}
		if(f.pay_ing.value == 1){
			alert('결제 진행중 입니다.');
			return false;
		}
		f.pay_ing.value = 1;

		window.open(url, "_authorize_", "");
		f.target = "_authorize_";
		//window.open(url, "temp", "width=600,height=500,scrollbars=1,status=1,top=100,left=100");
		//f.target = "temp";




		f.submit();
		return false;
	}
	else
		return false;
}

<?php
if( $_MASTER_CARD_EVENT ){ // 마스터 카드 프로모션 기간 한정 2014-10-16 홍민기
?>
// 마스터 카드 체크
function master_card_chk(num){
	num = String(num);
	var chk_num = num.substr(0,2);
	chk_num = Number(chk_num);

	if(chk_num >= 51 && chk_num <=55){
		return true;
	}else{
		return false;
	}
}

// KB VCN 체크
function kb_vcn_chk(num){
	num = String(num);
	var chk_num = num.substr(0,6);
	// vcn bin값
	var vcn_arr = new Array(
		'517633','538798','520982','510194'
	);

	var result = false;

	var cnt = vcn_arr.length;
	for(var i=0; i<cnt; i++){
		if(chk_num == vcn_arr[i]){
			result = true;
			break;
		}
	}

	return result;
}

// 할인 혜택 체크
function master_card_pro(num){
	if(master_card_chk(num) == false){
		return false;
	}


	var send_cost = Number('<?php echo $send_cost;?>'); // 배송비
	var tot_amount = Number('<?php echo $od[od_temp_card];?>'); // 총 카드결제 금액
	var result_tot_amount = Number('<?php echo $result_tot_amount;?>'); // 제외상품을 제외한 카드결제 금액

	var result_amount = tot_amount;


	if(tot_amount < 50000){
		return false;
	}

	if(tot_amount >= 50000){ // 5만원 이상 결제시 무료배송
		result_amount -= send_cost;
	}

	if(tot_amount >= 100000){ // 10만원 이상 결제시 10% 할인
		result_amount -= (result_tot_amount-send_cost) * 0.1;
	}

	var x_amount = usd_convert(result_amount);
	return Array(Math.ceil(result_amount),x_amount);
}

function usd_convert(amount){ // 달러로 변환
	var usd_price = Number('<?php echo $default['de_conv_pay'];?>');

	return Math.ceil(amount / usd_price * 100)/100;
}


function master_card_price_view(num){
	var amount_arr = master_card_pro(num);
	var kb_vcn_check = kb_vcn_chk(num);
	if(kb_vcn_check == false){
		if(amount_arr == false || typeof(amount_arr) != 'object'){
			sales_comment_reset();
			return false;
		}
	}

	var dc_amount = Number(amount_arr[0]); // 할인된 금액
	var dc_amount_usd = (Number(amount_arr[1])).toFixed(2); // 할인된 금액(달러)

	var tot_amount = Number('<?php echo $od[od_temp_card];?>'); // 원래 총 결제액
	var result_tot_amount = Number('<?php echo $result_tot_amount;?>'); // 제외상품을 제외한 카드결제 금액
	var x_amount = Number(usd_convert(tot_amount)); // 원래 총 결제액(달러)

	var sale_amount = tot_amount - dc_amount; // 할인금액
	var sale_amount_usd = (x_amount - dc_amount_usd).toFixed(2); // 할인금액(달러)

	var send_cost = Number('<?php echo $send_cost;?>'); // 배송비



	var kb_vcn_point = 0; // kb vcn 적립 포인트
	var kb_vcn_comment = '';

	var master_card_comment = '';

	if(kb_vcn_check == true){

		if(amount_arr != false){
			kb_vcn_point = Math.round(amount_arr[0] * 0.05);
		}else{
			kb_vcn_point = Math.round(result_tot_amount * 0.05);
		}
	}

	if(sale_amount > 0){
		master_card_comment =
			'Master Card 프로모션 이벤트! Master Card로 5만원 이상 결제시 배송비 무료! <br/>'+
			'Master Card로 10만원 이상 결제시 카드결제금액의 10% 할인(일부상품 제외) <br/>'+
			'<br/>';
		master_card_comment +=
			'할인금액 : 무료배송(￦'+ number_format(String(send_cost)) + ')';
		if(sale_amount > send_cost){
			master_card_comment += ' + ￦' + number_format(String(sale_amount - send_cost)) + ' = ￦' + number_format(String(sale_amount));
		}
		master_card_comment +=  "&nbsp;<span style='font-size:9pt; font-family:tahoma; color:#6666FF;'>($"+ sale_amount_usd +")</span>" + '<br/>' +
			'결제액 : ￦' + number_format(String(dc_amount)) + "&nbsp;<span style='font-size:9pt; font-family:tahoma; color:#6666FF;'>($"+dc_amount_usd+")</span>";
	}

	if(kb_vcn_point > 0){
		kb_vcn_comment = '<br/>'+
			"KB-MASTER CARD 해외온라인 안전결제 프로모션 이벤트! <br/>"+
			"KB-MASTER CARD 해외온라인 안전결제(VCN)으로 주문 후 수령확인시 결제금액(카드)의 5%를 포인트로 적립해 드립니다.(1인 1회 한정)" +
			"적립예정 포인트 : " + number_format(String(kb_vcn_point));
		;
	}


	if(kb_vcn_comment != '' || master_card_comment != ''){

		$('.card_dc_comment').show();
		$('.card_dc_comment_contents').html(
			"<span class='amount'>"+
				master_card_comment +
				kb_vcn_comment +
			"</span>"
		);
	}else{
		sales_comment_reset();
//		return ;
	}
}

function sales_comment_reset(){
	$('.card_dc_comment').hide();
	$('.card_dc_comment_contents').empty();
}
<?php
}else{?>
function master_card_price_view(num){
	return false;
}

<?}?>
//-->
</script>