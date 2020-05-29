
<? phpinfo(); exit;
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

<div class="slider-wrapper">
<div id="slider1" class="nivoSlider">
<a href="<?=$g4['path']?>/shop/winners.php"><img src="http://115.68.20.84/main/winners.gif" alt="" /></a>
<a href="<?=$g4['path']?>/shop/event.php?ev_id=1351843377"><img src="http://115.68.20.84/main/banner_ev11.jpg" alt=""/></a>
<a href="<?=$g4['path']?>/shop/rainbowlight.php"><img src="http://115.68.20.84/main/rainbow_banner.jpg" alt=""/></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1332425915"><img src="http://115.68.20.84/main/banner-a201.jpg" alt=""/></a>
<!-- <a href="<?=$g4['path']?>/shop/item.php?it_id=1344452661"><img src="http://115.68.20.84/main/banner-a4.jpg"alt="" /></a> -->
</div>
</div>
<div id="htmlcaption" class="nivo-html-caption">
    <strong>This</strong> is an example of a <em>HTML</em> caption with <a href="#">a link</a>.
</div>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?=$g4['path']?>/js/jquery.nivo.slider.pack.js" ></script>
	<script type="text/javascript">
	$(window).load(function() {
	    $('#slider1').nivoSlider();
	});
	</script>

<?
include_once("./_tail.php");
?>

