<?php
/*
----------------------------------------------------------------------
file name	 : brand_list.php
comment		 : 전제 브랜드 리스트 출력
date		 : 2015-01-28
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/
include "./_common.php";

include $g4['full_path']."/cache/brand_cache.php";


$brand_data = '';
$st_fg = false;
if(is_array($cahce_brand)){
	foreach($cahce_brand as $it_maker_sort => $val){
		if($st_fg){
			$brand_data .= "</ul>";
		}
		$st_fg = true;
		$brand_data .= "<h3>".$it_maker_sort."</h3><ul>";
		if(is_array($val)){
			foreach($val as $it_maker => $data){
				$brand_img = '';
				if($data['logo_img']){
					$brand_img = "<img data-original='".$data['logo_img']."'/>";
				}
				$brand_data .= "<li><a href='".$g4['shop_path']."/search.php?it_maker=".urlencode($it_maker)."'><span class='brand_img'>".$brand_img."</span><span>".$it_maker."<br/><".$data['it_maker_kor']."></span></a></li>\n";
			}
		}
	}
}
unset($cahce_brand);

include_once $g4['full_shop_path'] . '/_head.php';


?>
<div class="PageTitle">
  <img alt="브랜드 리스트" src="../images/menu/menu_title09.gif" />
</div>
<div class="Brand_List">
<?php echo $brand_data;?>
</div>
<div style="clear:both;"></div>
<?php
include_once $g4['full_shop_path'] . '/_tail.php';