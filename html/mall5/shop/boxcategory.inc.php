<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

//
// 2단계 분류 레이어 표시
//
$menu = ""; // 메뉴 레이어 임시저장 변수 (처음엔 아무값도 없어야 합니다.)
/* $sub_menu_left = 0; // 2단계 메뉴 왼쪽 좌표 (1단계 좌표에서 부터) */
?>

<script type="text/javascript">

var menuids=["sidebarmenu1"] //Enter id(s) of each Side Bar Menu's main UL, separated by commas

function initsidebarmenu(){
for (var i=0; i<menuids.length; i++){
  var ultags=document.getElementById(menuids[i]).getElementsByTagName("ul")
    for (var t=0; t<ultags.length; t++){
    ultags[t].parentNode.getElementsByTagName("a")[0].className+=" subfolderstyle"
  if (ultags[t].parentNode.parentNode.id==menuids[i]) //if this is a first level submenu
   ultags[t].style.left=ultags[t].parentNode.offsetWidth+"px" //dynamically position first level submenus to be width of main menu item
  else //else if this is a sub level submenu (ul)
    ultags[t].style.left=ultags[t-1].getElementsByTagName("a")[0].offsetWidth+"px" //position menu to the right of menu item that activated it
    ultags[t].parentNode.onmouseover=function(){
    this.getElementsByTagName("ul")[0].style.display="block"
    }
    ultags[t].parentNode.onmouseout=function(){
    this.getElementsByTagName("ul")[0].style.display="none"
    }
    }
  for (var t=ultags.length-1; t>-1; t--){ //loop through all sub menus again, and use "display:none" to hide menus (to prevent possible page scrollbars
  ultags[t].style.visibility="visible"
  ultags[t].style.display="none"
  }
  }
}
if (window.addEventListener)
window.addEventListener("load", initsidebarmenu, false)
else if (window.attachEvent)
window.attachEvent("onload", initsidebarmenu)
</script>
<!-- <div class="leftbanner"><a href="<?=$g4['path']?>/shop/eventwinner03.php"><img src="http://115.68.20.84/main/eventwinner_btn.gif"></a></div> -->
<div class="leftbanner"><a href="<?=$g4['path']?>/shop/event.php?ev_id=1350354846"><img src="<?=$g4['path']?>/images/main/latest.gif"></a></div>
<div class="leftbanner"><a href="<?=$g4['path']?>/shop/event.php?ev_id=1393920134"><img src="http://115.68.20.84/main/manwonbtn.jpg"></a></div>
<div class="leftbanner"><a href="<?=$g4['path']?>/shop/take.php"><img src="http://115.68.20.84/main/take_bt.jpg"></a></div>
<div class="leftbanner"><a href="#" onclick="oneday_sms_popup(); return false;"><img src="http://115.68.20.84/main/goodday_SMSbt.jpg"></a></div>


<div style="position: relative; z-index: 3000">
<div class="sidebarmenu">
<ul id="sidebarmenu1">
<li class="sidebartitle age">연령별</li>
<li class="mm01"><a href="<?=$g4[shop_path]?>/list.php?ca_id=b0">어린이/유아</a>
	<ul class="sub-menu">
	<li class="mm011"><a href="<?=$g4[shop_path]?>/list.php?ca_id=b010">종합비타민/비타민</a></li>
	<li class="mm012"><a href="<?=$g4[shop_path]?>/list.php?ca_id=b020">칼슘-뼈건강</a></li>
	<li class="mm013"><a href="<?=$g4[shop_path]?>/list.php?ca_id=b030">면역력강화,항산화</a></li>
	<li class="mm014"><a href="<?=$g4[shop_path]?>/list.php?ca_id=b040">오메가,집중력,눈건강</a></li>
	<li class="mm015"><a href="<?=$g4[shop_path]?>/list.php?ca_id=b050">유산균/식이섬유</a></li>
	<li class="mm016"><a href="<?=$g4[shop_path]?>/list.php?ca_id=b060">아기 간식,먹거리</a></li>
	<li class="mm017"><a href="<?=$g4[shop_path]?>/list.php?ca_id=b070">유아용품</a></li>
	<li class="mm018"><a href="<?=$g4[shop_path]?>/list.php?ca_id=b080">장난감</a></li>
	</ul>
</li>
<li class="mm02"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j2">청소년</a>
	<ul class="sub-menu">
	<li class="mm021"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j210">종합비타민/비타민</a></li>
	<li class="mm022"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j220">칼슘-뼈건강</a></li>
	<li class="mm033"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j230">두뇌발달 눈건강</a></li>
	<li class="mm044"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j240">면역력강화</a></li>
	<li class="mm055"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j250">유산균</a></li>
	</ul>
</li>
<li class="mm03"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j1">남성건강</a>
	<ul class="sub-menu">
	<li class="mm031"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j110">종합비타민/비타민</a></li>
	<li class="mm032"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j120">칼슘-뼈건강</a></li>
	</ul>
</li>
<li class="mm04"><a href="<?=$g4[shop_path]?>/list.php?ca_id=zk">여성건강</a>
	<ul class="sub-menu">
	<li class="mm041"><a href="<?=$g4[shop_path]?>/list.php?ca_id=zk10">PMS여성기능</a></li>
	<li class="mm042"><a href="<?=$g4[shop_path]?>/list.php?ca_id=zk20">방광</a></li>
	<li class="mm043"><a href="<?=$g4[shop_path]?>/list.php?ca_id=zk30">염증</a></li>
	<li class="mm044"><a href="<?=$g4[shop_path]?>/list.php?ca_id=zk40">갱년기증상</a></li>
	</ul>
</li> 
<li class="mm05"><a href="<?=$g4[shop_path]?>/list.php?ca_id=g0">임신/수유중인 여성</a>
	<ul class="sub-menu">
		<li class="mm051"><a href="<?=$g4[shop_path]?>/list.php?ca_id=g010">종합비타민</a></li>
		<li class="mm052"><a href="<?=$g4[shop_path]?>/list.php?ca_id=g020">DHA</a></li>
		<li class="mm053"><a href="<?=$g4[shop_path]?>/list.php?ca_id=g030">엽산</a></li>
		<li class="mm054"><a href="<?=$g4[shop_path]?>/list.php?ca_id=g040">허브</a></li>
		<li class="mm054"><a href="<?=$g4[shop_path]?>/list.php?ca_id=g040">그외</a></li>
	</ul>
</li>

<li class="sidebartitle ing">성분별</li>
<li class="mm06"><a href="<?=$g4[shop_path]?>/list.php?ca_id=60">종합비타민</a>
	<ul class="sub-menu">
		<li class="mm061"><a href="<?=$g4[shop_path]?>/list.php?ca_id=6020">종합비타민일반</a></li>
		<?if($domain_flag != 'kr'){?>
		<li class="mm062"><a href="<?=$g4[shop_path]?>/list.php?ca_id=60d0">센트룸</a></li>
		<?}?>

	</ul>
</li>
<li class="mm07"><a href="<?=$g4[shop_path]?>/list.php?ca_id=d0">오메가3,6,9</a>
	<ul class="sub-menu">
		<li class="mm071"><a href="<?=$g4[shop_path]?>/list.php?ca_id=d010">오메가3</a></li>
		<li class="mm072"><a href="<?=$g4[shop_path]?>/list.php?ca_id=d020">오메가3,6,9</a></li>
		<li class="mm073"><a href="<?=$g4[shop_path]?>/list.php?ca_id=d030">오메가복합제품</a></li>
		<li class="mm074"><a href="<?=$g4[shop_path]?>/list.php?ca_id=d040">식물성오메 가(아마씨등)</a></li>
		<li class="mm075"><a href="<?=$g4[shop_path]?>/list.php?ca_id=d050">레시틴(대두)</a></li>
		<li class="mm076"><a href="<?=$g4[shop_path]?>/list.php?ca_id=d060">GLA(달맞이꽃종자유)</a></li>
		<li class="mm077"><a href="<?=$g4[shop_path]?>/list.php?ca_id=d070">CLA(공액리놀렌산)</a></li>
		<li class="mm078"><a href="<?=$g4[shop_path]?>/list.php?ca_id=d080">그외</a></li>
		<li class="mm079"><a href="<?=$g4[shop_path]?>/list.php?ca_id=d090"><b>Nordic Naturals</b></a></li>
	</ul>
</li>

<li class="mm08"><a href="<?=$g4[shop_path]?>/list.php?ca_id=70">비타민A-Z</a>
	<ul class="sub-menu">
		<li class="mm081"><a href="<?=$g4[shop_path]?>/list.php?ca_id=7070">비타민 A</a></li>
		<li class="mm082"><a href="<?=$g4[shop_path]?>/list.php?ca_id=7060">비타민 B</a></li>
		<li class="mm083"><a href="<?=$g4[shop_path]?>/list.php?ca_id=7010">비타민 C</a></li>
		<li class="mm084"><a href="<?=$g4[shop_path]?>/list.php?ca_id=7080">비타민 D</a></li>
		<li class="mm085"><a href="<?=$g4[shop_path]?>/list.php?ca_id=7020">비타민 E</a></li>
		<li class="mm086"><a href="<?=$g4[shop_path]?>/list.php?ca_id=7050">기타 비타민</a></li>
	</ul>
</li>
<li class="mm08a"><a href="<?=$g4[shop_path]?>/list.php?ca_id=10">글루코사민</a>
	<ul class="sub-menu">
		<li class="mm261"><a href="<?=$g4[shop_path]?>/list.php?ca_id=1010">MSM</a></li>
		<li class="mm262"><a href="<?=$g4[shop_path]?>/list.php?ca_id=1020">글루코사민 복합제품</a></li>
		<li class="mm263"><a href="<?=$g4[shop_path]?>/list.php?ca_id=1030">히알루론산 </a></li>
		<li class="mm264"><a href="<?=$g4[shop_path]?>/list.php?ca_id=1040">상어연골</a></li>
		<li class="mm265"><a href="<?=$g4[shop_path]?>/list.php?ca_id=1060">크림 글루코사민</a></li>
		<li class="mm266"><a href="<?=$g4[shop_path]?>/list.php?ca_id=1070">액체 글루코사민</a></li>
		<li class="mm267"><a href="<?=$g4[shop_path]?>/list.php?ca_id=1080">체리 추출물</a></li>
		<li class="mm268"><a href="<?=$g4[shop_path]?>/list.php?ca_id=1090">그외</a></li>
	</ul>
</li>
<li class="mm09"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j0">미네랄/무기질</a>
	<ul class="sub-menu">
		<li class="mm091"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j010">멀티미네랄(종합무기질)</a></li>
		<li class="mm092"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j020">미네랄 복합제</a></li>
		<li class="mm093"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j030">아연</a></li>
		<li class="mm094"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j040">철분</a></li>
		<li class="mm095"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j050">붕소</a></li>
		<li class="mm096"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j060">크로미움</a></li>
		<li class="mm097"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j070">마그네슘</a></li>
		<li class="mm098"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j080">칼슘</a></li>
		<li class="mm099"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j090">포타슘</a></li>
		<li class="mm09a"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j0b0">셀레늄</a></li>
		<li class="mm09b"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j0c0">구리</a></li>
		<li class="mm09c"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j0d0">트레이스 미네랄</a></li>
		<li class="mm09d"><a href="<?=$g4[shop_path]?>/list.php?ca_id=j0e0">그외</a></li>
	</ul>
</li>
<li class="mm10"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v0">아미노산</a>
	<ul class="sub-menu">
		<li class="mm101"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v010">L-아르기닌</a></li>
		<li class="mm102"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v020">L-카르니틴</a></li>
		<li class="mm103"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v030">L-시스테인</a></li>
		<li class="mm104"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v040">L-글루타민</a></li>
		<li class="mm105"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v050">L-글루타티온</a></li>
		<li class="mm106"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v060">L-라이신</a></li>
		<li class="mm107"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v070">L-메티오닌</a></li>
		<li class="mm108"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v080">L-오르니틴</a></li>
		<li class="mm109"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v090">L-테아닌</a></li>
		<li class="mm10a"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v0a0">L-타이로신</a></li>
		<li class="mm10b"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v0b0">L-트립토판</a></li>
		<li class="mm10c"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v0c0">Beta-Alanine</a></li>
		<li class="mm10d"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v0d0">NAC</a></li>
		<li class="mm10e"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v0e0">GABA </a></li>
		<li class="mm10f"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v0f0">Glycine(글리신)</a></li>
		<li class="mm10g"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v0g0">Theanine(테아닌)</a></li>
		<li class="mm10h"><a href="<?=$g4[shop_path]?>/list.php?ca_id=v0h0">복합 아미노산</a></li>

	</ul>
</li>
<li class="mm11"><a href="<?=$g4[shop_path]?>/list.php?ca_id=x0">그린푸드</a></li>
<li class="mm12"><a href="<?=$g4[shop_path]?>/list.php?ca_id=k0">허브</a></li>
<li class="mm13"><a href="<?=$g4[shop_path]?>/list.php?ca_id=zg">항산화제</a></li>
<li class="mm14"><a href="<?=$g4[shop_path]?>/list.php?ca_id=d1">코엔자임(COQ10)</a></li>
<li class="mm15"><a href="<?=$g4[shop_path]?>/list.php?ca_id=11">프로폴리스/로얄젤리/꿀</a></li>
<li class="mm16"><a href="<?=$g4[shop_path]?>/list.php?ca_id=cp">버섯 추출물</a></li>
<li class="mm17"><a href="<?=$g4[shop_path]?>/list.php?ca_id=w0">마늘 추출물</a></li>
<li class="mm18"><a href="<?=$g4[shop_path]?>/list.php?ca_id=l0">유산균</a>
	<ul class="sub-menu">
		<li class="mm181"><a href="<?=$g4[shop_path]?>/list.php?ca_id=l010">유산균</a></li>
		<li class="mm182"><a href="<?=$g4[shop_path]?>/list.php?ca_id=l020">식이섬유</a></li>
		<li class="mm183"><a href="<?=$g4[shop_path]?>/list.php?ca_id=l030">디톡스</a></li>
		<li class="mm184"><a href="<?=$g4[shop_path]?>/list.php?ca_id=l040">허브</a></li>
		<li class="mm185"><a href="<?=$g4[shop_path]?>/list.php?ca_id=l050">그외</a></li>
	</ul>
</li>
<li class="mm19"><a href="<?=$g4[shop_path]?>/list.php?ca_id=fg">칼슘</a></li>
<li class="mm20"><a href="<?=$g4[shop_path]?>/list.php?ca_id=o010">콜라겐</a></li> <!-- 피부건강과 링크공유 -->
<li class="mm21"><a href="<?=$g4[shop_path]?>/list.php?ca_id=y0">초유</a></li>
<?if($domain_flag != 'kr'){?>
<li class="mm2a"><a href="<?=$g4[shop_path]?>/list.php?ca_id=97">동종요법</a></li>
<?}?>

<li class="sidebartitle sym">증상별</li>
<li class="mm22"><a href="<?=$g4[shop_path]?>/list.php?ca_id=hd">혈압,혈당 관리</a>
	<ul class="sub-menu">
		<li class="mm221"><a href="<?=$g4[shop_path]?>/list.php?ca_id=hd10">혈압</a></li>
		<li class="mm222"><a href="<?=$g4[shop_path]?>/list.php?ca_id=hd20">혈당</a></li>
	</ul>
</li>
<li class="mm23"><a href="<?=$g4[shop_path]?>/list.php?ca_id=k1">콜레스테롤</a></li>
<li class="mm24"><a href="<?=$g4[shop_path]?>/list.php?ca_id=k6">심장혈관건강</a></li>
<li class="mm25"><a href="<?=$g4[shop_path]?>/list.php?ca_id=fg">골다공증/뼈건강</a></li> <!-- 칼슘제와 링크공유 -->
<li class="mm26"><a href="<?=$g4[shop_path]?>/list.php?ca_id=10">관절건강</a>
	<ul class="sub-menu">
		<li class="mm261"><a href="<?=$g4[shop_path]?>/list.php?ca_id=1010">MSM</a></li>
		<li class="mm262"><a href="<?=$g4[shop_path]?>/list.php?ca_id=1020">글루코사민 복합제품</a></li>
		<li class="mm263"><a href="<?=$g4[shop_path]?>/list.php?ca_id=1030">히알루론산 </a></li>
		<li class="mm264"><a href="<?=$g4[shop_path]?>/list.php?ca_id=1040">상어연골</a></li>
		<li class="mm265"><a href="<?=$g4[shop_path]?>/list.php?ca_id=1060">크림 글루코사민</a></li>
		<li class="mm266"><a href="<?=$g4[shop_path]?>/list.php?ca_id=1070">액체 글루코사민</a></li>
		<li class="mm267"><a href="<?=$g4[shop_path]?>/list.php?ca_id=1080">체리 추출물</a></li>
		<li class="mm268"><a href="<?=$g4[shop_path]?>/list.php?ca_id=1090">그외</a></li>
	</ul>
</li>
<li class="mm27"><a href="<?=$g4[shop_path]?>/list.php?ca_id=q0">눈/귀/두뇌,집중력</a>
	<ul class="sub-menu">
		<li class="mm271"><a href="<?=$g4[shop_path]?>/list.php?ca_id=q030">눈건강</a></li>
		<li class="mm272"><a href="<?=$g4[shop_path]?>/list.php?ca_id=q020">귀건강</a></li>
		<li class="mm273"><a href="<?=$g4[shop_path]?>/list.php?ca_id=q010">두뇌,집중력</a></li>
	</ul>
</li>
<li class="mm28"><a href="<?=$g4[shop_path]?>/list.php?ca_id=zg">노화방지</a></li> <!-- 항산화제와 링크공유 -->
<li class="mm29"><a href="<?=$g4[shop_path]?>/list.php?ca_id=p0">간/폐 기능개선</a>
	<ul class="sub-menu">
		<li class="mm291"><a href="<?=$g4[shop_path]?>/list.php?ca_id=p010">간건강</a></li>
		<li class="mm292"><a href="<?=$g4[shop_path]?>/list.php?ca_id=p020">페건강</a></li>
		<li class="mm293"><a href="<?=$g4[shop_path]?>/list.php?ca_id=p030">간 디톡스(해독)</a></li>
	</ul>
</li>
<li class="mm30"><a href="<?=$g4[shop_path]?>/list.php?ca_id=kn">신장</a></li>
<li class="mm31"><a href="<?=$g4[shop_path]?>/list.php?ca_id=51">소화/위건강</a>
	<ul class="sub-menu">
		<li class="mm311"><a href="<?=$g4[shop_path]?>/list.php?ca_id=5110">알로에</a></li>
		<li class="mm312"><a href="<?=$g4[shop_path]?>/list.php?ca_id=5120">브로멜라닌</a></li>
		<li class="mm313"><a href="<?=$g4[shop_path]?>/list.php?ca_id=5130">소화효소</a></li>
		<li class="mm314"><a href="<?=$g4[shop_path]?>/list.php?ca_id=5140">그외</a></li>
	</ul>
</li>
<li class="mm32"><a href="<?=$g4[shop_path]?>/list.php?ca_id=l0">장기능 강화</a> <!-- 유산균과 링크 공유 -->
	<ul class="sub-menu">
		<li class="mm181"><a href="<?=$g4[shop_path]?>/list.php?ca_id=l010">유산균</a></li>
		<li class="mm182"><a href="<?=$g4[shop_path]?>/list.php?ca_id=l020">식이섬유</a></li>
		<li class="mm183"><a href="<?=$g4[shop_path]?>/list.php?ca_id=l030">디톡스</a></li>
		<li class="mm184"><a href="<?=$g4[shop_path]?>/list.php?ca_id=l040">허브</a></li>
		<li class="mm185"><a href="<?=$g4[shop_path]?>/list.php?ca_id=l050">그외</a></li>
	</ul>
</li>
<li class="mm33"><a href="<?=$g4[shop_path]?>/list.php?ca_id=m8">면역력 강화</a></li>
<li class="mm35"><a href="<?=$g4[shop_path]?>/list.php?ca_id=zk40">갱년기 장애</a></li>
<li class="mm36"><a href="<?=$g4[shop_path]?>/list.php?ca_id=gp">스트레스/피로/우울/수면</a>
	<ul class="sub-menu">
		<li class="mm361"><a href="<?=$g4[shop_path]?>/list.php?ca_id=gp10">스트레스/피로</a></li>
		<li class="mm362"><a href="<?=$g4[shop_path]?>/list.php?ca_id=gp20">우울</a></li>
		<li class="mm363"><a href="<?=$g4[shop_path]?>/list.php?ca_id=gp30">수면보조</a></li>
	</ul>
</li>
<li class="mm37"><a href="<?=$g4[shop_path]?>/list.php?ca_id=o0">피부건강(비타민)</a>
	<ul class="sub-menu">
		<li class="mm371"><a href="<?=$g4[shop_path]?>/list.php?ca_id=o010">콜라겐</a></li>
		<li class="mm372"><a href="<?=$g4[shop_path]?>/list.php?ca_id=o020">히알루론산</a></li>
		<li class="mm373"><a href="<?=$g4[shop_path]?>/list.php?ca_id=o030">코코넛오일</a></li>
		<li class="mm374"><a href="<?=$g4[shop_path]?>/list.php?ca_id=o040">DMAE</a></li>
		<li class="mm375"><a href="<?=$g4[shop_path]?>/list.php?ca_id=o050">오메가7</a></li>
		<li class="mm376"><a href="<?=$g4[shop_path]?>/list.php?ca_id=o060">피부건강 종함제품</a></li>
		<li class="mm377"><a href="<?=$g4[shop_path]?>/list.php?ca_id=o070">그외</a></li>
	</ul>
</li>
<li class="mm38"><a href="<?=$g4[shop_path]?>/list.php?ca_id=98">다이어트/헬스보충제</a>
	<ul class="sub-menu">
		<li class="mm381"><a href="<?=$g4[shop_path]?>/list.php?ca_id=9810">프로틴</a></li>
		<li class="mm382"><a href="<?=$g4[shop_path]?>/list.php?ca_id=9820">글루타민</a></li>
		<li class="mm383"><a href="<?=$g4[shop_path]?>/list.php?ca_id=9830">카보게인</a></li>
		<li class="mm384"><a href="<?=$g4[shop_path]?>/list.php?ca_id=9840">다이어트</a></li>
	</ul>
</li>
<li class="sidebartitle fnh">가정/식품</li>
<li class="mm41"><a href="<?=$g4[shop_path]?>/list.php?ca_id=23">가정용품</a>
	<ul class="sub-menu">
		<li class="mm411"><a href="<?=$g4[shop_path]?>/list.php?ca_id=2310">세제/세면/제지/일용잡화</a></li>
		<li class="mm412"><a href="<?=$g4[shop_path]?>/list.php?ca_id=2320">생활/수납/욕실/청소</a></li>
		<li class="mm412"><a href="<?=$g4[shop_path]?>/list.php?ca_id=2330">보온/보냉용품</a></li>
		<?if($domain_flag != 'kr'){?>
		<li class="mm412"><a href="<?=$g4[shop_path]?>/list.php?ca_id=2340">가정용 상비/의약외품</a></li>
		<?}?>
	</ul>
</li>
<li class="mm41"><a href="<?=$g4[shop_path]?>/list.php?ca_id=m0">식품관</a>
	<ul class="sub-menu">
		<li class="mm411"><a href="<?=$g4[shop_path]?>/list.php?ca_id=m010">견과류,말린 과일</a></li>
		<li class="mm412"><a href="<?=$g4[shop_path]?>/list.php?ca_id=m020">커피/코코아</a></li>
		<li class="mm412"><a href="<?=$g4[shop_path]?>/list.php?ca_id=m030">간식류</a></li>
		<li class="mm411"><a href="<?=$g4[shop_path]?>/list.php?ca_id=m040">차/음료</a></li>
		<li class="mm412"><a href="<?=$g4[shop_path]?>/list.php?ca_id=m050">조미료/오일/소스</a></li>
		<li class="mm412"><a href="<?=$g4[shop_path]?>/list.php?ca_id=m060">영양바</a></li>
		<li class="mm412"><a href="<?=$g4[shop_path]?>/list.php?ca_id=m070">콩/잡곡/분말/면 종류등</a></li>
	</ul>
</li>
<li class="sidebartitle etc">기타</li>
<li class="mm39"><a href="<?=$g4[shop_path]?>/list.php?ca_id=n0">피부미용(화장품)</a>
	<ul class="sub-menu">
		<li class="mm391"><a href="<?=$g4[shop_path]?>/list.php?ca_id=n0b0">선블락</a></li>
		<li class="mm392"><a href="<?=$g4[shop_path]?>/list.php?ca_id=n0c0">클렌징/비누</a></li>
		<?if($domain_flag != 'kr'){?>
		<li class="mm393"><a href="<?=$g4[shop_path]?>/list.php?ca_id=n0d0">치약</a></li>
		<?}?>
		<li class="mm394"><a href="<?=$g4[shop_path]?>/list.php?ca_id=n0e0">팩/스크럽</a></li>
		<li class="mm395"><a href="<?=$g4[shop_path]?>/list.php?ca_id=n0f0">스킨(아스트린젠트)</a></li>
		<li class="mm396"><a href="<?=$g4[shop_path]?>/list.php?ca_id=n0g0">에센스(세럼)</a></li>
		<li class="mm397"><a href="<?=$g4[shop_path]?>/list.php?ca_id=n0h0">로션</a></li>
		<li class="mm398"><a href="<?=$g4[shop_path]?>/list.php?ca_id=n0i0">크림/연고</a></li>
		<li class="mm399"><a href="<?=$g4[shop_path]?>/list.php?ca_id=n0j0">오일</a></li>
		<li class="mm39a"><a href="<?=$g4[shop_path]?>/list.php?ca_id=n0k0">립밤</a></li>
		<li class="mm39b"><a href="<?=$g4[shop_path]?>/list.php?ca_id=n0l0">손세정제</a></li>
		<li class="mm39c"><a href="<?=$g4[shop_path]?>/list.php?ca_id=z0">어린이용</a></li>
	</ul>
</li>
<li class="mm39"><a href="<?=$g4[shop_path]?>/list.php?ca_id=z0">피부미용(어린이용)</a></li>
<li class="mm40"><a href="<?=$g4[shop_path]?>/list.php?ca_id=00">헤어용품</a>
	<ul class="sub-menu">
		<li class="mm401"><a href="<?=$g4[shop_path]?>/list.php?ca_id=0010">샴푸</a></li>
		<li class="mm402"><a href="<?=$g4[shop_path]?>/list.php?ca_id=0020">컨디셔너</a></li>
		<li class="mm403"><a href="<?=$g4[shop_path]?>/list.php?ca_id=0030">스타일링(무스,젤)</a></li>
		<li class="mm404"><a href="<?=$g4[shop_path]?>/list.php?ca_id=0040">에센스/영양제</a></li>
		<li class="mm405"><a href="<?=$g4[shop_path]?>/list.php?ca_id=0050">그외(샤워젤등)</a></li>
	</ul>
</li>
<li class="mm43"><a href="<?=$g4[shop_path]?>/list.php?ca_id=45">반려동물</a></li>
<li class="mm44"><a href="<?=$g4[shop_path]?>/list.php?ca_id=u0">선결제포인트구매</a></li>
<li class="mm45"><a href="<?=$g4[shop_path]?>/list.php?ca_id=t0">SALE</a></li>
</ul>
</div>
</div>

<!-- <a href="<?=$g4['bbs_path']?>/board.php?bo_table=qa"><img src="<?=$g4['path']?>/images/main/main_customer_01.gif"></a> -->
<!-- <a href="<?=$g4['bbs_path']?>/board.php?bo_table=faq"><img src="<?=$g4['path']?>/images/main/main_customer_02.gif"></a> -->
<a href="<?=$g4[shop_path]?>/25m.php"><img src="<?=$g4['path']?>/images/main/25m_banner.gif"></a>
<img src="<?=$g4['path']?>/images/main/main_customer_03.gif">
<p style="padding:5px 0 0 0;margin:0;"><a href="<?=$g4['shop_path'];?>/call.php"><img src="<?=$g4['path']?>/images/main/left_banner_call.gif" alt="전화요청"></a></p>

