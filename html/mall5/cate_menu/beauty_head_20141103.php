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
$cate_depth_1 = sub_cate_cache_load('40',11);
$cate_depth_2 = sub_cate_cache_load('41',11);
$cate_depth_3 = sub_cate_cache_load('42',5);
$cate_depth_4 = sub_cate_cache_load('43',11);
$cate_depth_5 = sub_cate_cache_load('44',11);
$cate_depth_6 = sub_cate_cache_load('45',11);



?>
<ul>
	<li class="depth01 first"><a href="<?=$g4['shop_path']?>/list.php?ca_id=40" class="<?=($active_ca == '40')? 'active':''?>">페이스</a></li>
	<li class="depth02"><a href="<?=$g4['shop_path']?>/list.php?ca_id=41" class="<?=($active_ca == '41')? 'active':''?>">바디</a></li>
	<li class="depth03"><a href="<?=$g4['shop_path']?>/list.php?ca_id=42" class="<?=($active_ca == '42')? 'active':''?>">헤어</a></li>
	<li class="depth04"><a href="<?=$g4['shop_path']?>/list.php?ca_id=43" class="<?=($active_ca == '43')? 'active':''?>">용품/소품</a></li>
	<li class="depth05"><a href="<?=$g4['shop_path']?>/list.php?ca_id=44" class="<?=($active_ca == '44')? 'active':''?>">피부비타민</a></li>
	<li class="depth06"><a href="<?=$g4['shop_path']?>/list.php?ca_id=45" class="<?=($active_ca == '45')? 'active':''?>">남성</a></li>
</ul>

<!--DepthCategory_카테고리전체 동일-->
<div class="DepthCategory depth01_box" style="display:;">
  <!--<p class="pointer">포인터(열기)</p>-->
  <?=$cate_depth_1;?>
  <?/*
  <div>
    <dl>
      <dt>페이스</dt>
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
      <dt>바디</dt>
      <dd><a href="">비타민</a></dd>
      <dd><a href="">주스/음료</a></dd>
      <dd><a href="">이유식</a></dd>
      <dd><a href="">핑거푸드</a></dd>
      <dd><a href="">그외</a></dd>
    </dl>
  </div>
  <div>
    <dl>
      <dt>헤어</dt>
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
      <dt>용품/소품</dt>
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
      <dt>피부비타민</dt>
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
      <dt>남성</dt>
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
  */?>
  
</div>

<div class="DepthCategory depth02_box" style="display:;"><?=$cate_depth_2;?></div>
<div class="DepthCategory depth03_box" style="display:;"><?=$cate_depth_3;?></div>
<div class="DepthCategory depth04_box" style="display:;"><?=$cate_depth_4;?></div>
<div class="DepthCategory depth05_box" style="display:;"><?=$cate_depth_5;?></div>
<div class="DepthCategory depth06_box" style="display:;"><?=$cate_depth_6;?></div>