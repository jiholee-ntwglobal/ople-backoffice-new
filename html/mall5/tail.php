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

<style type="text/css">
.DepthCategory dd{
	position:relative;
}
</style>

<?php
$sec = get_microtime() - $begin_time;
$file = $_SERVER[PHP_SELF];
?>

<!--FooterArea-->
    <div class="footerArea">
        <!--TopButton-->
        <div style="position: absolute;text-align: right;width: 1090px;margin-top:-12px;"><a href="#" onclick="scroll_top(); return false;"><img src="http://115.68.20.84/main/btn_top.png" alt="상단으로"></img></a></div>
        <!--정보영역-->
        <div class="Footer_info">
            <div class="customerCenter"><p><img src="http://115.68.20.84/mall6/footer_customer_center3.png" alt="고객상담안내:070-7678-7004 / 070-7678-7809" /></p></div>
            <div class="banner">
                <span><a href="<?php echo $g4['path'];?>/shop/call.php"><img src="http://115.68.20.84/mall6/btn_phone_call.png" alt="전화상담요청" /></a></span>
				<span><a href="#" onclick="oneday_sms_popup(); return false;"><img src="http://115.68.20.84/mall6/btn_gooday_sms.png" alt="굿데이세일SMS알람요청" /></a></span>
                <span><a href="<?php echo $g4['path'];?>/shop/event.php?ev_id=1418090574"><img src="http://115.68.20.84/mall6/btn_minishop.png" alt="만원의행복" /></a></span>
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
                <!--<li><a href="<?php echo $g4['shop_path'];?>/content.php?co_id=provision">이용안내</a></li>-->
                <li><a href="<?php echo $g4['shop_path'];?>/content.php?co_id=privacy">개인정보보호정책</a></li>
                <li><a href="<?php echo $g4['path'];?>/cs_center.php">고객센터</a></li>
            </ul>
            <p>9033 Applewhite Rd.,Lytle Creek, CA, 92358 고객상담:070-7678-7004 / 정보관리책임자:로버트정 email:<a href="mailto:info@ople.com">info@ople.com</a></p>
            <p>(C) 2003~<?php echo date('Y');?> OPLE.COM<?/* & OKFLEX*/?>. All rights Reserved. </p>
			<p style='line-height: 12px;font-size: 11px; margin-top:5px;'>The products and the claims made about specific products on or through this site have not been evaluated by Ople.com or the United States Food and Drug Administration and are not approved to diagnose, treat, cure or prevent disease. The information provided on this site is for informational purposes only and is not intended as a substitute for advice from your physician or other health care professional or any information contained on or in any product label or packaging. You should not use the information on this site for diagnosis or treatment of any health problem or for prescription of any medication or other treatment. <br/>You should consult with a healthcare professional before starting any diet, exercise or supplementation program, before taking any medication, or if you have or suspect you might have a health problem.<br/>
Not responsible for typographical errors or misprints. Product availability, pricing, and promotions are subject to change without notice.<br/>
</p>
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
		주소 : 서울 금천구 서부샛길 638, 402-2호(가산동, 대륭테크노타운 7차) / 사업자등록번호 : 122-32-71636 / 통신판매업신고번호 : 제2014-서울금천-0718호 / <br/> 대표 : 차영석 / 상호명 : 네이코
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

.layer_wrap2{
	position:absolute;
	top:0px;
	bottom:0px;
	left:0px;
	right:0px;
	z-index:3;
	display:none;
}

.layer_mask2{
	position:absolute;
	top:0px;
	bottom:0px;
	left:0px;
	right:0px;
	background-color:#000000;
	opacity: 0.4;
	filter: alpha(opacity=40);
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

<div class='layer_wrap2'>
	<div class='layer_mask2'></div>
</div>

<?if($goodday_flag){?>
<style type="text/css">
  .event_detail_layer{
  position:absolute;
  top:0px;
  left:0px;
  right:0px;
  height:200px;
  background-color:#f69125;
  display:none;
  }
  .event_contA{
  position:relative;
  width:1100px;
  margin:0 auto;
  }
  .event_close{
  position:absolute;
  right:-100px;
  top:10px;
  cursor:pointer;
  }
</style>
<div class='event_detail_layer'>
  <p class='event_contA'>
    <span class='event_img'><img src="http://115.68.20.84/event/top_full_banner.jpg" alt="event" usemap="#event_full" border="0" />
    <map name="event_full" id="event_full">
      <area shape="rect" coords="17,15,1349,187" href="<?=$g4['shop_path']?>/goodday.php" target="_self" onfocus="this.blur();" />
    </map>
    </span>
    <span class="event_close" onclick='event_detail_layer_close();'><img src='http://115.68.20.84/event/button_close.png' alt='이벤트닫기'/></span>
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


			}
		});

	}

}

$('.layer_mask').click(function (t){
	oneday_sms_popup();
});

</script>

<script type="text/javascript">
var sub_cate_detail = false;
var sub_cate_detail_open = false;

$('.category > ul > li').hover(function(){

//	var ca_id = $(this).attr('ca_id');
	var this_class = $(this).attr('class').replace(' first','');
	if(sub_cate_detail_open == true){
		sub_cate_detail = false;
	}


	if($('.DepthCategory[ca_id=11]').hasClass('depth_full_box') == true){
		$('.DepthCategory').removeClass("depth_full_box").empty();
		$('.'+this_class+'_box').hide();
	}





	if($('.'+this_class+'_box').css('display') == 'none'){

		$('.DepthCategory').stop();
		$('.DepthCategory').removeAttr('style');
		$('.DepthCategory').children().remove();

		$("#hidden_cate_depth_" + $('.'+this_class+'_box').attr("ca_id")).children().clone().appendTo('.'+this_class+'_box');


		$('.DepthCategory:visible').slideUp(300);
		$('.'+this_class+'_box').slideDown(300);



	}
});

$('.category').mouseleave(function(){
	if(sub_cate_detail == false){
		$('.DepthCategory').stop();
		$('.DepthCategory').removeAttr('style');
		$('.DepthCategory:visible').slideUp(300);
		$('.DepthCategory').removeClass("depth_full_box").empty();
		$(".inBox_depth_warp").hide();
	}

});

function showDetailCategory(num){
	sub_cate_detail = true;
	sub_cate_detail_open = true;
	//$(".DepthCategory[ca_id=" + num + "]").children().hide(function(){ $(this).empty(); }).show(function(){ $("#hidden_cate_detail_" + num).clone().appendTo(".DepthCategory[ca_id=" + num + "]");});
	//$("#hidden_cate_detail_" + num).clone().appendTo(".DepthCategory[ca_id=" + num + "]");
	$(".DepthCategory[ca_id=" + num + "]").html($("#hidden_cate_detail_" + num).clone().html()).addClass("depth_full_box");
	//$("#hidden_cate_detail_" + num).clone().appendTo(".DepthCategory[ca_id=" + num + "]")
	//$("#hidden_cate_detail_" + num).show();
}

$('.category').delegate('.depth_full_box','mouseover',function(){
	sub_cate_detail = false;
});

$('.DepthCategory').delegate(".DepthCategory > div > dl >dd > strong > a","mouseover",function(){
	$(".inBox_depth_warp").hide();
	var depth3 = $(this).parent().next();
	if(depth3.find('.inBox_depth').children().length > 0){

		depth3.show();
		depth3.css({
			'right' : '-' + (depth3.width() ) + 'px'
		});
	}
});







function scroll_top(){
	$("html, body").stop().animate({ scrollTop: 0 });
	return false;
}



</script>



<?php
include_once($g4['full_path']."/tail.sub.php");
?>
