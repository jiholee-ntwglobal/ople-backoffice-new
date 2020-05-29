<?php
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
<ul>
	<li class="depth01 first"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=10" class="<?php echo ($active_ca == '10')? 'active':'';?>">대상별</a></li>
	<li class="depth02"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=11" class="<?php echo ($active_ca == '11')? 'active':'';?>">성분별</a></li>
	<li class="depth03"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=12" class="<?php echo ($active_ca == '12')? 'active':'';?>">증상별</a></li>
	<li class="depth04"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=13" class="<?php echo ($active_ca == '13')? 'active':'';?>">비타민&미네랄</a></li>
	<li class="depth05"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=14" class="<?php echo ($active_ca == '14')? 'active':'';?>">오메가-3</a></li>
	<li class="depth06"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=15" class="<?php echo ($active_ca == '15')? 'active':'';?>">유산균</a></li>
	<li class="depth07"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=16" class="<?php echo ($active_ca == '16')? 'active':'';?>">허브/각종 추출물</a></li>
	<li class="depth08"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=17" class="<?php echo ($active_ca == '17')? 'active':'';?>">항산화 ∙ 면역력</a></li>
	<li class="depth09"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=18" class="<?php echo ($active_ca == '18')? 'active':'';?>">동종요법</a></li>
	<li class="depth10"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=19" class="<?php echo ($active_ca == '19')? 'active':'';?>">다이어트/스포츠</a></li>
</ul>
<!--Depth_Category_건강식품성분별-->
<div class="DepthCategory depth01_box" style="display:none;" ca_id='10'>
</div>

<!--Depth_Category_건강식품증상별-->
<div class="DepthCategory depth02_box" style="display:none;" ca_id='11'>
</div>

<!--Depth_Category_건강식품연령별-->
<div class="DepthCategory depth03_box" style="display:none;" ca_id='12'>
 </div>

<!--Depth_Category_허브-->
<div class="DepthCategory depth04_box" style="display:none;" ca_id='13'>
</div>

<!--Depth_Category_비타민_미네럴-->
<div class="DepthCategory depth05_box" style="display:none;" ca_id='14'>
</div>

<!--Depth_Category_아미노산-->
<div class="DepthCategory depth06_box" style="display:none;" ca_id='15'>
</div>

<!--Depth_Category_오메가3_COQ10-->
<div class="DepthCategory depth07_box" style="display:none;" ca_id='16'>
</div>

<!--Depth_Category_스포츠-->
<div class="DepthCategory depth08_box" style="display:none;" ca_id='17'>
</div>

<!--Depth_Category_동종요법-->
<div class="DepthCategory depth09_box" style="display:none;" ca_id='18'>
</div>

<!--Depth_Category_동종요법-->
<div class="DepthCategory depth10_box" style="display:none;" ca_id='19'>
</div>


<div id="hidden_cate_depth_10" style="display:none;">
<?php echo $cate_depth_1; ?>
</div>
<div id="hidden_cate_depth_11" style="display:none;">
<?php echo $cate_depth_2; ?>
</div>
<div id="hidden_cate_depth_12" style="display:none;">
<?php echo $cate_depth_3; ?>
</div>
<div id="hidden_cate_depth_13" style="display:none;">
<?php echo $cate_depth_4; ?>
</div>
<div id="hidden_cate_depth_14" style="display:none;">
<?php echo $cate_depth_5; ?>
</div>
<div id="hidden_cate_depth_15" style="display:none;">
<?php echo $cate_depth_6; ?>
</div>
<div id="hidden_cate_depth_16" style="display:none;">
<?php echo $cate_depth_7; ?>
</div>
<div id="hidden_cate_depth_17" style="display:none;">
<?php echo $cate_depth_8; ?>
</div>
<div id="hidden_cate_depth_18" style="display:none;">
<?php echo $cate_depth_9; ?>
</div>
<div id="hidden_cate_depth_19" style="display:none;">
<?php echo $cate_depth_10; ?>
</div>



<div id="hidden_cate_detail_11" style="display:none;">
<?php echo $detail_cate; ?>
</div>
