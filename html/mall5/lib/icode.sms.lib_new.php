<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-04-16
 * Time: 오후 3:40
 */

function spacing($text,$size) {
    for ($i=0; $i<$size; $i++) $text.=" ";
    $text = substr($text,0,$size);
    return $text;
}

function cut_char($word, $cut) {
//	$word=trim(stripslashes($word));
    $word=substr($word,0,$cut);						// 필요한 길이만큼 취함.
    for ($k=$cut-1; $k>1; $k--) {
        if (ord(substr($word,$k,1))<128) break;		// 한글값은 160 이상.
    }
    $word=substr($word,0,$cut-($cut-$k+1)%2);
    return $word;
}


class SMS{

    var $Data = array();

    function __construct(){
        //include "/ssd/html/mall5/lib/sms_connection.php";
        include "/ssd/ople_data/sms_connection.php";

        $this->SMS_SERVER = new mysqli($sms_host, $sms_user, $sms_pass, $sms_db);
        $this->SMS_SERVER->set_charset("utf8");
    }
    function __destruct(){
        $this->SMS_SERVER->close();
    }

    function SMS_con($sms_server=null,$sms_id=null,$sms_pw=null,$port=null) {

    }

    function Add($dest, $callBack, $Caller='', $msg, $rsvTime=""){
        /*
         *
         * 받는사람번호,보내는사람번호,null,메시지내용,보내는시간
         *
         * */

        // 내용 검사 1
        /*
        $Error = CheckCommonType($dest, $rsvTime);
        if ($Error) return $Error;
        */
        // 내용 검사 2
        if ( preg_replace("/[0-9]/u",'',$callBack) ) return "회신 전화번호가 잘못되었습니다";
        if ( preg_replace("/^01([0|1|6|7|8|9]?)([0-9]{3,4})([0-9]{4})$/",'',$dest) ) return '받는 사람 번호가 잘못되었습니다.';



        $msg=cut_char($msg,90); // 80자 제한
        // 보낼 내용을 배열에 집어넣기
        $dest = spacing($dest,11);
        $callBack = spacing($callBack,11);
        $Caller = spacing($Caller,10);
        $rsvTime = preg_replace("/[^0-9]/i",'',$rsvTime);
        $rsvTime = spacing($rsvTime,12);
        $msg = spacing($msg,90);

        $this->Data[] = array(
            'dest' => $dest,
            'callBack' => $callBack,
            'Caller' => $Caller,
            'msg' => $msg,
            'rsvTime' => $rsvTime
        );

        return "";
    }


    function Send(){


        foreach($this->Data as $val){


            if(in_array($val['rsvTime'],array('now','now()')) || !$val['rsvTime']){
                $time = 'now()';
                $bind_type = 'bssss';

            }else{
                $time = date('Y-m-d H:i:s',strtotime($val['rsvTime']));
                $bind_type = 'sssss';

            }


            $send_sql = " insert into SC_TRAN (TR_SENDDATE, TR_PHONE,TR_CALLBACK,TR_MSG,TR_ETC1) values (?,?,?,?,?)";
            $stmt = $this->SMS_SERVER->prepare($send_sql);
            $stmt->bind_param($bind_type,$data1,$data2,$data3,$data4,$data5);
            $data1 = $time;
            $data2 = $val['dest'];
            $data3 = $val['callBack'];
            $data4 = $val['msg'];
            $data5 = 'ople';
            $stmt->execute();

        }

    }

    /*
     * sms,lms 발송 취소 2015-10-29 홍민기
     * */
    function send_cancel($hp_no){
        $hp_no = preg_replace('/[^0-9]/','',$hp_no);
        $this->SMS_SERVER->query("delete from SC_TRAN where TR_PHONE = '".$hp_no."' ");
        $this->SMS_SERVER->query("delete from MMS_MSG where PHONE = '".$hp_no."' ");
    }

    function Init(){
        $this->Data = array();
    }
}