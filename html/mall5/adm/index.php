<?
include_once("./_common.php");

$g4['title'] = "관리자메인";

$cnt_info = sql_fetch("select count(*) as cnt from manager_access_ip where ip='$_SERVER[REMOTE_ADDR]'");

if($cnt_info['cnt'] < 1){
    sql_query("insert into manager_access_ip (ip) values ('$_SERVER[REMOTE_ADDR]')");
}


include_once ("./admin.head.php");

echo $member['mb_name'].'님의 접속 IP는 <span style="color:red;">'.$_SERVER['REMOTE_ADDR'].'</span>입니다.';

if($member['mb_id']!="naver_cps") {
    include "$g4[admin_path]/html_block/admin_main_block_member.html"; // 회원현황

    include "$g4[admin_path]/html_block/admin_main_block_cms.html"; // 고객센터 오플,아이해피

    include "$g4[admin_path]/html_block/admin_main_block_call.html"; // 전화요청

    include "$g4[admin_path]/html_block/admin_main_block_iq.html"; // 상품문의
}else{
    echo "<br><br><b>CPS관련 페이지는 통계 메뉴에 있습니다.</b><br/><br/>";
}
/*if($_SERVER['REMOTE_ADDR'] == '112.218.8.99'){
	include "../cron/admin_main_block_cms_bs.php";
}*/
include_once ("./admin.tail.php");
?>
