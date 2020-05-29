<?
# 상단 카테고리 버튼 고정 #
if(is_string($ca_id)){
	$active_ca = substr($ca_id,0,2);
}else{
//	$active_ca = 'i1';
}

# 서브 카테고리 로드 #
/*
$cate_depth_1 = sub_cate_view('30',11);
$cate_depth_2 = sub_cate_view('31',11);
$cate_depth_3 = sub_cate_view('32',5);
$cate_depth_4 = sub_cate_view('33',11);
$cate_depth_5 = sub_cate_view('34',11);
$cate_depth_6 = sub_cate_view('35',11);
$cate_depth_7 = sub_cate_view('36',11);
*/
$cate_depth_1 = sub_cate_cache_load_new('30');
$cate_depth_2 = sub_cate_cache_load_new('31');
$cate_depth_3 = sub_cate_cache_load_new('32');
$cate_depth_4 = sub_cate_cache_load_new('33');



?>
<ul>
	<li class="depth01 first"><a href="<?=$g4['shop_path']?>/list.php?ca_id=30" class="<?=($active_ca == '30')? 'active':''?>">베이비케어</a></li>
	<li class="depth02"><a href="<?=$g4['shop_path']?>/list.php?ca_id=31" class="<?=($active_ca == '31')? 'active':''?>">베이비용품</a></li>
	<li class="depth03"><a href="<?=$g4['shop_path']?>/list.php?ca_id=32" class="<?=($active_ca == '32')? 'active':''?>">출산/임부용품</a></li>
	<li class="depth04"><a href="<?=$g4['shop_path']?>/list.php?ca_id=33" class="<?=($active_ca == '33')? 'active':''?>">영유아식품</a></li>
</ul>
<!--DepthCategory_카테고리전체 동일-->
<div class="DepthCategory depth01_box" style="display:none;" ca_id='30'></div>
<div class="DepthCategory depth02_box" style="display:none;" ca_id='31'></div>
<div class="DepthCategory depth03_box" style="display:none;" ca_id='32'></div>
<div class="DepthCategory depth04_box" style="display:none;" ca_id='33'></div>

<div id="hidden_cate_depth_30" style="display:none;">
<?php echo $cate_depth_1; ?>
</div>
<div id="hidden_cate_depth_31" style="display:none;">
<?php echo $cate_depth_2; ?>
</div>
<div id="hidden_cate_depth_32" style="display:none;">
<?php echo $cate_depth_3; ?>
</div>
<div id="hidden_cate_depth_33" style="display:none;">
<?php echo $cate_depth_4; ?>
</div>