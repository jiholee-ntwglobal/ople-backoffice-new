<?php
include_once("./_common.php");


//if(!$is_member)	alert_close("카드결제 보안에 의해 카드결제는 회원만 사용할 수 있습니다.");

if($_POST['x_amount'] == '' || $_POST['x_card_num'] == '' || $_POST['x_card_code'] == '' || !isset($_POST['x_cust_id']))
	alert_close("정상적인 접근이 아닙니다.");

if($default['de_card_pg'] != 'authorize')
	alert_close("PG 사가 Authorize.net 이 아닙니다.");
if(trim($default['de_authorize_id']) == '' || trim($default['de_authorize_key']) == '')
	alert_close("API, tranjaction key 정보가 없습니다.");

// 부모윈도우
function alert_parent($msg, $href)
{
	global $g4;
	echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=$g4[charset]\">";
	echo"
		<script type='text/javascript'>
		   alert(\"$msg\");
	       parent.location.replace('$href');
		   window.close();
		</script>
		";
	exit;
}


if($_MASTER_CARD_EVENT){

	$mk_chk = master_card_chk($_POST['x_card_num']);
	if($mk_chk && $member['mb_level'] == 3 && $_POST['u_amount'] > 80000 && $_POST['u_amount'] <= 99999){ // 8~9 만원 구간은 골드회원 할인폭이 크기 때문에 마스터카드 적용 안함
		$mc_off = true;
		$result_amount = $_POST['u_amount'];
		$result_usd = $_POST['x_amount'];
	}
	if($mk_chk && $member['mb_level'] == 3 && $_POST['u_amount'] >= 50000 && !$mc_off && false){ // 골드회원이면서 오만원 이상일 경우에는 골드회원 혜텍 빼고 마스터 카드 이벤트 적용

		# 주문내역 로드 #
		$od_list = sql_query("
			select
				a.it_id,
				a.ct_qty,
				a.ct_amount,
				b.it_amount,
				b.it_amount2,
				b.it_amount3,
				b.it_tel_inq
			from
				".$g4['yc4_cart_table']." a,
				".$g4['yc4_item_table']." b
			where
				a.it_id = b.it_id
				and
				on_uid = '".$_POST['on_uid']."'");


		$off_arr = explode("|", $default['de_mb_level_off']);
		if(is_array($off_arr)){
			foreach($off_arr as $val){
				$lv_chk = explode('=>',$val);
				if($lv_chk[0] == $member['mb_level']){
					$dc_amount_per = $lv_chk[1]; // 회원 할인율
				}
			}
		}

		$no_dc_item = array('1306524520','1251860612','1222827644','1222682189','1210012129','1210591619'); // 할인 제외상품
		while($row = sql_fetch_array($od_list)){
			if(!in_array($row['it_id'],$no_dc_item) && get_amount($row) == $row['ct_amount']){
				$no_dc_amount += $row['it_amount'] * $row['ct_qty'];
			}else{
				$no_dc_amount += $row['ct_amount'] * $row['ct_qty'];
			}

		}

		// 배송비
		$send_cost = sql_fetch("select od_send_cost,od_temp_point from ".$g4['yc4_order_table']." where od_id = '".$_POST['od_id']."'");
		$tmp_point = $send_cost['od_temp_point'];

		$send_cost = $send_cost['od_send_cost'];

		// 결제금액 재설정

		$_POST['u_amount'] =  $no_dc_amount + $send_cost - $tmp_point;
		$_POST['x_amount'] = usd_convert($_POST['u_amount']);
		$u_amount_item = $_POST['u_amount'];

	}




	if(!$mc_off){
		# 결제 실패시 다시 결제를 할 수 있기 때문에 해당 주문서의 마스터카드 히스토리를 삭제한다
		sql_query("delete from master_card_event where od_id = '".$_POST['od_id']."'");
		# 마스터카드 프로모션 할인 적용 #
		$result = master_card_pro($_POST['x_card_num'],$_POST['od_id'],$u_amount_item);

	}
	if($result[1]){
		$event_uid = $result[1];
		$amount2 = $result[0];
	}else{
		$amount2 = $_POST['u_amount'];
	}




	# kb_vcn 적립 포인트 저장 #
	$kb_result = master_card_pro_kb_vcn($_POST['x_card_num'],$_POST['od_id'],$amount2,$result[1]);
	if($kb_result){
		$event_uid = $kb_result;
	}
	



	if($result){
		$result_amount = $result[0]; // 결제금액(원)
		$result_usd = usd_convert($result[0]); // 결제금액(달러)
		$result_dc = $_POST['u_amount'] - $result[0];

	}else{
		
		$result_amount = $_POST['u_amount'];
		$result_usd = $_POST['x_amount'];
	}

    if($hana_event_fg){
        if(hanacard_chk($_POST['x_card_num'])){ // 하나카드 맞는지 검사
            if(hanacard_order_chk($member['mb_id'])){ // 하나카드 첫주문인지 검사
                hanacard_first_event($_POST['od_id']);

            }

            $hana_sendcost_free = hanacard_send_cost_free($_POST['od_id'],$result_amount); // 무료배송 처리 시작
            if($hana_sendcost_free != false){ // 배송비 할인금액 적용
                if($result_amount>0) {
                    $result_amount = $hana_sendcost_free['result_amount'];
                }
                if($result_usd > 0) {
                    $result_usd = $hana_sendcost_free['result_amount_usd'];
                }
            }
            $hanacard_apply_fg = true;
        }
    }


	if($result || $kb_result){
		$master_card_apply = true;
	}

}else{
	$result_amount = $_POST['u_amount'];
	$result_usd = $_POST['x_amount'];
	

}




/*
	마스터카드 프로모션 프로세스 흐름
	1. 마스터 카드인지 확인
	2. 제외상품을 제외한 합산금액에 해당하는 혜택 로드
	3. master_card_event 저장
	4. kb_vcn 카드 체크
	5. master_card_event insert or insert
	6. 최종 결제금액 할인된 금액으로 변경
	7. 결제 성공시 order 테이블 od_temp_card 업데이트
	8. master_card_event의 complate_fg = y 로 변경
	9. 완료

*/
//the maerchant login id or password is invalid or the account is inactive




$post_array = array(
	"x_test_request"	=> $_POST['x_test_request'],
	//"x_test_request"	=> true,
	"x_login"			=> $default['de_authorize_id'],
	"x_tran_key"		=> $default['de_authorize_key'],
	"x_type"			=> $_POST['x_type'],
	"x_version"			=> $_POST['x_version'],
	"x_delim_data"		=> $_POST['x_delim_data'],
	"x_delim_char"		=> $_POST['x_delim_char'],
	"x_relay_response"	=> $_POST['x_relay_response'],
	"x_method"			=> $_POST['x_method'],
	"x_cust_id"			=> $_POST['x_cust_id'],
	"x_amount"			=> $result_usd,
	"x_customer_ip"		=> $_POST['x_customer_ip'],
	"x_currency_code"	=> $_POST['x_currency_code'],
	"x_first_name"		=> $_POST['x_first_name'],
	"x_last_name"		=> $_POST['x_last_name'],
	"x_email"			=> $_POST['x_email'],
	"x_email_customer"	=> $_POST['x_email_customer'],
	"x_phone"			=> $_POST['x_phone'],
	"x_card_num"		=> $_POST['x_card_num'],
	"x_exp_date"		=> $_POST['x_exp_m'].$_POST['x_exp_y'],
	"x_card_code"		=> $_POST['x_card_code']
);




# 삼성카드일 경우 아이디위 트렌젝션 키 변경 2014-12-18 홍민기 #
if( $_POST['interest'] == '3' && $result_usd > 49 && samsung_card_chk($post_array['x_card_num']) ){
	$post_array['x_login'] = '35105flex';
	$post_array['x_tran_key'] = $default['de_authorize_id'] = '7j376D67BTecp8dP';
	$samsung_fg_sql = "cd_opt02 = '삼성카드 무이자할부',	";
	$samsung_fg = true;
	$od_shop_memo = "\n삼성카드 무이자 3개월 할부 신청 주문";
}

/*
# 서스펜드로 인한 임시 변경 #
$post_array['x_login'] = '35105flex';
$post_array['x_tran_key'] = $default['de_authorize_id'] = '7j376D67BTecp8dP';
*/
# 하나카드 무이자 할부 키 변경 #
if( $_POST['interest'] == '3'  && hanacard_chk($post_array['x_card_num']) ){
    $post_array['x_login'] = '35105flex';
    $post_array['x_tran_key'] = $default['de_authorize_id'] = '7j376D67BTecp8dP';
    $samsung_fg_sql = "cd_opt02 = '하나카드 무이자할부',	";
    $samsung_fg = true;
    $od_shop_memo = "\n하나카드 무이자 3개월 할부 신청 주문";
}



if($_SERVER['REMOTE_ADDR'] == '59.17.43.129'){
	//$post_array['x_test_request'] = true;

}





$data = array();
reset($post_array);
while(list($key, $val) = each($post_array))
	$data[] = $key."=".urlencode($val);

$data = implode("&", $data);
$aurl = "https://secure.authorize.net/gateway/transact.dll";
//$aurl = "https://test.authorize.net/gateway/transact.dll";
$conn = curl_init();
curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, 1); // SSL 사용/지원시
curl_setopt($conn, CURLOPT_URL, $aurl);
curl_setopt($conn, CURLOPT_POST, 1);
curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($conn, CURLOPT_POSTFIELDS, $data);
$res_str = curl_exec($conn);
curl_close($conn);
$response_code = explode("|", $res_str);

//print_r2($response_code); exit;

// 결제성공
if($response_code[0] == "1")
{
    /*
    if($_SERVER['REMOTE_ADDR'] == '59.17.43.129'){
        echo $result_usd;
        print_r2($response_code);
        exit;

    }
    */
	# 신한 글로벌 카드 체크 #
	if(shinhan_global_chk($_POST['x_card_num'])){
		shinhan_global_event($on_uid,$result_amount); // 적립 포인트 저장
	}

    # 신한 아이행복 카드 #
    shinhan_ihappy_pay_save($response_code[12],$result_amount,$response_code[9],$_POST['x_card_num']);

	$sql = " update $g4[yc4_order_table]
		set od_receipt_card = '$result_amount',
			od_card_time = '{$g4['time_ymdhis']}',
			od_receipt_card_usd = '$response_code[9]', /* USD 금액 저장 */
			od_shop_memo=concat(od_shop_memo, '\\n', 'authorize.net 신용카드 결제".$od_shop_memo."'),
			od_status_update_dt = '".$g4['time_ymdhis']."'
		where od_id = '$response_code[12]'
		and on_uid = '$on_uid' ";
	sql_query($sql);
	rand_event_gogo($_POST['x_cust_id']);

    $sql = "insert $g4[yc4_card_history_table]
		set od_id = '$response_code[12]',
			on_uid = '$on_uid',
			cd_mall_id = '{$default['de_authorize_id']}',
			cd_amount = '$result_amount',
			cd_amount_usd = '$response_code[9]', /* USD 금액 저장 */
			cd_app_no = '$response_code[4]',
			cd_app_rt = '$response_code[0]',
			cd_trade_ymd = left('{$g4['time_ymdhis']}',10),
			cd_trade_hms = right('{$g4['time_ymdhis']}',8),
			cd_opt01 = '{$member['mb_name']}',
			".$samsung_fg_sql."
			cd_time = '{$g4['time_ymdhis']}',
			cd_ip = '$_SERVER[REMOTE_ADDR]',
			cd_card_name = '".$response_code[51]."',
			cd_card_bin = '".substr($_POST['x_card_num'],0,6)."',
			cd_conv_pay = '".$default['de_conv_pay']."'
			";
    sql_query($sql);

	sql_query("update {$g4['yc4_cart_table']} set ct_status='준비',ct_status_update_dt = '".$g4['time_ymdhis']."' where on_uid='$on_uid' ");

	if($master_card_apply){

		sql_query("update master_card_event set complate_fg = 'y' where uid = '".$event_uid."'");
		sql_query("
		update $g4[yc4_order_table]
		set
			".($result_dc ? "od_dc_amount = '".$result_dc."',":"")."
			od_shop_memo=concat(od_shop_memo, '\\n', '마스터카드 프로모션 할인 적용')
		where od_id = '$response_code[12]'
		and on_uid = '$on_uid' ");
	}

	// 김선용 201207 : sms발송
	if ($default['de_sms_use3'])
	{
		$od = sql_fetch("select od_name, od_hp from {$g4['yc4_order_table']} where od_id='{$response_code[12]}' ");
		$receive_number = preg_replace("/[^0-9]/", "", $od['od_hp']); // 수신자번호
		$send_number = preg_replace("/[^0-9]/", "", $default['de_sms_hp']); // 발신자번호

		//$default['de_sms_cont3'];
		$sms_contents = "{이름}님 신용카드 결제확인. {입금액}원. 주문번호:{주문번호}. {회사명}";
		$sms_contents = preg_replace("/{이름}/", $od['od_name'], $sms_contents);
		$sms_contents = preg_replace("/{입금액}/", nf($result_amount), $sms_contents);
		$sms_contents = preg_replace("/{주문번호}/", $response_code[12], $sms_contents);
		$sms_contents = preg_replace("/{회사명}/", $default['de_admin_company_name'], $sms_contents);

		include_once($g4['full_path']."/lib/icode.sms.lib.php");
		$SMS = new SMS; // SMS 연결
		$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
		$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
		$SMS->Send();
	}

	// 김선용 201208 : 포인트차감 여기서 처리. 응답지연등으로 settleresult 로 이동하지 않는경우 포인트차감안됨
	// 포인트 결제를 했다면 실제 포인트 결제한 것으로 수정합니다.
	$od = sql_fetch("select mb_id, od_receipt_point, od_temp_point from $g4[yc4_order_table] where od_id='{$response_code[12]}' and on_uid='$on_uid' ");
	if ($od[od_receipt_point] == '0' && $od[od_temp_point] != '0' && $od['mb_id'])
	{
		sql_query(" update $g4[yc4_order_table] set od_receipt_point = od_temp_point where od_id='{$response_code[12]}' and on_uid='$on_uid' ");
		insert_point($od[mb_id], (-1) * $od[od_temp_point], "주문번호:{$response_code[12]} 결제", "@order", $od[mb_id], "{$response_code[12]}");
	}

	rand_event_gogo($_POST['x_cust_id']);
	sales_report_cart_update($on_uid,null,$default['de_conv_pay']);
	clearance_sell_qty($on_uid);

	# 삼성카드 5% 적립 포인트 저장 처리 2015-01-02 홍민기 #
	// 1월 말일까지만 진행 2015-01-22 홍민기
	if( samsung_card_chk($_POST['x_card_num']) && date('Ym') < '201502'){
		$return_point = round($result_amount * 0.05);
		sql_query("
			delete from
				yc4_card_return_point
			where
					od_id  = '".$response_code[12]."'
				and on_uid = '".$on_uid."'
		");
		sql_query("
			insert into
				yc4_card_return_point
			(
				od_id,on_uid,
				mb_id,return_point,
				cp_dt,cp_fg
			)values(
				'".$response_code[12]."','".$on_uid."',
				'".$od['mb_id']."','".$return_point."',
				null,'N'
			)
		");
	}

	# 삼성카드 무이자 할부 신청시 카드번호 임시로 저장 삼성카드 요청사항!! 2015-01-07 홍민기 #
	if($samsung_fg){
		$sql = "
			insert
				yc4_card_history_samsung
			set
				od_id			= '".$response_code[12]."',
				on_uid			= '".$on_uid."',
				cd_mall_id		= '".$default['de_authorize_id']."',
				cd_amount		= '".$result_amount."',
				cd_amount_usd	= '".$response_code[9]."', /* USD 금액 저장 */
				cd_app_no		= '".$response_code[4]."',
				cd_app_rt		= '".$response_code[0]."',
				mb_id			= '".$member['mb_id']."',
				mb_name			= '".$member['mb_name']."',
				cd_time			= '".$g4['time_ymdhis']."',
				cd_ip			= '".$_SERVER['REMOTE_ADDR']."',
				cd_card_name	= '".$response_code[51]."',
				cd_card_no		= '".$_POST['x_card_num']."'
		";
		sql_query($sql);
	}



    $result_url = "./settleresult.php?on_uid=$on_uid";
    $result_msg = '';
    if($hanacard_apply_fg){
        hanacard_first_event($response_code[12]); // 하나카드 첫구매시 10% 적립 처리
        $hanacard_event_item = hanacard_event_item_chk($response_code[12]);
        if($hanacard_event_item != false){
            $result_msg = " 하나카드 이벤트 사은품 선택 페이지로 이동합니다.";
            $result_url = $g4['shop_path'].'/hanacard_event_select.php?od_id='.$response_code[12].'&on_uid='.$on_uid;
        }
    }



	/*
	echo "
		<script>
			parent.document.AIMform.pay_ing.value=0;
		</script>
	";
	*/

	alert_parent("카드결제가 정상적으로 승인되었습니다.".$result_msg, $result_url);
}
else{

	echo "
		<script>
			parent.document.AIMform.pay_ing.value=0;
		</script>
	";


	# 결제실패 히스토리 저장 2015-01-05 홍민기 #
	$fail_history_sql = "
		insert into
			yc4_card_fail
		(
			od_id,on_uid,amount_usd,mb_id,cd_time,error_msg,res_str
		)
		values(
			'".$response_code[12]."','".$on_uid."',
			'".$response_code[9]."','".$member['mb_id']."',
			'".$g4['time_ymdhis']."','".mysql_real_escape_string($response_code[3])."',
			'".mysql_real_escape_string($res_str)."'
		)
	";
	sql_query($fail_history_sql);
    if($hana_event_fg){
        if($hana_sendcost_free != false){ // 배송비 할인을 받았다면 초기화
            sql_query("
                update ".$g4['yc4_order_table']."
                set
                    od_dc_amount = od_dc_amount - od_send_cost,
                    od_shop_memo = concat(od_shop_memo,'\\n','하나카드 무료배송 대상자 배송비 할인 결제실패! 할인 취소(".number_format($dc_amount)."원)')
                where
                    od_id = '".$_POST['od_id']."'

            ");
            sql_query("
                delete from yc4_event_data where ev_code = 'hana' and ev_data_type = 'send_cost_free' and value1 = '".$_POST['od_id']."'
            ");
        }
    }




	alert_close("카드결제 실패\\n\\n실패메세지를 확인하시고 다시 시도하거나 \'주문상세조회페이지\'를 통해 다시 카드결제를 하실 수 있습니다.\\n\\n실패메세지 : ".stripslashes($response_code[3]));
}
?>