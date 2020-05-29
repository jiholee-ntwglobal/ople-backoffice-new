<?
$sub_menu = "200200";
include_once("./_common.php");

$points = array('rnjseh44' => '5653', 'eos0623' => '4452' , 'miranpower' => '4136' , 'oplebigfish80' => '5774' , 'recon40th' => '3045' , 'nigro' => '3615' , 'sinbee7329' => '5313' , 'monocorp' => '3542' , 'ufaop' => '4985' , 'mik6232' => '3688' , 'dudalss' => '3930' , '1024sky' => '4537' , 'mmesong' => '4294' , 'limits2' => '3154' , 'mynak20' => '5580' , 'hs791226' => '4221' , 'hammira' => '5317' , 'yji640' => '8510' , 'mazunamu' => '9081' , 'ssoona' => '6167' , 'ampm1004' => '5232' , 'threekey3' => '5062' , 'okddc674' => '8971' , 'singhii' => '5524' , 'pavanes' => '3193' , 'singiry4' => '3339' , 'sang5561' => '4249' , 'jkh9984' => '5779' , 'my5rang2' => '6155' , 'rlawkfid' => '4358' , '5ttugi' => '8632' , 'j4005632' => '5669' , 'jns8511' => '8122' , 'rlaengls' => '5014' , 'leetaihe' => '3921' , 'lupinus2' => '6653' , 'permermerri' => '6386' , 'obcobc67' => '4783' , 'jungs3355' => '3933' , 'cometrue777' => '7539' , 'akeja' => '7952' , 'balhyojoo' => '7964' , 'saruba' => '4310' , 'rococo07' => '3703');

//$points = array('ricemen' => '5653', 'wonseoktest' => '4452' );

$i = 1;
$pointReasonText = '싹쓰리 할인 미적용 포인트 적립';

$insertUsers = array();

foreach($points as $id => $point) {
//    if(!in_array($id, $insertUsers)) {
//        $insertResult = insert_point($id, $point, $pointReasonText, '@passive', $id, $id . '-' . uniqid(''));
//
////        $insertResult = true;
//        if($insertResult) {
//            echo $i . "::: " . $id . " - " . $pointReasonText . " :: " . $point . " :: OK" . "<br />";
//        } else {
//            echo $i . "::: " . $id . " - " . $pointReasonText . " :: " . $point . " :: Error" . "<br />";
//        }
//
//    } else {
//        echo "DUPE USERID : " . $id ." - " . " - " . $point . "<br />";
//    }
//
//    array_push($insertUsers, $id);
//
//    $i++;
}


//
//foreach ($userIds as $id){
//
//
//    $accumulateUserId = trim($id);
//
//    if(!in_array($accumulateUserId, $insertUsers)) {
////        $insertResult = insert_point($accumulateUserId, $point, $pointReasonText, '@passive', $accumulateUserId, $accumulateUserId . '-' . uniqid(''));
//
//        if($insertResult) {
//            echo $i . "::: " . $accumulateUserId . " - " . $pointReasonText . " :: " . $point . " :: OK" . "<br />";
//        } else {
//            echo $i . "::: " . $accumulateUserId . " - " . $pointReasonText . " :: " . $point . " :: Error" . "<br />";
//        }
//
////        echo $i . "::: " . $accumulateUserId . " - " . $pointReasonText . " :: " . $point . " :: OK" . "<br />";
//        array_push($insertUsers, $accumulateUserId);
//
//    } else {
//        echo "DUPE USERID : " . $accumulateUserId ."<br />";
//    }
//
//
//    array_push($insertUsers, $accumulateUserId);
//
//    $i++;
//
//
//}
