<?
# 상단 카테고리 버튼 고정 #
if(is_string($ca_id)){
	$active_ca = substr($ca_id,0,2);
}else{
//	$active_ca = 'i1';
}

# 푸드는 서브카테고리 없음


$cate_depth_1 = sub_cate_cache_load_new('50');
$cate_depth_2 = sub_cate_cache_load_new('51');
$cate_depth_3 = sub_cate_cache_load_new('52');
$cate_depth_4 = sub_cate_cache_load_new('53');
$cate_depth_5 = sub_cate_cache_load_new('54');
$cate_depth_6 = sub_cate_cache_load_new('55');
$cate_depth_7 = sub_cate_cache_load_new('65');

?>
<ul>
	<li class="depth01 first"><a href="<?=$g4['shop_path']?>/list.php?ca_id=50" class="<?=($active_ca == '50')? 'active':''?>">커피/차/음료</a></li>
	<li class="depth02"><a href="<?=$g4['shop_path']?>/list.php?ca_id=51" class="<?=($active_ca == '51')? 'active':''?>">가공식품</a></li>
	<li class="depth03"><a href="<?=$g4['shop_path']?>/list.php?ca_id=52" class="<?=($active_ca == '52')? 'active':''?>">간편식품</a></li>
	<li class="depth04"><a href="<?=$g4['shop_path']?>/list.php?ca_id=53" class="<?=($active_ca == '53')? 'active':''?>">과자/간식</a></li>
	<li class="depth05"><a href="<?=$g4['shop_path']?>/list.php?ca_id=54" class="<?=($active_ca == '54')? 'active':''?>">다이어트보조식품</a></li>
	<li class="depth06"><a href="<?=$g4['shop_path']?>/list.php?ca_id=55" class="<?=($active_ca == '55')? 'active':''?>">베이비푸드</a></li>
	<li class="depth07"><a href="<?=$g4['shop_path']?>/list.php?ca_id=65" class="<?=($active_ca == '65')? 'active':''?>">잡곡/혼합곡</a></li>
</ul>

<!--DepthCategory_카테고리전체 동일-->
<div class="DepthCategory depth01_box" style="display:none;" ca_id='50'></div>
<div class="DepthCategory depth02_box" style="display:none;" ca_id='51'></div>
<div class="DepthCategory depth03_box" style="display:none;" ca_id='52'></div>
<div class="DepthCategory depth04_box" style="display:none;" ca_id='53'></div>
<div class="DepthCategory depth05_box" style="display:none;" ca_id='54'></div>
<div class="DepthCategory depth06_box" style="display:none;" ca_id='55'></div>
<div class="DepthCategory depth07_box" style="display:none;" ca_id='65'></div>


<div id="hidden_cate_depth_50" style="display:none;">
<?php echo $cate_depth_1; ?>
</div>
<div id="hidden_cate_depth_51" style="display:none;">
<?php echo $cate_depth_2; ?>
</div>
<div id="hidden_cate_depth_52" style="display:none;">
<?php echo $cate_depth_3; ?>
</div>
<div id="hidden_cate_depth_53" style="display:none;">
<?php echo $cate_depth_4; ?>
</div>
<div id="hidden_cate_depth_54" style="display:none;">
<?php echo $cate_depth_5; ?>
</div>
<div id="hidden_cate_depth_55" style="display:none;">
<?php echo $cate_depth_6; ?>
</div>
<div id="hidden_cate_depth_65" style="display:none;">
<?php echo $cate_depth_7; ?>
</div>