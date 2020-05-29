<?php
# 상단 카테고리 버튼 고정 #
if($ca_id){
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
/*
$cate_depth_1 = sub_cate_view('10',35,true);
$cate_depth_2 = sub_cate_view('11',11,true);
$cate_depth_3 = sub_cate_view('12',5);
$cate_depth_4 = sub_cate_view('13',26,true);
$cate_depth_5 = sub_cate_view('14',11);
$cate_depth_6 = sub_cate_view('15',11,true);
$cate_depth_7 = sub_cate_view('16',11);
$cate_depth_8 = sub_cate_view('17',11);
*/
$cate_depth_1 = sub_cate_cache_load('10',21,true);
$cate_depth_2 = sub_cate_cache_load('11',10,true);
$cate_depth_3 = sub_cate_cache_load('12',5);
$cate_depth_4 = sub_cate_cache_load('13',17,true);
$cate_depth_5 = sub_cate_cache_load('14',11);
$cate_depth_6 = sub_cate_cache_load('15',11,true);
$cate_depth_7 = sub_cate_cache_load('16',11);
$cate_depth_8 = sub_cate_cache_load('17',11);


?>
<ul>
	<li class="depth01 first"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=10" class="<?php echo ($active_ca == '10')? 'active':'';?>">건강식품 성분별</a></li>
	<li class="depth02"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=11" class="<?php echo ($active_ca == '11')? 'active':'';?>">건강식품 증상별</a></li>
	<li class="depth03"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=12" class="<?php echo ($active_ca == '12')? 'active':'';?>">건강식품 연령별</a></li>
	<li class="depth04"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=13" class="<?php echo ($active_ca == '13')? 'active':'';?>">허브</a></li>
	<li class="depth05"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=14" class="<?php echo ($active_ca == '14')? 'active':'';?>">비타민&미네럴</a></li>
	<li class="depth06"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=15" class="<?php echo ($active_ca == '15')? 'active':'';?>">아미노산</a></li>
	<li class="depth07"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=16" class="<?php echo ($active_ca == '16')? 'active':'';?>">오메가3&COQ10</a></li>
	<li class="depth08"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=17" class="<?php echo ($active_ca == '17')? 'active':'';?>">스포츠</a></li>
</ul>
<!--Depth_Category_건강식품성분별-->
<div class="DepthCategory depth01_box" style="display:none;" ca_id='10'>
  <p class="pointer">포인터(열기)</p>
  <?php echo $cate_depth_1;?>
</div>

<!--Depth_Category_건강식품증상별-->
<div class="DepthCategory depth02_box" style="display:none;" ca_id='11'>
  <p class="pointer">포인터(열기)</p>
  <?php echo $cate_depth_2;?>
</div>

<!--Depth_Category_건강식품연령별-->
<div class="DepthCategory depth03_box" style="display:none;" ca_id='12'>
  <p class="pointer">포인터(열기)</p>
  <?php echo $cate_depth_3;?>
 </div>

<!--Depth_Category_허브-->
<div class="DepthCategory depth04_box" style="display:none;" ca_id='13'>
  <p class="pointer">포인터(열기)</p>
  <?php echo $cate_depth_4;?>
</div>

<!--Depth_Category_비타민_미네럴-->
<div class="DepthCategory depth05_box" style="display:none;" ca_id='14'>
  <p class="pointer">포인터(열기)</p>
  <?php echo $cate_depth_5;?>
</div>

<!--Depth_Category_아미노산-->
<div class="DepthCategory depth06_box" style="display:none;" ca_id='15'>
  <p class="pointer">포인터(열기)</p>
  <?php echo $cate_depth_6;?>
</div>

<!--Depth_Category_오메가3_COQ10-->
<div class="DepthCategory depth07_box" style="display:none;" ca_id='16'>
  <p class="pointer">포인터(열기)</p>
  <?php echo $cate_depth_7;?>
</div>

<!--Depth_Category_스포츠-->
<div class="DepthCategory depth08_box" style="display:none;" ca_id='17'>
  <p class="pointer">포인터(열기)</p>
  <?php echo $cate_depth_8;?>
</div>