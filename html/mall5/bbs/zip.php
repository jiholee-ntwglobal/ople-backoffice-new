<?php
define('NEW_WINDOW',true);
include_once("./_common.php");

$g4['title'] = "주소 검색";
include_once($g4['full_path']."/head.sub.php");

if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') {  //https 통신
	echo '<script src="https://spi.maps.daum.net/imap/map_js_init/postcode.v2.js"></script>'.PHP_EOL;
} else {  //http 통신
	echo '<script src="http://dmaps.daum.net/map_js_init/postcode.v2.js"></script>'.PHP_EOL;
}
echo '<script src="'.$g4['path'].'/js/zip.js"></script>'.PHP_EOL;
?>
<style>
#daum_juso_wrap{position:absolute;left:0;top:0;width:100%;height:100%}
</style>

<div id="daum_juso_wrap" class="daum_juso_wrap"></div>

<script>
function put_data2(data)
{

	var of = window.opener.document.<?php echo $frm_name; ?>;

	<?php
		/*


		address: "인천 계양구 계산로 165",
		addressEnglish: "165 Gyesan-ro, Gyeyang-gu, Incheon, Korea",
		addressType: "R",
		autoJibunAddress: "",
		autoRoadAddress: "",
		bcode: "2824510200",
		bname: "계산동",
		buildingCode: "2824510200100620001016926",
		buildingName: "현대아파트",
		jibunAddress: "인천 계양구 계산동 62-1",
		postcode: "407-765",
		postcode1: "407",
		postcode2: "765",
		postcodeSeq: "002",
		roadAddress: "인천 계양구 계산로 165",
		sido: "인천",
		sigungu: "계양구",
		userSelectedType: "R",
		zonecode: "21082"



	*/
	?>

	var address = '';
	var address_sub = '';
	var address_fg = true;
	var address_jibun = data.jibunAddress;


	if(data.roadAddress != ''){
		address = data.roadAddress;
	}else if(data.autoRoadAddress != ''){
		address = data.autoRoadAddress;
	}else{
		address = data.address;
		address_fg = false;
	}


	if(address_fg == true) {
		if(data.bname != ''){
			address_sub += data.bname;

		}
		if(data.buildingName != ''){
			address_sub += ", "+data.buildingName;
		}
		if(address_sub != ''){
			address += " ("+address_sub+")";
		}
	}

	if(data.zonecode != ''){
		data.postcode1 = data.zonecode.substr(0,3);
		data.postcode2 = data.zonecode.substr(3,2);
	}


	of.<?php echo $frm_zip1; ?>.value = data.postcode1;
	of.<?php echo $frm_zip2; ?>.value = data.postcode2;
	of.<?php echo $frm_addr1; ?>.value = address;

	of.<?php echo $frm_addr2; ?>.value = '';



	if(address_jibun){
		if(of.<?php echo $frm_jibeon; ?> !== undefined){
			of.<?php echo $frm_jibeon; ?>.value = address_jibun;
			if($('#<?php echo $frm_jibeon; ?>',opener.document).length>0){
				$('#<?php echo $frm_jibeon; ?>',opener.document).text('지번주소 : '+address_jibun);
			}
		}
	}else{
		if(of.<?php echo $frm_jibeon; ?> !== undefined){
			of.<?php echo $frm_jibeon; ?>.value = address;
			if($('#<?php echo $frm_jibeon; ?>',opener.document).length>0){
				$('#<?php echo $frm_jibeon; ?>',opener.document).text('지번주소 : '+address);
			}
		}
	}

	if(of.<?php echo $_GET['frm_zonecode']; ?> !== undefined){
		of.<?php echo $_GET['frm_zonecode']; ?>.value = data.zonecode;
	}




	of.<?php echo $frm_addr2; ?>.focus();
	window.close();
}
</script>
<?php
include_once($g4['full_path']."/tail.sub.php");
?>