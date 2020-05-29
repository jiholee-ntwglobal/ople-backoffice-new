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


<img src='http://115.68.20.84/event/han_evnet_b.jpg' border="0" usemap="#han_event">
<map id="han_event" name="han_event">
<area onfocus="this.blur();" href="<?php echo $g4['shop_path']; ?>/han_event.php" shape="rect" coords="0,0,228,186">
<area onfocus="this.blur();" href="<?php echo $g4['shop_path']; ?>/han_event_nordic.php" shape="rect" coords="525,0,755,186">
</map>

<img src='http://115.68.20.84/event/han_event_best.jpg' border="0" usemap="#han_event_best">
<map id="han_event_best" name="han_event_best">
<area onfocus="this.blur();" href="#best_1" shape="rect" coords="12,380,134,425">
<area onfocus="this.blur();" href="#best_2" shape="rect" coords="134,380,256,425">
<area onfocus="this.blur();" href="#best_3" shape="rect" coords="256,380,378,425">
<area onfocus="this.blur();" href="#best_4" shape="rect" coords="378,380,500,425">
<area onfocus="this.blur();" href="#best_5" shape="rect" coords="500,380,622,425">
<area onfocus="this.blur();" href="#best_6" shape="rect" coords="622,380,742,425">
<area onfocus="this.blur();" href="#best_7" shape="rect" coords="12,425,134,468">
<area onfocus="this.blur();" href="#best_8" shape="rect" coords="134,425,256,468">
<area onfocus="this.blur();" href="#best_9" shape="rect" coords="256,425,378,468">
<area onfocus="this.blur();" href="#best_10" shape="rect" coords="378,425,500,468">
<area onfocus="this.blur();" href="#best_11" shape="rect" coords="500,425,622,468">
</map>


<img src='http://115.68.20.84/event/best/1.jpg' border="0" id='best_1'><br/>
<? 
display_event('10', 1408674144 , 4, 4, 180, 180, ''); 
?>

<img src='http://115.68.20.84/event/best/2.jpg' border="0" id='best_2'><br/>
<? 
display_event('10', 1408674159 , 4, 4, 180, 180, ''); 
?>

<img src='http://115.68.20.84/event/best/3.jpg' border="0" id='best_3'><br/>
<? 
display_event('10', 1408674242 , 4, 4, 180, 180, ''); 
?>

<img src='http://115.68.20.84/event/best/4.jpg' border="0" id='best_4'><br/>
<? 
display_event('10', 1408674256 , 4, 4, 180, 180, ''); 
?>

<img src='http://115.68.20.84/event/best/5.jpg' border="0" id='best_5'><br/>
<? 
display_event('10', 1408674536 , 4, 4, 180, 180, ''); 
?>

<img src='http://115.68.20.84/event/best/6.jpg' border="0" id='best_6'><br/>
<? 
display_event('10', 1408674580 , 4, 4, 180, 180, ''); 
?>

<img src='http://115.68.20.84/event/best/7.jpg' border="0" id='best_7'><br/>
<? 
display_event('10', 1408674604 , 4, 4, 180, 180, ''); 
?>

<img src='http://115.68.20.84/event/best/8.jpg' border="0" id='best_8'><br/>
<? 
display_event('10', 1408674634 , 4, 4, 180, 180, ''); 
?>

<img src='http://115.68.20.84/event/best/9.jpg' border="0" id='best_9'><br/>
<? 
display_event('10', 1408674657 , 4, 4, 180, 180, ''); 
?>

<img src='http://115.68.20.84/event/best/10.jpg' border="0" id='best_10'><br/>
<? 
display_event('10', 1408674690 , 4, 4, 180, 180, ''); 
?>

<img src='http://115.68.20.84/event/best/11.jpg' border="0" id='best_11'><br/>
<? 
display_event('10', 1408674708 , 4, 4, 180, 180, ''); 
?>



<?
include_once("./_tail.php");
?>
