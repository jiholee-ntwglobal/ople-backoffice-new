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
<img src='http://115.68.20.84/mailing/mailing_bragg.jpg' usemap="#Map" border="none">
<map name="Map" id="Map">
<area shape="rect" coords="33,1520,360,1884" href="<?php echo $g4['shop_path']; ?>/item.php?it_id=1378920537" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="381,1519,708,1882" href="<?php echo $g4['shop_path']; ?>/item.php?it_id=1378920716" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="35,1900,359,2260" href="<?php echo $g4['shop_path']; ?>/item.php?it_id=1378920954" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="386,1898,710,2260" href="<?php echo $g4['shop_path']; ?>/item.php?it_id=1378921398" target='_blank' onfoucs="this.blur();"/>
</map>
</p>
</div>

<? 
// display_event('10', 1400140449 , 4, 4, 160, 160, ''); 
?>

<?
include_once("./_tail.php");
?>
