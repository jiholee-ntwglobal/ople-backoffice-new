<?php
/**
 * Created by Eclipse
 * User: kyung-in
 * Date: 2015.09.09
 * file: test_2/ev_list_update.php
 */
$sub_menu = "500800";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "이벤트통계관리";
include_once ("$g4[admin_path]/admin.head.php");


$ev_name	= "";
$ev_query	= "";

$list_path	= "./eventresearch_list.php";

// 모드에 따라 처리내용 변경
// 디비 리소스 표기합시다.

// 비활성부분 동작
// 세션을 가져와서 글등록자와 비교확인 부분 필요
if(isset($_GET['mode']) && $_GET['mode'] == "Y"){
	$seqno	= trim($_GET['seqno']);
//	$seqno	= mysql_real_escape_string($seqno);
	$sql	= "
			UPDATE
				event_research
			SET
				stat = 'N'
			WHERE
				seqno = '".$seqno."'
	";
	$res	= mysql_query($sql);
	$str	= "비활성";
	$list_path	= $list_path."?vmode=Y";
	
// 활성부분 동작
// 세션을 가져와서 글등록자와 비교확인 부분 필요
}elseif(isset($_GET['mode']) && $_GET['mode'] == "N"){
	$seqno	= trim($_GET['seqno']);
//	$seqno	= mysql_real_escape_string($seqno);
	$sql	= "
			UPDATE
				event_research
			SET
				stat = 'Y'
			WHERE
				seqno = '".$seqno."'
	";
	$res	= mysql_query($sql);
	$str	= "활성";
	$list_path	= $list_path."?vmode=N";

// 수정부분 동작
// 세션을 가져와서 글등록자와 비교확인 부분 필요
}elseif(isset($_POST['mode']) && $_POST['mode'] == "rewrite"){
	if(!isset($_POST['ev_name']) || !isset($_POST['ev_query'])){
	echo "
		<script type=text/JavaScript>
			alert('필수항목이 비었습니다.')
			history.back()
		</script>
	";
	}
	
	$seqno	= trim($_POST['seqno']);
//	$seqno	= mysql_real_escape_string($seqno);

// 시작날짜 유효성검사
	$st_dt	= $_POST['st_dt'];
	if ( !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/',$st_dt,$match_s) || !checkdate($match_s[2],$match_s[3],$match_s[1]) ) { 
		echo "
			<script type=text/JavaScript>
				alert('시작날짜는 1999-12-31 형식의 유효한 날짜를 입력해주세요')
				history.back()
			</script>
		";
		exit;
	} 
	
// 종료날짜 유효성 검사
	$ed_dt	= $_POST['ed_dt'];
	if ( !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/',$ed_dt,$match_e) || !checkdate($match_e[2],$match_e[3],$match_e[1]) ) { 
		echo "
			<script type=text/JavaScript>
				alert('종료날짜는 0000-00-00 형식의 유효한 날짜를 입력해주세요')
				history.back()
			</script>
		";
		exit;
	} 

// 시작날짜와 종료날짜 비교
	if (mktime(0,0,0,$match_s[2],$match_s[3],$match_s[1]) > mktime(0,0,0,$match_e[2],$match_e[3],$match_e[1])){
		echo "
			<script type=text/JavaScript>
				alert('종료날짜는 시작날짜를 앞설 수 없습니다.')
				history.back()
			</script>
		";
		exit;
	}

// 이벤트 이름 이스케이프처리
	$ev_name	= $_POST['ev_name'];
	$ev_name	= substr($ev_name, 0, 100);
//	$ev_name	= mysql_real_escape_string($ev_name);

// 쿼리문이 select로 시작하는지 검사	
	$ev_query	= $_POST['ev_query'];
	$trim_sql	= trim($ev_query);
	$subs_sql	= substr($trim_sql, 0, 6);
	$low_sql	= strtolower($subs_sql);
	if( $low_sql != "select" ){
		echo "
			<script type=text/JavaScript>
				alert('쿼리는 select 구문만 등록하실 수 있습니다.')
				history.back()
			</script>
		";
		exit;
	}
// 쿼리 구문 이스케이프처리
//	$ev_query	= mysql_real_escape_string($ev_query);
	
	$sql	= "
			UPDATE
				event_research
			SET
				ev_name = '".$ev_name."' 
			,	ev_query = '".$ev_query."'
			,	st_dt = '".$st_dt."'
			,	ed_dt = '".$ed_dt."'
			,	stat = 'Y'
			WHERE
				seqno = '".$seqno."'
	";
	$res	= mysql_query($sql);
	$str	= "수정";


// 새로 등록부분
}else{
	if($_POST['ev_name'] == "" || $_POST['ev_query'] == ""){
		echo "
			<script type=text/JavaScript>
				alert('필수항목이 비었습니다.')
				history.back()
			</script>
		";

		exit;
	}

// 시작날짜 유효성검사
	$st_dt		= $_POST['st_dt'];
	if ( !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/',$st_dt,$match_s) || !checkdate($match_s[2],$match_s[3],$match_s[1]) ) { 
		echo "
			<script type=text/JavaScript>
				alert('시작날짜는 1999-12-31 형식의 유효한 날짜를 입력해주세요')
				history.back()
			</script>
		";
		exit;
	} 
	
// 종료날짜 유효성 검사
	$ed_dt		= $_POST['ed_dt'];
	if ( !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/',$ed_dt,$match_e) || !checkdate($match_e[2],$match_e[3],$match_e[1]) ) { 
		echo "
			<script type=text/JavaScript>
				alert('종료날짜는 0000-00-00 형식의 유효한 날짜를 입력해주세요')
				history.back()
			</script>
		";
		exit;
	} 

// 시작날짜와 종료날짜 비교
	if (mktime(0,0,0,$match_s[2],$match_s[3],$match_s[1]) > mktime(0,0,0,$match_e[2],$match_e[3],$match_e[1])){
		echo "
			<script type=text/JavaScript>
				alert('종료날짜는 시작날짜를 앞설 수 없습니다.')
				history.back()
			</script>
		";
		exit;
	}
	
// 이벤트 이름 이스케이프 처리
	$ev_name	= $_POST['ev_name'];
	$ev_name	= substr($ev_name, 0, 100);
//	$ev_name	= mysql_real_escape_string($ev_name);

// 쿼리문이 select로 시작하는지 확인	
	$ev_query	= $_POST['ev_query'];
	$trim_sql	= trim($ev_query);
	$subs_sql	= substr($trim_sql, 0, 6);
	$low_sql	= strtolower($subs_sql);
	if( $low_sql != "select" ){
		echo "
			<script type=text/JavaScript>
				alert('쿼리는 select 구문만 사용하실 수 있습니다.')
				history.back()
			</script>
		";
		exit;
	}
// 쿼리 구문 이스케이프 처리
//	$ev_query	= mysql_real_escape_string($ev_query);

	$sql	= "
			insert into
			event_research (
		  		ev_name
			,	ev_query
			,	st_dt
			,	ed_dt
			,	stat
			) VALUES (
				'".$ev_name."'
			,	'".$ev_query."'
			,	'".$st_dt."'
			,	'".$ed_dt."'
			,	'Y'
			)
	";
	$res	= mysql_query($sql);
	$str	= "등록";
}

if(!$res){
	echo "
		<script type=text/JavaScript>
			alert('".$str."에 실패하였습니다.')
			history.back()
		</script>";
}
else{
	echo "
		<script type=text/JavaScript>
			alert('".$str."되었습니다.')
			location.replace('".$list_path."')
		</script>";
}?>