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
<style>
#event2012-1 {position: relative}
.winner01 {position: absolute; top:948; left:490; width: 224; height: 33;z-index:9999}
#event2012-1 ul {float: left;}
</style>
<script type="text/javascript">
if(document.getElementById('search-input'))
	document.getElementById('search-input').value = "<?=stripslashes($_GET['search_str'])?>";
</script>
<!-- <table width=100% cellpadding=0 cellspacing=0 align=center border=0> -->
	<!-- // 김선용 201206 : -->
<div id="event2012-1">
<h1>오플닷컴리뉴얼빅이벤트</h1>
<a href="<?=$g4['path']?>/shop/search.php?it_maker=Nordic+Naturals"><img src='http://115.68.20.84/main/event2308.jpg'>
	
<img src='http://115.68.20.84/main/event2307_btn.jpg'></a>
<a href="<?=$g4['path']?>/bbs/board.php?bo_table=nordicevent"><img src='http://115.68.20.84/main/event2307_btn2.jpg'></a>
<a href="<?=$g4['path']?>/bbs/board.php?bo_table=qa&wr_id=321742"><img src='http://115.68.20.84/main/event2308_win01.jpg'></a>
<a href="<?=$g4['path']?>/bbs/board.php?bo_table=qa&wr_id=322398"><img src='http://115.68.20.84/main/event2308_win02.jpg'></a>
<a href="<?=$g4['path']?>/bbs/board.php?bo_table=qa&wr_id=323642"><img src='http://115.68.20.84/main/event2308_win03.jpg'></a>
<a href="<?=$g4['path']?>/bbs/board.php?bo_table=qa&wr_id=325347"><img src='http://115.68.20.84/main/event2308_win04.jpg'></a>

</div>
<!-- </table> -->


<?
include_once("./_tail.php");
?>
