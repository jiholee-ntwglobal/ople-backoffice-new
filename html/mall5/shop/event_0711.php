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

<img src="http://115.68.20.84/mailing/mailing_72.jpg" border="0" usemap="#mailing" >
    <map name="mailing">
        <!-- <area shape="rect" coords="0,0,755,1037 " href="<?php echo $g4['shop_path']; ?>/event_0711.php" onfocus="this.blur();" target="_blank" /> -->
        <area shape="rect" coords="0,1037,257,1363" href="<?php echo $g4['shop_path']; ?>/item.php?it_id=1351295014" onfocus="this.blur();" target="_blank" />
        <area shape="rect" coords="0,1037,496,1363" href="<?php echo $g4['shop_path']; ?>/item.php?it_id=1393016202" onfocus="this.blur();" target="_blank" />
        <area shape="rect" coords="0,1037,755,1363" href="<?php echo $g4['shop_path']; ?>/item.php?it_id=1334672877" onfocus="this.blur();" target="_blank" />
        <area shape="rect" coords="0,1363,257,1708" href="<?php echo $g4['shop_path']; ?>/item.php?it_id=1367312435" onfocus="this.blur();" target="_blank" />
        <area shape="rect" coords="0,1363,496,1708" href="<?php echo $g4['shop_path']; ?>/item.php?it_id=1200020669" onfocus="this.blur();" target="_blank" />
        <area shape="rect" coords="0,1363,755,1708" href="<?php echo $g4['shop_path']; ?>/item.php?it_id=1327539719" onfocus="this.blur();" target="_blank" />
        <area shape="rect" coords="0,1754,378,1789" href="<?php echo $g4['shop_path']; ?>/list.php?ca_id=o0" onfocus="this.blur();" target="_blank" />
        <area shape="rect" coords="0,1754,755,1789" href="<?php echo $g4['shop_path']; ?>/list.php?ca_id=zg" onfocus="this.blur();" target="_blank" />
    </map>
<?
include_once("./_tail.php");
?>
