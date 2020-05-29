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

<div style="width:1030px;margin:20px auto;">
<p style='padding:0;margin:0;'>
<img src='http://115.68.20.84/event/event_hair.jpg' usemap="#Map" border="none">
<map name="Map" id="Map">
<area shape="rect" coords="5,695,518,1086" target='_blank' onfoucs="this.blur();" href="<?=$g4['path'];?>/shop/item.php?it_id=1416928965" />
<area shape="rect" coords="520,693,756,1084" target='_blank' onfoucs="this.blur();" href="<?=$g4['path'];?>/shop/item.php?it_id=1355734573" />
<area shape="rect" coords="763,694,1004,1088" target='_blank' onfoucs="this.blur();" href="<?=$g4['path'];?>/shop/item.php?it_id=1340751332" />
<area shape="rect" coords="6,1175,242,1580" target='_blank' onfoucs="this.blur();" href="<?=$g4['path'];?>/shop/item.php?it_id=1416325774" />
<area shape="rect" coords="245,1174,506,1581" target='_blank' onfoucs="this.blur();" href="<?=$g4['path'];?>/shop/item.php?it_id=1355730655" />
<area shape="rect" coords="509,1176,762,1580" target='_blank' onfoucs="this.blur();" href="<?=$g4['path'];?>/shop/item.php?it_id=1355730339" />
<area shape="rect" coords="766,1174,1022,1579" target='_blank' onfoucs="this.blur();" href="<?=$g4['path'];?>/shop/item.php?it_id=1228738337" />
<area shape="rect" coords="6,1687,239,2095" target='_blank' onfoucs="this.blur();" href="<?=$g4['path'];?>/shop/item.php?it_id=1371081428" />
<area shape="rect" coords="247,1682,507,2096" target='_blank' onfoucs="this.blur();" href="<?=$g4['path'];?>/shop/item.php?it_id=1221808631" />
<area shape="rect" coords="512,1681,760,2095" target='_blank' onfoucs="this.blur();" href="<?=$g4['path'];?>/shop/item.php?it_id=1376595907" />
<area shape="rect" coords="764,1678,1016,2095" target='_blank' onfoucs="this.blur();" href="<?=$g4['path'];?>/shop/item.php?it_id=1388380696" />
</map>
</p>
</div>

<? 
// display_event('10', 1400140449 , 4, 4, 160, 160, ''); 
?>

<?
include_once("./_tail.php");
?>
