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

# 레인보우라이트 구미 비타민C 슬라이스 가격이 이벤트가가 아니면 솔드아웃 이미지로 대체 2014-12-26 홍민기 #
$gummy_c_chk = sql_fetch("select it_amount from ".$g4['yc4_item_table']." where it_id = '1328910099'");
if($gummy_c_chk['it_amount'] != '6700'){
	$top_img_src = "http://115.68.20.84/event/event_boxingdaySale_out.jpg";
}else{
	$top_img_src = "http://115.68.20.84/event/event_boxingdaySale.jpg";
}
?>
<script type="text/javascript">
if(document.getElementById('search-input'))
	document.getElementById('search-input').value = "<?=stripslashes($_GET['search_str'])?>";
</script>
<!-- <table width=100% cellpadding=0 cellspacing=0 align=center border=0> -->
	<!-- // 김선용 201206 : -->

<style type="text/css">
	a:focus { outline:none; background-color:whitesmoke; }
</style>
<div style="width:1100px;margin:20px auto;">
<img src='<?=$top_img_src;?>' border="none" usemap="#sale">
<map name="sale">
<area shape="rect" coords="35,546,334,983" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1337803621" target="_blank" />
<area shape="rect" coords="370,546,668,983" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1328910099" target="_blank" />
<area shape="rect" coords="702,545,1000,979" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1391014331" target="_blank" />
<area shape="rect" coords="35,1009,335,1445" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1181760429" target="_blank" />
<area shape="rect" coords="367,1010,671,1446" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1395792795" target="_blank" />
<area shape="rect" coords="701,1011,1002,1445" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1357627213" target="_blank" />
<area shape="rect" coords="35,1474,335,1909" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1418201685" target="_blank" />
<area shape="rect" coords="369,1475,669,1911" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1314862064" target="_blank" />
<area shape="rect" coords="699,1473,1003,1909" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1221544887" target="_blank" />
<area shape="rect" coords="35,1940,337,2375" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1387519473" target="_blank" />
<area shape="rect" coords="368,1940,669,2376" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1411161134" target="_blank" />
<area shape="rect" coords="700,1939,1002,2375" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1411160321" target="_blank" />
<area shape="rect" coords="35,2403,336,2839" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1359950869" target="_blank" />
<area shape="rect" coords="368,2404,671,2841" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1410144442" target="_blank" />
<area shape="rect" coords="700,2404,1003,2839" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1412162106" target="_blank" />
</map>
</div>



<?
include_once("./_tail.php");
?>
