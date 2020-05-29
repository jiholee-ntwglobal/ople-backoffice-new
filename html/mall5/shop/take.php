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

<div style='text-align:center;'>
<img src='http://115.68.20.84/main/take.jpg' usemap="#take" border="none">
<map name="take">
  <area shape="rect" coords="478,226,725,508" href="<?=$g4['path'];?>/shop/item.php?it_id=1306248387" target="_blank" onfocus="this.blur();"/>
  <area shape="rect" coords="45,426,257,754" href="<?=$g4['path'];?>/shop/item.php?it_id=1336079729" target="_blank" onfocus="this.blur();"/>
  <area shape="rect" coords="502,653,674,1003" href="<?=$g4['path'];?>/shop/item.php?it_id=1345580847" target="_blank"onfocus="this.blur();" />
  <area shape="rect" coords="65,914,240,1212" href="<?=$g4['path'];?>/shop/item.php?it_id=1352846561" target="_blank" onfocus="this.blur();"/>
  <area shape="rect" coords="472,1140,713,1487" href="<?=$g4['path'];?>/shop/item.php?it_id=1270847266" target="_blank" onfocus="this.blur();"/>
  <area shape="rect" coords="52,1364,245,1698" href="<?=$g4['path'];?>/shop/item.php?it_id=1368506592" target="_blank" onfocus="this.blur();"/>
  <area shape="rect" coords="464,1632,722,1970" href="<?=$g4['path'];?>/shop/item.php?it_id=1314862063" target="_blank" onfocus="this.blur();"/>
  <area shape="rect" coords="60,1860,241,2188" href="<?=$g4['path'];?>/shop/item.php?it_id=1176403890" target="_blank" onfocus="this.blur();"/>
</map>
</div>



<?
include_once("./_tail.php");
?>
