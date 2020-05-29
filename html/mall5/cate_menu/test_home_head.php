<?
# 상단 카테고리 버튼 고정 #
if($ca_id){
	$active_ca = substr($ca_id,0,2);
}else{
//	$active_ca = 'i1';
}

# 서브 카테고리 로드 #
/*
$cate_depth_1 = sub_cate_view('20',11);
$cate_depth_2 = sub_cate_view('21',11);
$cate_depth_3 = sub_cate_view('22',5);
$cate_depth_4 = sub_cate_view('23',11);
$cate_depth_5 = sub_cate_view('24',11);
*/
$cate_depth_1 = cate_sub_navi_view('20');
$cate_depth_2 = cate_sub_navi_view('21');
$cate_depth_3 = cate_sub_navi_view('22');
$cate_depth_4 = cate_sub_navi_view('23');
$cate_depth_5 = cate_sub_navi_view('24');


?>
<ul>
	<li class="depth01 first"><a href="<?=$g4['shop_path']?>/list.php?ca_id=20" class="<?=($active_ca == '20')? 'active':''?>">주방/식기용품</a></li>
	<li class="depth02"><a href="<?=$g4['shop_path']?>/list.php?ca_id=21" class='<?=($active_ca == '21')? 'active':''?>'>청소/세탁용품</a></li>
	<li class="depth03"><a href="<?=$g4['shop_path']?>/list.php?ca_id=22" class='<?=($active_ca == '22')? 'active':''?>'>욕실/위생용품</a></li>
	<li class="depth04"><a href="<?=$g4['shop_path']?>/list.php?ca_id=23" class='<?=($active_ca == '23')? 'active':''?>'>생활잡화용품</a></li>
	<li class="depth05"><a href="<?=$g4['shop_path']?>/list.php?ca_id=24" class='<?=($active_ca == '24')? 'active':''?>'>애완용품</a></li>
</ul>
<!--DepthCategory_카테고리전체 동일-->
<div class="DepthCategory depth01_box" style="display:none;" ca_id='20'></div>
<div class="DepthCategory depth02_box" style="display:none;" ca_id='21'></div>
<div class="DepthCategory depth03_box" style="display:none;" ca_id='22'></div>
<div class="DepthCategory depth04_box" style="display:none;" ca_id='23'></div>
<div class="DepthCategory depth05_box" style="display:none;" ca_id='24'></div>


<div id="hidden_cate_depth_20" style="display:none;">
<?php echo $cate_depth_1; ?>
</div>
<div id="hidden_cate_depth_21" style="display:none;">
<?php echo $cate_depth_2; ?>
</div>
<div id="hidden_cate_depth_22" style="display:none;">
<?php echo $cate_depth_3; ?>
</div>
<div id="hidden_cate_depth_23" style="display:none;">
<?php echo $cate_depth_4; ?>
</div>
<div id="hidden_cate_depth_24" style="display:none;">
<?php echo $cate_depth_5; ?>
</div>