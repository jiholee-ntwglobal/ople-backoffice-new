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

<style type="text/css">
a:focus { outline:none; }
</style>

<div style="width:1030px;">
<span><img src='http://115.68.20.84/event/blackFriday/event_header_cybermonday.jpg' border="0" usemap="#black_event"></span>
<map name="black_event" id="black_event">
  <area shape="rect" coords="4,557,340,621" href="#healthZone" />
  <area shape="rect" coords="344,558,684,622" href="#lifeZone" />
  <area shape="rect" coords="686,557,1024,620" href="#foodZone" />
</map>
</div>

<div style="width:1030px;" id="healthZone">
<p><img src='http://115.68.20.84/event/blackFriday/cyber_title_01.jpg' border="0"></p>
<? 
display_event('10', 1417162374 , 4, 20, 200, 200, ''); 
?>
</div>

<div style="width:1030px;">
<p><img src='http://115.68.20.84/event/blackFriday/cyber_title_02.jpg' border="0"></p>
<? 
display_event('10', 1417162401 , 4, 20, 200, 200, ''); 
?>
</div>

<div style="width:1030px;">
<p><img src='http://115.68.20.84/event/blackFriday/cyber_title_03.jpg' border="0"></p>
<? 
display_event('10', 1417162418 , 4, 20, 200, 200, ''); 
?>
</div>

<div style="width:1030px;">
<p><img src='http://115.68.20.84/event/blackFriday/cyber_title_04.jpg' border="0"></p>
<? 
display_event('10', 1417162433 , 4, 20, 200, 200, ''); 
?>
</div>

<div style="width:1030px;">
<p><img src='http://115.68.20.84/event/blackFriday/cyber_title_05.jpg' border="0"></p>
<? 
display_event('10', 1417162459 , 4, 20, 200, 200, ''); 
?>
</div>

<div style="width:1030px;">
<p><img src='http://115.68.20.84/event/blackFriday/cyber_title_06.jpg' border="0"></p>
<? 
display_event('10', 1417162478 , 4, 20, 200, 200, ''); 
?>
</div>

<div style="width:1030px;">
<p><img src='http://115.68.20.84/event/blackFriday/cyber_title_07.jpg' border="0"></p>
<? 
display_event('10', 1417162498 , 4, 20, 200, 200, ''); 
?>
</div>

<div style="width:1030px;">
<p><img src='http://115.68.20.84/event/blackFriday/cyber_title_08.jpg' border="0"></p>
<? 
display_event('10', 1417162514 , 4, 20, 200, 200, ''); 
?>
</div>


<div style="width:1030px;">
<p><img src='http://115.68.20.84/event/blackFriday/cyber_title_10.jpg' border="0"></p>
<? 
display_event('10', 1417162552 , 4, 20, 200, 200, ''); 
?>
</div>

<div style="width:1030px;">
<p><img src='http://115.68.20.84/event/blackFriday/cyber_title_13.jpg' border="0"></p>
<? 
display_event('10', 1417162652 , 4, 20, 200, 200, ''); 
?>
</div>

<div style="width:1030px;" id="lifeZone">
<p><img src='http://115.68.20.84/event/blackFriday/cyber_title_11.jpg' border="0"></p>
<? 
display_event('10', 1417162638 , 4, 20, 200, 200, ''); 
?>
</div>

<div style="width:1030px;" id="foodZone">
<p><img src='http://115.68.20.84/event/blackFriday/cyber_title_12.jpg' border="0"></p>
<? 
display_event('10', 1417162651 , 4, 20, 200, 200, ''); 
?>
</div>


<?
include_once("./_tail.php");
?>
