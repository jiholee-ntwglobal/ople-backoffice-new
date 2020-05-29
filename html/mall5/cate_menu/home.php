<?php
$st_new_item_data = array();
foreach($main_json->main_new_item as $val){
    if($val->s_id == $_SESSION['s_id']){
        $st_new_item_data[] = $val;
    }
}
shuffle($st_new_item_data);

$st_new_item_data_cnt = count($st_new_item_data);
$new_item_html = '';
foreach($st_new_item_data as $val){
    $item_info = '';
    switch($val->type){
        case 'I' :
            $link = $g4['shop_path']."/item.php?it_id=".$val->type_value;
            if(!$main_new_item->item_data->it_amount_usd){
                $main_new_item->item_data->it_amount_usd = usd_convert($val->item_data->it_amount);
            }
            $val->it_maker = $val->item_data->it_maker;
            $val->it_maker_kor = $val->item_data->it_maker_kor;
            $item_info = "
				<div class='item_con'>
					<span class='title'>
						<span class='ko'>".$val->it_maker_kor."</span>
						<span class='e'>".$val->it_maker."</span>
					</span>
					<span class='price'>￦ ".number_format($val->item_data->it_amount)." ($ ".number_format(usd_convert($val->item_data->it_amount),2).")</span>
				</div>
			";
            break;
        case 'B' :
            $link = $g4['shop_path']."/search.php?it_maker=".urlencode($val->type_value);
            $val->it_maker = $val->item_data->it_maker;
            $val->it_maker_kor = $val->item_data->it_maker_kor;
            break;
    }
    $new_item_html .= "
        <li class='new_list'>
            <a href='".$link."'>
                <span class='brand'>[".$val->it_maker."] ".$val->it_maker_kor."</span>
                <span class='list_title'>
                    <span class='b_title'>".nl2br($val->title)."</span>
                    <span class='s_title'>".nl2br($val->title_desc)."</span>
                </span>
                ".$item_info."
                <span class='img'><img src='".$val->img_url."'/></span>
            </a>
        </li>
    ";
}

?>

<!--MainSpotZone-->
<div class="MainSpotZone">
    <!--<ul class="bxslider" style='display:none;'>
					<--<?=$banner_lst;?>
				</ul>-->
    <div class='MainSpotImg'>
        <div class='MainSpotImg_mask'>
            <?
            # 롤링배너 캐시파일 로드 2014-09-29 홍민기
            # 캐시 생성은 banner_config_new.php?mode=banner_update 로 처리 #
            include $g4['full_path']."/cache/rolling_banner_".$_SESSION['s_id'].'.htm';
            ?>

            <?/*
						<div>
							<span class='MainSpotImgTitle'><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Marvis+">치약계의샤넬!마비스</a></span>
							<a href="<?=$g4['path'];?>/shop/search.php?it_maker=Marvis+"><img src="http://115.68.20.84/mall6/home_banner/main_spot01.jpg" alt="메인스팟" /></a>
						</div>
						<div>
							<span class='MainSpotImgTitle'><a href="<?=$g4['path'];?>/shop/search.php?it_maker=ZOKU">아이디어상품!조쿠</a></span>
							<a href="<?=$g4['path'];?>/shop/search.php?it_maker=ZOKU"><img src="http://115.68.20.84/mall6/home_banner/main_spot02.jpg" alt="메인스팟" /></a>
						</div>
						<div>
							<span class='MainSpotImgTitle'><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Joseph+Joseph">당당한1인자!조셉조셉</a></span>
							<a href="<?=$g4['path'];?>/shop/search.php?it_maker=Joseph+Joseph"><img src="http://115.68.20.84/mall6/home_banner/main_spot03.jpg" alt="메인스팟" /></a>
						</div>
						<div>
							<span class='MainSpotImgTitle'><a href="#">명품캔들!우드윅</a></span>
							<a href="#"><img src="http://115.68.20.84/mall6/home_banner/main_spot04.jpg" alt="메인스팟" /></a>
						</div>
						<div>
							<span class='MainSpotImgTitle'><a href="<?=$g4['path'];?>/shop/search.php?it_maker=ATTITUDE">친환경세제!에티튜드</a></span>
							<a href="<?=$g4['path'];?>/shop/search.php?it_maker=ATTITUDE"><img src="http://115.68.20.84/mall6/home_banner/main_spot05.jpg" alt="메인스팟" /></a>
						</div>
*/?>
        </div>

    </div>
    <ul class='MainSpotTitle'>
        <?/*
                    <li class="active"><a href="">건강한장을 위한 유산균</a></li>
                    <li><a href="">슈퍼푸드 아사이베리</a></li>
                    <li><a href="">애완동물을 위한 코세퀸</a></li>
                    <li><a href="">계절의바램 향초</a></li>
                    <li><a href="">헐리웃 스타일의 잇 블랜드보틀</a></li>
					*/
        ?>
    </ul>
</div>

<?php if(date('YmdH') >= '2014112909' && date('YmdH') <= '2014120112'){?>
    <!--Event_timeSale 14.1106-->
    <style type="text/css">
        .blackFriday_event {
            position: absolute;
            text-align: center;
            width: 100%;
            left: 0;
            margin-top: 20px;
            background: url(http://115.68.20.84/event/blackFriday/bg_blackFriday.jpg)repeat-x 0 0;
            z-index:10;
        }
        .home .best_zone {padding-top:205px;}
    </style>
    <div class="blackFriday_event">
        <a href="<?=$g4['path'];?>/shop/event_cybermonday.php"><img src="http://115.68.20.84/event/blackFriday/main_title_blackFriday_day.jpg" alt="블랙프라이데이_사이버먼데이">
        </a>
    </div>
<?php }?>

<!--HotDealZpne
			<div class="hotdealzoneArea">
				<ul>
					<?php
//	include $g4['full_path']."/cate_menu/hotdeal_zone.php";
?>

				</ul>
			</div>-->

<?php if($new_item_html){?>
    <div class="new_product_wrap">
        <h2><img data-original="http://115.68.20.84/mall6/title_newproduct.png" alt="New Product" /></h2>
        <!--div class="icon_new_hot" style=" position: static;"><span><img src="http://115.68.20.84/mall6/ico_new_hot.png" alt="신규상품"/></span></div-->
        <ul class="new_product">
            <?php echo $new_item_html;?>
        </ul>
        <?php if($st_new_item_data_cnt>1){?>
            <span class="main_list_btn main_list_btn_prev" onclick="main_new_item_rolling('prev');"> < </span>
            <span class="main_list_btn main_list_btn_next" onclick="main_new_item_rolling('next');"> > </span>
        <?}?>
    </div>
    <script>
        $(function(){
            var main_list_li_cnt = $('.new_product > li.new_list').length;
            var main_list_li_width = $('.new_product > li.new_list').outerWidth();
            $('.new_product').width(main_list_li_cnt*main_list_li_width);


        });
        function main_new_item_rolling(mode){
            $('.new_product').stop(true,true);
            var main_list_li_cnt = $('.new_product > li.new_list').length;
            var ul_width = $('.new_product').width();
            var li_width = ul_width/main_list_li_cnt;
            var now_left = Number($('.new_product').css('left').replace(/[a-z]/g,''));
            if(now_left == ''){
                now_left = 0;
            }


            switch (mode){
                case 'prev' :
                    if(now_left == 0){
                        now_left = (ul_width - li_width) * -1;
                    }else{
                        now_left += li_width;
                    }
                    break;
                case 'next' :
                    if(now_left <= (ul_width - li_width)*-1){
                        now_left = 0;
                    }else{
                        now_left -= li_width;
                    }
                    break;
            }

            $('.new_product').animate({
                left : +now_left
            },300);
            return;
        }
    </script>
<?php }?>

<!--베스트상품-->
<div class="best_zone">
    <h2><img src="http://115.68.20.84/mall6/title_BestChoice.png" alt="Ople Best" /></h2>
    <div class="best_list_new">
        <ul>
            <?php
            $i=1;
            foreach($main['main_station_item'][$_SESSION['s_id']] as $it_id => $data){
                if(in_array($i,array(1,5,9,13))){
                    $first = " class='first'";
                }else{
                    $first = '';
                }
                $it_amount_usd = round($data['it_amount'] / $default['de_conv_pay'],2);
                $msrp_per = round(($data['msrp'] - $it_amount_usd) / $data['msrp'] * 100);
                $it_link = $g4['shop_path'].'/item.php?it_id='.$it_id;
                ?>
                <li<?php echo $first;?>>
                    <span class="discount_box"><strong><?php echo $msrp_per;?>%</strong></span>
                    <a href="<?php echo $it_link;?>">
                        <?php echo get_it_image($it_id.'_s',175,175,null,null,null,null,null);?>
                    </a>
                    <span class="best_title"><a href="<?php echo $it_link;?>"><?php echo $data['it_name']?></a></span>
                    <span class="best_price"><em class="msrp_price">$ <?php echo number_format($data['msrp'],2)?></em> <strong>$ <?php echo number_format($it_amount_usd,2);?></strong><em class="won_price">(\ <?php echo number_format($data['it_amount'])?>)</em></span>
                </li>
                <?php
                $i++;
            }
            ?>
        </ul>
    </div>
</div>
<!--SpecialArea-->

<!--FeaturedCategoris-->
<div class="featured_category">
    <h2><img data-original="http://115.68.20.84/mall6/title_FeaturedCategory.png" alt="Featured Category" /></h2>
    <div class="Area_wrap">
        <dl class="category_zoneA">
            <a href="<?=$g4['shop_path']?>/list.php?ca_id=22"><dt>욕실/위생용품</dt></a>
            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=2210">핸드워시/손소독제</a></dd>
            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=2211">구강위생용품
                </a></dd>
            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=2212">여성용품
                </a></dd>
            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=2213">쉐이빙용품
                </a></dd>
            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=2214">욕실잡화
                </a></dd>
        </dl>
        <dl class="category_zoneB">
            <a href="<?=$g4['shop_path']?>/list.php?ca_id=20"><dt>주방/식기</dt></a>
            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=2014">조리도구
                </a></dd>

            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=2010">식기
                </a></dd>

            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=2013">물병/물통
                </a></dd>

            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=2011">컵
                </a></dd>

            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=2015">주방잡화
                </a></dd>
        </dl>
        <dl class="category_zoneC">
            <a href="<?=$g4['shop_path']?>/list.php?ca_id=21"><dt>세제/청소</dt></a>
            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=2110">주방세제
                </a></dd>

            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=2111">청소소제
                </a></dd>

            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=212110">세탁세제
                </a></dd>

            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=212111">섬유유연제
                </a></dd>

            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=212113">세탁소품
                </a></dd>
        </dl>
        <dl class="category_zoneD">
            <a href="<?=$g4['shop_path']?>/list.php?ca_id=23"><dt>홈/소품</dt></a>
            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=231110">향초/디퓨저
                </a></dd>

            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=231112">방향제/차방향제
                </a></dd>

            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=2310">수납/정리용품
                </a></dd>

            <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=2312">가정용 상비용품
                </a></dd>
        </dl>
    </div>
</div>

<!--추천상품
<div class="recommend_box">
    <span><span class="ico_discount">초특가</span><a href="<?=$g4['path'];?>/shop/item.php?it_id=1400007404"><img data-original="http://115.68.20.84/mall6/home_banner/banner_recommend12.jpg" alt="추천1" /></a></span>
    <span><a href="<?=$g4['path'];?>/shop/item.php?it_id=1407144534"><img data-original="http://115.68.20.84/mall6/home_banner/banner_recommend23.jpg" alt="추천2" /></a></span>
</div>-->
<div class="SpecialZone">
    <!--Weekley-->
    <div class="weekleyZone">
        <h2><img data-original="http://115.68.20.84/mall6/title_SpecialWeekly.png" alt="Special Weekley" /></h2>
        <p><a href="<?=$g4['path'];?>/shop/search.php?it_maker=LEGO&sh_s_id%5B%5D=4&ca_id%5B%5D=20&ca_id%5B%5D=23&sh_s_id%5B%5D=5&ca_id%5B%5D=31"><img data-original="http://115.68.20.84/mall6/home_banner/banner_specialWeek.jpg" alt="스페셜브랜드" /></a></p>
    </div>
    <!--brand-->
    <div class="brandZone">
        <h2><img data-original="http://115.68.20.84/mall6/title_SpecialBrand.png" alt="Special Brand" /></h2>
        <ul>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=ATTITUDE" target="_blank"><img data-original="http://115.68.20.84/mall6/home_banner/brand01.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Ecover" target="_blank"><img data-original="http://115.68.20.84/mall6/home_banner/brand02.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Method" target="_blank"><img data-original="http://115.68.20.84/mall6/home_banner/brand03.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Radius" target="_blank"><img data-original="http://115.68.20.84/mall6/home_banner/brand04.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Seven+Generation" target="_blank"><img data-original="http://115.68.20.84/mall6/home_banner/brand05.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=NatraCare" target="_blank"><img data-original="http://115.68.20.84/mall6/home_banner/brand06.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Chef%27n" target="_blank"><img data-original="http://115.68.20.84/mall6/home_banner/brand07.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Joseph+Joseph" target="_blank"><img data-original="http://115.68.20.84/mall6/home_banner/brand08.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Tea+forte+" target="_blank"><img data-original="http://115.68.20.84/mall6/home_banner/brand09.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=BlenderBottle" target="_blank"><img data-original="http://115.68.20.84/mall6/home_banner/brand10.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Thermos" target="_blank"><img data-original="http://115.68.20.84/mall6/home_banner/brand11.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Nellie%27s" target="_blank"><img data-original="http://115.68.20.84/mall6/home_banner/brand12.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Mrs.+Meyer%27s" target="_blank"><img data-original="http://115.68.20.84/mall6/home_banner/brand13.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Yankee+Candle" target="_blank"><img data-original="http://115.68.20.84/mall6/home_banner/brand14.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=WoodWick" target="_blank"><img data-original="http://115.68.20.84/mall6/home_banner/brand15.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=LEGO" target="_blank"><img data-original="http://115.68.20.84/mall6/home_banner/brand16.png"></a></li>
        </ul>
    </div>

</div>
</div>