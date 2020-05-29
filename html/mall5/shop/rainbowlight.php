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
<div id="event2012-1">
<h1>레인보우라이트 특가전 온라인최저가 Rainbow Light</h1>
<img src='http://115.68.20.84/main/rainbowlight_01.jpg'>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1267140717"><img src='http://115.68.20.84/main/rainbowlight_02.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1267142668"><img src='http://115.68.20.84/main/rainbowlight_03.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1185565798"><img src='http://115.68.20.84/main/rainbowlight_04.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1186260572"><img src='http://115.68.20.84/main/rainbowlight_05.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1196391803"><img src='http://115.68.20.84/main/rainbowlight_06.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1186261115"><img src='http://115.68.20.84/main/rainbowlight_07.jpg'></a>
<img src='http://115.68.20.84/main/rainbowlight_08.jpg'>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1273540705"><img src='http://115.68.20.84/main/rainbowlight_11.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1273540952"><img src='http://115.68.20.84/main/rainbowlight_12.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1273540461"><img src='http://115.68.20.84/main/rainbowlight_13.jpg'></a>
<img src='http://115.68.20.84/main/rainbowlight_14.jpg'>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1330609760"><img src='http://115.68.20.84/main/rainbowlight_17.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1326751649"><img src='http://115.68.20.84/main/rainbowlight_18.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1327442669"><img src='http://115.68.20.84/main/rainbowlight_19.jpg'></a>
<img src='http://115.68.20.84/main/rainbowlight_20.jpg'>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1201512556"><img src='http://115.68.20.84/main/rainbowlight_21.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1308747964"><img src='http://115.68.20.84/main/rainbowlight_22.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1186261562"><img src='http://115.68.20.84/main/rainbowlight_23.jpg'></a>
<a href="<?=$g4['path']?>/shop/event.php?ev_id=1350622987"><img src='http://115.68.20.84/main/rainbowlight_btn.jpg'></a>
</div>
<!-- </table> -->


<?
include_once("./_tail.php");
?>
