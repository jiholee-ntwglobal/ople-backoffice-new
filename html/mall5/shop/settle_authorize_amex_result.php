<?
include_once("./_common.php");
if(!$is_member)	alert_close("카드결제 보안에 의해 카드결제는 회원만 사용할 수 있습니다.");

if($_POST['x_amount'] == '' || $_POST['x_card_num'] == '' || $_POST['x_card_code'] == '' || !isset($_POST['x_cust_id']))
	alert_close("정상적인 접근이 아닙니다.");

if($default['de_card_pg'] != 'authorize')
	alert_close("PG 사가 Authorize.net 이 아닙니다.");
/*
if(trim($default['de_authorize_id']) == '' || trim($default['de_authorize_key']) == '')
	alert_close("API, tranjaction key 정보가 없습니다.");
*/

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

$post_array = array(
	"x_test_request"	=> $_POST['x_test_request'],
	"x_login"			=> "35105flex", //$default['de_authorize_id'],
	"x_tran_key"		=> "8Fcg6986q22zKuNS", //$default['de_authorize_key'],
	"x_type"			=> $_POST['x_type'],
	"x_version"			=> $_POST['x_version'],
	"x_delim_data"		=> $_POST['x_delim_data'],
	"x_delim_char"		=> $_POST['x_delim_char'],
	"x_relay_response"	=> $_POST['x_relay_response'],
	"x_method"			=> $_POST['x_method'],
	"x_cust_id"			=> $_POST['x_cust_id'],
	"x_amount"			=> $_POST['x_amount'],
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

$data = array();
reset($post_array);
while(list($key, $val) = each($post_array))
	$data[] = $key."=".urlencode($val);

$data = implode("&", $data);
$url = "https://secure.authorize.net/gateway/transact.dll";
$conn = curl_init();
curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, 1); // SSL 사용/지원시
curl_setopt($conn, CURLOPT_URL, $url);
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
	$sql = " update $g4[yc4_order_table]
		set od_receipt_card = '$u_amount',
			od_card_time = '{$g4['time_ymdhis']}',
			od_receipt_card_usd = '$response_code[9]' /* USD 금액 저장 */
		where od_id = '$response_code[12]'
		and on_uid = '$on_uid' ";
	sql_query($sql);

    $sql = "insert $g4[yc4_card_history_table]
		set od_id = '$response_code[12]',
			on_uid = '$on_uid',
			cd_mall_id = '35105flex', /*'{$default['de_authorize_id']}',*/
			cd_amount = '$u_amount',
			cd_amount_usd = '$response_code[9]', /* USD 금액 저장 */
			cd_app_no = '$response_code[4]',
			cd_app_rt = '$response_code[0]',
			cd_trade_ymd = left('{$g4['time_ymdhis']}',10),
			cd_trade_hms = right('{$g4['time_ymdhis']}',8),
			cd_opt01 = '{$member['mb_name']}',
			cd_time = '{$g4['time_ymdhis']}',
			cd_ip = '$_SERVER[REMOTE_ADDR]' ";
    sql_query($sql);

	sql_query("update {$g4['yc4_cart_table']} set ct_status='준비' where on_uid='$on_uid' ");

	alert_parent("카드결제가 정상적으로 승인되었습니다.", "./settleresult.php?on_uid=$on_uid");
}
else
	alert_close("카드결제 실패\\n\\n실패메세지를 확인하시고 다시 시도하거나 \'주문상세조회페이지\'를 통해 다시 카드결제를 하실 수 있습니다.\\n\\n실패메세지 : ".stripslashes($response_code[3]));
?>