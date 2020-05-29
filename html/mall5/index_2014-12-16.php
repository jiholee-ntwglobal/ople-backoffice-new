<?php
include_once("./_common.php");
# 최신글 사용 안함으로 주석처리 2014-10-08 홍민기
//include_once($g4['full_path']."/lib/latest.lib.php");

define("_INDEX_", TRUE);


$g4['title'] = "";
include_once($g4['full_path']."/head.php");
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
<link href="<?php echo $g4['path'];?>/css/jquery.bxslider.css" rel="stylesheet" type="text/css">
<script type="text/JavaScript" src="<?php echo $g4['path'];?>/js/shop.js"></script>
<?/*
안쓰는것 같아 주석처리
<script type="text/javascript" src="<?php echo $g4['path'];?>/js/jquery.banner.js"></script>
*/
?>
<?/* 이전 슬라이드 사용 안함
<script type="text/javascript" src="<?php echo $g4['path'];?>/js/jquery.bxslider.js"></script>
*/?>
<style type="text/css">
	.bxslider a {display:block;}
	.bx-wrapper .bx-viewport{ margin:0px;border:none;}
	.bx-wrapper{margin: 0 0 0 5px;}
	.bx-wrapper .bx-pager {text-align: left;bottom: 4px;}
	.bx-wrapper .bx-pager.bx-default-pager a{width:8px; height:8px;}
</style>


<!--Contents-->
	<?php
	if(file_exists($g4['full_path'].'/cate_menu/'.$station['index_file'].'.php')){
		# cate_menu/(Wrap의 클래스명).php 파일 로드 2014-07-08 홍민기 #
		include $g4['full_path'].'/cate_menu/'.$station['index_file'].'.php';
	}else{
		echo "<b>제품관 Index파일이 존재하지 않습니다. ".$g4['full_path'].'/cate_menu/'.$station['index_file'].'.php 파일을 업로드해 주세요.</b>';
	}
	?>


<script type="text/javascript">


<?/* MainSpot 롤링 2014-07-08 홍민기 */?>

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

	$('.MainSpotImg_mask').stop(); <?/* 돌아가고 있는데 또 돌리면 일단 돌던건 정지시켜야지...*/?>
	$('.MainSpotImg_mask').animate({
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
<?php
include $g4['full_path']."/shop/newwin.inc.php"; // 새창띄우기
include_once $g4['full_path']."/tail.php";
?>