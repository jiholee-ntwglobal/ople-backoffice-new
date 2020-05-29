<?php
//echo "test";
//exit;


$arr1	= array('1');
$arr2	= array('1','2');

var_dump(array_diff_assoc($arr2, $arr1));

var_dump(array_diff($arr2, $arr1));

//$str	= '1234';
//$str2	= 'FB0001234';
//echo (int)str_replace(array('FA','FB'),'',$str);
//echo (int)str_replace(array('FA','FB'),'',$str2);

//exit;

//include_once "./_common.php";
//include_once $g4['full_path'] . '/lib/nfo.php';
//
//$nfo = new nfo();
//
//var_dump($nfo->test_kki());

exit;

//echo 'test 02';
//$before_upcs	= array();
//var_dump($before_upcs);
//foreach($nfo->get_ople_mapping_data('1511008115') as $bf_map_row){
//	if(!in_array(trim($bf_map_row['upc']),$before_upcs)) array_push($before_upcs,trim($bf_map_row['upc']));
//}
//
//var_dump($before_upcs);
//echo 'test 03';


//$a	= array('223');
//$b	= array('123');
//
//if(count(array_diff($a, $b)) > 0) echo 'test';
//
//var_dump($a);
//var_dump($b);

//if(in_array(date('H',strtotime('20180618042356')), array('04','12','16'))){
//	echo date('Y-m-d H:i:s');
//}else{
//	echo date('H');
//}

exit;

//$it_id	= '1511223623';
//$max_id	= "test";
//
//$ftp_info	= array(
//	'host'	=> '66.209.90.19'
//,	'user'	=> 'ntwglobal'
//,	'pass'	=> 'qwe123qwe!@#'
//);
//
//$ftp	=  ftp_connect($ftp_info['host']);
//ftp_login($ftp, $ftp_info['user'], $ftp_info['pass']) or die( 'Oh No!' );
//// 프론트 서버 이미지 파일명 변경
//$img_files	= array(
//	$it_id."_l1" => $max_id."_l1"
//,	$it_id."_m"  => $max_id."_m"
//,	$it_id."_s"  => $max_id."_s"
//);
//foreach($img_files as $old_img => $new_img){
//	if(ftp_size($ftp, '/ssd/html/mall5/data/item/'.$old_img) != -1){
////		@ftp_rename($conn_id,'/ssd/html/mall5/data/item/'.$old_img,'/ssd/html/mall5/data/item/'.$new_img);
//		echo $old_img;
//	}
//}

//$pr_id	= '11';
//$ftp_info	= array(
//	'host'	=> '66.209.90.19'
//,	'user'	=> 'ntwglobal'
//,	'pass'	=> 'qwe123qwe!@#'
//);
//
//$pr_niddle		= array('pc_'.$pr_id, 'mobile_'.$pr_id);
//$cache_niddle	= '.cache';
//
//$cache_path	= '/ssd/html/mall5/shop/promotion/cache';
//
//$ftp	=  ftp_connect($ftp_info['host']);
//
//ftp_login($ftp, $ftp_info['user'], $ftp_info['pass']) or die( 'Oh No!' );
//ftp_chdir($ftp, $cache_path);
//
//$files	= ftp_nlist($ftp,'./');
//$filtered_files	= preg_grep('/(_'.$pr_id.'_)/', $files);
//
//foreach($filtered_files as $path){
//
//}


//include_once $g4['full_path']."/lib/open_db.php";
//$open_db = new open_db;
////$open_db	= $db->open_db;
//
//$test	= $open_db->sql_fetch("SELECT it_id FROM open_market_mapping ORDER BY uid DESC LIMIT 0, 1");
//var_dump($test);

//phpinfo();

//$str	= '';
//include_once $g4['full_path'] . '/lib/db.php';
//$db	= new db;
//$ntics_db	= $db->test_pdo;
//
//if(!$ntics_db->inTransaction()){
//	$ntics_db->beginTransaction(); // 트렌젝션 시작
//}
//
//$smt	= $ntics_db->prepare("UPDATE N_MASTER_ITEM SET attext = N? WHERE upc='021888108923'");
//$smt->bindValue(1,$str);
//if(!$smt->execute()){
//	$ntics_db->rollBack();
//	echo 'error!!!!';
//	exit;
//}
//
//$ntics_db->commit();
//echo 'success';
////var_dump($sql->execute());


//include_once $g4['full_path'] . '/lib/new_db.lib.php';
//$db = new new_db;
//if(!isset($new_opk_db)){
//	echo "point 01";
//	$new_opk_db = $db->init_db('new_opk');
//}
//
//echo $new_opk_db->query("SELECT product_id FROM oc_product WHERE sku='1511132114'")->fetchObject()->product_id;


//include_once $g4['full_path'] . '/lib/ople_mapping.php';
//$ople_mapping = new ople_mapping();
//$it_arr	= array();
//$n=0;
//foreach($it_arr as $it_id){
//	var_dump($ople_mapping->insert_ns_o01($it_id));
//	echo $n."<br/>";
//	$n++;
//}

//var_dump(holiday_Special_Week_2017_Point_Cancel('1703310366'));

//var_dump(prepay_17_chu_ev_cancel('1710070425', 'dreamfox9'));
//var_dump(prepay_17_chu_ev_cancel('1710070619', 'esly1204'));
//var_dump(prepay_17_chu_ev_cancel('1710100810', 'dksowh'));
//var_dump(prepay_17_chu_ev_cancel('1710100357', 'hasitlike'));

//var_dump(master_16_50_100_dc_cancel('1612120066'));
//var_dump(bbeabbearo_16_gift_cancel('1611100176'));
//$od_arr0 = array(
//'1609010002', '1609010011', '1609010010', '1609010031', '1609010040', '1609010049', '1609010046', '1608310466', '1608311641', '1609010074');
//$n_i	= 1;
//foreach($od_arr0 as $row){
//	echo "(".$n_i.")";
//	var_dump(harvest_day_16_point_cancel($row));
//	echo "<br/>";
//	$n_i++;
//}

//var_dump(nordic_1704_gift_cancel('1606080645'));
//var_dump(nordic_1704_gift_cancel('1704120295'));
//var_dump(nordic_1704_gift_cancel('1704120294'));

//var_dump(olympic_16_point_cancel('1608190092'));
//var_dump(harvest_16_etc_gift_cancel('paik2002','1609070343'));
//var_dump(harvest_day_16_gift_cancel('1608250019'));

//function get_header_curl($url){
//	$ch = curl_init($url);
//	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');
//	curl_setopt($ch, CURLOPT_HEADER, 1);
//	curl_setopt($ch, CURLOPT_NOBODY, true);
//	$response = curl_exec($ch);
//	curl_close($ch);
//	return $response;
//}
////		('1449826861','1505207641','1508140844','1509160155','1509164619','1510582922','1510657519','1510657619','1510657719','1510657819'
////		,'1510657919','1510768682','1510796282')
//
//$sql	= sql_query("SELECT it_id FROM yc4_item WHERE it_use='1' AND it_discontinued='0' AND IFNULL(it_maker,'') != '' AND it_amount_usd NOT IN ('999','999.99')
//		AND it_id IN
//		('1504193838', '1510744830', '1510744831', '1510744832', '1510744833', '1510744834', '1510744835', '1510470545', '1510744836', '1510471582',
//'1510744837', '1510744838', '1510469518', '1510470547', '1510470550', '1510470552', '1510470554', '1510619650', '1510619651', '1510469519',
//'1510474728', '1510619653', '1510469521', '1510469528', '1376090167', '1510619654', '1510472633', '1510471585', '1510471589', '1412154950',
//'1509152324', '1505207641', '1510471587', '1510469525', '1510469526', '1510796282', '1505140707', '1507175827', '1508140844', '1510474739',
//'1509160155', '1509164619', '1510474732', '1510768682', '1449826861', '1510582922', '1510657519', '1510657619', '1510657719', '1510657819',
//'1510657919')
//");
//echo "cURL start".PHP_EOL;
//while($row = sql_fetch_array($sql)){
//	$header	= get_header_curl("http://ople.com/mall5/data/item/".trim($row['it_id'])."_l1");
//	if(!strpos($header, "200")){
//		echo $row['it_id']."<br />";
//	}
//}
//echo "cURL end".PHP_EOL;
//
//
//
exit;
