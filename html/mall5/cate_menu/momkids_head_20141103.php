<?
# 상단 카테고리 버튼 고정 #
if($ca_id){
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
$cate_depth_1 = sub_cate_cache_load('30',11);
$cate_depth_2 = sub_cate_cache_load('31',11);
$cate_depth_3 = sub_cate_cache_load('32',5);
$cate_depth_4 = sub_cate_cache_load('33',11);
$cate_depth_5 = sub_cate_cache_load('34',11);
$cate_depth_6 = sub_cate_cache_load('35',11);
$cate_depth_7 = sub_cate_cache_load('36',11);


?>
<ul>
	<li class="depth01 first"><a href="<?=$g4['shop_path']?>/list.php?ca_id=30" class="<?=($active_ca == '30')? 'active':''?>">베이비스킨</a></li>
	<li class="depth02"><a href="<?=$g4['shop_path']?>/list.php?ca_id=31" class="<?=($active_ca == '31')? 'active':''?>">베이비푸드</a></li>
	<li class="depth03"><a href="<?=$g4['shop_path']?>/list.php?ca_id=32" class="<?=($active_ca == '32')? 'active':''?>">장난감</a></li>
	<li class="depth04"><a href="<?=$g4['shop_path']?>/list.php?ca_id=33" class="<?=($active_ca == '33')? 'active':''?>">교육용품</a></li>
	<li class="depth05"><a href="<?=$g4['shop_path']?>/list.php?ca_id=34" class="<?=($active_ca == '34')? 'active':''?>">출산 / 유아</a></li>
	<li class="depth06"><a href="<?=$g4['shop_path']?>/list.php?ca_id=35" class="<?=($active_ca == '35')? 'active':''?>">의류 / 잡화 / 침구</a></li>
	<li class="depth07"><a href="<?=$g4['shop_path']?>/list.php?ca_id=36" class="<?=($active_ca == '36')? 'active':''?>">식기 / 주방 / 세제</a></li>
</ul>
<!--DepthCategory_카테고리전체 동일-->
<div class="DepthCategory depth01_box" style="display:;">
  <!--<p class="pointer">포인터(열기)</p>-->
  <?=$cate_depth_1;?>
  <?/*
  <div>
    <dl>
      <dt>베이비스킨</dt>
      <dd><a href="">로션/크림/밤</a></dd>
      <dd><a href="">오일/미스트</a></dd>
      <dd><a href="">발진/오인트먼트</a></dd>
      <dd><a href="">썬크림</a></dd>
      <dd><a href="">샴푸/컨디셔너</a></dd>
      <dd><a href="">2in1</a></dd>
      <dd><a href="">바디워시/비누/버블</a></dd>
      <dd><a href="">핸드워시</a></dd>
      <dd><a href="">치약/칫솔</a></dd>
      <dd><a href="">유아네일케어</a></dd>
      <dd><a href="">세트</a></dd>
      <dd><a href="">그외</a></dd>
    </dl>
  </div>
  <div>
    <dl>
      <dt>베이비푸드</dt>
      <dd><a href="">비타민</a></dd>
      <dd><a href="">주스/음료</a></dd>
      <dd><a href="">이유식</a></dd>
      <dd><a href="">핑거푸드</a></dd>
      <dd><a href="">그외</a></dd>
    </dl>
  </div>
  <div>
    <dl>
      <dt>장난감</dt>
      <dd><a href="">딸랑이/치발기</a></dd>
      <dd><a href="">목욕/물놀이</a></dd>
      <dd><a href="">인형/봉제인형</a></dd>
      <dd><a href="">야외/물놀이</a></dd>
      <dd><a href="">세트</a></dd>
      <dd><a href="">그외</a></dd>
    </dl>
  </div>
  <div>
    <dl>
      <dt>교육용품</dt>
      <dd><a href="">악기놀이</a></dd>
      <dd><a href="">교육요구</a></dd>
      <dd><a href="">블록/조립/빌딩</a></dd>
      <dd><a href="">미술/공작/클레이</a></dd>
      <dd><a href="">게임/퍼즐</a></dd>
      <dd><a href="">그외</a></dd>
    </dl>
  </div>
  <div>
    <dl>
      <dt>출산/유아</dt>
      <dd><a href="">출산 전.후케어</a></dd>
      <dd><a href="">수유용품</a></dd>
      <dd><a href="">기저귀/물티슈/기저귀가방</a></dd>
      <dd><a href="">젖병/젖꼭지</a></dd>
      <dd><a href="">속싸게/겉싸게/턱받이</a></dd>
      <dd><a href="">배변기/부스터싯</a></dd>
      <dd><a href="">유모차/카시트/아기띠</a></dd>
      <dd><a href="">상비약</a></dd>
      <dd><a href="">세트</a></dd>
      <dd><a href="">그외</a></dd>
    </dl>
  </div>
  <div>
    <dl>
      <dt>의류/잡화/침구</dt>
      <dd><a href="">목욕가운</a></dd>
      <dd><a href="">신생아 의류</a></dd>
      <dd><a href="">아동의류</a></dd>
      <dd><a href="">양말</a></dd>
      <dd><a href="">가방</a></dd>
      <dd><a href="">담요/이불</a></dd>
      <dd><a href="">침구</a></dd>
      <dd><a href="">수영복/우산/장화</a></dd>
      <dd><a href="">모자</a></dd>
      <dd><a href="">세트</a></dd>
      <dd><a href="">그외</a></dd>
    </dl>
  </div>
  <div>
    <dl>
      <dt>식기/주방/세제</dt>
      <dd><a href="">유아식기</a></dd>
      <dd><a href="">물병</a></dd>
      <dd><a href="">스푼/포크/젓가락</a></dd>
      <dd><a href="">유아세제</a></dd>
      <dd><a href="">수납/정리</a></dd>
      <dd><a href="">가방</a></dd>
      <dd><a href="">일회용품</a></dd>
      <dd><a href="">세트</a></dd>
      <dd><a href="">그외</a></dd>
    </dl>
  </div>
  */
  ?>
</div>
<div class="DepthCategory depth02_box" style="display:;"><?=$cate_depth_2;?></div>
<div class="DepthCategory depth03_box" style="display:;"><?=$cate_depth_3;?></div>
<div class="DepthCategory depth04_box" style="display:;"><?=$cate_depth_4;?></div>
<div class="DepthCategory depth05_box" style="display:;"><?=$cate_depth_5;?></div>
<div class="DepthCategory depth06_box" style="display:;"><?=$cate_depth_6;?></div>
<div class="DepthCategory depth07_box" style="display:;"><?=$cate_depth_7;?></div>