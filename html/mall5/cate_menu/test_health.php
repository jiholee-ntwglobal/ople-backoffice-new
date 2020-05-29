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
					<span class='price'>￦ ".number_format($val->item_data->it_amount)." ($ ".number_format($val->item_data->it_amount_usd,2).")</span>
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
            <?php
            # 롤링배너 캐시파일 로드 2014-09-29 홍민기
            # 캐시 생성은 banner_config_new.php?mode=banner_update 로 처리 #
            include $g4['full_path']."/cache/rolling_banner_".$_SESSION['s_id'].'.htm';
            ?>
            <?/*
						<div>
							<span class='MainSpotImgTitle'><a href="<?=$g4['path'];?>/shop/event.php?ev_id=1400140449">슈퍼푸드 아사이베리</a></span>
							<a href="<?=$g4['path'];?>/shop/event.php?ev_id=1400140449"><img src="http://115.68.20.84/mall6/health_banner/main_spot01.jpg" alt="메인스팟" /></a>
						</div>
			            <div>
							<span class='MainSpotImgTitle'><a href="<?=$g4['path'];?>/shop/list.php?ca_id=1061">건강한장을 위한 유산균</a></span>
							<a href="<?=$g4['path'];?>/shop/list.php?ca_id=1061"><img src="http://115.68.20.84/mall6/health_banner/main_spot02.jpg" alt="메인스팟" /></a>
						</div>
						<div>
							<span class='MainSpotImgTitle'><a href="<?=$g4['path'];?>/shop/event_0627.php">눈건강 영양제 모음</a></span>
							<a href="<?=$g4['path'];?>/shop/event_0627.php"><img src="http://115.68.20.84/mall6/health_banner/main_spot03.jpg" alt="메인스팟" /></a>
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
        .health .featured_category {padding-top:205px;}
    </style>
    <div class="blackFriday_event"><a href="<?=$g4['path'];?>/shop/event_cybermonday.php"><img src="http://115.68.20.84/event/blackFriday/main_title_blackFriday_day.jpg" alt="블랙프라이데이_사이버먼데이"></a></div>
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
            var main_list_li_cnt = $('.main_list > li.new_list').length;
            var main_list_li_width = $('.main_list > li.new_list').outerWidth();
            $('.main_list').width(main_list_li_cnt*main_list_li_width);


        });
        function main_new_item_rolling(mode){
            $('.main_list').stop(true,true);
            var main_list_li_cnt = $('.main_list > li.new_list').length;
            var ul_width = $('.main_list').width();
            var li_width = ul_width/main_list_li_cnt;
            var now_left = Number($('.main_list').css('left').replace(/[a-z]/g,''));
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

            $('.main_list').animate({
                left : +now_left
            },300);
            return;
        }
    </script>
<?php }?>

<!--베스트상품-->
<div class="best_zone">
    <h2><img data-original="http://115.68.20.84/mall6/title_BestChoice.png" alt="Ople Best" /></h2>
    <!-- 2014.1105 안보이기 / <p class="planning"><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Doctor%27s+Best&sh_s_id%5B%5D=3&ca_id%5B%5D=10&ca_id%5B%5D=11&ca_id%5B%5D=12&ca_id%5B%5D=13&ca_id%5B%5D=16&ca_id%5B%5D=17&ca_id%5B%5D=19"><img data-original="http://115.68.20.84/mall6/health_banner/banner_best.jpg" alt="기획전 닥터스베스트" /></a></p> /-->

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
                    <span class="best_title"><a href="<?php echo $it_link;?>"><?php echo stripslashes($data['it_name']);?></a></span>
                    <span class="best_price"><em class="msrp_price">$ <?php echo number_format($data['msrp'],2)?></em> <strong>$ <?php echo number_format($it_amount_usd,2);?></strong><em class="won_price">(\ <?php echo number_format($data['it_amount'])?>)</em></span>
                </li>
                <?php
                $i++;
            }

            ?>


            <?/*
                        <li class="first">
                            <span class="discount_box"><strong>44%</strong></span>
                            <a href="./shop/item.php?it_id=1413898640"><img src="http://115.68.20.84/mall6/health_banner/best_1.jpg" alt="베스트상품1" /></a>
                            <span class="best_title"><a href="">대용량 ULTIMATE OMEGA 180정</a></span>
                            <span class="best_price"><em class="msrp_price">$ 39.80</em> <strong>$ 51.35</strong><em class="won_price">(\ 56,900)</em></span>
                        </li>

                        <li>
                            <span class="discount_box"><strong>44%</strong></span>
                            <a href="./shop/item.php?it_id=1413898640"><img src="http://115.68.20.84/mall6/health_banner/best_2.jpg" alt="베스트상품1" /></a>
                            <span class="best_title"><a href="">대용량 ULTIMATE OMEGA 180정</a></span>
                            <span class="best_price"><em class="msrp_price">$ 39.80</em> <strong>$ 51.35</strong><em class="won_price">(\ 56,900)</em></span>
                        </li>
                        <li>
                            <span class="discount_box"><strong>44%</strong></span>
                            <a href="./shop/item.php?it_id=1413898640"><img src="http://115.68.20.84/mall6/health_banner/best_3.jpg" alt="베스트상품1" /></a>
                            <span class="best_title"><a href="">대용량 ULTIMATE OMEGA 180정</a></span>
                            <span class="best_price"><em class="msrp_price">$ 39.80</em> <strong>$ 51.35</strong><em class="won_price">(\ 56,900)</em></span>
                        </li>
                        <li>
                            <span class="discount_box"><strong>44%</strong></span>
                            <a href="./shop/item.php?it_id=1413898640"><img src="http://115.68.20.84/mall6/health_banner/best_4.jpg" alt="베스트상품1" /></a>
                            <span class="best_title"><a href="">대용량 ULTIMATE OMEGA 180정</a></span>
                            <span class="best_price"><em class="msrp_price">$ 39.80</em> <strong>$ 51.35</strong><em class="won_price">(\ 56,900)</em></span>
                        </li>
                        <li class="first">
                            <span class="discount_box"><strong>44%</strong></span>
                            <a href="./shop/item.php?it_id=1413898640"><img src="http://115.68.20.84/mall6/health_banner/best_5.jpg" alt="베스트상품1" /></a>
                            <span class="best_title"><a href="">대용량 ULTIMATE OMEGA 180정</a></span>
                            <span class="best_price"><em class="msrp_price">$ 39.80</em> <strong>$ 51.35</strong><em class="won_price">(\ 56,900)</em></span>
                        </li>
                        <li>
                            <span class="discount_box"><strong>44%</strong></span>
                            <a href="./shop/item.php?it_id=1413898640"><img src="http://115.68.20.84/mall6/health_banner/best_6.jpg" alt="베스트상품1" /></a>
                            <span class="best_title"><a href="">대용량 ULTIMATE OMEGA 180정</a></span>
                            <span class="best_price"><em class="msrp_price">$ 39.80</em> <strong>$ 51.35</strong><em class="won_price">(\ 56,900)</em></span>
                        </li>
                        <li>
                            <span class="discount_box"><strong>44%</strong></span>
                            <a href="./shop/item.php?it_id=1413898640"><img src="http://115.68.20.84/mall6/health_banner/best_7.jpg" alt="베스트상품1" /></a>
                            <span class="best_title"><a href="">대용량 ULTIMATE OMEGA 180정</a></span>
                            <span class="best_price"><em class="msrp_price">$ 39.80</em> <strong>$ 51.35</strong><em class="won_price">(\ 56,900)</em></span>
                        </li>
                        <li>
                            <span class="discount_box"><strong>44%</strong></span>
                            <a href="./shop/item.php?it_id=1413898640"><img src="http://115.68.20.84/mall6/health_banner/best_8.jpg" alt="베스트상품1" /></a>
                            <span class="best_title"><a href="">대용량 ULTIMATE OMEGA 180정</a></span>
                            <span class="best_price"><em class="msrp_price">$ 39.80</em> <strong>$ 51.35</strong><em class="won_price">(\ 56,900)</em></span>
                        </li>
                         */?>
        </ul>
    </div>
</div>
<!--FeaturedCategoris-->
<div class="featured_category">
    <h2><img src="http://115.68.20.84/mall6/title_FeaturedCategory.png" alt="Featured Category" /></h2>
    <dl class="category_zoneA">
        <a href="<?=$g4['shop_path']?>/list.php?ca_id=1110"><dt> 종합비타민</dt></a>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=101210">부모님 종합 비타민
            </a></dd>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=101410">여성 종합 비타민
            </a></dd>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=101510">남성 종합 비타민
            </a></dd>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=101310">임산부 종합 비타민
            </a></dd>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=101010">어린이 종합 비타민
            </a></dd>
    </dl>
    <dl class="category_zoneB">
        <a href="<?=$g4['shop_path']?>/list.php?ca_id=1112"><dt> 오메가-3</dt></a>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=111218">Nordic Naturals
            </a></dd>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=111210">오메가-3
            </a></dd>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=111211">오메가 3-6-9
            </a></dd>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=111213">식물성 오메가
            </a></dd>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=111217">기타 오메가
            </a></dd>
    </dl>
    <dl class="category_zoneC">
        <a href="<?=$g4['shop_path']?>/list.php?ca_id=1113"><dt>글루코사민 / 콘드로이친</dt></a>
        <dd><a href="<?=$g4['path'];?>/shop/search.php?it_maker=REXALL+SUNDOWN">오스테오 바이플렉스
            </a></dd>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=111310">글루코사민 복합제품
            </a></dd>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=111311">식이유황 (MSM)
            </a></dd>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=111312">히알루론산
            </a></dd>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=111317">기타 관절건강
            </a></dd>
    </dl>
    <dl class="category_zoneD">
        <a href="<?=$g4['shop_path']?>/list.php?ca_id=19"><dt>헬스보충제</dt></a>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=191110">단백질(프로틴)
            </a></dd>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=191011">지방연소(팻버너)
            </a></dd>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=191014">식사 대용
            </a></dd>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=191013">해독(디톡스)
            </a></dd>
        <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=191113">단백질 바/에너지 바
            </a></dd>
    </dl>
</div>
<!--추천상품
<div class="recommend_box">
    <span><span class="ico_discount">초특가</span><a href="<?=$g4['path'];?>/shop/item.php?it_id=1332425915"><img data-original="http://115.68.20.84/mall6/health_banner/banner_recommend_01.jpg" alt="추천1" /></a></span>
    <span><a href="<?=$g4['path'];?>/shop/item.php?it_id=1357627213"><img data-original="http://115.68.20.84/mall6/health_banner/banner_recommend_02.jpg" alt="추천2" /></a></span>
</div>-->
<!--SpecialArea-->
<div class="SpecialZone">
    <!--Weekley-->
    <div class="weekleyZone">
        <h2><img data-original="http://115.68.20.84/mall6/title_SpecialWeekly.png" alt="Special Weekley" /></h2>
        <p><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Enzymedica"><img data-original="http://115.68.20.84/mall6/health_banner/banner_specialWeek.jpg" alt="스페셜브랜드" /></a></p>
    </div>
    <!--brand-->
    <div class="brandZone">
        <h2><img data-original="http://115.68.20.84/mall6/title_SpecialBrand.png" alt="Special Brand" /></h2>
        <ul>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Nordic+Naturals" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand01.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Source+Naturals" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand02.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Nature%27s+way" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand03.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Now+foods" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand04.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Rainbow+Light" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand05.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Jarrow+Formulas" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand06.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=New+Chapter" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand07.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Natural+Factors" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand08.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Doctor%27s+Best" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand09.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Carlson+Labs" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand10.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Enzymatic+Therapy+" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand11.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Bluebonnet+Nutrition" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand12.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=NeoCell" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand13.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Nature%27s+Plus" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand14.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Barlean%27s" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand15.png"></a></li>
            <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Natrol" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand16.png"></a></li>
        </ul>
    </div>

</div>
