<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-04-02
 * Time: 오후 12:54
 */

include "_common.php";

# 전체 카운트 #
$sql = sql_fetch("
	select
		count(*) as cnt
	from
		yc4_item_new a
		left join
		".$g4['yc4_item_table']." b on a.type='I' and a.type_value = b.it_id
	where
		a.use_fg = 'Y'
");
$total_count = $sql['cnt'];

// 전체 페이지 계산
$total_page  = ceil($total_count / 5);
// 페이지가 없으면 첫 페이지 (1 페이지)
if ($page == "") $page = 1;
// 시작 레코드 구함
$from_record = ($page - 1) * 5;

$qstr = $_GET;
unset($qstr['page']);
$qstr = http_build_query($qstr);

$page_btn = get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr1&page=");
if($page_btn){
    $page_btn = "<div class='paging'>$page_btn</div>";
}
# 신규상품 리스트 로드 #
$sql = sql_query("
	select
		a.* ,b.it_amount,b.it_amount_usd,b.it_maker_kor,b.it_maker
	from
		yc4_item_new a
		left join
		".$g4['yc4_item_table']." b on a.type='I' and a.type_value = b.it_id
	where
		a.use_fg = 'Y'
	order by a.sort
	limit ".$from_record." , 5
");
$list_li = '';
while($row = sql_fetch_array($sql)){
    $item_info = "";
    if($row['type'] == 'B'){
        $maker = sql_fetch("select it_maker,it_maker_kor from ".$g4['yc4_item_table']." where it_maker = '".$row['type_value']."' limit 1");
        $row['it_maker'] = $maker['it_maker'];
        $row['it_maker_kor'] = $maker['it_maker_kor'];
        $link = $g4['shop_path'].'/search.php?it_maker='.urlencode($row['it_maker']);
    }else{
        $link = $g4['shop_path'].'/item.php?it_id='.$row['type_value'];if(!$row['it_amount_usd']){
            $row['it_amount_usd'] = usd_convert($row['it_amount']);
        }
        $item_info = "
			<div class='item_con'>
				<span class='title'>
					<span class='ko'>".$row['it_name_kor']."</span>
					<span class='e'>".$row['it_name_eng']."</span>
				</span>
				<span class='price'>￦ ".number_format($row['it_amount'])." ($ ".number_format($row['it_amount_usd'],2).")</span>
			</div>
		";
    }

    $list_li .= "
		<li class='new_list'>
			<a href='".$link."'>
				<span class='brand'>[".$row['it_maker']."] ".$row['it_maker_kor']."</span>
				<span class='list_title'>
					<span class='b_title'>".nl2br($row['title'])."</span>
					<span class='s_title'>".nl2br($row['title_desc'])."</span>
				</span>
				".$item_info."
				<span class='img'><img src='".$row['img_url']."'/></span>
			</a>
		</li>
	";
}

include_once "_head.php";
?>


<div style="width:1030px;">
    <img src="http://115.68.20.84/new_item_list/new_item_title.jpg">
</div>
<?php echo $page_btn;?>
<ul>
    <?php echo $list_li;?>
    <?/*
	<li class="new_list">
		<a href="#">
			<span class="brand">[Quinn Popcorn] 퀸 팝콘</span>
			<span class="list_title">
				<span class="b_title">고소한 팝콘에 시즈닝을 뿌려서 Shake it Shake it!</span>
				<span class="s_title">NON-GMO 인증을 받은 올가닉 팝콘! <br /> NON-GMO 인증을 받은 올가닉 팝콘!</span>
			</span>
			<div class="item_con">
				<span class="title">
					<span class="ko">구미 비타민C 슬라이스 250mg, 90정</span>
					<span class="e">Gummy Vitamin C Slices 250mg, 90 gummies</span>
				</span>

				<span class="price">￦ 7,700 ($ 6.92)</span>
			</div>
			<span class="img"><img src="http://115.68.20.84/new_item_list/list_img01.jpg"></span>
		</a>
	</li>

	<li class="new_list">
		<a href="#">
			<span class="brand">[Quinn Popcorn] 퀸 팝콘</span>
			<span class="list_title">
				<span class="b_title">고소한 팝콘에 시즈닝을 뿌려서 Shake it Shake it! <br /> NON-GMO 인증을 받은 올가닉 팝콘!</span>
				<span class="s_title">프랑스의 청정지역 '프로방스'<br />유기농 원료의 에너지를 담은 스킨케어로<br />아기와 함께 온 가족이 사용하는 제품을 만들어<br />전세계인에게 사랑을 받고 있습니다.</span>
			</span>
			<!--
			<div class="item_con">
				<span class="title">
					<span class="ko">구미 비타민C 슬라이스 250mg, 90정</span>
					<span class="e">Gummy Vitamin C Slices 250mg, 90 gummies</span>
				</span>

				<span class="price">￦ 7,700 ($ 6.92)</span>
			</div>
				-->
			<span class="img"><img src="http://115.68.20.84/new_item_list/list_img02.jpg"></span>
		</a>
	</li>
	*/?>
</ul>
<?php echo $page_btn;?>
<?php
include_once "_tail.php";
?>