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
<h1>감기예방을 위한 면역력 강화 제품 </h1>
<img src='http://115.68.20.84/main/immune_h1.jpg'>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1357627213"><img src='http://115.68.20.84/main/immune_01.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1341953195"><img src='http://115.68.20.84/main/immune_02.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1350329814"><img src='http://115.68.20.84/main/immune_03.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1235381848"><img src='http://115.68.20.84/main/immune_04.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1310410495"><img src='http://115.68.20.84/main/immune_05.jpg'></a>
<img src='http://115.68.20.84/main/immune_h2.jpg'>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1320798945"><img src='http://115.68.20.84/main/immune_06.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1203570428"><img src='http://115.68.20.84/main/immune_07.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1262822014"><img src='http://115.68.20.84/main/immune_08.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1341339753"><img src='http://115.68.20.84/main/immune_09.jpg'></a>
<a href="<?=$g4['path']?>/shop/item.php?it_id=1228464089"><img src='http://115.68.20.84/main/immune_10.jpg'></a>
</div>
<!-- </table> -->


<?
include_once("./_tail.php");
?>
