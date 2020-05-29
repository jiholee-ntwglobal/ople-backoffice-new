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

<div style="width:755px">

<img src='http://115.68.20.84/main/ecofriendly.jpg' border="none">
<? 
echo "<br><br><h2>Indigo Wild [인디고와일드]</h2>"; 
display_event('10', 1397029782 , 4, 4, 160, 160, ''); 

echo "<br><br><br><br><br><h2>Mrs. Meyer's [미세스마이어스]</h2>"; 
display_event('10', 1397029920 , 4, 4, 160, 160, ''); 

echo "<br><br><br><br><br><h2>Method [메쏘드]</h2>"; 
display_event('10', 1397030139 , 4, 4, 160, 160, ''); 

echo "<br><br><br><br><br><h2>Ecover [에코버]</h2>"; 
display_event('10', 1397030281 , 4, 4, 160, 160, ''); 

echo "<br><br><br><br><br><h2>Nellie's [넬리스]</h2>"; 
display_event('10', 1397030705 , 4, 4, 160, 160, ''); 

echo "<br><br><br><br><br><h2>GrabGreen [그랩그린]</h2>"; 
display_event('10', 1397030825 , 4, 4, 160, 160, ''); 

echo "<br><br><br><br><br><h2>Biokleen [바이오클린]</h2>"; 
display_event('10', 1397030954 , 4, 4, 160, 160, ''); 

echo "<br><br><br><br><br><h2>Citrus Magic [시트러스매직]</h2>"; 
display_event('10', 1397031032 , 4, 4, 160, 160, ''); 

echo "<br><br><br><br><br><h2>아기용세제</h2>"; 
display_event('10', 1397031112 , 4, 4, 160, 160, ''); 

echo "<br><br><br><br><br><h2>기타용품</h2>"; 
display_event('10', 1397031195 , 4, 4, 160, 160, ''); 
?>

</span>

<?
include_once("./_tail.php");
?>
