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
<h1>노르딕 내추럴스 애완동물용 오메가-3</h1>
<img src='http://115.68.20.84/main/nordicpet_01.jpg'>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1299103645"><img src='http://115.68.20.84/main/nordicpet_03.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1336073191"><img src='http://115.68.20.84/main/nordicpet_04.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1299103579"><img src='http://115.68.20.84/main/nordicpet_05.jpg'></a>
</div>
<!-- </table> -->


<?
include_once("./_tail.php");
?>
