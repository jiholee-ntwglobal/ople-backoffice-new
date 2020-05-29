<?
include_once("./_common.php");

// 김선용 201006 : 전역변수 XSS/인젝션 보안강화 및 방어
include_once "sjsjin.shop_guard.php";
//guard_script1($search_str);

// 상품이미지 사이즈(폭, 높이)를 몇배 축소 할것인지를 설정
// 0 으로 설정하면 오류남 : 기본 2
$image_rate = 2;

$g4[title] = "상품 검색";
include_once("./_head.php");


// 김선용 201206 : 서제스트 쿼리에서 이스케이프 처리돼서 넘어옴
//$search_str = (trim($_GET['search_str']) != '' ? mysql_real_escape_string($_GET['search_str']) : '');
$search_str = (trim($_GET['search_str']) != '' ? $_GET['search_str'] : '');
$it_maker = (trim($_GET['it_maker']) != '' ? mysql_real_escape_string(stripslashes($_GET['it_maker'])) : '');
?>
<script type="text/javascript">
if(document.getElementById('search-input'))
	document.getElementById('search-input').value = "<?=stripslashes($_GET['search_str'])?>";
</script>
<!-- <table width=100% cellpadding=0 cellspacing=0 align=center border=0> -->
	<!-- // 김선용 201206 : -->



<img src='http://115.68.20.84/mall6/page/event/nordic_freegift/Nordic_FreeGift.jpg' border="0">

<br/><br/>

<img src='http://115.68.20.84/mall6/page/event/nordic_freegift/nordic_title01.jpg' border="0"><br/><br/>
<? 
display_event('10', 1403855488 , 4, 10, 180, 180, ''); 
?>
<br/><br/>
<img src='http://115.68.20.84/mall6/page/event/nordic_freegift/nordic_title02.jpg' border="0"><br/><br/>
<? 
display_event('10', 1403858892 , 4, 10, 180, 180, ''); 
?>
<br/><br/>
<img src='http://115.68.20.84/mall6/page/event/nordic_freegift/nordic_title03.jpg' border="0"><br/><br/>
<? 
display_event('10', 1403859681 , 4, 10, 180, 180, ''); 
?>
<br/><br/>
<img src='http://115.68.20.84/mall6/page/event/nordic_freegift/nordic_title04.jpg' border="0"><br/><br/>
<? 
display_event('10', 1403859989 , 4, 10, 180, 180, ''); 
?>
<br/><br/>
<img src='http://115.68.20.84/mall6/page/event/nordic_freegift/nordic_title05.jpg' border="0"><br/><br/>
<? 
display_event('10', 1403860194 , 4, 10, 180, 180, ''); 
?>
<br/><br/>
<img src='http://115.68.20.84/mall6/page/event/nordic_freegift/nordic_title06.jpg' border="0"><br/><br/>
<? 
display_event('10', 1403861458 , 4, 10, 180, 180, ''); 
?>
<br/><br/>
<img src='http://115.68.20.84/mall6/page/event/nordic_freegift/nordic_title07.jpg' border="0"><br/><br/>
<? 
display_event('10', 1403863303 , 4, 10, 180, 180, ''); 
?>
<br/><br/>
<img src='http://115.68.20.84/mall6/page/event/nordic_freegift/nordic_title08.jpg' border="0"><br/><br/>
<? 
display_event('10', 1403863917 , 4, 10, 180, 180, ''); 
?>
<br/><br/>




<?
include_once("./_tail.php");
?>
