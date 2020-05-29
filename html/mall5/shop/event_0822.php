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

<div style="width:755px;margin:0 auto;">
<p style="padding:0;margin:0;">
<img src='http://115.68.20.84/mailing/mailing_superfood.jpg' usemap="#Map" border="none">
<map name="Map" id="Map">
<area shape="rect" coords="14,708,250,1022" href="<?=$g4['shop_path']?>/item.php?it_id=1361925870" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="261,708,494,1021" href="<?=$g4['shop_path']?>/item.php?it_id=1407144057" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="508,710,741,1022" href="<?=$g4['shop_path']?>/item.php?it_id=1313786511" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="17,1289,251,1603" href="<?=$g4['shop_path']?>/item.php?it_id=1361928301" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="261,1293,497,1605" href="<?=$g4['shop_path']?>/item.php?it_id=1398908786" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="509,1292,742,1603" href="<?=$g4['shop_path']?>/item.php?it_id=1331219924" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="17,1842,248,2150" href="<?=$g4['shop_path']?>/item.php?it_id=1407172731" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="260,1840,496,2153" href="<?=$g4['shop_path']?>/item.php?it_id=1402029002" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="506,1842,742,2155" href="<?=$g4['shop_path']?>/item.php?it_id=1400878220" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="15,2386,740,2699" href="<?=$g4['shop_path']?>/item.php?it_id=1343682748" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="15,2939,251,3251" href="<?=$g4['shop_path']?>/item.php?it_id=1402042870" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="260,2940,497,3252" href="<?=$g4['shop_path']?>/item.php?it_id=1402036876" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="506,2937,743,3251" href="<?=$g4['shop_path']?>/item.php?it_id=1402036739" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="13,3447,251,3761" href="<?=$g4['shop_path']?>/item.php?it_id=1355991400" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="261,3445,498,3761" href="<?=$g4['shop_path']?>/item.php?it_id=1350332819" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="14,4015,251,4327" href="<?=$g4['shop_path']?>/item.php?it_id=1368484111" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="261,4017,496,4328" href="<?=$g4['shop_path']?>/item.php?it_id=1388384434" target='_blank' onfoucs="this.blur();"/>
</map>
</p>
</div>

<? 
// display_event('10', 1400140449 , 4, 4, 160, 160, ''); 
?>

<?
include_once("./_tail.php");
?>
