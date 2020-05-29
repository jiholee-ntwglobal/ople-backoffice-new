<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-05-11
 * File: lms_request.php
 */

include "db.config.php";

$ople_link	= mysqli_connect($ople_db['host'], $ople_db['id'], $ople_db['pw'], 'okflex5');
$sms_link	= mysqli_connect($sms_db['host'], $sms_db['id'], $sms_db['pw'], $sms_db['dbname']);

$insert_sql = "
	INSERT INTO LGUPLUS.MMS_MSG
	(REQDATE, PHONE, CALLBACK, SUBJECT ,MSG, ETC1, ETC2, ETC3) VALUES
";

//var_dump(mysqli_query($ople_link, "select uid from yc4_lms order by uid desc limit 10"));
//var_dump(mysqli_query($sms_link, "select count(*) AS cnt from LGUPLUS.MMS_MSG"));
//exit;

$insert_val_arr = array();

$sql	= mysqli_query($ople_link, "SELECT * FROM yc4_lms WHERE req_fg = 'N'");

$uid_arr	= array();
$i			= 0;
$no			= 0;
while($row = mysqli_fetch_assoc($sql)){

    //R로 바꾸는 로직 ( 2019. 09. 18 ) - 강소진, 우선 주석처리.
    // ETC3, 폰번호로 MMS_MSG, MMS_LOG_날짜 테이블 조회해서 중복체크 하려 했지만 20분이 넘어도 다 안 돌아가서 해당로직으로 변경함
    mysqli_query($ople_link, "UPDATE yc4_lms SET req_fg = 'R' where uid = '".$row['uid']."'");
	
	$uid_arr[]		= $row['uid'];
	$row['CALLBACK']= preg_replace("/[^0-9]/","",$row['CALLBACK']);
	
	$hp_arr = array();
	switch($row['send_code']){
		case 'goodday' :
			$sql2	= mysqli_query($ople_link, "SELECT DISTINCT hp_no FROM yc4_oneday_sms");
			break;
		case 'all_member' :
			$sql2	= mysqli_query($ople_link, "SELECT DISTINCT mb_hp AS hp_no FROM g4_member WHERE mb_sms = 1 AND mb_leave_date = ''");
			break;
	}
	
	if($sql2){
		while($hp = mysqli_fetch_assoc($sql2)){
			$hp['hp_no'] = preg_replace("/[^0-9]/",'',$hp['hp_no']);
			if($hp['hp_no']){
				$hp_arr[] = $hp['hp_no'];
			}
		}
	}
	
	if($row['send_addtion_no']){ // 추가 전송 번호를 배열에 병합
		$send_addtion_no = json_decode($row['send_addtion_no']);
		$hp_arr = array_merge($hp_arr,$send_addtion_no);
	}
	
	$hp_arr = array_unique($hp_arr); // 중복값 제거

	foreach($hp_arr as $val){
		$val = preg_replace("/[^0-9]/",'',$val);
		if($no%250 == 0){
			$i++;
			if(!isset($insert_val_arr[$i])){
				$insert_val_arr[$i] = '';
			}
		}

        $insert_val_arr[$i] .= ($insert_val_arr[$i] ? ", " : "") .
            "('" . $row['REQDATE'] . "','" . $val . "','" . $row['CALLBACK'] . "','" . mysqli_real_escape_string($sms_link, $row['SUBJECT']) . "','" . mysqli_real_escape_string($sms_link, $row['MSG']) . "','ople','" . $row['send_code'] . "','" . $row['uid'] . "')" . PHP_EOL;
        $no++;

	}
}

foreach($insert_val_arr as $val){
	if($val){
		mysqli_query($sms_link,$insert_sql.$val);
		echo mysqli_error($sms_link);
//        echo $insert_sql.$val;
	}
}

foreach($uid_arr as $uid){
	mysqli_query($ople_link, "UPDATE yc4_lms SET req_fg = 'Y',req_dt = now() WHERE uid = '".$uid."'");
}

include "/ssd/html/history_api.php";

$history_api = new scheduler\History_api();
$history_api->getHistoryID(40);

$rest = $history_api->sendHistoryID();

