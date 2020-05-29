<?php
$sub_menu = "200200";
include_once("./_common.php");

// 자로우 포뮬러스 찐 포인트 적립
//$targetUsers = array('air5602', 'ssccgirl', 'wooky1201', 'raison14', 'bleuhiver', 'ek1042', 'pminjoo79', 'dngPtmd', 'arden5', 'jhj12', 'snseun', 'iamjis', 'llbodll', 'jylee1224', 'kkksh', 'cisitalia', 'habojjabo', 'viva06', 'abtu73', 'sunin74', 'coolboy65', 'wlwjtm', 'kimtbotb', 'oshinyun', 'iammaji', 'neo692', 'hj326219', 'nare4475', 'ru079', 'topczar', 'hyks1546', 'liegirlx', 'yusl73', 'waterj80', 'zzica', 'gudtjr5057', 'neolife', 'jeil0105', 'west3069', 'shim0905', 'lifeis2005', 'latiti', 'buenos', 'jihyeruru', 'tigerya', 'wideee3', 'jjh93914', 'annasui1000', 'dionys32', 'ahnlee', 'pjh8792', 'hyunhik', 'srobbins', 'yjyjyj89', 'mmwqueen', 'leehyun0908', 'niangel', 'dks292', 'psj840', 'kong2i', 'avalon317', 'yeaheemin');
//$point = 3000;

// 가정의 달 5% 할인 포인트 적립
//$targetUsers = array('rnjseh44'=>'5651', 'eos0623'=>'4453', 'miranpower'=>'4132', 'oplebigfish80'=>'5770', 'recon40th'=>'3048', 'nigro'=>'3612', 'sinbee7329'=>'5315', 'monocorp'=>'3536', 'ufaop'=>'4989', 'mik6232'=>'3693', 'dudalss'=>'3935', '1024sky'=>'4531', 'mmesong'=>'4288', 'limits2'=>'3153', 'hs791226'=>'4220', 'hammira'=>'5323', 'yji640'=>'8507', 'mazunamu'=>'9076', 'ssoona'=>'6167', 'ampm1004'=>'5238', 'threekey3'=>'5060', 'okddc674'=>'8969', 'singhii'=>'5525', 'pavanes'=>'3195', 'singiry4'=>'3342', 'sang5561'=>'4247', 'jkh9984'=>'5774', 'my5rang2'=>'6158', 'rlawkfid'=>'4354', '5ttugi'=>'8636', 'j4005632'=>'5674', 'jns8511'=>'8119', 'rlaengls'=>'5014', 'leetaihe'=>'3925', 'lupinus2'=>'6657', 'permermerri'=>'6380', 'obcobc67'=>'4779', 'jungs3355'=>'3927', 'cometrue777'=>'7536', 'akeja'=>'7951', 'jikki89'=>'4672', 'balhyojoo'=>'7959', 'saruba'=>'4314', 'rococo07'=>'3703', 'kwang6750'=>'3077');


// 가정의 달 $60 이상 포인트 적립
//$targetUsers = array('rnjseh44', 'eos0623', 'miranpower', 'oplebigfish80', 'sinbee7329', 'ufaop', 'mik6232', 'dudalss', '1024sky', 'mmesong', 'hs791226', 'hammira', 'ampm1004', 'threekey3', 'singhii', 'sang5561', 'jkh9984', 'rlawkfid', 'j4005632', 'rlaengls', 'leetaihe', 'obcobc67', 'jungs3355', 'jikki89', 'saruba', 'rococo07', 'jaimekim10', 'leuna76');
//$point = 3000;

//$targetUsers = array('yji640', 'mazunamu', 'ssoona', 'okddc674', 'my5rang2', '5ttugi', 'jns8511', 'lupinus2', 'permermerri', 'cometrue777', 'akeja', 'balhyojoo');
//$pointReasonText = '가정의 달 $100 이상 포인트 적립';
//$point = 6000;

$i = 1;

$insertUsers = array();

//foreach ($targetUsers as $id => $point){
foreach ($targetUsers as $id){

    $accumulateUserId = trim($id);
    $insertResult = '';

    if(!in_array($accumulateUserId, $insertUsers)) {
        $insertResult = insert_point($accumulateUserId, $point, $pointReasonText, '@passive', $accumulateUserId, $accumulateUserId . '-' . uniqid(''));

        if($insertResult) {
            echo $i . "::: " . $accumulateUserId . " - " . $pointReasonText . " :: " . $point . " :: OK" . "<br />";
        } else {
            echo $i . "::: " . $accumulateUserId . " - " . $pointReasonText . " :: " . $point . " :: Error" . "<br />";
        }

//        echo $i . "::: " . $accumulateUserId . " - " . $pointReasonText . " :: " . $point . " :: OK" . "<br />";
        array_push($insertUsers, $accumulateUserId);

    } else {
        echo "DUPE USERID : " . $accumulateUserId ."<br />";
    }


    array_push($insertUsers, $accumulateUserId);

    $i++;


}