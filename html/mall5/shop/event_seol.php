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
<style>
img {margin: 0;padding: 0;border: 0}
</style>
<div id="event2012-1">
<h1>노르딕 내추럴스 추천하기 이벤트</h1>
<img src='http://115.68.20.84/main/event_seol_01.jpg' border="none">
<img src='http://115.68.20.84/main/event_seol_02.jpg' border="none">
<a href="<?=$g4['path']?>/shop/item.php?it_id=1359314435"><img src='http://115.68.20.84/main/event_seol_03.jpg' border="none"></a>
<!-- <a href="<?=$g4['path']?>/shop/item.php?it_id=1336430992"><img src='http://115.68.20.84/main/event_seol_04.jpg' border="none"></a> -->
<img src='http://115.68.20.84/main/event_seol_05.jpg'>

<table width="755" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><a href="<?=$g4['path']?>/shop/item.php?it_id=1359082841"><img src='http://115.68.20.84/main/event_seol_06.jpg' border="none"></a></td>
    <td><a href="<?=$g4['path']?>/shop/item.php?it_id=1359083176"><img src='http://115.68.20.84/main/event_seol_07.jpg' border="none"></a></td>
  </tr>
</table>
<table width="755" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><a href="<?=$g4['path']?>/shop/item.php?it_id=1359083328"><img src='http://115.68.20.84/main/event_seol_08.jpg' border="none"></a></td>
    <td><a href="<?=$g4['path']?>/shop/item.php?it_id=1359083574"><img src='http://115.68.20.84/main/event_seol_09.jpg' border="none"></a></td>
  </tr>
</table>

<div style="float:left;display:block"><a href="<?=$g4['path']?>/shop/item.php?it_id=1359083848"><img src='http://115.68.20.84/main/event_seol_10.jpg' border="none"></a></div>
<img src='http://115.68.20.84/main/event_seol_12.jpg' border="none">
</div>



<?
include_once("./_tail.php");
?>
