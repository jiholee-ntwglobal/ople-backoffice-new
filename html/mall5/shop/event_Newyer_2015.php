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
	height:680px;
	left:0;
	background: url(http://115.68.20.84/event/newyear/bg_header_title.jpg) repeat-x 0 0;
}
.event_header_title .imageArea{
	display: block;
	width: 1885px;
	margin: auto;
	position:relative;
}
.price_USD {
	position:absolute;
	right: 323px;
	top: 382px;
	font-size: 16px;
	color: #000;
	letter-spacing: -0.5px;
	display: block;
	width: 228px;
	text-align: center;
}
.price_USD em {font-style:normal;font-size: 13px;letter-spacing: normal;}
</style>

<div class="event_header_title">
    <p class="imageArea">
        <span class="price_USD"><strong>6만원</strong> <em>($<?php echo number_format(round($default['no_send_cost'] / $default['de_conv_pay'],2),2,'.',',');?>)</em>이상 구매 시</span>
        <span><img src='http://115.68.20.84/event/newyear/event_header_newyer2015.jpg' border="0" alt="설맞이대전"></span>
    </p>
</div>

<div style="width:1030px;padding-top:680px;">
<p><img src='http://115.68.20.84/event/newyear/newyer_title_01.jpg' border="0"></p>
<?
display_event('10', 1422604872 , 4, 20, 200, 200, '');
?>
</div>

<div style="width:1030px;">
<p><img src='http://115.68.20.84/event/newyear/newyer_title_02.jpg' border="0"></p>
<?
display_event('10', 1422604926 , 4, 20, 200, 200, '');
?>
</div>

<div style="width:1030px;">
<p><img src='http://115.68.20.84/event/newyear/newyer_title_03.jpg' border="0"></p>
<?
display_event('10', 1422604942 , 4, 20, 200, 200, '');
?>
</div>

<div style="width:1030px;">
<p><img src='http://115.68.20.84/event/newyear/newyer_title_04.jpg' border="0"></p>
<?
display_event('10', 1422604956 , 4, 20, 200, 200, '');
?>
</div>



<?
include_once("./_tail.php");
?>
