<?
include_once("./_common.php");
include_once("$g4[path]/lib/latest.lib.php");

define("_INDEX_", TRUE);

# 인덱스 롤링베너 데이터 로드 2014-04-15 홍민기 #
$banner_chkQ = sql_query("select * from banner_table order by sort asc");
$banner_cnt = mysql_num_rows($banner_chkQ);
if($banner_cnt>0){
	$i=1;
	while($banner_data = sql_fetch_array($banner_chkQ)){
		$banner_data['contents'] = Stripslashes($banner_data['contents']);
		$banner_lst .= "<div id='slide".$i."' class='slide'>".$banner_data['contents']."</div>\n";
		$banner_btn .= "<span class='jFlowControl'></span>\n";
		$i++;
	}
}

$g4[title] = "";
include_once("$g4[path]/head.php");
?>
<script type="text/javascript">
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
<script type="text/JavaScript" src="<?=$g4['path']?>/js/shop.js"></script>
<script type="text/javascript" src="<?=$g4['path']?>/js/jquery.banner.js"></script>
<style type="text/css">
<!--
#apDiv1 {
	position:absolute;
	width:205px;
	height:80px;
	z-index:1;
}
/*
#apDiv2 {
	position:absolute;
	width:205px;
	height:80px;
	z-index:1;
	visibility: hidden;
}
*/
-->
</style>




<div id="popupID2" style="z-index:9999;position:absolute;background-color:white;display:none;"></div>
<!-- <div class="longbanner"><img src="http://115.68.20.84/main/shipping_chusuk.png" width=755></a></div> -->
<!--
<table class="longbanner"width="755" border="0" cellspacing="0" cellpadding="0">
  <tr>
  	<td><a href="<?=$g4['path']?>/shop/event2012.php"><img src="http://115.68.20.84/main/event_top01.gif" width="379" border="0"></a></td>
    <td><a href="<?=$g4['path']?>/shop/event2012_2.php"><img src="http://115.68.20.84/main/event_top02.gif"width="270" border="0" ></a></td>
    <td><a href="<?=$g4['path']?>/bbs/board.php?bo_table=nordicevent"><img src="http://115.68.20.84/main/event_top03.gif" width="106" border="0"></a></td>
  </tr>
</table>
-->

<div id="sliderContainer">
	<div id="mySlides">
	
	<?=$banner_lst;?>
	
	<?/*
	<div id="slide2" class="slide"><a href="<?=$g4['path']?>/shop/event_0509.php"><img src="http://115.68.20.84/main/main_banner_tea.jpg" alt=""/></a><div class="slideContent"></div></div>
	
	<?if(date('Ymd')>='20140510' && date('Ymd')<'20140513'){?>
	<div id="slide3" class="slide"><!--<a href="<?=$g4['path']?>/shop/cleansing.php">--><img src="http://115.68.20.84/main/weekendgift.jpg" alt=""/><!--</a>--><div class="slideContent"></div></div>
	<?}?>
	<div id="slide4" class="slide"><a href="<?=$g4['path']?>/shop/event.php?ev_id=1395642194"><img src="http://115.68.20.84/main/vitamind_ban.jpg" alt=""/></a><div class="slideContent"></div></div>
	<div id="slide5" class="slide"><a href="<?=$g4['path']?>/shop/event.php?ev_id=1393217385"><img src="http://115.68.20.84/main/banner-a50.gif" alt=""/></a><div class="slideContent"></div></div>
<!-- <div id="slide4" class="slide"><a href="<?=$g4['path']?>/shop/event.php?ev_id=1380647643"><img src="http://115.68.20.84/main/banner-a1-nnsd.jpg" alt=""/></a><div class="slideContent"></div></div> -->
<!-- <div id="slide5" class="slide"><a href="<?=$g4['path']?>/shop/event.php?ev_id=1377246901"><img src="http://115.68.20.84/main/banner-a1-newchapter.jpg" alt=""/></a><div class="slideContent"></div></div> -->
	<div id="slide6" class="slide"><a href="<?=$g4['path']?>/shop/item.php?it_id=1219209427"><img src="http://115.68.20.84/main/pharmanac.jpg"alt="" /></a><div class="slideContent"></div></div>
<!-- <div id="slide4" class="slide"><a href="<?=$g4['path']?>/shop/event.php?ev_id=1379713763"><img src="http://115.68.20.84/main/ys.jpg" alt=""/></a><div class="slideContent"></div></div> -->
	*/?>
	</div>
	<div id="myController">
		<?=$banner_btn;?>
		<?/*
		<?if(date('Ymd')>='20140510' && date('Ymd')<'20140513'){?>
		<span class="jFlowControl"></span>		    		    
		<?}?>
		<span class="jFlowControl"></span>		    		    
		<span class="jFlowControl"></span>
		<span class="jFlowControl"></span> 
		<span class="jFlowControl"></span>		
		*/?>

	</div>
	<div class="jFlowPrev"></div>
	<div class="jFlowNext"></div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
	    $("#myController").jFlow({
			controller: ".jFlowControl", // must be class, use . sign
			slideWrapper : "#jFlowSlider", // must be id, use # sign
			slides: "#mySlides",  // the div where all your sliding divs are nested in
			selectedWrapper: "jFlowSelected",  // just pure text, no sign		
			effect: "flow", //this is the slide effect (rewind or flow)
			width: "755px",  // this is the width for the content-slider
			height: "230px",  // this is the height for the content-slider
			duration: 700,  // time in milliseconds to transition one slide			
			pause: 5000, //time between transitions
			prev: ".jFlowPrev", // must be class, use . sign
			next: ".jFlowNext", // must be class, use . sign
			auto: true	
    });
});
</script>
   <table width="755" border="0" cellspacing="0" cellpadding="0">
  		<tr>
          <td style="padding-top:20px;">
          <table width="755" border="0" cellspacing="0" cellpadding="0" style="height:197px;background: url(http://115.68.20.84/main/05event_main_bannerBG.jpg) no-repeat 0 0;">
          <tr>
            <td style="width:172px;"></td>
			<td width="193px" style="text-align:left;padding-top:57px;"><a href="<?=$g4['path']?>/shop/5event_1.php"><img src="http://115.68.20.84/main/05event_banner01.jpg" border="0"></a></td>
            <td width="193px" style="text-align:left;padding-top:57px;"><a href="<?=$g4['path']?>/shop/5event_2.php"><img src="http://115.68.20.84/main/05event_banner02.jpg"  border="0"></a></td>
            <td width="193px" style="text-align:left;padding-top:57px;"><a href="<?=$g4['path']?>/shop/5event_3.php"><img src="http://115.68.20.84/main/05event_banner03.jpg" border="0"></a></td>
          </tr>
        </table></td>
      </tr>
	  <tr>
	  <td style="padding-top:15px;">
	  <a href="<?=$g4['path']?>/shop/event.php?ev_id=1393920134"><img src="http://115.68.20.84/main/manwon_ban.gif" alt=""/></a>
	  </td>
	  </tr>
      <tr>
        <td style="padding:15px 0">
        <table width="755" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td><a href="<?=$g4['path']?>/shop/search.php?it_maker=Enzymedica"><img src="http://115.68.20.84/main/banner-c1.jpg" width="252" height="130" border="0"></a></td>
            <td><a href="<?=$g4['path']?>/shop/item.php?it_id=1177782798"><img src="http://115.68.20.84/main/banner-c2_uo.jpg" width="252" height="130" border="0"></a></td>
            <td><a href="<?=$g4['path']?>/shop/search.php?it_maker=Redd+Remedies"><img src="http://115.68.20.84/main/banner-c3.jpg" width="251" height="130" border="0"></a></td>
          </tr></table>

      <tr>
        <td style="padding-top:5px">
        <table width="755" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td><a href="<?=$g4['path']?>/shop/list.php?ca_id=10"><img src="http://115.68.20.84/main/banner_d1.jpg" border="0"></a></td>
            <td><a href="<?=$g4['path']?>/shop/search.php?it_maker=NeoCell"><img src="http://115.68.20.84/main/banner_d2.jpg"  border="0"></a></td>
          </tr>
          </table>
   		<tr>
          <td style="padding-top:5px; ">
          <table width="755" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td><img src="http://115.68.20.84/main/LifeStyle_b_01.jpg" border="0"></td>
            <td><a href="<?=$g4['path']?>/shop/search.php?it_maker=ATTITUDE+Eco"><img src="http://115.68.20.84/main/LifeStyle_b_02.jpg" border="0"></a></td>
            <td><a href="<?=$g4['path']?>/shop/search.php?it_maker=Nuxe"><img src="http://115.68.20.84/main/LifeStyle_b_03.jpg" border="0"></a></td>
            <td><a href="<?=$g4['path']?>/shop/search.php?it_maker=Tea+forte"><img src="http://115.68.20.84/main/LifeStyle_b_04.jpg" border="0"></a></td>
          </tr>
        </td>
      </tr>         
     
          </table>
           
          <br/><br/><a href="<?=$g4['path']?>/shop/list.php?ca_id=9840"><img src='http://115.68.20.84/main/banner_diet.jpg'></a>
        </td>
      </tr>
      <tr>
        <td style="padding-top:20px">
        <!-- 브랜드배너 -->
        <div class="brands">
        <center><br><br><br>
        <table class="brandtable" width="95%"  border="0" cellpadding="0" cellspacing="0" >
      <tr>
        <td><a href="<?=$g4['path']?>/shop/search.php?it_maker=Nordic+Naturals"><img src="<?=$g4['path']?>/images/main/brand01.gif" width="145" height="57" border="0"></a></td>
        <td><a href="<?=$g4['path']?>/shop/search.php?it_maker=new+chapter"><img src="<?=$g4['path']?>/images/main/brand02.gif" width="145" height="57" border="0"></a></td>
        <td><a href="<?=$g4['path']?>/shop/search.php?it_maker=Bluebonnet+Nutrition"><img src="<?=$g4['path']?>/images/main/brand03.gif" width="145" height="57" border="0"></a></td>
        <td><a href="<?=$g4['path']?>/shop/search.php?it_maker=Now+Foods"><img src="<?=$g4['path']?>/images/main/brand04.gif" width="145" height="57" border="0"></a></td>
        <td><a href="<?=$g4['path']?>/shop/search.php?it_maker=Source+Naturals"><img src="<?=$g4['path']?>/images/main/brand05.gif" width="145" height="57" border="0"></a></td>
      </tr>
      <tr>
        <td><a href="<?=$g4['path']?>/shop/search.php?it_maker=Nature%27s+way"><img src="<?=$g4['path']?>/images/main/brand06.gif" width="145" height="57" border="0"></a></td>
        <td><a href="<?=$g4['path']?>/shop/search.php?it_maker=Country+Life"><img src="<?=$g4['path']?>/images/main/brand07.gif" width="145" height="57" border="0"></a></td>
        <td><a href="<?=$g4['path']?>/shop/search.php?it_maker=Carlson+Laboratories"><img src="<?=$g4['path']?>/images/main/brand08.gif" width="145" height="57" border="0"></a></td>
        <td><a href="<?=$g4['path']?>/shop/search.php?it_maker=Rainbow+light"><img src="<?=$g4['path']?>/images/main/brand09.gif" width="145" height="57" border="0"></a></td>
        <td><a href="<?=$g4['path']?>/shop/search.php?it_maker=Natural+Factors"><img src="<?=$g4['path']?>/images/main/brand10.gif" width="145" height="57" border="0"></a></td>
      </tr>
    </table>
    <div class="brandall"><a href="<?=$g4['path']?>/shop/brands.php"><img src="<?=$g4['path']?>/images/main/brands_viewall.gif" width="102" height="17" border="0"></a></div>
    </center>
    </div>
        </td>
      </tr>

      <tr>
        <td style="padding-top:10px">
  		<?
        // 최신상품
        $type = 3;
        if ($default["de_type{$type}_list_use"])
        {
            display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
        }
        ?>
		</td>
      </tr>
      <tr>
        <td style="padding-top:10px">
		<?
		// 히트(인기)상품
		$type = 1;
		if ($default["de_type{$type}_list_use"])
		{
			display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
		}
		?>
		</td>
      </tr>

	</table>

<?
include "$g4[shop_path]/newwin.inc.php"; // 새창띄우기

include_once("$g4[path]/tail.php");
?>
