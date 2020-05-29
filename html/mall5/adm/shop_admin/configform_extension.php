<?php
$sub_menu = "100600";
include_once("_common.php");
auth_check($auth[$sub_menu], "r");

$g4['title'] = "확장설정";
include_once ("$g4[admin_path]/admin.head.php");


function get_icode_info($host, $get_url)
{
	if($host == '' || $get_url == '') return null;
	$fsock = fsockopen($host, 80, $errno, $errstr, 20);
	if(!$fsock)
		return null;
	else
	{
        fputs($fsock, "GET $get_url HTTP/1.0\r\n");
        fputs($fsock, "Host: $host\r\n");
        fputs($fsock, "\r\n");
        while (trim($buffer = fgets($fsock,1024)) != "")
            $header .= $buffer;
        while (!feof($fsock))
            $buffer .= fgets($fsock,1024);
    }
    fclose($fsock);
    return $buffer;
}
?>
<?=subtitle($g4['title'])?>

<script type="text/JavaScript">
function byte_check(el_cont, el_byte)
{
	var cont = document.getElementById(el_cont);
	var bytes = document.getElementById(el_byte);
	var i = 0;
	var cnt = 0;
	var exceed = 0;
	var ch = '';

	for (i=0; i<cont.value.length; i++) {
		ch = cont.value.charAt(i);
		if (escape(ch).length > 4) {
			cnt += 2;
		} else {
			cnt += 1;
		}
	}

	//byte.value = cnt + ' / 80 bytes';
	bytes.innerHTML = cnt + ' / 80 bytes';

	if (cnt > 80) {
		exceed = cnt - 80;
		alert('메시지 내용은 80바이트를 넘을수 없습니다.\r\n작성하신 메세지 내용은 '+ exceed +'byte가 초과되었습니다.\r\n초과된 부분은 자동으로 삭제됩니다.');
		var tcnt = 0;
		var xcnt = 0;
		var tmp = cont.value;
		for (i=0; i<tmp.length; i++) {
			ch = tmp.charAt(i);
			if (escape(ch).length > 4) {
				tcnt += 2;
			} else {
				tcnt += 1;
			}

			if (tcnt > 80) {
				tmp = tmp.substring(0,i);
				break;
			} else {
				xcnt = tcnt;
			}
		}
		cont.value = tmp;
		//byte.value = xcnt + ' / 80 bytes';
		bytes.innerHTML = xcnt + ' / 80 bytes';
		return;
	}
}
</script>

<style type="text/css">
.span_left { padding-left:3px; }
.span_right { }
.fieldset_div { margin:5px; }
</style>

<script type="Text/JavaScript">
function fconfig_check(f)
{
	return true;
}
</script>



<form name=fconfig method=post action='configform_extensionupdate.php' onsubmit="return fconfig_check(this)" enctype="multipart/form-data" style="margin:0px;">

<table border=2 cellspacing=0 cellpadding=2 align="center" bordercolor='#95A3AC'  class="state_table">
<tr>
	<td class=yalign_head width=130>SMS 정보<br/>(아이코드)</td>
	<td style="padding-left:10px;">
		<?
		if($default['de_icode_id'] && $default['de_icode_pw']) $icode_arr = get_icode_info("www.icodekorea.com", "/res/userinfo.php?userid={$default['de_icode_id']}&userpw={$default['de_icode_pw']}");
		if($icode_arr !== null)
		{
			$icode_arr = explode(';', $icode_arr);
			$icode_info = array(
				'code'      => $icode_arr[0], // 결과코드 : 0 인경우 성공
				'coin'      => $icode_arr[1], // 고객 잔액 (충전제만 해당)
				'gpay'      => $icode_arr[2], // 건당 요금
				'payment'   => $icode_arr[3]  // 요금제 표시, A:충전제, C:정액제
			);
		}
		?>
		<fieldset style="margin:5px 10px 5px 0;">
			<legend>icodekorea.com 가입/사용 정보(실시간으로 받아오는 정보입니다.)</legend>
			<div class=fieldset_div>
				<?if($icode_info['code'] == '0'){?>
				<span class=span_left>
					<strong>사용중인요금제</strong> : <?=($icode_info['payment'] == 'A' ? '충전제' : '정액제');?>,&nbsp;&nbsp;
					<strong>건당요금</strong> : <?=nf($icode_info['gpay']);?>원,&nbsp;&nbsp;
					<strong>현재잔액</strong> : <?=nf($icode_info['coin']);?>원&nbsp;&nbsp;
					<input type=button class=btn1 value='충전하기' onclick="window.open('http://icodekorea.com/company/credit_card_input.php?icode_id=<?=$default['de_icode_id']?>&icode_passwd=<?=$default['de_icode_pw']?>','icode_payment','width=800,height=600,scrollbars=1')">
				</span>
				<?}?>
			</div>
		</fieldset>

	</td>
</tr>

<tr>
	<td class=yalign_head>복수배송설정</td>
	<td style="padding-left:10px;">
		<fieldset style="margin:5px 10px 5px 0;">
			<legend>배송정책설정</legend>
			<div class=fieldset_div>

				복수배송적용 회원등급은 <?=get_member_level_select("de_order_ship_multi_level", 1, 10, $default['de_order_ship_multi_level'])?> 이상(포함) 적용합니다. (테스트등을 할 때 등급을 일정이상 설정)<br/>

				<!--상품합계가 <input type="text" name="de_order_ship_multi_cost_amount" id="de_order_ship_multi_cost_amount" size=6 class="ed" value="<?=$default['de_order_ship_multi_cost_amount']?>"> 원 <b>미만</b>인 경우 기본배송수는 <input type="text" name="de_order_ship_multi_default" id="de_order_ship_multi_default" size=4 class="ed" value="<?=$default['de_order_ship_multi_default']?>"> 개 까지 무료이고<br/>

				추가시 1개당 <input type="text" name="de_order_ship_multi_cost_amount_add" id="de_order_ship_multi_cost_amount_add" size=6 class="ed" value="<?=$default['de_order_ship_multi_cost_amount_add']?>"> 원의 추가요금을 적용합니다.<br/>
				-->

				기본배송수는 <input type="text" name="de_order_ship_multi_default" id="de_order_ship_multi_default" size=4 class="ed" value="<?=$default['de_order_ship_multi_default']?>"> 개 까지 무료<br/>

				상품합계가 <input type="text" name="de_order_ship_multi_free_amount" id="de_order_ship_multi_free_amount" size=6 class="ed" value="<?=$default['de_order_ship_multi_free_amount']?>"> 원 이상인 경우는 배송지수에 관계없이 모두 무료로 적용합니다.<br/>
				<span style="color:blue;">※ 무료배송 적용금액은 '쇼핑몰설정'의 최대 유료배송비 설정금액을 입력하십시오.</span><br/>

				<b>주1) 추가시 1개당 추가되는 배송비는 '쇼핑몰설정'의 배송비설정대로 해당 금액구간에 대한 배송비가 추가됩니다.<br/>주2) 기본배송수는 '쇼핑몰설정'의 배송비설정에서 지정한 기본 배송비에 해당하는 배송지 갯수입니다.</b>
			</div>
		</fieldset>

	</td>
</tr>


<tr>
	<td class=yalign_head width=130>추천인정책</td>
	<td style="padding-left:10px;">
		<fieldset style="margin:5px 10px 5px 0;">
			<legend>피추천인(추천받은회원) 적립포인트</legend>
			<div class=fieldset_div>
				<span class=span_left><input type="text" name="de_recom_point" id="de_recom_point" size=10 class="ed" value="<?=$default['de_recom_point']?>" />%&nbsp;&nbsp;&nbsp; ※ 소수2자리<br/>
				주1) 첫번째 기준입니다. 아래 할인분류를 설정해도 여기에 설정값이 없으면 미사용으로 간주합니다.<br/>
				주2) 적립률은 아래 설정분류에 대한 상품개별의 상품합에서 적립률(%)만큼 적립됩니다.<br/>
				주3) 상품개별 주문상태가 '완료'인 경우만 해당합니다.(그외 처리안함) &nbsp;<?=help("회원가입시 가입회원이 추천한 회원에 대해 가입회원이 상품구매완료시에 여기서 설정한 적립률만큼 적립금(포인트)을 지급하는 기능입니다.<br/>단, 구매완료후에 취소/반품등으로 변경된 경우(완료이외의 상태)는 다시 회수처리 됩니다.");?></span>
			</div>
		</fieldset>

		<fieldset style="margin:5px 10px 5px 0;">
			<legend>추천인 구매시 할인상품분류, 할인금액</legend>
			<div class=fieldset_div>
				<span class=span_left>
					할인분류 : <select name="de_recom_off_ca_id" id="de_recom_off_ca_id">
						<option value="" selected>전체분류</option>
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
					</select><br/>※ 예) 60 선택시 60xxxx 포함. 전체분류가 설정되면 모든 상품에 대해 할인적용.
					<script type="text/javascript">
					if("<?=$default['de_recom_off_ca_id']?>" != '')
						document.getElementById ('de_recom_off_ca_id').value = "<?=$default['de_recom_off_ca_id']?>";
					</script>
					<br/>
					할인금액 : <input type="text" name="de_recom_off_amount" id="de_recom_off_amount" size=10 class="ed" value="<?=$default['de_recom_off_amount']?>"> ※ 정수만입력 &nbsp;<?=help("위의 '피추천인 적립포인트'를 사용하는 경우에 한해 여기서 설정된 내용으로 추천인이 상품구매시 할인이 적용됩니다.");?><br/>
					주1) 할인금액은 위 분류설정에 해당하는 상품이 있는경우 설정한 할인금액이 할인됩니다.(분류에 해당하는 개별상품들의 합계는 무관합니다)<br/>
					주2) 할인금액이 없는 경우, 할인은 적용하지 않습니다. (단, 피추천인 적립률이 있다면 적립만 처리합니다)<br/>
					주3) 피추천인 적립포인트와 별개로 적용됩니다.(할인분류와 할인금액이 있는경우)
				</span>
			</div>
		</fieldset>
	</td>
</tr>

<tr>
	<td class=yalign_head width=130>베스트 사용후기</td>
	<td style="padding-left:10px;">
		<fieldset style="margin:5px 10px 5px 0;">
			<legend>지급포인트</legend>
			<div class=fieldset_div>
				<span class=span_left><input type="text" name="de_it_use_best_postpoint" id="de_it_use_best_postpoint" size=10 class="ed" value="<?=$default['de_it_use_best_postpoint']?>"> ※ 숫자</span>
			</div>
		</fieldset>

	</td>
</tr>
<tr>
	<td class=yalign_head width=130>첫 사용후기<br/>(구매후작성)</td>
	<td style="padding-left:10px;">
		<fieldset style="margin:5px 10px 5px 0;">
			<legend>지급포인트</legend>
			<div class=fieldset_div>
				<span class=span_left><input type="text" name="de_it_use_first_postpoint" id="de_it_use_first_postpoint" size=10 class="ed" value="<?=$default['de_it_use_first_postpoint']?>"> ※ 숫자</span>
			</div>
		</fieldset>
	</td>
</tr>
<tr>
	<td class=yalign_head width=130>회원등급별 설정</td>
	<td style="padding-left:10px;">
		<fieldset style="margin:5px 10px 5px 0;">
			<legend>설정</legend>
			<div class=fieldset_div>
				<span>
				<?
				$off_arr = explode("|", $default['de_mb_level_off']);
				$post_arr = explode("|", $default['de_mb_level_free_post']);
				for($k=3; $k<5; $k++){
					if($mb_level_str[$k] != ''){
				?>
					<div style="display:inline; width:70px;"><?=$mb_level_str[$k]?> [<?=$k?>]</div>
					<div style="display:inline; width:250px;">상품가격 할인 <input type="text" name="de_mb_level_off[<?=$k?>]" id="de_mb_level_off[<?=$k?>]" size=4 class="ed" value="<?=array_pop(explode('=>', $off_arr[($k-3)]))?>"> % (정수, 숫자만)</div>
					<div style="display:inline;"><input type="checkbox" name="de_mb_level_free_post[<?=$k?>]" id="de_mb_level_free_post[<?=$k?>]" value="1" <?if(array_pop(explode('=>', $post_arr[($k-3)]))) echo 'checked';?>><label for="de_mb_level_free_post[<?=$k?>]">무료배송</label></div><br/>
				<?}}?>
				</span>
			</div>
		</fieldset>
	</td>
</tr>
<tr>
	<td class=yalign_head>상품입고<br>SMS통보 설정</td>
	<td style="padding-left:10px;">
		<fieldset style="margin:5px 10px 5px 0;">
			<legend>자동발송 사용</legend>
			<div class=fieldset_div>
				<input type="checkbox" name="de_item_sms_auto_use" id="de_item_sms_auto_use" value=1 <?if($default['de_item_sms_auto_use']) echo 'checked';?>><label for="de_item_sms_auto_use">사용</label>&nbsp;<?=help("자동발송 사용시 상품재고가 0 에서 1 이상으로 상품정보를 수정시, 자동으로 해당상품의 SMS 입고통보 신청자들에게 SMS를 발송합니다.<br/>※ 수동발송은 '상품입고 SMS관리' 를 이용하십시오.");?>&nbsp;&nbsp;<span style="color:red;">※ 한국시간 09~21시 사이만 재고변경시 자동발송으로 처리합니다.</span>
			</div>
		</fieldset>

		<fieldset style="margin:5px 10px 5px 0;">
			<legend>메시지 설정</legend>
			<div class=fieldset_div>
				<textarea cols=80 rows=3 id='de_item_sms_msg' name='de_item_sms_msg' ONKEYUP="byte_check('de_item_sms_msg', 'msg_check1');" class=textarea><?=$default['de_item_sms_msg']?></textarea>&nbsp;&nbsp;<span id='msg_check1'>0 / 80 바이트</span>
				<div style="padding-top:3px;">※ 발송시 메시지 기본내용입니다. {이름},{상품명} 을 입력하면 해당 내용으로 치환하여 발송합니다.
				<p style="margin-top:0; color:red;">※ 단, 최대 80바이트가 넘으면 문자전송이 안되므로 상품명은 한글10자 정도에서 잘라서 치환됩니다.(한글1자는 2바이트)
				<div style="padding-top:3px;"><u><b>입력 예)</b></u> {이름}님 알림신청 품절상품({상품명})이 입고되었습니다. [회사명]</div>

				<script type="text/JavaScript">
				byte_check('de_item_sms_msg', 'msg_check1');
				</script>
			</div>
		</fieldset>
	</td>
</tr>

<tr>
	<td class=yalign_head>상품이미지<br>CDN 설정</td>
	<td style="padding-left:10px;">
		<fieldset style="margin:5px 10px 5px 0;">
			<legend>상품 이미지 CDN 사용</legend>
			<div class=fieldset_div>
				<input type="checkbox" name="de_cdn" id="de_cdn" value=1 <?if($default['de_cdn']) echo 'checked';?>><label for="de_cdn">사용</label>&nbsp;<?=help("CDN 사용시 이미지 서버에서 상품 이미지를 로드합니다. (없을 시 오플 서버에서 로드)");?>&nbsp;&nbsp;<span style="color:red;">※ CDN 사용시 이미지 서버에서 상품 이미지를 로드합니다.<br/>(없을 시 오플 서버에서 로드)</span>
			</div>
		</fieldset>

	</td>
</tr>
<tr>
	<td class="yalign_head">아이허브 판매가 대비 할인 판매금액(%)</td>
	<td style="padding-left:10px;">
		<fieldset style="margin:5px 10px 5px 0">
			<legend>아이허브 판매가 대비 할인 판매금액(%)</legend>
			<div class="fieldset_div">
				<input type="text" name='de_iherb_amount_ratio' value="<?php echo $default['de_iherb_amount_ratio'];?>" />% 할인
			</div>
		</fieldset>
	</td>
</tr>
<tr>
	<td class="yalign_head">SRP(NTICS) 대비 할인 판매금액(%)</td>
	<td style="padding-left:10px;">
		<fieldset style="margin:5px 10px 5px 0">
			<legend>SRP(NTICS) 대비 할인 판매금액(%)</legend>
			<div class="fieldset_div">
				<input type="text" name='de_srp_amount_ratio' value="<?php echo $default['de_srp_amount_ratio'];?>" />% 할인
			</div>
		</fieldset>
	</td>
</tr>


</table>

<p align=center><input type=submit class=btn1 value='  확  인  '>
</form>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>