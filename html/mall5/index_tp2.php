<?
include "_common.php";
function cate_sub_navi_view_test($ca_id){
	$sql = sql_query("
		select
			b.ca_id,b.ca_name,b.ca_view
		from
			shop_category_tmp a
			left join
			yc4_category_new_tmp b on b.ca_id like concat(a.ca_id,'%')
		where
			a.s_id = '".$_SESSION['s_id']."'
			and
			b.ca_id like '".$ca_id."%'
			and
			b.ca_use = 1
			and
			length(b.ca_id) > ".strlen($ca_id)."
		order by
			b.ca_id
	");
	$cate_cnt = mysql_num_rows($sql);
	$cnt = 0;
	echo $cate_cnt;
	while($row=sql_fetch_array($sql)){
		$cnt++;
		$depth = strlen($row['ca_id'])/2 -1;


		switch($depth){
			case '1' :
				if($st[$depth]) {
					if($st[2]){
						$cate_view .=  " </a></em>\n";
						if($bf_depth && $bf_depth != $depth){
							$cate_view .=  "\t\t</span><!--inBox_depth end-->\n";
							$cate_view .=  "\t</span><!--inBox_depth_warp end-->\n";
						}
					}
					$cate_view .=  "</dd> \n";

					$st[2] = false;
					$st[3] = false;
					$st[4] = false;
					$st[5] = false;
				}
				$cate_view .=  "<dd> \n";
				$cate_view .=  "\t<strong><a href='".$g4['shop_path']."/list.php?ca_id=".$row['ca_id']."'>".$row['ca_name']."</a></strong>\n";
				if($bf_depth != $depth){

					$cate_view .=  "\t<span class='inBox_depth_warp' style='display:none;'>\n";
					$cate_view .=  "\t\t<span class='pointer'>포인트</span>\n";
					$cate_view .= "\t\t<span class='inBox_depth'>\n";
				}
				break;
			case '2' :
				if($st[$depth]){
					$cate_view .=  " </a></em>\n";
					$st[3] = false;
					$st[4] = false;
					$st[5] = false;
				}
				$cate_view .=  "\t\t\t<em><a href='".$g4['shop_path']."/list.php?ca_id=".$row['ca_id']."'>".$row['ca_name']."";
				if($cate_cnt == $cnt){
					$cate_view .= "</a></em>\n";
					$cate_view .=  "\t\t</span><!--inBox_depth end-->\n";
					$cate_view .=  "\t</span><!--inBox_depth_warp end-->\n";
					$cate_view .= "</dd>";
				}
				break;
		}
		$st[$depth] = true;
		$bf_depth = $depth;

	}
	if($cate_view){
		$cate_view = "
	<p class='pointer'>포인터(열기)</p>
	<div>
		<dl>
	".$cate_view."
		</dl>
	</div>
				";
	}
	return $cate_view;
}

function cate_sub_navi_view_more($ca_id){
	$sql = sql_query("
		select
			b.ca_id,b.ca_name,b.ca_view
		from
			shop_category_tmp a
			left join
			yc4_category_new_tmp b on b.ca_id like concat(a.ca_id,'%')
		where
			a.s_id = '".$_SESSION['s_id']."'
			and
			b.ca_id like '".$ca_id."%'
			and
			b.ca_use = 1
			and
			length(b.ca_id) > ".strlen($ca_id)."
		order by
			b.ca_id
	");
	$cate_cnt = mysql_num_rows($sql);

	$cnt = 0;
	while($row=sql_fetch_array($sql)){
		$cnt++;
		$depth = strlen($row['ca_id'])/2 -1;
		switch($depth){
			case 1 :
				if(!$bf_depth){
					$ca_view .= "<p class='pointer'>포인터(열기)</p>\n";
					$ca_view .= "<div>\n";
					$ca_view .= "\t<dl>\n";
				}else{
					$ca_view .= "\t</dl>\n";
					$ca_view .= "</div>\n";
					$ca_view .= "<div>\n";
					$ca_view .= "\t<dl>\n";
				}
				$ca_view .= "\t\t<dt ca_view=''><a href='".$g4['shop_path']."/list.php?ca_id=".$row['ca_id']."'>".$row['ca_name']."</a></dt>\n";

				break;
			case 2 :
				$ca_view .= "\t\t<dd ca_view=''><a href='".$g4['shop_path']."/list.php?ca_id=".$row['ca_id']."'>".$row['ca_name']."</a></dd>\n";
				if($bf_depth > $depth){
					$ca_view .= "\t</dl>\n";
					$ca_view .= "</div>";
				}
				break;
		}

		$bf_depth = $depth;
	}
	if($ca_view){
		$ca_view .= "<span onclick=\"return false;\">접기</span>";
	}
	return $ca_view;
}

echo cate_sub_navi_view_more(10);
/*
<div class="DepthCategory depth03_box" ca_id="12">
  <p class="pointer">포인터(열기)</p>
  <div>
	<dl>
		<dt ca_view=""><a href="./shop/list.php?ca_id=1210">어린이,유아</a></dt>
		<dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121010">종합비타민</a></dd>
		<dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121011">칼슘(뼈건강)</a></dd>
		<dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121012">면역력강화</a></dd>
		<dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121013">오메가, 집중력, 눈</a></dd>
		<dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121014">유산균, 식이섬유</a></dd>
		<dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121015">기타</a></dd>
	</dl>
</div>
<div>
	<dl><dt ca_view=""><a href="./shop/list.php?ca_id=1211">학생,청소년</a></dt><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121110">종합비타민</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121111">칼슘(뼈건강)</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121112">두뇌건강, 눈</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121113">면역력강화</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121114">유산균</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121115">기타</a></dd></dl>
</div>
<div>
	<dl><dt ca_view=""><a href="./shop/list.php?ca_id=1212">부모님건강</a></dt><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121210">종합비타민</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121211">오메가3</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121212">눈건강 , 두뇌건강</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121213">Ahcc , 베타글루칸</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121214">관절건강</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121215">혈압/당뇨</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121216">골다공증</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121217">기타</a></dd></dl>
</div>
<div>
	<dl><dt ca_view=""><a href="./shop/list.php?ca_id=1213">임산부</a></dt><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121310">종합비타민</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121311">오메가3</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121312">엽산 &amp; 철분</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121313">유산균 &amp; 변비</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121314">칼슘</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121315">입덧완화</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121316">기타</a></dd></dl>
</div>
<div>
	<dl><dt ca_view=""><a href="./shop/list.php?ca_id=1214">여성건강</a></dt><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121410">종합비타민</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121411">뼈건강</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121412">방광 , 질건강</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121413">갱년기</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121414">피부</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121415">변비</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121416">생리통</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121417">다이어트</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121418">기타</a></dd></dl>
</div>
<div>
	<dl><dt ca_view=""><a href="./shop/list.php?ca_id=1215">남성건강</a></dt><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121510">종합비타민</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121511">전립선건강</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121512">활력/스트레스</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121513">간&amp;폐건강</a></dd><dd ca_view="" style="font-weight: bold;"><a href="./shop/list.php?ca_id=121514">기타</a></dd></dl>
</div>
<div class="hide_cate_flag" ca_id="12"></div> </div>


*/
/*
<div class="DepthCategory depth01_box" ca_id="10">
  <p class="pointer">포인터(열기)</p>
  <div>
	<dl>
        <dd>
            <strong><a href="./shop/list.php?ca_id=1010">유아/어린이</a></strong>
            <span class="inBox_depth_warp" style="display:none;">
                <span class="pointer">포인트</span>
                <span class="inBox_depth">
                    <em><a href="">종합비타민</a></em>
                    <em><a href="">유산균</a></em>
                    <em><a href="">면역력</a></em>
                    <em><a href="">뼈(칼슘)</a></em>
                    <em><a href="">두뇌</a></em>
                </span>
            </span>
        </dd>
        <dd><strong><a href="./shop/list.php?ca_id=1063">학생/청소년</a></strong>
            <span class="inBox_depth_warp">
                <span class="pointer">포인트</span>
                <span class="inBox_depth">
                    <em><a href="">종합비타민</a></em>
                    <em><a href="">유산균</a></em>
                    <em><a href="">면역력</a></em>
                    <em><a href="">뼈(칼슘)</a></em>
                    <em><a href="">두뇌</a></em>
                </span>
            </span>
        </dd>
        <dd><strong><a href="./shop/list.php?ca_id=1001">부모님</a></strong></dd>
        <dd><strong><a href="./shop/list.php?ca_id=1090">임산부</a></strong></dd>
        <dd><strong><a href="./shop/list.php?ca_id=1066">여성</a></strong></dd>
        <dd><strong><a href="./shop/list.php?ca_id=1093">남성</a></strong></dd>
     </dl>
</div>
*/


?>
<!doctype html>
<html lang="ko">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="shortcut icon" type="image/x-icon" href="./favicon.ico">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">

<title>오플닷컴 (OPLE.COM)-오플닷컴 No.1 미국 직배송 건강식품 쇼핑몰 </title>

<link rel="stylesheet" href="style_test.css" type="text/css" />
<link rel="stylesheet" href="css2/jquery.autocomplete.css"  type="text/css"/>

<script type="text/javascript">
// 자바스크립트에서 사용하는 전역변수 선언
var g4_path      = ".";
var g4_bbs       = "bbs";
var g4_bbs_img   = "img";
var g4_url       = "http://www.ople.com/mall5";
var g4_is_member = "1";
var g4_is_admin  = "super";
var g4_bo_table  = "";
var g4_sca       = "";
var g4_charset   = "UTF-8";
var g4_cookie_domain = "";
var g4_is_gecko  = navigator.userAgent.toLowerCase().indexOf("gecko") != -1;
var g4_is_ie     = navigator.userAgent.toLowerCase().indexOf("msie") != -1;
var g4_cf_title = "오플닷컴 (OPLE.COM)";
var g4_admin = 'adm';</script>
<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="js/jquery.lazyload.min.js"></script>
<script type="text/javascript">
$(function() {
	$('img[data-original]').lazyload({
		thresold : 1100,
		placeholder : './img/loding_image.gif',
//		event : 'mouseover',
		skip_invisible : false,
		failure_limit : 10,
		effect : 'fadeIn'
	});

});

</script>
<script type="text/javascript" src="js/jquery.easing.1.3.js"></script>
<script type="text/javascript" src="js/jquery.autocomplete.pack.js"></script>
<script type="text/javascript" src="js/script.js"></script>
<script type="text/javascript" src="js/common.js"></script>
</head>

<body ><div class="site_wrap">
<style>
.amount { color:#2266BB; font-weight:bold; font-family:Verdana;}
.c1 { background-color:#94D7E7; }
.c2 { background-color:#E7F3F7; }
.cate_hide_navi{
	display:none;
}
</style>

<script type="text/javascript">
/*
function totopbottom() { if (document.body.scrollTop == 0)
{ window.scrollTo(0,document.body.scrollHeight); }
else {window.scrollTo(0,0); }}
function topbottom() { document.body.ondblclick = totopbottom;}
*/
/*
$(function() {
	$(window).scroll(function() {
		_top = $(document).scrollTop();
		setTimeout(function() {
			$('#sideBanner').stop().animate({ top: _top }, 500, 'easeOutBack');
		}, 500);
	});
});
*/
</script>
<!--TobBanner-->
<div class="TopBannerArea">
    <p class="BannerObject"><a href="./shop/content.php?co_id=Renewal_Event"><img src="http://115.68.20.84/event/Top_banner_renewal.jpg" alt="리뉴얼이벤트"/></a></p>

</div>
<div class="wrap health">
    <!--HeaderArea-->
    <div class="headerArea">
        <!--GNBArea-->
        <div class="gnbArea">


          <!-- 주말 증정이벤트(FreeGift) 시작 -->
                    <!-- 주말 증정이벤트 끝 -->


            <div class="gnb_left">
                <ul>
					<li class="first"><a href="./bbs/logout.php"><strong>로그아웃</strong></a>					<span class="button_ad"><a href="./adm"><img src="./images/common/bt_admin.gif" alt="관리자" /></a></span>          <span class='point_box'>0</span>          </li>
					<li><a href="./bbs/member_confirm.php?url=register_form.php">정보수정</a></li>
					                    <li><a href="./shop/mypage.php">마이페이지</a></li>
                    <li><a href="./bbs/board.php?bo_table=qa" class="ico_customer"><strong>고객센터</strong></a></li>
                </ul>
            </div>
			<!-- Total Search -->
			<div class="TotalSearchArea">
				<form action="./shop/search.php" method='get'>
					<p class="OpleBI"><a href=".?s_id=3">ople.com</a></p>
					<fieldset>
						<span><input type="text" name="search_str_all" required itemname="검색어" autocomplete="off" class="auto-search" style="background-image: url(./js/wrest.gif); background-position: 100% 0%; background-repeat: no-repeat;" value=''></span>
						<span class="button"><input type="image" src="http://115.68.20.84/mall6/btn_totalsearch.gif" border="0" alt="검색"></span>
					</fieldset>
				</form>
			</div>
			<!-- Total End Search -->
            <div class="gnb_right">
                <ul>
                    <li class="basket"><a href="./shop/cart.php">장바구니 <strong>0</strong></a></li>
                    <li><a href="./shop/wishlist.php">위시리스트 <strong>3</strong></a></li>
                    <li><a href="./shop/orderinquiry.php">주문/배송</a></li>
                </ul>
            </div>
        </div>
        <!--TabArea-->
        <div class="TabArea">
            <ul>
                <li class="tab_health"><a href=".?s_id=3" class="active">Health</a></li>
                <li class="tab_home"><a href=".?s_id=4" class="">Home</a></li>
                <li class="tab_momnbaby"><a href=".?s_id=5" class="">Mom&Baby,Kids</a></li>
                <li class="tab_bueaty"><a href=".?s_id=1" class="">bueaty</a></li>
                <li class="tab_food"><a href=".?s_id=2" class="">Food</a></li>
                <!--<li class="tab_fat"><a href=".?s_id=6" class="">Fat</a></li>-->
            </ul>
			<p class="btn_prepay"><a href="./shop/event.php?ev_id=1413794551">선결제포인트</a></p>
        </div>
    </div>
	<!--ContentsArea-->
    <div class="contentsArea">
        <!--ContentsHeader-->
        <div class="cont_header">
            <h1><a href=".">OpleHealth</a></h1>
            <!--SearchArea-->
			<form name='frmsearch1' style='margin:0px;' action="./shop/search.php" method='get'>
            <fieldset class="searchArea">

                <span><input type='text' name='search_str_all' value='' required itemname="검색어" autocomplete="off" class="auto-search-kwan"/></span>
                <span class="button"><input type='image' src="http://115.68.20.84/mall6/btn_search.gif" border="0" id='search-button' alt="검색"></span>
            </fieldset>
			</form>

            <!--banner-->
            <div class="top_banner">
                <p class="btn_best"><a href="./shop/event.php?ev_id=1413794344">베스트상품</a></p>
                <p class="btn_user"><a href="./sjsjin/hoogi_list.php">사용자후기</a></p>
                <!--p class="btn_requst"><a href="./sjsjin/item_onrequest_write.php">상품입고요청</a></p-->
            </div>
            <!--CategoryArea-->
            <div class="category">
                <ul>
	<li class="depth01 first"><a href="./shop/list.php?ca_id=10" class="">대상별</a></li>
	<li class="depth02"><a href="./shop/list.php?ca_id=11" class="">성분별</a></li>
	<li class="depth03"><a href="./shop/list.php?ca_id=12" class="">증상별</a></li>
	<li class="depth04"><a href="./shop/list.php?ca_id=13" class="">비타민&미네럴</a></li>
	<li class="depth05"><a href="./shop/list.php?ca_id=14" class="">오메가-3</a></li>
	<li class="depth06"><a href="./shop/list.php?ca_id=15" class="">유산균</a></li>
	<li class="depth07"><a href="./shop/list.php?ca_id=16" class="">허브&각종추출물</a></li>
	<li class="depth08"><a href="./shop/list.php?ca_id=17" class="">항산화&면역력</a></li>
    <li class="depth09"><a href="./shop/list.php?ca_id=17" class="">동종요법</a></li>
    <li class="depth10"><a href="./shop/list.php?ca_id=17" class="">다이어트&스포츠</a></li>
</ul>
<!--Depth_Category_건강식품성분별-->
<div class="DepthCategory depth01_box" style="display:none;" ca_id='10'>
  <?//=$cate_view;?>
  <?=cate_sub_navi_view_more('10')?>
<?/*
  <p class="pointer">포인터(열기)</p>
  <div>
	<dl>


		<dd>
            <strong><a href='./shop/list.php?ca_id=1010'>유아/어린이</a></strong>
            <span class="inBox_depth_warp" style="display:none;">
                <span class="pointer">포인트</span>
                <span class="inBox_depth">
                    <em><a href="">종합비타민</a></em>
                    <em><a href="">유산균</a></em>
                    <em><a href="">면역력</a></em>
                    <em><a href="">뼈(칼슘)</a></em>
                    <em><a href="">두뇌</a></em>
                </span>
            </span>
        </dd>
        <dd><strong><a href='./shop/list.php?ca_id=1063'>학생/청소년</a></strong>
            <span class="inBox_depth_warp">
                <span class="pointer">포인트</span>
                <span class="inBox_depth">
                    <em><a href="">종합비타민</a></em>
                    <em><a href="">유산균</a></em>
                    <em><a href="">면역력</a></em>
                    <em><a href="">뼈(칼슘)</a></em>
                    <em><a href="">두뇌</a></em>
                </span>
            </span>
        </dd>
        <dd><strong><a href='./shop/list.php?ca_id=1001'>부모님</a></strong></dd>
        <dd><strong><a href='./shop/list.php?ca_id=1090'>임산부</a></strong></dd>
        <dd><strong><a href='./shop/list.php?ca_id=1066'>여성</a></strong></dd>
        <dd><strong><a href='./shop/list.php?ca_id=1093'>남성</a></strong></dd>

     </dl>
</div>





<div class='hide_cate_flag' ca_id='10'></div>
*/?>
</div>

<!--Depth_Category_건강식품증상별-->
<div class="DepthCategory depth02_box" style="display:none;" ca_id='11'>
  <p class="pointer">포인터(열기)</p>
  <div>
	<dl>
<dd ca_view><a href='./shop/list.php?ca_id=1116'>간,폐건강</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1123'>갱년기</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1121'>골다공증,뼈,치아건강</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1120'>관절건강</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1110'>눈,코,입,귀</a></dd>	<dl>
</div>
<div>
	<dl>
<dd ca_view><a href='./shop/list.php?ca_id=1114'>두뇌,집중력</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1122'>면역력강화</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1112'>소화,위건강</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1133'>손톱/헤어 건강</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1115'>스트레스,피로,우울,불면</a></dd>	<dl>
</div>
<div>
	<dl>
<dd ca_view><a href='./shop/list.php?ca_id=1113'>신장건강</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1118'>심장건강</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1143'>염증,항염</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1163'>장건강</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1117'>전립선건강</a></dd>	<dl>
</div>
<div>
	<dl>
<dd ca_view><a href='./shop/list.php?ca_id=1173'>콜레스테롤</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1111'>피부건강</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1183'>해독(디톡스)</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1119'>혈압,혈당건강,혈액</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1153'>호흡기건강</a></dd>	<dl>
</div>
<div class='hide_cate_flag' ca_id='11'></div></div>

<!--Depth_Category_건강식품연령별-->
<div class="DepthCategory depth03_box" style="display:none;" ca_id='12'>
  <p class="pointer">포인터(열기)</p>
  <div>
	<dl><dt ca_view><a href='./shop/list.php?ca_id=1210'>어린이,유아</a></dt><dd ca_view><a href='./shop/list.php?ca_id=121010'>종합비타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121011'>칼슘(뼈건강)</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121012'>면역력강화</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121013'>오메가, 집중력, 눈</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121014'>유산균, 식이섬유</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121015'>기타</a></dd></dl>
</div>
<div>
	<dl><dt ca_view><a href='./shop/list.php?ca_id=1211'>학생,청소년</a></dt><dd ca_view><a href='./shop/list.php?ca_id=121110'>종합비타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121111'>칼슘(뼈건강)</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121112'>두뇌건강, 눈</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121113'>면역력강화</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121114'>유산균</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121115'>기타</a></dd></dl>
</div>
<div>
	<dl><dt ca_view><a href='./shop/list.php?ca_id=1212'>부모님건강</a></dt><dd ca_view><a href='./shop/list.php?ca_id=121210'>종합비타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121211'>오메가3</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121212'>눈건강 , 두뇌건강</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121213'>Ahcc , 베타글루칸</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121214'>관절건강</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121215'>혈압/당뇨</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121216'>골다공증</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121217'>기타</a></dd></dl>
</div>
<div>
	<dl><dt ca_view><a href='./shop/list.php?ca_id=1213'>임산부</a></dt><dd ca_view><a href='./shop/list.php?ca_id=121310'>종합비타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121311'>오메가3</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121312'>엽산 & 철분</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121313'>유산균 & 변비</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121314'>칼슘</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121315'>입덧완화</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121316'>기타</a></dd></dl>
</div>
<div>
	<dl><dt ca_view><a href='./shop/list.php?ca_id=1214'>여성건강</a></dt><dd ca_view><a href='./shop/list.php?ca_id=121410'>종합비타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121411'>뼈건강</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121412'>방광 , 질건강</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121413'>갱년기</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121414'>피부</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121415'>변비</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121416'>생리통</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121417'>다이어트</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121418'>기타</a></dd></dl>
</div>
<div>
	<dl><dt ca_view><a href='./shop/list.php?ca_id=1215'>남성건강</a></dt><dd ca_view><a href='./shop/list.php?ca_id=121510'>종합비타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121511'>전립선건강</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121512'>활력/스트레스</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121513'>간&폐건강</a></dd><dd ca_view><a href='./shop/list.php?ca_id=121514'>기타</a></dd></dl>
</div>
<div class='hide_cate_flag' ca_id='12'></div> </div>

<!--Depth_Category_허브-->
<div class="DepthCategory depth04_box" style="display:none;" ca_id='13'>
  <p class="pointer">포인터(열기)</p>
  <div>
	<dl>
<dd ca_view><a href='./shop/list.php?ca_id=1310'>감초 (DGL)</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1311'>노니</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1365'>라이코펜</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1367'>밀크 시슬 (실리마린)</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1315'>베타 - 글루칸</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1368'>블루 베리</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1316'>빌베리</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1352'>생강 뿌리</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1353'>심황 / 커큐민</a></dd>	<dl>
</div>
<div>
	<dl>
<dd ca_view><a href='./shop/list.php?ca_id=1370'>아마씨</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1371'>알로에 베라</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1356'>오미자</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1340'>은행 나무 (은행나무 추출물)</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1360'>코코넛</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1377'>크랜베리</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1327'>표고버섯 (AHCC)</a></dd><dd><a href='./shop/list.php?ca_id=1345'>Jenol 놀 피크 (소나무 액체)</a></dd><dd><a href='./shop/list.php?ca_id=1328'>계피</a></dd>	<dl>
</div>
<div>
	<dl>
<dd><a href='./shop/list.php?ca_id=1380'>기타</a></dd><dd><a href='./shop/list.php?ca_id=1329'>노팔 선인장</a></dd><dd><a href='./shop/list.php?ca_id=1364'>녹차</a></dd><dd><a href='./shop/list.php?ca_id=1312'>달맞이 꽃 종자유</a></dd><dd><a href='./shop/list.php?ca_id=1330'>당살초 짐네마</a></dd><dd><a href='./shop/list.php?ca_id=1348'>동충하초</a></dd><dd><a href='./shop/list.php?ca_id=1331'>레드 클로버</a></dd><dd><a href='./shop/list.php?ca_id=1366'>마늘</a></dd><dd><a href='./shop/list.php?ca_id=1314'>마카</a></dd>	<dl>
</div>
<div>
	<dl>
<dd><a href='./shop/list.php?ca_id=1350'>민들레 뿌리</a></dd><dd><a href='./shop/list.php?ca_id=1351'>블랙 코호시</a></dd><dd><a href='./shop/list.php?ca_id=1369'>석류 나무</a></dd><dd><a href='./shop/list.php?ca_id=1317'>세인트 존스 윌트</a></dd><dd><a href='./shop/list.php?ca_id=1318'>아사이</a></dd><dd><a href='./shop/list.php?ca_id=1337'>앨더베리 (삼부 커스)</a></dd><dd><a href='./shop/list.php?ca_id=1355'>야생 얌</a></dd><dd><a href='./shop/list.php?ca_id=1372'>에시악</a></dd><dd><a href='./shop/list.php?ca_id=1320'>에키 네시아</a></dd>	<dl>
</div>
<div>
	<dl>
<dd><a href='./shop/list.php?ca_id=1338'>영지</a></dd><dd><a href='./shop/list.php?ca_id=1373'>올리브</a></dd><dd><a href='./shop/list.php?ca_id=1321'>우엉</a></dd><dd><a href='./shop/list.php?ca_id=1339'>운지버섯</a></dd><dd><a href='./shop/list.php?ca_id=1375'>인삼</a></dd><dd><a href='./shop/list.php?ca_id=1341'>징계 베리 (바이 텍스)</a></dd><dd><a href='./shop/list.php?ca_id=1359'>차가버섯</a></dd><dd><a href='./shop/list.php?ca_id=1376'>체리 (야생 체리, 블랙 체리)</a></dd><dd><a href='./shop/list.php?ca_id=1324'>칡</a></dd>	<dl>
</div>
<div>
	<dl>
<dd><a href='./shop/list.php?ca_id=1342'>카테킨 (EGCG)</a></dd><dd><a href='./shop/list.php?ca_id=1325'>톱 야자 (쏘팔메토)</a></dd><dd><a href='./shop/list.php?ca_id=1343'>파슬리</a></dd><dd><a href='./shop/list.php?ca_id=1361'>포도</a></dd><dd><a href='./shop/list.php?ca_id=1326'>홍경천</a></dd><dd><a href='./shop/list.php?ca_id=1344'>화란 국화</a></dd>	<dl>
</div>
<div class='hide_cate_flag' ca_id='13'></div></div>

<!--Depth_Category_비타민_미네럴-->
<div class="DepthCategory depth05_box" style="display:none;" ca_id='14'>
  <p class="pointer">포인터(열기)</p>
  <div>
	<dl><dt ca_view><a href='./shop/list.php?ca_id=1401'>종합비타민</a></dt><dd ca_view><a href='./shop/list.php?ca_id=140110'>일반 종합비타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=140111'>센트룸 (CENTRUM)</a></dd><dd ca_view><a href='./shop/list.php?ca_id=140112'>남성용 종합비타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=140113'>여성용 종합비타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=140114'>어린이 종합비타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=140115'>청소년/시니어 종합비타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=140116'>임산부 종합비타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=140117'>구미/과립형 종합비타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=140118'>분말/액상형 종합비타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=140119'>철분미첨가종합비타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=140120'>천연 원료 종합비타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=140121'>그외 종합비타민</a></dd></dl>
</div>
<div>
	<dl><dt ca_view><a href='./shop/list.php?ca_id=1410'>미네럴</a></dt><dd ca_view><a href='./shop/list.php?ca_id=141010'>게르마</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141011'>구리</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141012'>마그네슘</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141013'>망간</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141014'>멀티미네랄</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141015'>불소</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141016'>셀레늄</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141017'>아연</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141018'>철</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141019'>카프릴산</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141020'>칼슘</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141021'>크롬</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141022'>포타시움</a></dd></dl>
</div>
<div>
	<dl><dt ca_view><a href='./shop/list.php?ca_id=1411'>비타민 A-Z</a></dt><dd ca_view><a href='./shop/list.php?ca_id=141110'>비타민 A</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141111'>비타민 B</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141112'>비타민 C</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141113'>비타민 D</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141114'>비타민 E</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141115'>비타민 K</a></dd><dd ca_view><a href='./shop/list.php?ca_id=141116'>기타 비타민</a></dd></dl>
</div>
<div class='hide_cate_flag' ca_id='14'></div></div>

<!--Depth_Category_아미노산-->
<div class="DepthCategory depth06_box" style="display:none;" ca_id='15'>
  <p class="pointer">포인터(열기)</p>
  <div>
	<dl>
<dd ca_view><a href='./shop/list.php?ca_id=1510'>가바</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1511'>글루타민</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1512'>글루타치온</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1513'>라이신</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1514'>메티오닌</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1515'>베타 알라닌</a></dd>	<dl>
</div>
<div>
	<dl>
<dd ca_view><a href='./shop/list.php?ca_id=1516'>복합 아미노산</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1517'>시스테인</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1518'>아르기닌</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1519'>오르니틴</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1520'>카르니틴</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1521'>글리신</a></dd>	<dl>
</div>
<div>
	<dl>
<dd ca_view><a href='./shop/list.php?ca_id=1522'>타이로신</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1523'>테아닌</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1524'>트림토판</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1525'>NAC</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1526'>L-시트룰린</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1527'>D-메틸아미노에탄올</a></dd>	<dl>
</div>
<div>
	<dl>
<dd ca_view><a href='./shop/list.php?ca_id=1528'>DL-페닐알라닌은</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1529'>N-아세틸 타이로신</a></dd><dd ca_view><a href='./shop/list.php?ca_id=1530'>N-아세틸 시스테인</a></dd>	<dl>
</div>
<div class='hide_cate_flag' ca_id='15'></div></div>

<!--Depth_Category_오메가3_COQ10-->
<div class="DepthCategory depth07_box" style="display:none;" ca_id='16'>
  <p class="pointer">포인터(열기)</p>
  <div>
	<dl><dt ca_view><a href='./shop/list.php?ca_id=1610'>오메가3</a></dt><dd ca_view><a href='./shop/list.php?ca_id=161010'>오메가3</a></dd><dd ca_view><a href='./shop/list.php?ca_id=161011'>오메가 369</a></dd><dd ca_view><a href='./shop/list.php?ca_id=161012'>오메가 복합제품</a></dd><dd ca_view><a href='./shop/list.php?ca_id=161013'>식물성오메가 (아마씨등)</a></dd><dd ca_view><a href='./shop/list.php?ca_id=161014'>레시틴 (대두)</a></dd><dd ca_view><a href='./shop/list.php?ca_id=161015'>달맞이꽃종자유GLA</a></dd><dd ca_view><a href='./shop/list.php?ca_id=161016'>CLA(공액리놀렌산)</a></dd><dd ca_view><a href='./shop/list.php?ca_id=161017'>기타</a></dd><dd ca_view><a href='./shop/list.php?ca_id=161018'>Nordic Naturals</a></dd></dl>
</div>
<div>
	<dl><dt ca_view><a href='./shop/list.php?ca_id=1612'>COQ10</a></dt></dl>
</div>
<div class='hide_cate_flag' ca_id='16'></div></div>

<!--Depth_Category_스포츠-->
<div class="DepthCategory depth08_box" style="display:none;" ca_id='17'>
  <p class="pointer">포인터(열기)</p>
  <div>
	<dl><dt ca_view><a href='./shop/list.php?ca_id=1710'>다이어트</a></dt><dd ca_view><a href='./shop/list.php?ca_id=171010'>팻 버너, 지방 연소</a></dd><dd ca_view><a href='./shop/list.php?ca_id=171011'>탄수화물 차단</a></dd><dd ca_view><a href='./shop/list.php?ca_id=171012'>식욕억제</a></dd><dd ca_view><a href='./shop/list.php?ca_id=171013'>디톡스</a></dd><dd ca_view><a href='./shop/list.php?ca_id=171014'>식사대용 파우더</a></dd></dl>
	<dl><dt ca_view><a href='./shop/list.php?ca_id=1711'>헬스보충제</a></dt><dd ca_view><a href='./shop/list.php?ca_id=171110'>프로틴</a></dd><dd ca_view><a href='./shop/list.php?ca_id=171111'>아미노산</a></dd><dd ca_view><a href='./shop/list.php?ca_id=171112'>지방연소 (펫버너)</a></dd><dd ca_view><a href='./shop/list.php?ca_id=171113'>프로틴 바, 에너지 바</a></dd></dl>
</div>
<div>
	<dl><dt ca_view><a href='./shop/list.php?ca_id=1712'>스포츠 용품</a></dt><dd ca_view><a href='./shop/list.php?ca_id=171210'>쉐이커, 텀블러, 케이스</a></dd><dd ca_view><a href='./shop/list.php?ca_id=171211'>피트니스 악세서리</a></dd><dd ca_view><a href='./shop/list.php?ca_id=171212'>바디 트리트먼트</a></dd></dl>
</div>
<div class='hide_cate_flag' ca_id='17'></div></div>            </div>
        </div>

		<div class="contents ">
    <!--FloatingBannerZone-->
    <div class="Floating_bannerArea">
            <p class="flating_banner1"><a href="./shop/4color.php"><img src="http://115.68.20.84/event/4color_mainB.png" alt="배너1"></a></p>
                </div>

<!-- ContentsArea --><script type="text/javascript">
//<![CDATA[
function getCookie(sName) {
var aCookie = document.cookie.split("; ");
    for (var i = 0; i < aCookie.length; i++) {
          var aCrumb = aCookie[i].split("=");
          if (sName == aCrumb[0]){
                return unescape(aCrumb[1]);
          }
     }
     return null;
}
function setCookie(name, value, expiredays) {
    var todayDate = new Date();
    todayDate.setDate(todayDate.getDate() + expiredays);
    document.cookie = name + "=" + escape( value ) + "; path=/; expires=" + todayDate.toGMTString() + ";"
}

// 팝업이미지를 넣을 경로를 지정합니다.
function addPopup(imagename, top, left, width, height){
    var popup = document.getElementById('popupID2');
    var src = '<img src=http://115.68.20.84/main/'+imagename+' alt="" />';
    src += '<div style="border:1px solid #ff982b;margin-bottom:1px;color:white;background-color:#ff982b;text-align:right;height:20px;padding-right:5px;">하루동안 열지 않음 <input type="checkbox" onclick="hidePopup(\'popupID2\', \'ok\', \'1\');" /></div>';
    popup.innerHTML = src;
    popup.style.top = top+'px';
    popup.style.left = left+'px';
    popup.style.width = width+'px';
    popup.style.height = height+'px';
    popup.style.display = 'block';
}
function hidePopup(name, value, expiredays){
    setCookie(name, value, expiredays);
    var popup = document.getElementById(name);
    popup.style.display = 'none';
}
/*
팝업 사용시 주석을 풀어주세요
window.onload = function(){
    if(getCookie('popupID2') != 'ok'){
        // 이미지이름, 위치(위,아래), 폭(가로), 높이(세로)
        addPopup('popup.gif', '5', '200', '400', '451');
    }
};
*/
//]]>
</script>
<link href="./css/jquery.bxslider.css" rel="stylesheet" type="text/css">
<script type="text/JavaScript" src="./js/shop.js"></script>
<style type="text/css">
	.bxslider a {display:block;}
	.bx-wrapper .bx-viewport{ margin:0px;border:none;}
	.bx-wrapper{margin: 0 0 0 5px;}
	.bx-wrapper .bx-pager {text-align: left;bottom: 4px;}
	.bx-wrapper .bx-pager.bx-default-pager a{width:8px; height:8px;}
</style>


<!--Contents-->

<!--FloatingBannerZone-->
<div class="Floating_bannerArea">
  <p class="flating_banner1"><a href="./shop/4color.php"><img src="http://115.68.20.84/event/4color_mainB.png" alt="배너1"></a></p>
</div>
<!--MainSpotZone-->
            <div class="MainSpotZone">
				<!--<ul class="bxslider" style='display:none;'>
					<--				</ul>-->
        <div class='MainSpotImg'>
					<div class='MainSpotImg_mask'>
						<div><span class='MainSpotImgTitle'><a href='./shop/freegift_nordic.php'>노르딕 사은품 대잔치</a></span><a href="./shop/freegift_nordic.php"><img data-original="http://115.68.20.84/mall6/health_banner/Nordic_FreeGift_main.png" alt="메인스팟" /></a></div>
<div><span class='MainSpotImgTitle'><a href='./shop/list.php?ca_id=1612'>에너지업!COQ10</a></span><a href="./shop/list.php?ca_id=1612"><img data-original="http://115.68.20.84/mall6/health_banner/main_spot01.jpg" alt="메인스팟"></a></div>
<div><span class='MainSpotImgTitle'><a href='./shop/event_0904.php'>항산화비타민!비타민E</a></span><a href="./shop/event_0904.php"><img data-original="http://115.68.20.84/mall6/health_banner/main_spot02.jpg" alt="메인스팟" /></a></div>
<div><span class='MainSpotImgTitle'><a href='./shop/event_0926.php'>면연력!베타글루칸</a></span><a href="./shop/event_0926.php"><img data-original="http://115.68.20.84/mall6/health_banner/main_spot03.jpg" alt="메인스팟" /></a></div>
<div><span class='MainSpotImgTitle'><a href='./shop/event_1017.php'>감/기/타/파</a></span><a href="./shop/event_1017.php"><img data-original="http://115.68.20.84/mall6/health_banner/main_spot04.jpg" alt="메인스팟" /></a></div>
<div><span class='MainSpotImgTitle'><a href='./shop/list.php?ca_id=1061'>건강한장을위한 유산균</a></span><a href="./shop/list.php?ca_id=1061"><img data-original="http://115.68.20.84/mall6/health_banner/main_spot05.jpg" alt="메인스팟" /></a></div>
											</div>

				</div>
        <ul class='MainSpotTitle'>
					        </ul>
            </div>
            <!--FeaturedCategoris-->
            <div class="featured_category">
                <h2><img src="http://115.68.20.84/mall6/title_FeaturedCategory.png" alt="Featured Category" /></h2>
                <dl class="category_zoneA">
                    <a href="./shop/list.php?ca_id=1401"><dt> 종합비타민</dt></a>
                    <dd><a href="./shop/event.php?ev_id=1398849456">뉴챕터</a></dd>
                    <dd><a href="./shop/event.php?ev_id=1398845427">소스내추럴</a></dd>
                    <dd><a href="./shop/event.php?ev_id=1398848836">네이처스웨이</a></dd>
                    <dd><a href="./shop/event.php?ev_id=1398849008">레인보우라이트</a></dd>
                    <dd><a href="./shop/list.php?ca_id=121010">어린이비타민</a></dd>
                </dl>
                <dl class="category_zoneB">
                    <a href="./shop/list.php?ca_id=1028"><dt> 오메가3</dt></a>
                    <dd><a href="./shop/item.php?it_id=1332425915">노르딕내추럴스</a></dd>
                    <dd><a href="./shop/item.php?it_id=1320779680">내추럴팩터스</a></dd>
                    <dd><a href="./shop/item.php?it_id=1149781119">칼슨</a></dd>
                    <dd><a href="./shop/list.php?ca_id=1611">오메가 3 6 9</a></dd>
                    <dd><a href="./shop/list.php?ca_id=1610">모든 오메가3</a></dd>
                </dl>
                <dl class="category_zoneC">
                    <a href="./shop/list.php?ca_id=1090"><dt>글루코사민 / 콘드로이친</dt></a>
                    <dd><a href="./shop/search.php?it_maker=REXALL+SUNDOWN">오스테오</a></dd>
                    <dd><a href="./shop/item.php?it_id=1374115231">라이프익스텐션</a></dd>
                    <dd><a href="./shop/item.php?it_id=1109671190">네이쳐메이드</a></dd>
                    <dd><a href="./shop/item.php?it_id=1215559160">GNC</a></dd>
                    <dd><a href="./shop/list.php?ca_id=1120">관절건강모음</a></dd>
                </dl>
                <dl class="category_zoneD">
                    <a href="./shop/list.php?ca_id=1711"><dt>헬스보충제</dt></a>
                    <dd><a href="./shop/list.php?ca_id=171110">프로틴</a></dd>
                    <dd><a href="./shop/item.php?it_id=1349824387">옵티멈뉴트리션프로틴</a></dd>
                    <dd><a href="./shop/item.php?it_id=1314135156">머슬 밀크</a></dd>
                    <dd><a href="./shop/item.php?it_id=1393459135">퀘스트뉴트리션바</a></dd>
                    <dd><a href="./shop/list.php?ca_id=1711">모든 헬스보충제</a></dd>
                </dl>
            </div>
            <!--추천상품-->
            <div class="recommend_box">
                <span><span class="ico_discount">초특가</span><a href="./shop/item.php?it_id=1332425915"><img data-original="http://115.68.20.84/mall6/health_banner/banner_recommend1.jpg" alt="추천1" /></a></span>
                <span><a href="./shop/item.php?it_id=1219209427"><img data-original="http://115.68.20.84/mall6/health_banner/banner_recommend2.jpg" alt="추천2" /></a></span>
            </div>
            <!--베스트상품-->
            <div class="best_zone">
                <h2><img data-original="http://115.68.20.84/mall6/title_OpleBest.png" alt="Ople Best" /></h2>
                <p class="planning"><a href="./shop/event.php?ev_id=1395642195"><img data-original="http://115.68.20.84/mall6/health_banner/banner_best.jpg" alt="기획전 비타민D" /></a></p>
                <div class="best_list">
                    <ul>
                        <li class="first"><a href="./shop/item.php?it_id=1328910099"><img data-original="http://115.68.20.84/mall6/health_banner/best_product01.jpg" alt="베스트상품1" /></a></li>
                        <li><a href="./shop/item.php?it_id=1332425915"><img data-original="http://115.68.20.84/mall6/health_banner/best_product02.jpg" alt="베스트상품2" /></a></li>
                        <li><a href="./shop/item.php?it_id=1395792795"><img data-original="http://115.68.20.84/mall6/health_banner/best_product03.jpg" alt="베스트상품3" /></a></li>
                        <li class="first"><a href="./shop/item.php?it_id=1314862063"><img data-original="http://115.68.20.84/mall6/health_banner/best_product04.jpg" alt="베스트상품4" /></a></li>
                        <li><a href="./shop/item.php?it_id=1395265853"><img data-original="http://115.68.20.84/mall6/health_banner/best_product05.jpg" alt="베스트상품5" /></a></li>
                        <li><a href="./shop/item.php?it_id=1174531678"><img data-original="http://115.68.20.84/mall6/health_banner/best_product06.jpg" alt="베스트상품6" /></a></li>
                    </ul>
                </div>
            </div>
            <!--SpecialArea-->
            <div class="SpecialZone">
                <!--Weekley-->
                <div class="weekleyZone">
                    <h2><img data-original="http://115.68.20.84/mall6/title_SpecialWeekly.png" alt="Special Weekley" /></h2>
                    <p><a href="./shop/search.php?it_maker=Enzymedica"><img data-original="http://115.68.20.84/mall6/health_banner/banner_specialWeek.jpg" alt="스페셜브랜드" /></a></p>
                </div>
                <!--brand-->
                <div class="brandZone">
                    <h2><img data-original="http://115.68.20.84/mall6/title_SpecialBrand.png" alt="Special Brand" /></h2>
                    <ul>
                        <li><a href="./shop/search.php?it_maker=Nordic+Naturals" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand01.png"></a></li>
                        <li><a href="./shop/search.php?it_maker=Source+Naturals" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand02.png"></a></li>
                        <li><a href="./shop/search.php?it_maker=Nature%27s+way" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand03.png"></a></li>
                        <li><a href="./shop/search.php?it_maker=Now+foods" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand04.png"></a></li>
                        <li><a href="./shop/search.php?it_maker=Rainbow+Light" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand05.png"></a></li>
                        <li><a href="./shop/search.php?it_maker=Jarrow+Formulas" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand06.png"></a></li>
                        <li><a href="./shop/search.php?it_maker=New+Chapter" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand07.png"></a></li>
                        <li><a href="./shop/search.php?it_maker=Natural+Factors" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand08.png"></a></li>
                        <li><a href="./shop/search.php?it_maker=Doctor%27s+Best" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand09.png"></a></li>
                        <li><a href="./shop/search.php?it_maker=Carlson+Laboratories" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand10.png"></a></li>
                        <li><a href="./shop/search.php?it_maker=Enzymatic+Therapy+" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand11.png"></a></li>
                        <li><a href="./shop/search.php?it_maker=Bluebonnet+Nutrition" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand12.png"></a></li>
                        <li><a href="./shop/search.php?it_maker=NeoCell" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand13.png"></a></li>
                        <li><a href="./shop/search.php?it_maker=Nature%27s+Plus" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand14.png"></a></li>
                        <li><a href="./shop/search.php?it_maker=Barlean%27s" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand15.png"></a></li>
                        <li><a href="./shop/search.php?it_maker=Natrol" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand16.png"></a></li>
                    </ul>
                </div>

            </div>


<script type="text/javascript">



$(function(){
	// 롤링 타이틀 자동 생성
	var main_spot_cnt = $('.MainSpotImgTitle').length;
	var MainSpotTitle_html = '';
	var li_width = $('.MainSpotTitle').width()/main_spot_cnt;

	$('.MainSpotImg_mask').width($('.MainSpotImg').width() * main_spot_cnt);

	for(var i=0; i<main_spot_cnt; i++){
		var num = i + 1;
		$('.MainSpotImgTitle:eq('+i+')').parent().attr('num',num);
		MainSpotTitle_html += "<li num='"+num+"' "+(( i == 0 ) ? "class='active'":'')+" onmouseover=\"spot_change(this);\" style='width:"+li_width+"px'>"+$('.MainSpotImgTitle:eq('+i+')').html()+"</li>";
	}

	$('.MainSpotTitle').html(MainSpotTitle_html);

	spot_hover = false;
	jQuery.fx.interval = 5; // 애니메이션 프레임 조절
	sp_obj = $('.MainSpotTitle li:eq(0)');


	setInterval(function(){spot_change(sp_obj,1)},3000);
});

function spot_change (obj,mode){ // 롤링 체인지
	var width = $('.MainSpotImg').width();
	var num = $(obj).attr('num');


	if(mode != undefined && spot_hover == true){

		return false;
	}

	$('.MainSpotTitle li').removeClass('active');
	$(obj).addClass('active');

	$('.MainSpotImg_mask').stop(); 	$('.MainSpotImg_mask').animate({
		'left' : '-' + String( $('.MainSpotImg').width() * (num-1) )  + 'px'
	});


	if(sp_obj == ''){
		sp_obj = $('.MainSpotTitle li:eq('+0+')');
	}else if($(obj).next().length == 0){
		sp_obj = $('.MainSpotTitle li:eq('+0+')');
	}else{
		sp_obj = $(obj).next();
	}
}

$('.MainSpotZone').hover(function( result ){ // 마우스 오버시 자동롤링 되지 않도록
	var type = result.type;

	switch(type){
		case 'mouseenter' : spot_hover = true; break;
		case 'mouseleave' : spot_hover = false;  break;
	}
});
</script>
</div>
</td></tr></table>
</div>
<!-- 중간끝 -->


<!--FooterArea-->
    <div class="footerArea">
        <!--정보영역-->
        <div class="Footer_info">
            <div class="customerCenter"><p><img src="http://115.68.20.84/mall6/footer_customer_center.png" alt="고객상담안내:070-7678-7004 / 070-7678-7809" /></p></div>
            <div class="banner">
                <span><a href="./shop/call.php"><img src="http://115.68.20.84/mall6/btn_phone_call.png" alt="전화상담요청" /></a></span>
				<span><a href="#" onclick="oneday_sms_popup(); return false;"><img src="http://115.68.20.84/mall6/btn_gooday_sms.png" alt="굿데이세일SMS알람요청" /></a></span>
                <span><a href="./shop/event.php?ev_id=1393920135"><img src="http://115.68.20.84/mall6/btn_minishop.png" alt="만원의행복" /></a></span>
                <span><a href="./bbs/board.php?bo_table=health"><img src="http://115.68.20.84/mall6/btn_medicine.png" alt="영양제복용방법" /></a></span>
            </div>
            <div class="ople_infobox">
                <p class="goodChoice">
					<span><a onclick="window.open('http://ople.com/mall5/pop/goods_100Q.html','popup3', 'scrollbars=no,width=505,height=642,menubar=false'); return false;" href="#"><img src="http://115.68.20.84/mall6/btn_choicegoods.png" alt="정품보장" /></a></span>
					<span><a href="./shop/persnoal_number_info.php"><img src="http://115.68.20.84/mall6/btn_memNumber.png" alt="개인통관고유부호안내" /></a></span>
				</p>
                <p class="ople_sns">
                    <span class="facebook"><a href="https://www.facebook.com/opledotcom?ref=stream" target="_blank">오플 페이스북</a></span>
                    <span class="blog"><a href="http://ople.me/" target="_blank">오플 공식 블로그</a></span>
                </p>
            </div>
        </div>
        <!--Copyright-->
        <div class="copyright">
            <p class="BI"><img src="http://115.68.20.84/mall6/footer_opleBI.png" alt="오플닷컴" /></p>
            <ul class="footer_gnb">
                <li class="first"><a href="./shop/content.php?co_id=company">오플닷컴소개</a></li>
                <li><a href="./shop/content.php?co_id=FAQ">FAQ</a></li>
                <!--<li><a href="./shop/content.php?co_id=provision">이용안내</a></li>-->
                <li><a href="./shop/content.php?co_id=privacy">개인정보보호정책</a></li>
                <li><a href="./bbs/board.php?bo_table=qa">고객센터</a></li>
            </ul>
            <p>9033 Applewhite Rd.,Lytle Creek, CA, 92358 고객상담:070-7678-7004 / 정보관리책임자:로버트정 email:<a href="mailto:info@ople.com">info@ople.com</a></p>
            <p>(C) 2003~2014 OPLE.COM. All rights Reserved. </p>
        </div>

    </div>

	<!--div>
	9033 Applewhite Rd.,Lytle Creek, CA, 92358 고객상담:070-7678-7004<br> 정보관리책임자:로버트정 email:info@ople.com<br>
	(C) 2003~2012 <font color='#ff5b00'><b>OPLE.COM & OKFLEX</b></font>. All rights Reserved.</span>
	</div-->
<!--
<p style="width:243px;"><href="http://esc.wooribank.com/esc/cmmn/in/web2c001_06p.jsp?condition=www.ople.com" target="_blank"><img src="http://115.68.20.84/main/escrow.gif"></a></p>
-->

</div>

<style type="text/css">
.layer_wrap{
	position:absolute;
	top:0px;
	bottom:0px;
	left:0px;
	right:0px;
	z-index:99999;
	display:none;
}
.layer_mask{
	position:absolute;
	top:0px;
	bottom:0px;
	left:0px;
	right:0px;
	background-color:#000000;
	opacity: 0.4;
	filter: alpha(opacity=40);
}
.layer_contents_wrap{
	position:absolute;
	z-index:9999999;
	left:50%;
	top:50%;
	width: 459px;
	height: 277px;
	background-color: #fff;
	margin-left: -250px;
	margin-top: -138px;
}


.layer_close_btn{
	position:absolute;
	left:412px;
	top:28px;
	width: 24px;
	height: 30px;
	cursor: pointer;
	margin:0px;
}

.layer_contents{
	background-color:#ffffff;
	margin: 33px 17px 0 17px;
}
</style>


<div class='layer_wrap'>
	<div class='layer_mask'></div>
	<div class='layer_contents_wrap'>
		<div class="layer_title">
			<img src="http://115.68.20.84/main/sms_title.jpg">
			<p class='layer_close_btn' onclick="oneday_sms_popup();"></p>
		</div>

		<div class="layer_contents"></div>
	</div>

</div>


</div>

<script type="text/javascript">
/* 굿데이 이벤트 SMS 수신 레이어 */
function oneday_sms_popup(){
	if($('.layer_wrap').css('display') == 'block'){
		$('.layer_wrap').hide();
		$('.layer_title , .layer_contents').empty();
		$('.site_wrap').removeAttr('style');
		$('.Floating_bannerArea').show();

		return false;
	}else{

		$(window).scrollTop(0);
		$('.site_wrap').css({
			'overflow' : 'hidden',
			'width' : '100%',
			'height' : $(window).height()+'px'
		});


		$('.layer_wrap').show();


		$.ajax({
			url : './oneday_sms.php',
			cache : false,
			headers : {
				"cache-control" : "no-cache",
				"pragma" : "no-cache"
			},
			success : function ( result ) {
				$('.layer_title').html("<img src='http://115.68.20.84/main/sms_title.jpg'><p class='layer_close_btn' onclick='oneday_sms_popup();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>");
				$('.layer_contents').html(result);
				$('.layer_contents_wrap').css({
					'margin-left':'-'+ ($('.layer_contents_wrap').width()/2),
					'margin-top':'-'+ ($('.layer_contents_wrap').height()/2)
				});
				$('.Floating_bannerArea').hide();

			}
		});

	}

}

$('.layer_mask').click(function (t){
	oneday_sms_popup();
});

</script>

<script type="text/javascript">
$('.category > ul > li').hover(function(){


	var this_class = $(this).attr('class').replace(' first','');

	if($('.'+this_class+'_box').css('display') == 'none'){

		$('.DepthCategory').stop();
		$('.DepthCategory').removeAttr('style');
		$('.DepthCategory:visible').slideUp(300);
		$('.'+this_class+'_box').slideDown(300);

	}
});

$('.category').mouseleave(function(){
	$('.DepthCategory').stop();
	$('.DepthCategory').removeAttr('style');
	$('.DepthCategory:visible').slideUp(300);

});

/*
$(function(){
	hide_cate_cnt = $('.hide_cate_flag').length;
	for(var i=0; i<hide_cate_cnt; i++){
		var ca_id = $('.hide_cate_flag:eq('+i+')').attr('ca_id');
		var last_dd = $('.DepthCategory.depth01_box[ca_id='+ca_id+'] dd:last');
		var more_btn = "<dd><a href='#' class='navi_cate_more_btn' onclick=\"navi_hide_cate_show('"+ca_id+"'); return false;\">더보기</a></dd>";
		last_dd.after(more_btn);
	}
});

function navi_hide_cate_show(ca_id){
	$('.DepthCategory.depth01_box[ca_id='+ca_id+']').find('.cate_hide_navi').toggle();
}
*/
$(function(){
	var hide_cate_cnt = $('.hide_cate_flag').length;
	for(var i=0; i<hide_cate_cnt; i++){
		var ca_id = $('.hide_cate_flag:eq('+i+')').attr('ca_id');
		$('.DepthCategory[ca_id='+ca_id+']').find('dd[ca_view]').css({
			'font-weight' : 'bold'
		});

	}
});



$(window).scroll(function( sc ){
	var now_position = $(window).scrollTop();
	console.log($(document).height() - $(window).scrollTop() - $(window).height());

	if(now_position > 420 && $(document).height() - $(window).scrollTop() - $(window).height() >= 235){
		$('.Floating_bannerArea').stop();
		if($('.Floating_bannerArea').css('top') == 'auto'){
			$('.Floating_bannerArea').css({'top' : $(window).scrollTop()});
		}
		$('.Floating_bannerArea').animate({
			'top' : $(window).scrollTop()
		},250);
	}else if( $(document).height() - $(window).scrollTop() - $(window).height() < 235 ){
		$('.Floating_bannerArea').stop().removeAttr('style').css({
			'top' : 'auto'
		}).animate({
			'bottom' : '0px'
		},250);
	}else{
		/*
		try{
			$('.Floating_bannerArea').finish().removeAttr('style');
		}catch( e ){
			$('.Floating_bannerArea').stop().removeAttr('style');
		}
		*/
		$('.Floating_bannerArea').stop().removeAttr('style');
	}
});





</script>

<script type="text/javascript" src="./js/wrest.js"></script>
<script type="text/javascript">
</script>

<!-- 새창 대신 사용하는 iframe -->
<iframe width=0 height=0 name='hiddenframe' style='display:none;'></iframe>

<!-- <div style='float:left; width:px; text-align:center;'>RUN TIME : 0.0040009021759033<br></div> --> </body>
</html>
