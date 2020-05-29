<?php
if(count($main_json->item)>0){
    $main['item'] = json_decode(json_encode($main_json->item),true);
}elseif(!isset($main['item'])) {
    include_once $g4['full_path'] . '/cache/main_cache.php';
}

# 하단 상품 리스트 로드  hot new best 만원 #
list($main['item']['H']['it_maker_kor'],$main['item']['H']['it_name_kor']) = explode('||',$main['item']['H']['it_name']);

list($main['item']['N']['it_maker_kor'],$main['item']['N']['it_name_kor']) = explode('||',$main['item']['N']['it_name']);

list($main['item']['B']['it_maker_kor'],$main['item']['B']['it_name_kor']) = explode('||',$main['item']['B']['it_name']);

list($main['item']['M']['it_maker_kor'],$main['item']['M']['it_name_kor']) = explode('||',$main['item']['M']['it_name']);

# 상단 카테고리 버튼 고정 #
if(is_string($ca_id)){
	$active_ca = substr($ca_id,0,2);
}else{
//	$active_ca = 'a1';
}



# 상단 카테고리 로드 #
/*
$cate_1 = station_category_list('a1');

$cnt = 0;
$row_cnt = 11;
for($i=0; $i<count($cate_1); $i++){
	if(!$div_open){
		$div_open = true;
		$cate_depth_1 .= "<div>";
	}
	$cnt++;
	$cnt++;
	$cate_depth_1 .= "<dl>";
	# 1depth 카테고리 #
	$cate_depth_1 .= "<dt><a href='".$g4['shop_path']."/list.php?ca_id=".$cate_1[$i]['ca_id']."'>".$cate_1[$i]['ca_name']."</a></dt>";
	# 2depth 카테고리 #

	if($cate_sub = station_category_list($cate_1[$i]['ca_id'])){
		for($ii=0; $ii<count($cate_sub); $ii++){
			$cate_depth_1 .= "<dd><a href='".$g4['shop_path']."/list.php?ca_id=".$cate_sub[$ii]['ca_id']."'>".$cate_sub[$ii]['ca_name']."</a></dd>";

			$cnt++;
		}
	}
	$cate_depth_1 .= "</dl>";

	if($div_open && $cnt >= $row_cnt){
		$cate_depth_1 .= "</div>";
		$div_open = false;
		$cnt = 0;

	}

}
*/

$cate_depth_1 = sub_cate_cache_load_new('10');
$cate_depth_2 = sub_cate_cache_load_new('11',true);
$cate_depth_3 = sub_cate_cache_load_new('12');
$cate_depth_4 = sub_cate_cache_load_new('13');
$cate_depth_5 = sub_cate_cache_load_new('14');
$cate_depth_6 = sub_cate_cache_load_new('15');
$cate_depth_7 = sub_cate_cache_load_new('16');
$cate_depth_8 = sub_cate_cache_load_new('17');
$cate_depth_9 = sub_cate_cache_load_new('18');
$cate_depth_10 = sub_cate_cache_load_new('19');

$detail_cate = sub_cate_more_cache_load_new('11');

?>


<p class="button_category_view"><a href="#" onclick='main_all_category_view(); return false;'>카테고리보기</a></p>
<ul class="all_category_wrap" style="display:none;">
	<li class="all_health">
    	<a href="<?php echo $g4['path'];?>/?s_id=3">건강식품</a>
        <ul style="display:none;">
        	<?php
			if(is_array($main['category']['3'])){
				foreach($main['category']['3'] as $main_ca_id => $main_ca_name){
					echo "<li><a href='".$g4['shop_path']."/list.php?ca_id=".$main_ca_id."'>".$main_ca_name."</a></li>";
				}
			}
			?>

        </ul>
    </li>
    <li class="all_home">
    	<a href="<?php echo $g4['path'];?>/?s_id=4">생활</a>
        <ul style="display:none;">
        	<?php
			if(is_array($main['category']['4'])){
				foreach($main['category']['4'] as $main_ca_id => $main_ca_name){
					echo "<li><a href='".$g4['shop_path']."/list.php?ca_id=".$main_ca_id."'>".$main_ca_name."</a></li>";
				}
			}
			?>
        </ul>
    </li>
    <li class="all_mamnkiz">
    	<a href="<?php echo $g4['path'];?>/?s_id=5">출산/육아</a>
        <ul style="display:none;">
        	<?php
			if(is_array($main['category']['5'])){
				foreach($main['category']['5'] as $main_ca_id => $main_ca_name){
					echo "<li><a href='".$g4['shop_path']."/list.php?ca_id=".$main_ca_id."'>".$main_ca_name."</a></li>";
				}
			}
			?>
        </ul>
    </li>
    <li class="all_beauty">
    	<a href="<?php echo $g4['path'];?>/?s_id=1">뷰티용품</a>
        <ul style="display:none;">
        	<?php
			if(is_array($main['category']['1'])){
				foreach($main['category']['1'] as $main_ca_id => $main_ca_name){
					echo "<li><a href='".$g4['shop_path']."/list.php?ca_id=".$main_ca_id."'>".$main_ca_name."</a></li>";
				}
			}
			?>
        </ul>
    </li>
    <li class="all_food">
    	<a href="<?php echo $g4['path'];?>/?s_id=2">식품</a>
        <ul style="display:none;">
        	<?php
			if(is_array($main['category']['2'])){
				foreach($main['category']['2'] as $main_ca_id => $main_ca_name){
					echo "<li><a href='".$g4['shop_path']."/list.php?ca_id=".$main_ca_id."'>".$main_ca_name."</a></li>";
				}
			}
			?>
        </ul>
    </li>
</ul>
<style type="text/css">

</style>
<script type="text/javascript">
function main_all_category_view(){

	if($('.all_category_wrap:visible').length>0){
		$('.all_category_wrap').stop().slideUp(100,function(){
			$(this).attr('style','display:none;');
		});
		$('.button_category_view > a').removeClass('active');
	}else{
		$('.all_category_wrap').stop().slideDown(200,function(){
			$(this).removeAttr('style');
		});
		$('.button_category_view > a').addClass('active');
	}
}

$('.all_category_wrap > li').mouseover(function(){
	$('.all_category_wrap > li > a').removeClass('active');
	$('.all_category_wrap > li > ul').hide();
//	$('.all_category_wrap > li').width(370);
	$(this).find('ul').show();
	$(this).find(' > a').addClass('active');
});
$(document).mouseup(function (e){
	var container = $('.category');
	if(container.has(e.target).length == 0){
		$('.all_category_wrap').stop().slideUp(100,function(){
			$(this).attr('style','display:none;');
		});
		$('.button_category_view > a').removeClass('active');
	}
});
</script>