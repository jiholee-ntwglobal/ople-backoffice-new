<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

//$admin = get_admin("super");

// 사용자 화면 우측과 하단을 담당하는 페이지입니다.
// 우측, 하단 화면을 꾸미려면 이 파일을 수정합니다.
?>
</div>
</td></tr></table>
</div>
<!-- 중간끝 -->

<?php
$sec = get_microtime() - $begin_time;
$file = $_SERVER[PHP_SELF];
?>

<!--FooterArea-->
    <div class="footerArea">
        <!--정보영역-->
        <div class="Footer_info">
            <div class="customerCenter"><p><img src="http://115.68.20.84/mall6/footer_customer_center.png" alt="고객상담안내:070-7678-7004 / 070-7678-7809" /></p></div>
            <div class="banner">
                <span><a href="<?php echo $g4['path'];?>/shop/call.php"><img src="http://115.68.20.84/mall6/btn_phone_call.png" alt="전화상담요청" /></a></span>
				<span><a href="#" onclick="oneday_sms_popup(); return false;"><img src="http://115.68.20.84/mall6/btn_gooday_sms.png" alt="굿데이세일SMS알람요청" /></a></span>
                <span><a href="<?php echo $g4['path'];?>/shop/event.php?ev_id=1393920135"><img src="http://115.68.20.84/mall6/btn_minishop.png" alt="만원의행복" /></a></span>
                <span><a href="<?php echo $g4['path'];?>/bbs/board.php?bo_table=health"><img src="http://115.68.20.84/mall6/btn_medicine.png" alt="영양제복용방법" /></a></span>
            </div>
            <div class="ople_infobox">
                <p class="goodChoice">
					<span><a onclick="window.open('http://ople.com/mall5/pop/goods_100Q.html','popup3', 'scrollbars=no,width=505,height=642,menubar=false'); return false;" href="#"><img src="http://115.68.20.84/mall6/btn_choicegoods.png" alt="정품보장" /></a></span>
					<span><a href="<?php echo $g4['path'];?>/shop/persnoal_number_info.php"><img src="http://115.68.20.84/mall6/btn_memNumber.png" alt="개인통관고유부호안내" /></a></span>
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
                <li class="first"><a href="<?php echo $g4['shop_path'];?>/content.php?co_id=company">오플닷컴소개</a></li>
                <li><a href="<?php echo $g4['shop_path'];?>/content.php?co_id=FAQ">FAQ</a></li>
                <!--<li><a href="<?php echo $g4['shop_path'];?>/content.php?co_id=provision">이용안내</a></li>-->
                <li><a href="<?php echo $g4['shop_path'];?>/content.php?co_id=privacy">개인정보보호정책</a></li>
                <li><a href="<?php echo $g4['bbs_path'];?>/board.php?bo_table=qa">고객센터</a></li>
            </ul>
            <p>9033 Applewhite Rd.,Lytle Creek, CA, 92358 고객상담:070-7678-7004 / 정보관리책임자:로버트정 email:<a href="mailto:info@ople.com">info@ople.com</a></p>
            <p>(C) 2003~<?php echo date('Y');?> OPLE.COM<?/* & OKFLEX*/?>. All rights Reserved. </p>
        </div>

    </div>
	<?php // 에스크로 인증마크

		if($domain_flag == 'kr'){?>
		<div class='ADD_footerArea'>
		<form name="shop_check" method="post" action="http://admin.kcp.co.kr/Modules/escrow/kcp_pop.jsp" target="kcp_pop">
			<input type="hidden" name="site_cd" value="D2348">

			<img src="<?php echo $g4['path'];?>/img/es_foot.gif" alt="에스크로 인증마크" usemap="#es_Map"/>
			<map name="es_Map" id="es_Map">
				<area shape="rect" coords="5,62,74,83" onclick="go_check(); return false;" href="#" alt="가입사실확인" onfocus="this.blur()">
			</map>
		</form>
		<script type="text/javascript">

		function go_check(){

			var status  = "width=500 height=450 menubar=no,scrollbars=no,resizable=no,status=no";
			var obj     = window.open('', 'kcp_pop', status);

			document.shop_check.method = "post";
			document.shop_check.target = "kcp_pop";
			document.shop_check.action = "http://admin.kcp.co.kr/Modules/escrow/kcp_pop.jsp";

			document.shop_check.submit();
		}

		</script>
		<?php
		}
		?>
<?php
$copyright = "
	<!--div>
	".(($domain_flag == 'kr')? 'USA <br/>':'')."9033 Applewhite Rd.,Lytle Creek, CA, 92358 고객상담:070-7678-7004<br> 정보관리책임자:로버트정 email:info@ople.com<br>
	(C) 2003~2012 <font color='#ff5b00'><b>OPLE.COM & OKFLEX</b></font>. All rights Reserved.</span>
	</div-->
";
if($domain_flag == 'kr'){

$copyright = "
	<div class='ADD_footer_info1'>
	<strong>한국내 소비자 보호를 위한 사업자 정보를 제공합니다. 당사는 소비자 피해를 최소화 하는 대행사로 해당상품의 영업소가 아닙니다.</strong> <br/>본 사이트는 미국 및 전세계에 있는 교민을 위한 한국어 건강몰로 미국은 물론 전세계로 배송되며 모든 관리및 운영은 미국에서 제공되고 <br/> 법률적인 지역은 미국입니다.
	</div>
	<div class='ADD_footer_info2'>
		<strong>한국 광고 대행 </strong>
		주소 : 인천 계양구 계산동 오조산로45번길 8 203 / 사업자등록번호 : 122-32-71636 / 통신판매업신고번호 : 제2013-인천계양-0447호 / <br/> 대표 : 차영석 / 상호명 : 네이코
	</div></div>
	"
	.$copyright."
	";
}

echo $copyright;
?>
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

<?if($goodday_flag){?>
<style type="text/css">
  .event_detail_layer{
  position:absolute;
  top:0px;
  left:0px;
  right:0px;
  height:200px;
  background:url(http://115.68.20.84/event/bg_topfull_event.jpg) no-repeat 50% 0;
  display:none;
  }
  .event_contA{
  position:relative;
  width:1239px;
  margin:0 auto;
  }
  .event_close{
  position:absolute;
  right:0;
  top:10px;
  cursor:pointer;
  }
</style>
<div class='event_detail_layer'>
  <p class='event_contA'>
    <span class='event_img'><img src="http://115.68.20.84/event/top_full_banner.jpg" alt="event" usemap="#event_full" border="0">
    <map name="event_full" id="event_full">
      <area shape="rect" coords="41,15,367,182" href="<?=$g4['shop_path']?>/content.php?co_id=Renewal_Event" target="_self" onfocus="this.blur();" />
        <area shape="rect" coords="823,22,1128,189" href="<?=$g4['shop_path']?>/goodday.php" target="_self" onfocus="this.blur();" />
      </map>
    </span>
    <span class="event_close" onclick='event_detail_layer_close();'><img src='http://115.68.20.84/event/button_close.png' alt='이벤트닫기'></span>
  </p>
</div>
<script type="text/javascript">
<?php if(defined('_INDEX_')){?>
$(function(){
	if(getCookie('event_detail_layer_close') != 'close'){
		event_detail_layer_open();
	}
});
<?php }?>
function event_detail_layer_open(){
	$('.event_detail_layer').stop().slideDown();
	$('.TopBannerArea').slideUp();

}
function event_detail_layer_close(){
	$('.event_detail_layer').stop().slideUp(300,function(){
		$('.TopBannerArea').slideDown();
		setCookie('event_detail_layer_close','close',1);
	});
}
</script>
<?}?>

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
			url : '<?=$g4['path']?>/oneday_sms.php',
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


<?if($_SERVER['REMOTE_ADDR'] == '59.17.43.129'){?>

$(window).scroll(function( sc ){
	var now_position = $(window).scrollTop();
	var wrap_height = $('.site_wrap > .wrap').height();
	var top = $(window).height() - $('.flating_banner1').offset().top;


	if(wrap_height-now_position > 1000 && now_position-60 > 0){
		$('.Floating_bannerArea').stop(false,true).animate({'top' : now_position-60},250);
	}else if(now_position-60 <= 0){
		$('.Floating_bannerArea').stop(false,true).animate({'top' : 0},250);
	}

	/*
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

		$('.Floating_bannerArea').stop().removeAttr('style');
	}
	*/
});


<?}?>



</script>

<?php
include_once($g4['full_path']."/tail.sub.php");
?>