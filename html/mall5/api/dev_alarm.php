<?php
/**
 * 개발용 SMS 발송 프로세스 test2
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

$targetServer = isset($_GET['srv']) ? $_GET['srv'] : '';
$errorMessage = isset($_GET['msg']) ? $_GET['msg'] : '';
$referer = $_SERVER['REMOTE_ADDR'];

if(in_array($referer, getAccessip())) {

    if($targetServer != '' && $errorMessage != '') {
        $subject = "";
//        $message = "Warning!!!!!!! ".$targetServer . " Server: " . $errorMessage . " Error";
        $message = $targetServer . " : " . $errorMessage;

        $now = date("Y-m-d H:i:s");
        $sendDate = date("Y-m-d H:i:s", strtotime("+1 seconds")); // db 컨넥션 문제로 인해 5초로 설정

        $sendHps = getGeclDeveloperHp();
        foreach ( $sendHps as $idx  => $sendHp) {
            sendSms($sendDate, $sendHp, $subject, $message);
        }

        echo "OK";
    } else {
        echo "No Data";
    }

    exit;

} else {
    echo "Access is denied";
    exit;
}


function sendSms($sendData, $sendhp, $subject, $message) {
    $sms_db = array(
        'host'	=> '115.68.114.153'
    ,	'id'	=> 'neiko'
    ,	'pw'	=>'rsmaker@ntwglobal'
    ,	'dbname'=>'LGUPLUS'
    );

    $sms_link	= mysqli_connect($sms_db['host'], $sms_db['id'], $sms_db['pw'], $sms_db['dbname']);

    $insert_sql = " 
        INSERT INTO LGUPLUS.MMS_MSG (REQDATE, PHONE, CALLBACK, SUBJECT ,MSG, ETC1, ETC2, ETC3) 
        VALUES ('".$sendData."', '".$sendhp."', '07070939515', '".$subject."','".$message."', 'dev', 'system', '');
    ";
    $result = mysqli_query($sms_link,$insert_sql);
}

/**
 * 허용가능 IP
 * @return array
 */
function getAccessip() {
    $accessIp = array();

    array_push($accessIp, '211.214.213.239'); // 개발서버
    array_push($accessIp, '211.214.213.101'); // 개발팀 IP
    array_push($accessIp, '66.209.90.25'); // 자빅스 IP

    return $accessIp;
}


/**
 * GECL 개발자 HP
 * @return array
 */
function getGeclDeveloperHp() {
    $sendHp = array();

    array_push($sendHp, '010-6511-1003'); // 최판일
    array_push($sendHp, '010-3394-4321'); // 이성희


    return $sendHp;
}
