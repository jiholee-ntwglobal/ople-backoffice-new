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
							<span class='MainSpotImgTitle'><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Tea+forte">우아한티타임 티포르테</a></span>
							<a href="<?=$g4['path'];?>/shop/search.php?it_maker=Tea+forte"><img src="http://115.68.20.84/mall6/food_banner/main_spot01.jpg" alt="메인스팟" /></a>
						</div>
						<div>
							<span class='MainSpotImgTitle'><a href="<?=$g4['path'];?>/shop/search.php?search_str=퀴노아&station_search=y&x=0&y=0">신이내려준선물 퀴노아</a></span>
							<a href="<?=$g4['path'];?>/shop/search.php?search_str=퀴노아&station_search=y&x=0&y=0"><img src="http://115.68.20.84/mall6/food_banner/main_spot02.jpg" alt="메인스팟" /></a>
						</div>
						<div>
							<span class='MainSpotImgTitle'><a href="#">해독쥬스 직접 만들자</a></span>
							<a href="#"><img src="http://115.68.20.84/mall6/food_banner/main_spot03.jpg" alt="메인스팟" /></a>
						</div>
						<div>
							<span class='MainSpotImgTitle'><a href="#">아침거르지말자! 아침식사대용음식</a></span>
							<a href="#"><img src="http://115.68.20.84/mall6/food_banner/main_spot04.jpg" alt="메인스팟" /></a>
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
              .food .newBrand_info {padding-top:205px;}
            </style>
            <div class="blackFriday_event">
              <a href="<?=$g4['path'];?>/shop/event_cybermonday.php"><img src="http://115.68.20.84/event/blackFriday/main_title_blackFriday_day.jpg" alt="블랙프라이데이_사이버먼데이">
              </a>
            </div>
            <?php }?>



            <!-- NEW_brand-->
            <div class="newBrand_info">
              <span class="first"><a href='<?=$g4['path'];?>/shop/search.php?it_maker=GODIVA'><img src="http://115.68.20.84/mall6/food_banner/category_brand1.jpg" alt='brand1'/></a></span>
              <span><a href='<?=$g4['path'];?>/shop/search.php?it_maker=Quest+Nutrition'><img src="http://115.68.20.84/mall6/food_banner/category_brand2.jpg" alt='brand2'/></a></span>
              <span><a href='<?=$g4['path'];?>/shop/search.php?it_maker=YUMMY+EARTH&sh_s_id%5B%5D=5&ca_id%5B%5D=33&sh_s_id%5B%5D=2&ca_id%5B%5D=53&ca_id%5B%5D=55'><img src="http://115.68.20.84/mall6/food_banner/category_brand3_YummyEarth.jpg" alt='brand3'/></a></span>
            </div>

			<!--HotDealZpne
			<div class="hotdealzoneArea">
				<ul>
					<?php
					//	include $g4['full_path']."/cate_menu/hotdeal_zone.php";
					?>

				</ul>
			</div>-->

            <!--FeaturedCategoris
            <div class="featured_category">
                <h2><img src="http://115.68.20.84/mall6/title_FeaturedCategory.png" alt="Featured Category"/></h2>
                <dl class="category_zoneA">
                    <dt>베이비푸드</dt>
                    <dd><a href="">유기농 홀푸트 뉴챕터</a></dd>
                    <dd><a href="">어린이 종합비타민</a></dd>
                    <dd><a href="">철분미첨가 종합비타민</a></dd>
                    <dd><a href="">구미/과립형</a></dd>
                    <dd><a href="">모든 종합비타민</a></dd>
                </dl>
                <dl class="category_zoneB">
                    <dt>간식류</dt>
                    <dd><a href="">유기농 홀푸트 뉴챕터</a></dd>
                    <dd><a href="">어린이 종합비타민</a></dd>
                    <dd><a href="">철분미첨가 종합비타민</a></dd>
                    <dd><a href="">구미/과립형</a></dd>
                    <dd><a href="">모든 종합비타민</a></dd>
                </dl>
                <dl class="category_zoneC">
                    <dt>항산화/면역</dt>
                    <dd><a href="">유기농 홀푸트 뉴챕터</a></dd>
                    <dd><a href="">어린이 종합비타민</a></dd>
                    <dd><a href="">철분미첨가 종합비타민</a></dd>
                    <dd><a href="">구미/과립형</a></dd>
                    <dd><a href="">모든 종합비타민</a></dd>
                </dl>
                <dl class="category_zoneD">
                    <dt>미네랄/무기질</dt>
                    <dd><a href="">유기농 홀푸트 뉴챕터</a></dd>
                    <dd><a href="">어린이 종합비타민</a></dd>
                    <dd><a href="">철분미첨가 종합비타민</a></dd>
                    <dd><a href="">구미/과립형</a></dd>
                    <dd><a href="">모든 종합비타민</a></dd>
                </dl>
            </div>-->
            <!--베스트상품-->
            <div class="best_zone">
                <h2><img data-original="http://115.68.20.84/mall6/title_OpleBest.png" alt="Ople Best" /></h2>
                <p class="planning"><a href="<?=$g4['path'];?>/shop/search.php?it_maker=KETTLE&sh_s_id%5B%5D=2&ca_id%5B%5D=53"><img data-original="http://115.68.20.84/mall6/food_banner/banner_best_kettlechips.jpg" alt="기획전" /></a></p>
                <div class="best_list">
                    <ul>
                        <li class="first"><a href="<?=$g4['shop_path']?>/item.php?it_id=1378920716"><img data-original="http://115.68.20.84/mall6/food_banner/best_product011.jpg" alt="베스트상품1" /></a></li>
                        <li><a href="<?=$g4['shop_path']?>/item.php?it_id=1402037734"><img data-original="http://115.68.20.84/mall6/food_banner/best_product021.jpg" alt="베스트상품2" /></a></li>
                        <li><a href="<?=$g4['shop_path']?>/item.php?it_id=1345580060"><img data-original="http://115.68.20.84/mall6/food_banner/best_product032.jpg" alt="베스트상품3" /></a></li>
                        <li class="first"><a href="<?=$g4['shop_path']?>/item.php?it_id=1411160321"><img data-original="http://115.68.20.84/mall6/food_banner/best_product042.jpg" alt="베스트상품4" /></a></li>
                        <li><a href="<?=$g4['shop_path']?>/item.php?it_id=1361925309"><img data-original="http://115.68.20.84/mall6/food_banner/best_product052.jpg" alt="베스트상품5" /></a></li>
                        <li><a href="<?=$g4['shop_path']?>/item.php?it_id=1370118195"><img data-original="http://115.68.20.84/mall6/food_banner/best_product061.jpg" alt="베스트상품6" /></a></li>
                    </ul>
                </div>
            </div>
            <!--추천상품-->
            <div class="recommend_box2">
                <span><span class="ico_plane">모음전</span><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Annie%27s"><img data-original="http://115.68.20.84/mall6/food_banner/banner_recommend11.jpg" alt="추천1" /></a></span>
                <span><a href="<?=$g4['path'];?>/shop/list.php?ca_id=511211"><img data-original="http://115.68.20.84/mall6/food_banner/banner_recommend2.jpg" alt="추천2" /></a></span>
            </div>
            <!--SpecialArea-->
            <div class="SpecialZone">
                <!--Weekley-->
                <div class="weekleyZone">
                    <h2><img data-original="http://115.68.20.84/mall6/title_SpecialWeekly.png" alt="Special Weekley" /></h2>
                    <p><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Bob%27s+Red+Mill"><img data-original="http://115.68.20.84/mall6/food_banner/banner_specialWeek.jpg" alt="스페셜브랜드" /></a></p>
                </div>
                <!--brand-->
                <div class="brandZone">
                    <h2><img data-original="http://115.68.20.84/mall6/title_SpecialBrand.png" alt="Special Brand" /></h2>
                    <ul>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Y.S.+Organic+Bee+Farms" target="_blank"><img data-original="http://115.68.20.84/mall6/food_banner/brand01.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Arrowhead+Mills" target="_blank"><img data-original="http://115.68.20.84/mall6/food_banner/brand02.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Bob%27s+Red+Mill" target="_blank"><img data-original="http://115.68.20.84/mall6/food_banner/brand03.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=St.+Dalfour" target="_blank"><img data-original="http://115.68.20.84/mall6/food_banner/brand04.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=GODIVA" target="_blank"><img data-original="http://115.68.20.84/mall6/food_banner/brand05.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Harneys+%26+Sons" target="_blank"><img data-original="http://115.68.20.84/mall6/food_banner/brand06.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Quest+Nutrition" target="_blank"><img data-original="http://115.68.20.84/mall6/food_banner/brand07.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Simply+Organic" target="_blank"><img data-original="http://115.68.20.84/mall6/food_banner/brand08.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=YUMMY+EARTH" target="_blank"><img data-original="http://115.68.20.84/mall6/food_banner/brand09.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Eden+Foods" target="_blank"><img data-original="http://115.68.20.84/mall6/food_banner/brand10.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Nutiva" target="_blank"><img data-original="http://115.68.20.84/mall6/food_banner/brand11.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Ginger+People" target="_blank"><img data-original="http://115.68.20.84/mall6/food_banner/brand12.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=La+Tourangelle" target="_blank"><img data-original="http://115.68.20.84/mall6/food_banner/brand13.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Nurture+Inc" target="_blank"><img data-original="http://115.68.20.84/mall6/food_banner/brand14.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Woodstock+Foods" target="_blank"><img data-original="http://115.68.20.84/mall6/food_banner/brand15.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=GHIRARDELLI" target="_blank"><img data-original="http://115.68.20.84/mall6/food_banner/brand16.png"></a></li>
                    </ul>
                </div>

            </div>