<?
include_once("./_common.php");
echo 1;
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

<style type="text/css">
	a:focus { outline:none; background-color:whitesmoke; }
</style>
<div style="width:1100px;margin:20px auto;">
<img src='http://115.68.20.84/goodday/ople_goodaysale.jpg' border="none" usemap="#sale">
<map name="sale">
<area shape="rect" coords="825,5280,916,5376" onFocus="this.blur()" href="#" onclick="oneday_sms_popup(); return false;" target="_blank" />
<area shape="rect" coords="275,431,516,915" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1228474371" target="_blank" />
<area shape="rect" coords="519,429,760,913" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1407174301" target="_blank" />
<area shape="rect" coords="762,429,1001,914" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1382557304" target="_blank" />
<area shape="rect" coords="31,1034,271,1518" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1503154227" target="_blank" />
<area shape="rect" coords="277,1033,515,1518" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1503155353" target="_blank" />
<area shape="rect" coords="519,1035,760,1519" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1503155804" target="_blank" />
<area shape="rect" coords="761,1033,1001,1519" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1411223323" target="_blank" />
<area shape="rect" coords="30,1638,271,2125" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1411213427" target="_blank" />
<area shape="rect" coords="274,1638,516,2125" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1411214011" target="_blank" />
<area shape="rect" coords="519,1639,760,2123" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1411214344" target="_blank" />
<area shape="rect" coords="762,1640,1003,2124" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1411214658" target="_blank" />
<area shape="rect" coords="31,2244,272,2730" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1502155542" target="_blank" />
<area shape="rect" coords="275,2243,514,2729" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1502154433" target="_blank" />
<area shape="rect" coords="520,2244,757,2730" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1411160817" target="_blank" />
<area shape="rect" coords="762,2246,1003,2729" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1411155422" target="_blank" />
<area shape="rect" coords="31,2738,270,3222" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1401918014" target="_blank" />
<area shape="rect" coords="275,2736,515,3222" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1401917959" target="_blank" />
<area shape="rect" coords="517,2737,758,3221" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1398883965" target="_blank" />
<area shape="rect" coords="763,2738,1002,3223" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1398883911" target="_blank" />
<area shape="rect" coords="32,3229,272,3716" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1398893771" target="_blank" />
<area shape="rect" coords="276,3229,514,3714" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1387492294" target="_blank" />
<area shape="rect" coords="518,3229,757,3714" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1387491885" target="_blank" />
<area shape="rect" coords="761,3230,1002,3714" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1387492192" target="_blank" />
<area shape="rect" coords="31,3722,270,4207" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1392164843" target="_blank" />
<area shape="rect" coords="275,3723,514,4206" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1504141457" target="_blank" />
<area shape="rect" coords="519,3721,758,4207" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1504142405" target="_blank" />
<area shape="rect" coords="762,3721,999,4207" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1407173753" target="_blank" />
<area shape="rect" coords="31,4218,271,4701" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1504144751" target="_blank" />
<area shape="rect" coords="275,4218,514,4702" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1407143545" target="_blank" />
<area shape="rect" coords="520,4217,757,4701" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1398153903" target="_blank" />
<area shape="rect" coords="764,4219,1002,4702" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1398153700" target="_blank" />
<area shape="rect" coords="31,4713,271,5197" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1398152853" target="_blank" />
<area shape="rect" coords="276,4713,514,5198" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1398117034" target="_blank" />
<area shape="rect" coords="519,4711,758,5196" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1398115697" target="_blank" />
<area shape="rect" coords="763,4712,999,5197" onFocus="this.blur()" href="<?=$g4['shop_path']?>/item.php?it_id=1398116200" target="_blank" />
</map>
</div>

<?
include_once("./_tail.php");
?>
