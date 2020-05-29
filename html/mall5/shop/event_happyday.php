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

<style type="text/css">
a:focus { outline:none; }
.event_header_title {
	position:absolute;
	width:100%;
	text-align:center;
	height:495px;
	left:0;
	background: url(http://115.68.20.84/event/happyday/happyday_event_bg.jpg) repeat-x 0 0;
}
.event_header_title .imageArea{
	display: block;
	width: 1568px;
	margin: auto;
	position:relative;
	z-index:1;
}
.event_header_title .TabArea_event {position:relative;z-index:2;margin-top:-50px;}
.main .contents_sub .Floating_bannerArea {display:none !important;}
#Event_01 {position:absolute;height:10px;}
#Event_02 {position:absolute;height:10px;top:1584px;}
#Event_03 {position:absolute;height:10px;top:3674px;}
#Event_04 {position:absolute;height:10px;bottom:415px;}
</style>

<div class="event_header_title">
    <p class="imageArea">
        <span><img src='http://115.68.20.84/event/happyday/happyday_event_title.jpg' border="0" alt="가정의달 이벤트"></span>
    </p>
    <p class="TabArea_event"><img src='http://115.68.20.84/event/happyday/happyday_event_tab.jpg' alt='이벤트 탭' usemap="#Map3"  border="0" />
      <map name="Map3" id="Map3">
        <area shape="rect" coords="1,3,253,118" href="#Event_01" onfocus="blur();" />
        <area shape="rect" coords="255,4,516,121" href="#Event_02" onfocus="blur();" />
        <area shape="rect" coords="517,3,773,118" href="#Event_03" onfocus="blur();" />
        <area shape="rect" coords="775,3,1028,118" href="#Event_04" onfocus="blur();" />
      </map>
  </p>
</div>

<div style="position:relative;width:1030px;padding-top:580px;">
<p id="Event_01"></p>
<p id="Event_02"></p>
<p id="Event_03"></p>
<p id="Event_04"></p>
<p><img src='http://115.68.20.84/event/happyday/happyday_Procuct_item.jpg' usemap="#Map2" border="0">
  <map name="Map2" id="Map2">
    <area shape="rect" coords="11,153,347,576" href="<?=$g4['shop_path']?>/item.php?it_id=1504317938" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="347,153,683,578" href="<?=$g4['shop_path']?>/item.php?it_id=1504318238" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="684,152,1020,576" href="<?=$g4['shop_path']?>/item.php?it_id=1504318138" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="12,577,684,1000" href="<?=$g4['shop_path']?>/item.php?it_id=1504318038" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="685,576,1021,1001" href="<?=$g4['shop_path']?>/item.php?it_id=1504318338" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="12,1229,348,1656" href="<?=$g4['shop_path']?>/item.php?it_id=1504318638" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="349,1231,683,1655" href="<?=$g4['shop_path']?>/item.php?it_id=1504318538" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="683,1230,1021,1654" href="<?=$g4['shop_path']?>/item.php?it_id=1504318438" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="10,1721,261,2144" href="<?=$g4['shop_path']?>/item.php?it_id=1504319038" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="262,1718,512,2144" href="<?=$g4['shop_path']?>/item.php?it_id=1504318938" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="512,1717,766,2145" href="<?=$g4['shop_path']?>/item.php?it_id=1504318838" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="766,1718,1021,2144" href="<?=$g4['shop_path']?>/item.php?it_id=1504318738" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="11,2215,344,2639" href="<?=$g4['shop_path']?>/item.php?it_id=1504319538" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="345,2213,682,2639" href="<?=$g4['shop_path']?>/item.php?it_id=1504319438" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="681,2211,1019,2639" href="<?=$g4['shop_path']?>/item.php?it_id=1504319338" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="11,2640,346,3061" href="<?=$g4['shop_path']?>/item.php?it_id=1504319238" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="346,2639,1021,3061" href="<?=$g4['shop_path']?>/item.php?it_id=1504319138" target="_blank" onfocus="blur();" />
    <area shape="rect" coords="697,3466,972,3522"  href="<?=$g4['shop_path']?>/search.php?it_maker=Nordic+Naturals&sh_s_id%5B%5D=3&ca_id%5B%5D=10&ca_id%5B%5D=11&ca_id%5B%5D=12&ca_id%5B%5D=13&ca_id%5B%5D=14&ca_id%5B%5D=15&ca_id%5B%5D=16&ca_id%5B%5D=17&sh_s_id%5B%5D=5&ca_id%5B%5D=33&sh_s_id%5B%5D=4&ca_id%5B%5D=24" target="_blank" onfocus="blur();" />
  </map>
</p>
</div>


<?
include_once("./_tail.php");
?>
