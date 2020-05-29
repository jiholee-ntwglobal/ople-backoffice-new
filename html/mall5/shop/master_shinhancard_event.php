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


<img src='http://115.68.20.84/event/master_shinhan_event4.jpg' usemap="#Map" border="0" >
<map name="Map" id="Map">
  <area shape="rect" coords="191,742,414,781" href="https://www.shinhancard.com/conts/person/card_info/major/business/buy/1247584_12913.jsp?EntryLoc=2735&empSeq=15" onFocus='blur()' target="_blank"/>
  <area shape="rect" coords="604,739,811,780" href="https://www.shinhancard.com/conts/person/card_info/rookie/benefit/buy/1252727_13344.jsp?EntryLoc=2736&empSeq=15" onFocus='blur()' target="_blank"/>
  <area shape="rect" coords="673,995,847,1030" href="https://www.shinhancard.com/conts/person/event/event/1255917_14937.jsp" onFocus='blur()' target="_blank" />
</map>
<?
include_once("./_tail.php");
?>
