
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



<div id="sliderContainer">
<div id="mySlides">
	<div id="slide1" class="slide"><a href="<?=$g4['path']?>/shop/winners.php"><img src="http://115.68.20.84/main/winners.gif" alt="" /></a><div class="slideContent"></div></div>
   <div id="slide2" class="slide"><a href="<?=$g4['path']?>/shop/event.php?ev_id=1351843377"><img src="http://115.68.20.84/main/banner_ev11.jpg" alt=""/></a><div class="slideContent"></div></div>
   <div id="slide3" class="slide"><a href="<?=$g4['path']?>/shop/rainbowlight.php"><img src="http://115.68.20.84/main/rainbow_banner.jpg" alt=""/></a><div class="slideContent"></div></div>
   <div id="slide4" class="slide"><a href="<?=$g4['path']?>/shop/item.php?it_id=1332425915"><img src="http://115.68.20.84/main/banner-a201.jpg" alt=""/></a><div class="slideContent"></div></div>
   <div id="slide5" class="slide"><a href="<?=$g4['path']?>/shop/item.php?it_id=1344452661"><img src="http://115.68.20.84/main/banner-a4.jpg"alt="" /></a><div class="slideContent"></div></div>
</div>
	<div id="myController">
	   <span class="jFlowControl"></span>
	   <span class="jFlowControl"></span>
	   <span class="jFlowControl"></span> 
	   <span class="jFlowControl"></span>
	   <span class="jFlowControl"></span>
	</div>
		<div class="jFlowPrev"></div>
		<div class="jFlowNext"></div>
</div>
<script type="text/javascript">
if(document.getElementById('search-input'))
	document.getElementById('search-input').value = "<?=stripslashes($_GET['search_str'])?>";
</script>
<script type="text/javascript">
	$(document).ready(function(){
	    $("#myController").jFlow({
			controller: ".jFlowControl", // must be class, use . sign
			slideWrapper : "#jFlowSlider", // must be id, use # sign
			slides: "#mySlides",  // the div where all your sliding divs are nested in
			selectedWrapper: "jFlowSelected",  // just pure text, no sign		
			effect: "flow", //this is the slide effect (rewind or flow)
			width: "755px",  // this is the width for the content-slider
			height: "230px",  // this is the height for the content-slider
			duration: 400,  // time in milliseconds to transition one slide			
			pause: 5000, //time between transitions
			prev: ".jFlowPrev", // must be class, use . sign
			next: ".jFlowNext", // must be class, use . sign
			auto: true	
    });
});
</script>



<?
include_once("./_tail.php");
?>
