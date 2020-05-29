<?php
/*
----------------------------------------------------------------------
file name	 : pay_update.php
comment		 : 환율 로드 ( 다섯시간 전의 환율을 로드 )
				1. 다섯시간 전의 환율을 로드
				2. 히스토리 저장
				3. 현재 저장된 환율과 비교 후 5% 편차가 5% 이상이면 적용 안함 -> 담당자에게 알림 메일 발송
				4. 사이트 기준 환율 업데이트
date		 : 2015-01-26
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

include "db.config.php";
$ople_link = mysql_connect($ople_db['host'], $ople_db['id'], $ople_db['pw']);

$db_selected1 = mysql_select_db('okflex5');




$json = file_get_contents("http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.xchange%20where%20pair%20in%20(%22USDKRW%22)&format=json&env=store://datatables.org/alltableswithkeys&callback");

$data = json_decode($json);




# 현재 시간 로드 #
$date = new DateTime($data->query->created, new DateTimeZone('UTC'));
$create_dt = date("Y-m-d H:i:s", $date->getTimestamp()); // 현재시간



# 환율 적용 시간로드 #
$pay_date = $data->query->results->rate->Date .' '.$data->query->results->rate->Time;

$date2 = new DateTime($pay_date, new DateTimeZone('UTC'));

$pay_dt = date("Y-m-d H:i:s", $date2->getTimestamp()); // 환율 적용 시간


$new_pay = number_format(round($data->query->results->rate->Rate,2),2,'.','');
$sql = "
	insert into
		yc4_pay_history
	(
		create_dt,pay_dt,pay
	)
	values(
		'".$create_dt."','".$pay_dt."','".$new_pay."'
	)
";
if(mysql_query($sql)){
	$uid = mysql_insert_id();
	# 현재 환율과 비교 #
	$now_pay_sql = mysql_query("select de_conv_pay from yc4_default");
	$now_pay = mysql_result($now_pay_sql,0,0);
	$per = $new_pay / $now_pay * 100 - 100;
	if($per < 0){
		$per *= -1;
	}

	if($per >= 5){
		# 이전 환율과 5% 이상 차이가 난다면 알림 #
		echo "이전 환율과 변동폭이 5% 이상입니다. 환율이 변경되지 않았습니다.";
	}else{
		# 이전 환율과 5% 미만의 차이라면 UPDATE #
		echo '환율 변동 폭 : '. $per .'%'. PHP_EOL;
		$update_sql = "
			update yc4_default_test set de_conv_pay = '".$new_pay."'
		";
		if(mysql_query($update_sql)){
			$sql = "update yc4_pay_history set update_dt = '".$create_dt."' where uid = '".$uid."'";
			if(mysql_query($sql)){
				echo "기준환율 ".$new_pay."로 변경";
			}else{
				echo "환율은 변경되었지만 오류가 발생하였습니다. 관리자에게 문의하세요.";
			}

		}else{
			echo "환율 변경 실패 ! 관리자에게 문의하세요";
		}
	}
}else{
	echo "환율이 변경되지 않았습니다. 관리자에게 문의하세요.";
}




