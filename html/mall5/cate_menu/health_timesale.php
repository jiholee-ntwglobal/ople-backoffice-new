

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
            <!--Event_timeSale 14.1106-->
            <div class="timesale_event"><a href="<?=$g4['path'];?>/shop/event.php?ev_id=1415263690"><img src="http://115.68.20.84/event/timesale/main_title_timesale.jpg" alt="타임세일"></a></div>
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
            <!--추천상품-->
            <div class="recommend_box">
                <span><span class="ico_discount">초특가</span><a href="<?=$g4['path'];?>/shop/item.php?it_id=1332425915"><img data-original="http://115.68.20.84/mall6/health_banner/banner_recommend1.jpg" alt="추천1" /></a></span>
                <span><a href="<?=$g4['path'];?>/shop/item.php?it_id=1219209427"><img data-original="http://115.68.20.84/mall6/health_banner/banner_recommend2.jpg" alt="추천2" /></a></span>
            </div>
            <!--베스트상품-->
            <div class="best_zone">
                <h2><img data-original="http://115.68.20.84/mall6/title_OpleBest.png" alt="Ople Best" /></h2>
                <!-- 2014.1105 안보이기 / <p class="planning"><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Doctor%27s+Best&sh_s_id%5B%5D=3&ca_id%5B%5D=10&ca_id%5B%5D=11&ca_id%5B%5D=12&ca_id%5B%5D=13&ca_id%5B%5D=16&ca_id%5B%5D=17&ca_id%5B%5D=19"><img data-original="http://115.68.20.84/mall6/health_banner/banner_best.jpg" alt="기획전 닥터스베스트" /></a></p> /-->
                <div class="best_list_12">
                    <ul>
                        <li class="first"><a href="<?=$g4['shop_path']?>/item.php?it_id=1332425915"><img data-original="http://115.68.20.84/mall6/health_banner/best_product010.jpg" alt="베스트상품1" /></a></li>
                        <li><a href="<?=$g4['shop_path']?>/item.php?it_id=1314862063"><img data-original="http://115.68.20.84/mall6/health_banner/best_product020.jpg" alt="베스트상품2" /></a></li>
                        <li><a href="<?=$g4['shop_path']?>/item.php?it_id=1357617211"><img data-original="http://115.68.20.84/mall6/health_banner/best_product030.jpg" alt="베스트상품3" /></a></li>
                        <li><a href="<?=$g4['shop_path']?>/item.php?it_id=1313786511"><img data-original="http://115.68.20.84/mall6/health_banner/best_product040.jpg" alt="베스트상품4" /></a></li>
                        <li class="first"><a href="<?=$g4['shop_path']?>/item.php?it_id=1325635740"><img data-original="http://115.68.20.84/mall6/health_banner/best_product050.jpg" alt="베스트상품5" /></a></li>
                        <li><a href="<?=$g4['shop_path']?>/item.php?it_id=1200276244"><img data-original="http://115.68.20.84/mall6/health_banner/best_product060.jpg" alt="베스트상품6" /></a></li>
                        <li><a href="<?=$g4['shop_path']?>/item.php?it_id=1395265853"><img data-original="http://115.68.20.84/mall6/health_banner/best_product070.jpg" alt="베스트상품7" /></a></li>
                        <li><a href="<?=$g4['shop_path']?>/item.php?it_id=1174531678"><img data-original="http://115.68.20.84/mall6/health_banner/best_product080.jpg" alt="베스트상품8" /></a></li>
                    </ul>
                </div>
            </div>
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
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Carlson+Laboratories" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand10.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Enzymatic+Therapy+" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand11.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Bluebonnet+Nutrition" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand12.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=NeoCell" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand13.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Nature%27s+Plus" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand14.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Barlean%27s" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand15.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Natrol" target="_blank"><img data-original="http://115.68.20.84/mall6/health_banner/brand16.png"></a></li>
                    </ul>
                </div>

            </div>
