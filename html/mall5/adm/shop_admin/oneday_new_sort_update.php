<?php
/**
 * Created by PhpStorm.
 * User: NTWDEV_MAIN
 * Date: 2018-10-30
 * Time: 오후 2:56
 */
include_once("./_common.php");

if(count($_POST['sort']) < 1)
{
	$response = array(
		'result' => false,
		'message' => '순서를 변경할 상품이 존재하지 않습니다.'
	);
}
else
{
	$count = 0;
	foreach($_POST['sort'] as $uid => $sort)
	{
//		sql_query("update yc4_oneday_sale_item set sort = '".$sort."' where uid = '".$uid."' AND DATE_FORMAT(NOW(),'%Y%m%d') BETWEEN st_dt AND en_dt");
		$return = sql_query("update yc4_oneday_sale_item set sort = '".mysql_real_escape_string($sort)."' where uid = '".mysql_real_escape_string($uid)."'");
		$count += $return;
	}
	if($count >= 2)
	{
		$response = array(
			'result' => true,
			'count' => $count,
			'message' => '순서 변경이 완료되었습니다.'
		);
	}
	else
	{
		$response = array(
			'result' => false,
			'message' => "처리중 오류가 발생하였습니다.\n변경된 순서를 확인하신 후 변경되지 않았다면 잠시 후 다시 시도해 주세요."
		);
	}
}

echo json_encode($response);