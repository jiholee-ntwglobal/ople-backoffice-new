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

<div style="width:1030px;margin:0 auto;">
<p style='padding:0;margin:0;'>
<img src='http://115.68.20.84/event/event_cold.jpg' usemap="#Map" border="none">
<map name="Map" id="Map">
<area shape="rect" coords="521,683,979,1015" href="<?=$g4['path'];?>/shop/item.php?it_id=1398460831"  target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="46,1024,507,1358" href="<?=$g4['path'];?>/shop/item.php?it_id=1370567545" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="518,1025,979,1356" href="<?=$g4['path'];?>/shop/item.php?it_id=1184269759" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="48,1367,509,1699" href="<?=$g4['path'];?>/shop/item.php?it_id=1314862064" target='_blank' onfoucs="this.blur();"/>
<area shape="rect" coords="519,1366,981,1700" href="<?=$g4['path'];?>/shop/item.php?it_id=1219214139" target='_blank' onfoucs="this.blur();"/>
</map>
</p>
</div>

<? 
// display_event('10', 1400140449 , 4, 4, 160, 160, ''); 
?>

<?
include_once("./_tail.php");
?>
