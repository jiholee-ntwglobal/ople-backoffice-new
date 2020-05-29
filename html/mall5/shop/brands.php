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
//$search_str = (trim($_GET['search_str']) != '' ? $_GET['search_str'] : '');
//$it_maker = (trim($_GET['it_maker']) != '' ? mysql_real_escape_string(stripslashes($_GET['it_maker'])) : '');
?>
<script type="text/javascript">
if(document.getElementById('search-input'))
	document.getElementById('search-input').value = "<?=stripslashes($_GET['search_str'])?>";
</script>
<!-- <table width=100% cellpadding=0 cellspacing=0 align=center border=0> -->
	<!-- // 김선용 201206 : -->
<div id="brands">
<h1>제조사 리스트</h1>

<div id="brands-column">
<ul class="brandslist">
	<?
	$result = sql_query("select it_maker from $g4[yc4_item_table] a left join $g4[yc4_category_table] b on a.ca_id=b.ca_id
		where a.it_use = 1 and b.ca_use = 1 and it_maker<>'' group by it_maker order by it_maker");
	while($row=sql_fetch_array($result)){
		echo "<li><a href=\"{$g4['shop_path']}/search.php?it_maker=".urlencode(stripslashes($row['it_maker']))."\">".stripslashes($row['it_maker'])."</a></li>";
	}
	?>
</ul>
</div>
</div>
<!-- </table> -->


<?
include_once("./_tail.php");
?>
