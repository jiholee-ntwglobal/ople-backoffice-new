<?php
// 이 상수가 정의되지 않으면 각각의 개별 페이지는 별도로 실행될 수 없음
define("_GNUBOARD_", TRUE);

$g4['front_ftp_server']	 = "66.209.90.19";
$g4['front_ftp_user_name'] = "ntwglobal";
$g4['front_ftp_user_pass'] = "qwe123qwe!@#"; 

// 디렉토리
$g4['bbs']            = "bbs";
$g4['bbs_path']       = $g4['path'] . "/" . $g4['bbs'];
$g4['bbs_img']        = "img";
$g4['bbs_img_path']   = $g4['path'] . "/" . $g4['bbs'] . "/" . $g4['bbs_img'];

$g4['admin']          = "adm";
$g4['admin_path']     = $g4['path'] . "/" . $g4['admin'];

$g4['editor']         = "cheditor";
$g4['editor_path']    = $g4['path'] . "/" . $g4['editor'];

// 자주 사용하는 값
// 서버의 시간과 실제 사용하는 시간이 틀린 경우 수정하세요.
// 하루는 86400 초입니다. 1시간은 3600초
// 6시간이 빠른 경우 time() + (3600 * 6);
// 6시간이 느린 경우 time() - (3600 * 6);
//$g4['server_time'] = time() + (3600 * 16); // 김선용 201207 : 한국시간과 맞춤
$g4['server_time'] = time();
$g4['time_ymd']    = date("Y-m-d", $g4['server_time']);
$g4['time_his']    = date("H:i:s", $g4['server_time']);
$g4['time_ymdhis'] = date("Y-m-d H:i:s", $g4['server_time']);

//
// 테이블 명
// (상수로 선언한것은 함수에서 global 선언을 하지 않아도 바로 사용할 수 있기 때문)
//
$g4['table_prefix']        = "g4_"; // 테이블명 접두사
$g4['write_prefix']        = $g4['table_prefix'] . "write_"; // 게시판 테이블명 접두사

$g4['auth_table']          = $g4['table_prefix'] . "auth_new";          // 관리권한 설정 테이블
$g4['config_table']        = $g4['table_prefix'] . "config";        // 기본환경 설정 테이블
$g4['group_table']         = $g4['table_prefix'] . "group";         // 게시판 그룹 테이블
$g4['group_member_table']  = $g4['table_prefix'] . "group_member";  // 게시판 그룹+회원 테이블
$g4['board_table']         = $g4['table_prefix'] . "board";         // 게시판 설정 테이블
$g4['board_file_table']    = $g4['table_prefix'] . "board_file";    // 게시판 첨부파일 테이블
$g4['board_good_table']    = $g4['table_prefix'] . "board_good";    // 게시물 추천,비추천 테이블
$g4['board_new_table']     = $g4['table_prefix'] . "board_new";     // 게시판 새글 테이블
$g4['login_table']         = $g4['table_prefix'] . "login";         // 로그인 테이블 (접속자수)
$g4['mail_table']          = $g4['table_prefix'] . "mail";          // 회원메일 테이블
$g4['member_table']        = $g4['table_prefix'] . "member";        // 회원 테이블
$g4['memo_table']          = $g4['table_prefix'] . "memo";          // 메모 테이블
$g4['poll_table']          = $g4['table_prefix'] . "poll";          // 투표 테이블
$g4['poll_etc_table']      = $g4['table_prefix'] . "poll_etc";      // 투표 기타의견 테이블
$g4['point_table']         = $g4['table_prefix'] . "point";         // 포인트 테이블
$g4['popular_table']       = $g4['table_prefix'] . "popular";       // 인기검색어 테이블
$g4['scrap_table']         = $g4['table_prefix'] . "scrap";         // 게시글 스크랩 테이블
$g4['visit_table']         = $g4['table_prefix'] . "visit";         // 방문자 테이블
$g4['visit_sum_table']     = $g4['table_prefix'] . "visit_sum";     // 방문자 합계 테이블
$g4['token_table']         = $g4['table_prefix'] . "token";         // 토큰 테이블
//
// 기타
//
// 김선용 201107 : 가상계좌 자동입금통보용. shop.config.php 파일은 자동입금통보 송신파일에서 불러오지 않으므로 여기에 위치
$g4['yc4_gsmpg_table'] = 'yc4_gsmpg_receive';
// 김선용 201107 :
$g4['yc4_bui_ip_table'] = 'yc4_bui_access_ip';
$g4['yc4_gift_table'] = "yc4_gift"; // 김선용 201207 :
// 김선용 201208 :
$g4['item_sms_table'] = "yc4_add_item_sms";
// 김선용 201210 :
$g4['yc4_rc_table'] = "yc4_recommend_report_table";
$g4['yc4_ma_table'] = "yc4_member_addr_book";
$g4['yc4_os_table'] = "yc4_order_ship_addr";
// 김선용 201211 :
$g4['yc4_onrequest_table'] = "yc4_item_onrequest";
$g4['yc4_member_promo'] = "yc4_member_promo"; // 프로모션 생성
$g4['yc4_member_promor'] = "yc4_member_promor"; // 프로모션 처리결과 누적(가입자/주문등)
$g4['yc4_member_promo_order'] = "yc4_member_promo_order"; // 프로모션 가입회원 주문번호 누적,관리

// 김선용 201208 :
$mb_level_str = array(1=>'', '일반', 'GOLD', 'VIP', '리셀러', '', '', '', '', '');

// www.sir.co.kr 과 sir.co.kr 도메인은 서로 다른 도메인으로 인식합니다. 쿠키를 공유하려면 .sir.co.kr 과 같이 입력하세요.
// 이곳에 입력이 없다면 www 붙은 도메인과 그렇지 않은 도메인은 쿠키를 공유하지 않으므로 로그인이 풀릴 수 있습니다.
$g4['cookie_domain'] = "";

// 게시판에서 링크의 기본갯수를 말합니다.
// 필드를 추가하면 이 숫자를 필드수에 맞게 늘려주십시오.
$g4['link_count'] = 2;

$g4['charset'] = "UTF-8";

$g4['phpmyadmin_dir'] = $g4['admin'] . "/phpMyAdmin/";

$g4['token_time'] = 3; // 토큰 유효시간

// config.php 가 있는곳의 웹경로. 뒤에 / 를 붙이지 마세요.
// 예) http://g4.sir.co.kr
//$g4['url'] = "http://www.okflex.com/mall4";
$g4['home_dir'] = 'mall5';
$g4['url'] = "http://{$_SERVER['HTTP_HOST']}/".$g4['home_dir'];
$g4['url_login'] = "http://{$_SERVER['HTTP_HOST']}";
$g4['https_url'] = "";
$g4['https_url2'] = "http://{$_SERVER['HTTP_HOST']}/".$g4['home_dir']; //"http://www.newokflex.com/mall4";
// 입력예
//$g4['url'] = "http://www.sir.co.kr";
//$g4['https_url'] = "https://www.okflex.com";


# 서버 절대 경로 2014-10-06 홍민기 #
$g4['full_path'] = $_SERVER['DOCUMENT_ROOT'].'/'.$g4['home_dir'];
$g4['full_shop_path'] = $_SERVER['DOCUMENT_ROOT'].'/'.$g4['home_dir'].'/shop';
$g4['full_bbs_path'] = $_SERVER['DOCUMENT_ROOT'].'/'.$g4['home_dir'].'/bbs';

## 접속 도메인 검사 ##
/*
	com으로 접속시 $domain_flag = 'com';
	co.kr으로 접속시 $domain_flag = 'kr';
	2014-04-17 홍민기
*/



/*
$SERVER_HOST_ARR = explode('.',str_replace('www.','',$_SERVER['HTTP_HOST']));

$domain_flag = array_pop($SERVER_HOST_ARR);
*/
if(str_replace('www.','',$_SERVER['HTTP_HOST']) == 'ople.co.kr'){
	$domain_flag = 'kr';
}



# co.kr로 접속시 노출되지 않을 상품 배열 #
if($domain_flag == 'kr'){

	echo "
		<script>
			location.href='http://ople.com';
		</script>
	";
	exit;

	# 숨길 카테고리 처리 #
	$hide_ca = array('60d0','2340','n0d0','97');

	if(is_array($hide_ca)){
		$i = 0;
		foreach($hide_ca as $val){
			$hide_ca_result .= (($i > 0) ? ' , ':'')."'".$val."'";
			$i++;
		}

		$hide_caQ = " and ca_id not in (".$hide_ca_result.") ";
		$hide_caQ2 = " ca_id not in (".$hide_ca_result.") ";
		$hide_caQ3 = " and a.ca_id not in (".$hide_ca_result.") ";
		$hide_caQ4 = " and ca_id not in (".$hide_ca_result.") and ca_id2 not in (".$hide_ca_result.") and ca_id3 not in (".$hide_ca_result.") and ca_id4 not in (".$hide_ca_result.") and ca_id5 not in (".$hide_ca_result.")";
		$hide_caQ5 = " and a.ca_id not in (".$hide_ca_result.") and a.ca_id2 not in (".$hide_ca_result.") and a.ca_id3 not in (".$hide_ca_result.") and a.ca_id4 not in (".$hide_ca_result.") and a.ca_id5 not in (".$hide_ca_result.")";
	}

	# 숨길 브랜드 처리 #
	$hide_maker = array('Weleda');

	if(is_array($hide_maker)){
		$i = 0;
		foreach($hide_maker as $val){
			$hide_maker_result .= (($i > 0) ? ' , ':'')."'".$val."'";
		}
		$hide_makerQ = " and it_maker not in (".$hide_maker_result.") ";
		$hide_maker2 = " it_maker not in (".$hide_maker_result.") ";
		$hide_maker3 = " and a.it_maker not in (".$hide_maker_result.") ";
	}


	$hide_item = array();

	# 숨길 상품 처리 #
	$hide_itemQ = " and it_id not in (select it_id from yc4_item_hide)";
	$hide_itemQ2 = " and a.it_id not in (select it_id from yc4_item_hide)";
	$hide_itemQ3 = " and it_id not in (select it_id from yc4_item_hide)";
	$hide_itemQ4 = " and it_id not in (select it_id from yc4_item_hide)";
}


# 식물 검역 대상 상품 #
$plant_item = array('1357254282','1340755611','1353452463','1353452053','1343209200','1340755335','1365658764','1333546982','1503134726','1411185438');


# 마스터카드 프로모션 해당되지 않는 브랜드
$master_card_no_brand = array(
	"Nature's Plus",'GNC','Wyeth'
);

# 마스터카드 프로모션 해당되지 않는 상품코드
$master_cart_no_it_id = array('1306524520','1251860612','1222827644','1222682189','1210012129','1210591619'); // 선결제 포인트


$best_item_link = array(
	'1' => '1413794415', // 미용
	'2' => '1413794436', // 식품
	'3' => '1413794344', // 건강
	'4' => '1413794375', // 생활
	'5' => '1413794397' // 출산
);


// 마스터카드 프로모션
if( (date('Ymd') >= '20141110' && date('Ymd') <= '20141231')  || $_SERVER['REMOTE_ADDR'] == '59.17.43.129' ){
	$_MASTER_CARD_EVENT = true;
}
$_MASTER_CARD_EVENT = true;

$_HOTDEAL_FG = true;




if(date('Ymd') > 20150331){ // 배송비 이벤트 23일 00시부터 종료 2015-02-17 홍민기
	//$_BAESONG_CHANGE = false;
}




# 설날 세트 상품 코드 => 원래가격 #

$NTICS_DATA_ON = true;
$NTICS_DATA_URL = 'http://ntics.ntwsec.com/etc/item_info.php';
$_CURL_TIMEOUT_ = 4;

# 신한 글로벌카드 bin 2015-03-04 홍민기 #
$shinhan_global_bin = array(
	513243,511187
);

if($_SERVER['REMOTE_ADDR'] == '59.17.43.129'){
    $shinhan_global_bin[] = '552123';
}

$email_send_fg = false;

$hana_event_fg = true;
$_BAESONG_CHANGE = false;

//곽범석 문의 제어문
$customer_question =true;
if($_SERVER['REMOTE_ADDR'] == '112.218.8.99'){
	$customer_question= true;
}


?>