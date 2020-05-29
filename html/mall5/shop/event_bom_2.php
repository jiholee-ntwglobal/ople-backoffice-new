<?
include_once("./_common.php");

// 김선용 201006 : 전역변수 XSS/인젝션 보안강화 및 방어
include_once "./sjsjin.shop_guard.php";
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

<div style="width:755px;">
<img src='http://115.68.20.84/main/event_bom2_top.jpg' usemap="#event_bom2" border="none">
<map name="event_bom2">
<area shape="rect" coords="8,272,109,415" href="#title_1" onFocus="this.blur();">
<area shape="rect" coords="117,272,218,415" href="#title_2" onFocus="this.blur();">
<area shape="rect" coords="222,272,323,415" href="#title_3" onFocus="this.blur();">
<area shape="rect" coords="329,272,430,415" href="#title_4" onFocus="this.blur();">
<area shape="rect" coords="433,272,534,415" href="#title_5" onFocus="this.blur();">
<area shape="rect" coords="539,272,640,415" href="#title_6" onFocus="this.blur();">
<area shape="rect" coords="644,273,745,416" href="#title_7" onFocus="this.blur();">
<area shape="rect" coords="11,446,112,589" href="#title_8" onFocus="this.blur();">
<area shape="rect" coords="117,446,218,589" href="#title_9" onFocus="this.blur();">
<area shape="rect" coords="222,446,323,589" href="#title_10" onFocus="this.blur();">
<area shape="rect" coords="326,445,427,588" href="#title_11" onFocus="this.blur();">
<area shape="rect" coords="434,446,535,589" href="#title_12" onFocus="this.blur();">
<area shape="rect" coords="538,446,639,589" href="#title_13" onFocus="this.blur();">
<area shape="rect" coords="645,446,746,589" href="#title_14" onFocus="this.blur();">
</map>


<img src='http://115.68.20.84/main/event_bom2_title_01.jpg' border="none"> 
<a name="title_1" id="title_1"></a>
<br><br>
<? display_event('10', 1396229349 , 4, 4, 160, 160, ''); ?>


<br><br><br><br><br><img src='http://115.68.20.84/main/event_bom2_title_02.jpg' border="none"> 
<a name="title_2" id="title_2"></a>
<br><br>
<? display_event('10', 1396230035 , 4, 4, 160, 160, ''); ?>


<br><br><br><br><br><img src='http://115.68.20.84/main/event_bom2_title_03.jpg' border="none"> 
<a name="title_3" id="title_3"></a>
<br><br>
<? display_event('10', 1396230532 , 4, 4, 160, 160, ''); ?>


<br><br><br><br><br><img src='http://115.68.20.84/main/event_bom2_title_04.jpg' border="none"> 
<a name="title_4" id="title_4"></a>
<br><br>
<? display_event('10', 1396230991 , 4, 4, 160, 160, ''); ?>


<br><br><br><br><br><img src='http://115.68.20.84/main/event_bom2_title_05.jpg' border="none"> 
<a name="title_5" id="title_5"></a>
<br><br>
<? display_event('10', 1396233451 , 4, 4, 160, 160, ''); ?>


<br><br><br><br><br><img src='http://115.68.20.84/main/event_bom2_title_06.jpg' border="none"> 
<a name="title_6" id="title_6"></a>
<br><br>
<? display_event('10', 1396233841 , 4, 4, 160, 160, ''); ?>


<br><br><br><br><br><img src='http://115.68.20.84/main/event_bom2_title_07.jpg' border="none"> 
<a name="title_7" id="title_7"></a>
<br><br>
<? display_event('10', 1396234921 , 4, 4, 160, 160, ''); ?>


<br><br><br><br><br><img src='http://115.68.20.84/main/event_bom2_title_08.jpg' border="none"> 
<a name="title_8" id="title_8"></a>
<br><br>
<? display_event('10', 1396235046 , 4, 4, 160, 160, ''); ?>


<br><br><br><br><br><img src='http://115.68.20.84/main/event_bom2_title_09.jpg' border="none"> 
<a name="title_9" id="title_9"></a>
<br><br>
<? display_event('10', 1396235438 , 4, 4, 160, 160, ''); ?>


<br><br><br><br><br><img src='http://115.68.20.84/main/event_bom2_title_10.jpg' border="none"> 
<a name="title_10" id="title_10"></a>
<br><br>
<? display_event('10', 1396235548 , 4, 4, 160, 160, ''); ?>


<br><br><br><br><br><img src='http://115.68.20.84/main/event_bom2_title_11.jpg' border="none"> 
<a name="title_11" id="title_11"></a>
<br><br>
<? display_event('10', 1396235877 , 4, 4, 160, 160, ''); ?>


<br><br><br><br><br><img src='http://115.68.20.84/main/event_bom2_title_12.jpg' border="none"> 
<a name="title_12" id="title_12"></a>
<br><br>
<? display_event('10', 1396236254 , 4, 4, 160, 160, ''); ?>


<br><br><br><br><br><img src='http://115.68.20.84/main/event_bom2_title_13.jpg' border="none">
<a name="title_13" id="title_13"></a> 
<br><br>
<? display_event('10', 1396236390 , 4, 4, 160, 160, ''); ?>


<br><br><br><br><br><img src='http://115.68.20.84/main/event_bom2_title_14.jpg' border="none"> 
<a name="title_14" id="title_14"></a>
<br><br>
<? display_event('10', 1396236657 , 4, 4, 160, 160, ''); ?>
</div>



<?
include_once("./_tail.php");
?>
