<?php
// 이 파일은 새로운 파일 생성시 반드시 포함되어야 함
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$begin_time = get_microtime();

if (!$g4['title'])
    $g4['title'] = $config['cf_title'];

// 쪽지를 받았나?
if ($member['mb_memo_call']) {
    $mb = get_member($member['mb_memo_call'], "mb_nick");
    sql_query(" update {$g4[member_table]} set mb_memo_call = '' where mb_id = '$member[mb_id]' ");

    alert($mb['mb_nick']."님으로부터 쪽지가 전달되었습니다.", $_SERVER['REQUEST_URI']);
}


// 현재 접속자
//$lo_location = get_text($g4[title]);
//$lo_location = $g4[title];
// 게시판 제목에 ' 포함되면 오류 발생
$lo_location = addslashes($g4['title']);
if (!$lo_location)
    $lo_location = $_SERVER['REQUEST_URI'];
//$lo_url = $g4[url] . $_SERVER['REQUEST_URI'];
$lo_url = $_SERVER['REQUEST_URI'];
if (strstr($lo_url, "/$g4[admin]/") || $is_admin == "super") $lo_url = "";

// 자바스크립트에서 go(-1) 함수를 쓰면 폼값이 사라질때 해당 폼의 상단에 사용하면
// 캐쉬의 내용을 가져옴. 완전한지는 검증되지 않음
header("Content-Type: text/html; charset=$g4[charset]");
$gmnow = gmdate("D, d M Y H:i:s") . " GMT";
header("Expires: 0"); // rfc2616 - Section 14.21
header("Last-Modified: " . $gmnow);
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: pre-check=0, post-check=0, max-age=0"); // HTTP/1.1
header("Pragma: no-cache"); // HTTP/1.0

if(defined('admin')){ // admin 페이지에서만 document type 변경 ?>
<!-- <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"> -->
<?php }else{?><!doctype html>
<?php }?>
<html lang="ko">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="shortcut icon" type="image/x-icon" href="<?php echo $g4['path'];?>/favicon.ico">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?/*브라우저 캐쉬 끄기 메타 태그(리뉴얼로 때문에 잠시 걸어둠) 2014-10-23 홍민기*/?>
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">

<?php echo $meta_tag;?>
<title><?php echo ($g4['title'])?mb_substr($g4['title'],0,90,'utf-8').'-':'';?>오플닷컴 No.1 미국 직배송 건강식품 쇼핑몰 </title>
<?/*
<!--[if lt IE 9]>
<script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js">IE7_PNG_SUFFIX=".png";</script>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
*/?>

<link rel="stylesheet" href="<?php echo $g4['path'];?>/style_test.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $g4['path'];?>/css2/jquery.autocomplete.css"  type="text/css"/>

<script type="text/javascript">
// 자바스크립트에서 사용하는 전역변수 선언
var g4_path      = "<?php echo $g4['path'];?>";
var g4_bbs       = "<?php echo $g4['bbs'];?>";
var g4_bbs_img   = "<?php echo $g4['bbs_img'];?>";
var g4_url       = "<?php echo $g4['url'];?>";
var g4_is_member = "<?php echo $is_member;?>";
var g4_is_admin  = "<?php echo $is_admin;?>";
var g4_bo_table  = "<?php echo isset($bo_table)?$bo_table:'';?>";
var g4_sca       = "<?php echo isset($sca)?$sca:'';?>";
var g4_charset   = "<?php echo $g4['charset'];?>";
var g4_cookie_domain = "<?php echo $g4['cookie_domain'];?>";
var g4_is_gecko  = navigator.userAgent.toLowerCase().indexOf("gecko") != -1;
var g4_is_ie     = navigator.userAgent.toLowerCase().indexOf("msie") != -1;
var g4_cf_title = "<?php echo $config['cf_title']?>";
<?php if ($is_admin) { echo "var g4_admin = '{$g4['admin']}';"; } ?>
</script>
<script type="text/javascript" src="<?php echo $g4['path'];?>/js2/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="<?php echo $g4['path'];?>/js2/jquery.lazyload.min.js"></script>
<script type="text/javascript">
$(function() {
<?php
	// 화면에 보여지는 이미지 부터 로드 2014-10-08 홍민기
?>
	$('img[data-original]').lazyload({
		thresold : 1100,
		placeholder : '<?php echo $g4['path'];?>/img/loding_image.gif',
//		event : 'mouseover',
		skip_invisible : false,
		failure_limit : 10,
		effect : 'fadeIn'
	});

});

</script>
<script type="text/javascript" src="<?php echo $g4['path'];?>/js2/jquery.easing.1.3.js"></script>
<script type="text/javascript" src="<?php echo $g4['path'];?>/js2/jquery.autocomplete.pack.js"></script>
<script type="text/javascript" src="<?php echo $g4['path'];?>/js2/script.js"></script>
<script type="text/javascript" src="<?php echo $g4['path'];?>/js2/common.js"></script>
<?php
/*
뭔지 모르겠다 안쓰는것같아서 일단 주석처리
<script charset="utf-8" type="text/javascript">
$(function () {
	var tabContainers = $('div.tabs > div');
	tabContainers.hide().filter(':first').show();
	$('div.tabs ul.tabNavigation a').click(function () {
	tabContainers.hide();
	tabContainers.filter(this.hash).show();
	$('div.tabs ul.tabNavigation a').removeClass('selected');
	$(this).addClass('selected');
	return false;
	}).filter(':first').click();
});
</script>
*/?>
</head>

<body <?php echo isset($g4['body_script']) ? $g4['body_script'] : "";?>>