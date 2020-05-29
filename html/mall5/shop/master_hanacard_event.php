<?php
include_once("./_common.php");

// 김선용 201006 : 전역변수 XSS/인젝션 보안강화 및 방어
include_once "sjsjin.shop_guard.php";
//guard_script1($search_str);


# 잔여수량 로드 #
$sql = sql_query("
    select
        value3 as it_id,
        value4 - value5 as qty
    from
        yc4_event_data
    WHERE
        ev_code = 'hana'
        AND ev_data_type = 'event_item'
        /*AND ".date('Ymd')." between value1 and value2*/
");

$it_data = array();
while($row = sql_fetch_array($sql)){
    $it_data[$row['it_id']] = $row['qty'];
}

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
	.available {position:relative;top:980px;width:875px;margin-left:84px;margin-bottom:-26px;}
	.available span {position:absolute;display:block;width: 89px;padding: 2px 10px;height:26px;text-align:right;font-size:15px;letter-spacing:-1px;color:#fff;font-weight:bold;}
</style>
<div style="width:1100px;margin:20px auto;">
<p class="available">
	<span style="left:32px;"><?php echo number_format($it_data['1504184735']);?></span>
    <span style="left:203px;"><?php echo number_format($it_data['1418082783']);?></span>
    <span style="left:381px;"><?php echo number_format($it_data['1214964355']);?></span>
    <span style="left:555px;"><?php echo number_format($it_data['1327430820']);?></span>
    <span style="left:731px;"><?php echo number_format($it_data['1328910099']);?></span>
</p>
<img src='http://115.68.20.84/event/event_hanacard_0416.jpg' usemap="#Map" border="none" >
<map name="Map" id="Map">
  <area shape="rect" coords="88,1021,254,1320" href="<?=$g4['shop_path']?>/item.php?it_id=1504184735" target="_blank" onFocus="blur();"/>
  <area shape="rect" coords="266,1021,427,1320" href="<?=$g4['shop_path']?>/item.php?it_id=1418082783" target="_blank" onFocus="blur();"/>
  <area shape="rect" coords="438,1020,600,1318" href="<?=$g4['shop_path']?>/item.php?it_id=1214964355" target="_blank" onFocus="blur();"/>
  <area shape="rect" coords="616,1018,777,1316" href="<?=$g4['shop_path']?>/item.php?it_id=1327430820" target="_blank" onFocus="blur();"/>
  <area shape="rect" coords="789,1015,948,1317" href="<?=$g4['shop_path']?>/item.php?it_id=1328910099" target="_blank" onFocus="blur();"/>
</map>
</div>
<?php
include_once("./_tail.php");
?>