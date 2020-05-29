<?
# 상단 카테고리 버튼 고정 #
if($ca_id){
	$active_ca = substr($ca_id,0,2);
}else{
//	$active_ca = 'i1';
}

# 푸드는 서브카테고리 없음


?>
<ul>
	<li class="depth01 first"><a href="<?=$g4['shop_path']?>/list.php?ca_id=60" class="<?=($active_ca == '60')? 'active':''?>">차/음료</a></li>
	<li class="depth02"><a href="<?=$g4['shop_path']?>/list.php?ca_id=61" class="<?=($active_ca == '61')? 'active':''?>">커피/코코아</a></li>
	<li class="depth03"><a href="<?=$g4['shop_path']?>/list.php?ca_id=62" class="<?=($active_ca == '62')? 'active':''?>">시리얼/오트밀/영양바</a></li>
	<li class="depth04"><a href="<?=$g4['shop_path']?>/list.php?ca_id=63" class="<?=($active_ca == '63')? 'active':''?>">쨈/꿀/시럽</a></li>
	<li class="depth05"><a href="<?=$g4['shop_path']?>/list.php?ca_id=64" class="<?=($active_ca == '64')? 'active':''?>">향신료/조미료</a></li>
	<li class="depth06"><a href="<?=$g4['shop_path']?>/list.php?ca_id=65" class="<?=($active_ca == '65')? 'active':''?>">베이킹/파우더</a></li>
	<li class="depth07"><a href="<?=$g4['shop_path']?>/list.php?ca_id=66" class="<?=($active_ca == '66')? 'active':''?>">잡곡/혼합곡</a></li>
	<li class="depth08"><a href="<?=$g4['shop_path']?>/list.php?ca_id=67" class="<?=($active_ca == '67')? 'active':''?>">오일/소스</a></li>
	<li class="depth09"><a href="<?=$g4['shop_path']?>/list.php?ca_id=68" class="<?=($active_ca == '68')? 'active':''?>">즉석식품/면</a></li>
	<li class="depth10"><a href="<?=$g4['shop_path']?>/list.php?ca_id=69" class="<?=($active_ca == '69')? 'active':''?>">견과류/말린과일</a></li>
	<li class="depth11"><a href="<?=$g4['shop_path']?>/list.php?ca_id=6a" class="<?=($active_ca == '6a')? 'active':''?>">설탕/소금</a></li>
	<li class="depth12"><a href="<?=$g4['shop_path']?>/list.php?ca_id=6b" class="<?=($active_ca == '6b')? 'active':''?>">베이비푸드</a></li>
</ul>
