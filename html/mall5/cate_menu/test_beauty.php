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
							<span class='MainSpotImgTitle'><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Crabtree+%26+Evelyn">유명한 그녀의 가방 속크랩트리앤에블린</a></span>
							<a href="<?=$g4['path'];?>/shop/search.php?it_maker=Crabtree+%26+Evelyn"><img src="http://115.68.20.84/mall6/beauty_banner/main_spot01.jpg" alt="메인스팟" /></a>
						</div>
						<div>
							<span class='MainSpotImgTitle'><a href="<?=$g4['path'];?>/shop/search.php?it_maker=JASON">보습과진정효과 알로에베라</a></span>
							<a href="<?=$g4['path'];?>/shop/search.php?it_maker=JASON"><img src="http://115.68.20.84/mall6/beauty_banner/main_spot02.jpg" alt="메인스팟" /></a>
						</div>
						<div>
							<span class='MainSpotImgTitle'><a href="<?=$g4['path'];?>/shop/search.php?it_maker=AVALON+ORGANICS">두피청소 아발론오가닉</a></span>
							<a href="<?=$g4['path'];?>/shop/search.php?it_maker=AVALON+ORGANICS"><img src="http://115.68.20.84/mall6/beauty_banner/main_spot03.jpg" alt="메인스팟" /></a>
						</div>
						<div>
							<span class='MainSpotImgTitle'><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Naked+Earth">관리하는남자의 Bulldog</a></span>
							<a href="<?=$g4['path'];?>/shop/search.php?it_maker=Naked+Earth"><img src="http://115.68.20.84/mall6/beauty_banner/main_spot04.jpg" alt="메인스팟" /></a>
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
              .beauty .featured_category {padding-top:205px;}
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

            <!--FeaturedCategoris-->
            <div class="featured_category">
                <h2><img src="http://115.68.20.84/mall6/title_FeaturedCategory.png" alt="Featured Category" /></h2>
                <div class="Area_wrap">
                <dl class="category_zoneA">
                  <a href="<?=$g4['shop_path']?>/list.php?ca_id=40"><dt>스킨케어</dt></a>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4010">스킨/로션
                    </a></dd>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4011">에센스/세럼
                    </a></dd>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4012">크림/안티에이징
                    </a></dd>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4015">클렌징
                    </a></dd>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4013">선케어
                    </a></dd>
                </dl>
                <dl class="category_zoneB">
                  <a href="<?=$g4['shop_path']?>/list.php?ca_id=41"><dt>바디케어</dt></a>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4110">로션/크림/오일
                    </a></dd>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4111">바디워시
                    </a></dd>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4112">핸드/풋케어
                    </a></dd>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4113">청결/제모제
                    </a></dd>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4115">향수/미스트
                      </a></dd>
                </dl>
                <dl class="category_zoneC">
                  <a href="<?=$g4['shop_path']?>/list.php?ca_id=42"><dt>헤어케어</dt></a>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4210">샴푸/린스
                    </a></dd>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4211">에센스/세럼
                    </a>
                  </dd>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4212">왁스/젤/스프레이
                    </a></dd>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4213">염색제
                    </a></dd>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4214">세트
                    </a></dd>
                </dl>
                <dl class="category_zoneD">
                  <a href="<?=$g4['shop_path']?>/list.php?ca_id=44"><dt>남성용품</dt></a>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4410">스킨/로션
                    </a></dd>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4411">클렌징/팩
                    </a></dd>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4412">쉐이빙
                    </a></dd>
                    <dd><a href="<?=$g4['path'];?>/shop/list.php?ca_id=4413">세트
                </dl>
                </div>
            </div>
            <!--추천상품-->
            <div class="recommend_box">
                <span><span class="ico_discount">초특가</span><a href="<?=$g4['path'];?>/shop/item.php?it_id=1320764100"><img data-original="http://115.68.20.84/mall6/beauty_banner/banner_recommend21.jpg" alt="추천1" /></a></span>
                <span><a href="<?=$g4['path'];?>/shop/item.php?it_id=1323741347"><img data-original="http://115.68.20.84/mall6/beauty_banner/banner_recommend13.jpg" alt="추천2" /></a></span>
            </div>
            <!--베스트상품-->
            <div class="best_zone">
                <h2><img data-original="http://115.68.20.84/mall6/title_OpleBest.png" alt="Ople Best" /></h2>
                <p class="planning"><a href="<?=$g4['shop_path']?>/search.php?search_str=Real+Technique&station_search=y&x=0&y=0&sh_s_id%5B%5D=1&ca_id%5B%5D=43"><img data-original="http://115.68.20.84/mall6/beauty_banner/banner_best.jpg" alt="기획전" /></a></p>
                <div class="best_list">
                    <ul>
                        <li class="first"><a href="<?=$g4['shop_path']?>/item.php?it_id=1410945064"><img data-original="http://115.68.20.84/mall6/beauty_banner/best_product01.jpg" alt="베스트상품1" /></a></li>
                        <li><a href="<?=$g4['shop_path']?>/item.php?it_id=1329685384"><img data-original="http://115.68.20.84/mall6/beauty_banner/best_product02.jpg" alt="베스트상품2" /></a></li>
                        <li><a href="<?=$g4['shop_path']?>/item.php?it_id=1376682739"><img data-original="http://115.68.20.84/mall6/beauty_banner/best_product03.jpg" alt="베스트상품3" /></a></li>
                        <li class="first"><a href="<?=$g4['shop_path']?>/item.php?it_id=1330125920"><img data-original="http://115.68.20.84/mall6/beauty_banner/best_product04.jpg" alt="베스트상품4" /></a></li>
                        <li><a href="<?=$g4['shop_path']?>/item.php?it_id=1400277865"><img data-original="http://115.68.20.84/mall6/beauty_banner/best_product052.jpg" alt="베스트상품5" /></a></li>
                        <li><a href="<?=$g4['shop_path']?>/item.php?it_id=1221812566"><img data-original="http://115.68.20.84/mall6/beauty_banner/best_product06.jpg" alt="베스트상품6" /></a></li>
                    </ul>
                </div>
            </div>
            <!--SpecialArea-->
            <div class="SpecialZone">
                <!--Weekley-->
                <div class="weekleyZone">
                    <h2><img data-original="http://115.68.20.84/mall6/title_SpecialWeekly.png" alt="Special Weekley" /></h2>
                    <p><a href="<?=$g4['path'];?>/shop/search.php?search_str_all=%ED%80%B8%ED%97%AC%EB%A0%8C&search_str=%ED%80%B8%ED%97%AC%EB%A0%8C&sh_s_id%5B%5D=1&ca_id%5B%5D=40&ca_id%5B%5D=41"><img data-original="http://115.68.20.84/mall6/beauty_banner/banner_specialWeek_1106.jpg" alt="스페셜브랜드" /></a></p>
                </div>
                <!--brand-->
                <div class="brandZone">
                    <h2><img data-original="http://115.68.20.84/mall6/title_SpecialBrand.png" alt="Special Brand" /></h2>
                    <ul>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Jason" target="_blank"><img data-original="http://115.68.20.84/mall6/beauty_banner/brand01.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Nuxe" target="_blank"><img data-original="http://115.68.20.84/mall6/beauty_banner/brand02.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Crabtree+%26+Evelyn" target="_blank"><img data-original="http://115.68.20.84/mall6/beauty_banner/brand03.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Rosebud+Perfume" target="_blank"><img data-original="http://115.68.20.84/mall6/beauty_banner/brand04.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Thayers" target="_blank"><img data-original="http://115.68.20.84/mall6/beauty_banner/brand05.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Sibu" target="_blank"><img data-original="http://115.68.20.84/mall6/beauty_banner/brand06.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Giovanni" target="_blank"><img data-original="http://115.68.20.84/mall6/beauty_banner/brand07.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Mad+Hippie" target="_blank"><img data-original="http://115.68.20.84/mall6/beauty_banner/brand08.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=KISS+MY+FACE" target="_blank"><img data-original="http://115.68.20.84/mall6/beauty_banner/brand09.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Indigowild" target="_blank"><img data-original="http://115.68.20.84/mall6/beauty_banner/brand10.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Deep+Steep" target="_blank"><img data-original="http://115.68.20.84/mall6/beauty_banner/brand11.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=derma-e" target="_blank"><img data-original="http://115.68.20.84/mall6/beauty_banner/brand12.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Nubian+Heritage" target="_blank"><img data-original="http://115.68.20.84/mall6/beauty_banner/brand13.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Now+Foods" target="_blank"><img data-original="http://115.68.20.84/mall6/beauty_banner/brand14.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Desert+Essence" target="_blank"><img data-original="http://115.68.20.84/mall6/beauty_banner/brand15.png"></a></li>
                        <li><a href="<?=$g4['path'];?>/shop/search.php?it_maker=Avalon+Organics" target="_blank"><img data-original="http://115.68.20.84/mall6/beauty_banner/brand16.png"></a></li>
                    </ul>
                </div>

            </div>