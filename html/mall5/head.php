<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
// 사이트 최적화를 위한 불필요한 include 제거 2014-10-08 홍민기

include_once $g4['full_path']."/head.sub.php";

if(is_admin($member['mb_id'])){
	include_once $g4['full_path']."/lib/visit.lib.php"; // 관리자만 방문객 수 로드
	include_once $g4['full_path']."/lib/connect.lib.php"; // 관리자만 접속자 수 로드
}
//include_once $g4['full_path']."/lib/popular.lib.php"; // 인기검색어 사용 안함
//include_once $g4['full_path']."/lib/outlogin.lib.php"; // 아웃로그인 사용 안함
//include_once $g4['full_path']."/lib/poll.lib.php"; // 설문조사 사용 안함

//print_r2(get_defined_constants());

// 사용자 화면 상단과 좌측을 담당하는 페이지입니다.
// 상단, 좌측 화면을 꾸미려면 이 파일을 수정합니다.

//$table_width = 990;

//print_r2($g4);
//$dir = dirname($HTTP_SERVER_VARS["PHP_SELF"]);

# 관리자 여부 체크(최고관리자 제외)
if(!$is_admin){
	$is_auth = false;
	$sql = " select count(*) as cnt from ".$g4['auth_table']." where mb_id = '".$member['mb_id']."' ";
	$row = sql_fetch($sql);
	if ($row['cnt'] > 0){
		$is_auth = true;
	}
	unset($sql,$row);
}

# 무료배송 금액 #
$no_send_cost_usd = number_format(round($default['no_send_cost'] / $default['de_conv_pay'],2),2,'.',',');
# 캐싱 데이터 로드 #
include $g4['full_path'].'/cache/main_cache.php';
?>
<div class="site_wrap">
<style>
.amount { color:#2266BB; font-weight:bold; font-family:Verdana;}
.c1 { background-color:#94D7E7; }
.c2 { background-color:#E7F3F7; }
.cate_hide_navi{
	display:none;
}
</style>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-59003549-1', 'auto');
  ga('send', 'pageview');

</script>
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
  <!--
  <style type="text/css">
    .TopBannerArea {height:74px;background:url(http://115.68.20.84/event/bg_heaerTop.gif) repeat-x 0 0;width:100%;}
    .BannerObject {width:1100px;margin:0 auto;}
  </style>
<div class="TopBannerArea">
  <?php
  if(date('Ymd') >= '20141106' && date('Ymd') <= '20141106' ){
	  $goodday_flag = true;
  ?>
  <p class="BannerObject"><a href="#" onclick='event_detail_layer_open()'><img src="http://115.68.20.84/event/Top_banner_renewal_new.jpg" alt="리뉴얼이벤트"/></a></p>
  <?php }else{?>
  <p class="BannerObject"><a href="<?php echo $g4['shop_path'];?>/content.php?co_id=Renewal_Event"><img src="http://115.68.20.84/event/Top_banner_renewal.jpg" alt="리뉴얼이벤트"/></a></p>
  <?php }?>

</div>
 -->
  <!-- 2015.0202backup
  	<style type="text/css">
    .TopBannerArea {height:74px;background:url(http://115.68.20.84/event/bg_top_valentine.gif) repeat-x 0 0;width:100%;}
    .BannerObject {width:1190px;margin:0 auto;}
  </style>
  <div class="TopBannerArea">
    <?php if(date('Ymd') >= '20150129' && date('Ymd') <= '20150129' ){
	  $goodday_flag = true;
  ?>
    <p class="BannerObject"><a href="#" onclick='event_detail_layer_open()'><img src="http://115.68.20.84/event/Top_banner_godiva_gooday_new.jpg" alt="고디바+굿데이세일"/></a></p>
    <?php }else{?>
    <p class="BannerObject"><a href="<?php echo $g4['shop_path'];?>/event.php?ev_id=1418289584" ><img src="http://115.68.20.84/event/Top_banner_valentine.jpg" alt="발렌타인데이고디바"/></a></p>
    <?php }?>  -->

<?php if(date('Ymd') >= '20150319' && date('Ymd') <= '20150530'){?>
    <style type="text/css">		
		.BannerObject {position:relative;margin:0 auto;}
		.price_USD_top {position: absolute;color: #fff;font-size: 12px;height: 50px;line-height: 50px;}
		.price_USD_top strong {font-size:13px;}
		.price_USD_top strong span{font-weight: normal;}
		.price_USD_top em {font-style: normal;color: #f1b4b4;}
  </style>
  <div class="TopBannerArea">
      <?php if(date('Ymd') >= '20150430' && date('Ymd') <= '20150430'){ 
      $goodday_flag = true;
      ?>
      <style type="text/css">
	  	.TopBannerArea {height: 74px;background-color: #ffa126;width: 100%;display:none;}
	  </style>
  <!-- <p class="BannerObject" style="width:1300px"><a href="#" onclick='event_detail_layer_open()'><span><img src="http://115.68.20.84/event/Top_banner_gooday.jpg" alt="굿데이세일" /></span></a></p> -->
      <?php }else{?>
      <style type="text/css">
	  	.TopBannerArea {height: 50px;background-color: #d9c280;width: 100%;display:none;}
	  </style>
  <p class="BannerObject" style="width:1350px;">
  	<img src="http://115.68.20.84/event/top_title_banner_master.jpg" alt="마스터카드제휴" />
  </p>
  <?php }?>
  </div>
  <?php }?>

  <div class="wrap <?php echo $wrap_class;?>">
    <!--HeaderArea-->
    <div class="headerArea">
        <!--GNBArea-->
        <div class="gnbArea">


          <!-- 주말 증정이벤트(FreeGift) 시작 -->
          <?php if(date('Ymd') >= '20141115' && date('Ymd') <= '20141117'){?>
          <div style="position:absolute; margin-left: 1125px;">
            <img src="http://115.68.20.84/FreeGift/FreeGift11-2.png">
          </div>
          <?php }?>
          <!-- 주말 증정이벤트 끝 -->


            <div class="gnb_left">
                <ul>
					<?php if(!$is_member){?>
                    <li class="first"><a href="<?php echo $g4['bbs_path'];?>/login.php?url=<?php echo $urlencode?>"><strong>로그인</strong></a></li>
					<li><a href="<?php echo $g4['bbs_path'];?>/register.php">회원가입</a></li>
					<?php }else{?><li class="first"><a href="<?php echo $g4['bbs_path'];?>/logout.php"><strong>로그아웃</strong></a><?php if($is_admin == "super" || $is_auth){?>
					<span class="button_ad"><a href="<?php echo $g4['admin_path'];?>"><img src="<?php echo $g4['path'];?>/images/common/bt_admin.gif" alt="관리자" /></a></span><?php }?>
          <?php if($member){?><span class='point_box'><a href='javascript:win_point();'><?php echo $member['mb_point'];?></a></span><?php }?>
          </li>
					<li><a href="<?php echo $g4['bbs_path'];?>/member_confirm.php?url=register_form.php">정보수정</a></li>
					<?php }?>
                    <li><a href="<?php echo $g4['shop_path'];?>/mypage.php">마이페이지</a></li>
                    <li><a href="<?php echo $g4['path'];?>/cs_center.php" class="ico_customer"><strong>고객센터</strong></a></li>
                </ul>
            </div>
			<!-- Total Search -->
			<div class="TotalSearchArea">
				<form action="<?php echo $g4['shop_path'];?>/search<?=($search_test ? "_solr":"")?>.php" method='get'>
					<p class="OpleBI"><a href="<?php echo $g4['path'];?>?s_id=6">ople.com</a></p>
					<fieldset>
						<span><input type="text" name="search_str_all" required itemname="검색어" autocomplete="off" class="auto-search" style="background-image: url(<?php echo $g4['path'];?>/js/wrest.gif); background-position: 100% 0%; background-repeat: no-repeat;" value="<?php echo htmlspecialchars(stripslashes($_GET['search_str_all']));?>"></span>
						<span class="button"><input type="image" src="http://115.68.20.84/mall6/btn_totalsearch.gif" border="0" alt="검색"></span>
					</fieldset>
				</form>
			</div>
			<!-- Total End Search -->
            <div class="gnb_right">
                <ul>
                    <li class="basket"><a href="<?php echo $g4['shop_path']?>/cart.php">장바구니 <strong><?php echo $cate_cnt;?></strong></a></li>
                    <li><a href="<?php echo $g4['shop_path']?>/wishlist.php">위시리스트 <strong><?php echo $wish_cnt;?></strong></a></li>
                    <li><a href="<?php echo $g4['shop_path']?>/orderinquiry.php">주문/배송</a></li>
                </ul>
            </div>
        </div>
        <!--TabArea-->
        <div class="TabArea">
            <ul>
                <li class="tab_health"><a href="<?php echo $g4['path'];?>?s_id=3" class="<?php echo ($_SESSION['s_id'] == '3') ? 'active':'';?>">Health</a></li>
                <li class="tab_home"><a href="<?php echo $g4['path'];?>?s_id=4" class="<?php echo ($_SESSION['s_id'] == '4') ? 'active':'';?>">Home</a></li>
                <li class="tab_momnbaby"><a href="<?php echo $g4['path'];?>?s_id=5" class="<?php echo ($_SESSION['s_id'] == '5') ? 'active':'';?>">Mom&Baby,Kids</a></li>
                <li class="tab_bueaty"><a href="<?php echo $g4['path'];?>?s_id=1" class="<?php echo ($_SESSION['s_id'] == '1') ? 'active':'';?>">bueaty</a></li>
                <li class="tab_food"><a href="<?php echo $g4['path'];?>?s_id=2" class="<?php echo ($_SESSION['s_id'] == '2') ? 'active':'';?>">Food</a></li>
                <!--<li class="tab_fat"><a href="<?=$g4['path'];?>?s_id=6" class="<?=($_SESSION['s_id'] == '6') ? 'active':''?>">Fat</a></li>-->
            </ul>
			<p class="btn_prepay"><a href="<?php echo $g4['shop_path']?>/event.php?ev_id=1413794551">선결제포인트</a></p>
        </div>
    </div>
	<!--ContentsArea-->
    <div class="contentsArea">
        <!--ContentsHeader-->
        <div class="cont_header">
            <h1><a href="<?php echo $g4['path'];?>">OpleHealth</a></h1>
                        <!--banner-->
            <div class="top_banner">
				<?/*
                <p class="btn_best"><a href="<?php echo $g4['path'];?>/shop/event.php?ev_id=<?php echo $best_item_link[$_SESSION['s_id']];?>">베스트상품</a></p>
				*/?>
        <p class="btn_manwon"><a href="<?php echo $g4['path'];?>/shop/event.php?ev_id=1418090574">만원의행복</a></p>
				<p class="btn_hotdeal"><a href="<?php echo $g4['path'];?>/shop/list_new.php">신규상품</a></p>
        <p class="btn_best"><a href="<?php echo $g4['path'];?>/shop/best_item.php?s_id=<?php echo $_SESSION['s_id'];?>">베스트상품</a></p>
        <p class="btn_user"><a href="<?php echo $g4['path'];?>/sjsjin/hoogi_list.php">사용자후기</a></p>
                <!--p class="btn_requst"><a href="<?php echo $g4['path'];?>/sjsjin/item_onrequest_write.php">상품입고요청</a></p-->
            </div>
            <!--CategoryArea-->
            <div class="category">
                <?php
				# 관리자에서 지정한 제품관 헤더파일 로드 (cate_menu 디렉토리) 2014-07-08 홍민기 #
				if(file_exists($g4['full_path'].'/cate_menu/'.$station['head_file'])){
					include $g4['full_path'].'/cate_menu/'.$station['head_file'];
				}else{
					echo "<b>head파일이 존재하지 않습니다. ".$g4['full_path'].'/cate_menu/'.$station['head_file'].' 파일을 업로드해 주세요.</b>';
				}
				?>
            </div>
        </div>

		<div class="contents <?php echo (!defined('_INDEX_')) ? 'contents_sub':'';?>">
        
      <!-- 무료배송 띠배너 
       <style type="text/css">
		 	.contents_sub .HotEventArea {display:none;}
		 	.HotEventArea {position: absolute;top: 512px;}
			.health .HotEventArea {margin:23px -35px 0 -35px;}
			.home .HotEventArea {margin:-4px -35px 0 -35px;}			
			.momnkids .HotEventArea {margin:29px -35px 0 -35px;}
			.beauty .HotEventArea {margin:11px -35px 0 -35px;}
			.food .HotEventArea {margin:202px -35px 0 -35px;}
			
			.main .hotdealzoneArea { margin-top: 54px;}			
			.health .hotdealzoneArea {margin:65px -35px 10px -35px;}
			.home .hotdealzoneArea {  margin: 63px -35px 10px -35px;}
			.momnkids .hotdealzoneArea {margin:65px -35px 10px -35px;}
			.beauty .hotdealzoneArea {margin: 64px -35px 10px -35px}
			.food .hotdealzoneArea {margin:63px -35px 10px -35px;}
		 </style>
           
    <p class="HotEventArea">
  	<a href="<?php echo $g4['shop_path'];?>/Bom_Big_Event.php"><span class="price_USD_top" style="left: 460px;"><strong>$40<span>(<?php echo number_format(40 * $default['de_conv_pay']);?>원)</span></strong> 이상 구매 시, <em> (단, 건강식품 <?php echo $default['no_send_cost_health_cnt'];?>병까지)</em></span>  	<span><img src="http://115.68.20.84/event/top_banner_hot012.jpg" alt="무료배송이벤트" /></span></a></p>
    -->
    


<!-- ContentsArea -->

      <!--       <?php if(date('Ymd') >= '20141101' && date('Ymd') <= '20151101'){?>  <?php }?>-->