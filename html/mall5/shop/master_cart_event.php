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


<img src='http://115.68.20.84/event/master_card/mastercard_event_add_samsung_win.jpg' border="0" usemap="#master">

<map name="master">
  <area shape="rect" coords="180,985,438,1152" href="https://www.samsungcard.com/link/index_affiliate_pre.jsp?url=typeCardDetail?KIND_CODE=P1416&SITE=ople1412&INFO=bbb&MEM_CODE=ccc&cmpid=ople1412_jaehu_141212" target="_blank" onfocus="this.blur()">
  <area shape="rect" coords="790,1774,928,1818" href="<?=$g4['shop_path']?>/master_cart_event_chu.php"  target="_blank" onfocus="this.blur()"/>
  <area shape="rect" coords="252,1834,834,1901" href="<?=$g4['shop_path']?>/master_cart_event_win.php"  target="_blank" onfocus="this.blur()" />
</map>


<?
include_once("./_tail.php");
?>
