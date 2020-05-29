<?
# 상단 카테고리 버튼 고정 #
if($ca_id){
	$active_ca = substr($ca_id,0,2);
}else{
//	$active_ca = 'i1';
}

# 서브 카테고리 로드 #
/*
$cate_depth_1 = sub_cate_view('40',11);
$cate_depth_2 = sub_cate_view('41',11);
$cate_depth_3 = sub_cate_view('42',5);
$cate_depth_4 = sub_cate_view('43',11);
$cate_depth_5 = sub_cate_view('44',11);
$cate_depth_6 = sub_cate_view('45',11);
*/
$cate_depth_1 = cate_sub_navi_view('40');
$cate_depth_2 = cate_sub_navi_view('41');
$cate_depth_3 = cate_sub_navi_view('42');
$cate_depth_4 = cate_sub_navi_view('43');
$cate_depth_5 = cate_sub_navi_view('44');




?>
<ul>
	<li class="depth01 first"><a href="<?=$g4['shop_path']?>/list.php?ca_id=40" class="<?=($active_ca == '40')? 'active':''?>">스킨케어</a></li>
	<li class="depth02"><a href="<?=$g4['shop_path']?>/list.php?ca_id=41" class="<?=($active_ca == '41')? 'active':''?>">바디케어</a></li>
	<li class="depth03"><a href="<?=$g4['shop_path']?>/list.php?ca_id=42" class="<?=($active_ca == '42')? 'active':''?>">헤어케어</a></li>
	<li class="depth04"><a href="<?=$g4['shop_path']?>/list.php?ca_id=43" class="<?=($active_ca == '43')? 'active':''?>">뷰티소품</a></li>
	<li class="depth05"><a href="<?=$g4['shop_path']?>/list.php?ca_id=44" class="<?=($active_ca == '44')? 'active':''?>">남성화장품</a></li>

</ul>

<!--DepthCategory_카테고리전체 동일-->
<div class="DepthCategory depth01_box" style="display:;" ca_id='40'></div>
<div class="DepthCategory depth02_box" style="display:;" ca_id='41'></div>
<div class="DepthCategory depth03_box" style="display:;" ca_id='42'></div>
<div class="DepthCategory depth04_box" style="display:;" ca_id='43'></div>
<div class="DepthCategory depth05_box" style="display:;" ca_id='44'></div>


<div id="hidden_cate_depth_40" style="display:none;">
<?php echo $cate_depth_1; ?>
</div>
<div id="hidden_cate_depth_41" style="display:none;">
<?php echo $cate_depth_2; ?>
</div>
<div id="hidden_cate_depth_42" style="display:none;">
<?php echo $cate_depth_3; ?>
</div>
<div id="hidden_cate_depth_43" style="display:none;">
<?php echo $cate_depth_4; ?>
</div>
<div id="hidden_cate_depth_44" style="display:none;">
<?php echo $cate_depth_5; ?>
</div>