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
$cate_depth_1 = sub_cate_cache_load('20',11);
$cate_depth_2 = sub_cate_cache_load('21',11);
$cate_depth_3 = sub_cate_cache_load('22',5);
$cate_depth_4 = sub_cate_cache_load('23',11);
$cate_depth_5 = sub_cate_cache_load('24',11);


?>
<ul>
	<li class="depth01 first"><a href="<?=$g4['shop_path']?>/list.php?ca_id=20" class="<?=($active_ca == '20')? 'active':''?>">욕실</a></li>
	<li class="depth02"><a href="<?=$g4['shop_path']?>/list.php?ca_id=21" class='<?=($active_ca == '21')? 'active':''?>'>키친</a></li>
	<li class="depth03"><a href="<?=$g4['shop_path']?>/list.php?ca_id=22" class='<?=($active_ca == '22')? 'active':''?>'>세탁</a></li>
	<li class="depth04"><a href="<?=$g4['shop_path']?>/list.php?ca_id=23" class='<?=($active_ca == '23')? 'active':''?>'>리빙</a></li>
	<li class="depth05"><a href="<?=$g4['shop_path']?>/list.php?ca_id=24" class='<?=($active_ca == '24')? 'active':''?>'>애견</a></li>
</ul>
<!--DepthCategory_카테고리전체 동일-->
<div class="DepthCategory depth01_box" style="display:;">
  <!--<p class="pointer">포인터(열기)</p>-->
  <?=$cate_depth_1;?>
  <?/*
  <div>
    <dl>
      <dt>욕실</dt>
      <dd><a href="">핸드워시/손소독제</a></dd>
      <dd><a href="">구강용품/오랄케어</a></dd>
      <dd><a href="">생리대/여성용품/청결제</a></dd>
      <dd><a href="">면도기/쉐이빙용품</a></dd>
      <dd><a href="">욕실 소품</a></dd>
      <dd><a href="">세트</a></dd>
      <dd><a href="">그외</a></dd>
    </dl>
  </div>
  <div>
    <dl>
      <dt>키친</dt>
      <dd><a href="">앞치마</a></dd>
      <dd><a href="">조리기구</a></dd>
      <dd><a href="">식기용품</a></dd>
      <dd><a href="">커피/티 용품</a></dd>
      <dd><a href="">텀블러</a></dd>
      <dd><a href="">주방 소품</a></dd>
      <dd><a href="">세트</a></dd>
      <dd><a href="">그외</a></dd>
    </dl>
  </div>
  <div>
    <dl>
      <dt>세탁</dt>
      <dd><a href="">세탁세제</a></dd>
      <dd><a href="">섬유유연제</a></dd>
      <dd><a href="">주방세제</a></dd>
      <dd><a href="">소품</a></dd>
      <dd><a href="">세트</a></dd>
      <dd><a href="">그외</a></dd>
    </dl>
  </div>
  <div>
    <dl>
      <dt>리빙</dt>
      <dd><a href="">향초</a></dd>
      <dd><a href="">디퓨저</a></dd>
      <dd><a href="">방향제/차 방향제</a></dd>
      <dd><a href="">문구</a></dd>
      <dd><a href="">인테리어/소품</a></dd>
      <dd><a href="">공구/DIY/안전</a></dd>
      <dd><a href="">세트</a></dd>
      <dd><a href="">그외</a></dd>
    </dl>
  </div>
  <div>
    <dl>
      <dt>애견</dt>
      <dd><a href="">강아지</a></dd>
      <dd><a href="">고양이</a></dd>
      <dd><a href="">그외</a></dd>
    </dl>
  </div>
  */?>
</div>
<div class="DepthCategory depth02_box" style="display:;">
	<?=$cate_depth_2;?>
</div>
<div class="DepthCategory depth03_box" style="display:;">
	<?=$cate_depth_3;?>
</div>
<div class="DepthCategory depth04_box" style="display:;">
	<?=$cate_depth_4;?>
</div>
<div class="DepthCategory depth05_box" style="display:;">
	<?=$cate_depth_5;?>
</div>