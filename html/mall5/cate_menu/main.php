<?php

/* 캐싱 처리로 인한 주석 처리 2015-04-08 홍민기
# 신상품 1개 로드 #
$new_item = sql_fetch("
	select
		*
	from
		yc4_item_new a
	where
		use_fg = 'Y'
	order by rand()
	limit 1
");
if($new_item){
    $item_info = '';
    $link = "";
    switch($new_item['type']){
        case 'I' :
            $link = $g4['shop_path']."/item.php?it_id=".$new_item['type_value'];
            $new_item_data = sql_fetch("select it_maker,it_amount,it_maker_kor,it_amount_usd from ".$g4['yc4_item_table']." where it_id ='".$new_item['type_value']."'");
            if(!$new_item_data['it_amount_usd']){
                $new_item_data['it_amount_usd'] = usd_convert($new_item_data['it_amount']);
            }
            $new_item['it_maker'] = $new_item_data['it_maker'];
            $new_item['it_maker_kor'] = $new_item_data['it_maker_kor'];
            $item_info = "
				<div class='item_con'>
					<span class='title'>
						<span class='ko'>".$new_item['it_name_kor']."</span>
						<span class='e'>".$new_item['it_name_eng']."</span>
					</span>
					<span class='price'>￦ ".number_format($new_item_data['it_amount'])." ($ ".number_format($new_item_data['it_amount_usd'],2).")</span>
				</div>
			";
            break;
        case 'B' :
            $link = $g4['shop_path']."/search.php?it_maker=".urlencode($new_item['type_value']);
            $new_item_data = sql_fetch("select it_maker,it_maker_kor from ".$g4['yc4_item_table']." where it_maker = '".$new_item['type_value']."' limit 1");
            $new_item['it_maker'] = $new_item_data['it_maker'];
            $new_item['it_maker_kor'] = $new_item_data['it_maker_kor'];
            break;
    }
    $new_item_html = "
		<li class='new_list'>
			<a href='".$link."'>
				<span class='brand'>[".$new_item['it_maker']."] ".$new_item['it_maker_kor']."</span>
				<span class='list_title'>
					<span class='b_title'>".nl2br($new_item['title'])."</span>
					<span class='s_title'>".nl2br($new_item['title_desc'])."</span>
				</span>
				".$item_info."
				<span class='img'><img src='".$new_item['img_url']."'/></span>
			</a>
		</li>
	";
}
*/


$new_item_html = '';
shuffle($main_json->main_new_item);

foreach($main_json->main_new_item as $val){
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
/*
$item_info = '';
$link = "";
switch($main_new_item->type){
    case 'I' :
        $link = $g4['shop_path']."/item.php?it_id=".$main_new_item->type_value;
        if(!$main_new_item->item_data->it_amount_usd){
            $main_new_item->item_data->it_amount_usd = usd_convert($main_new_item->item_data->it_amount);
        }
        $main_new_item->it_maker = $main_new_item->item_data->it_maker;
        $main_new_item->it_maker_kor = $main_new_item->item_data->it_maker_kor;
        $item_info = "
				<div class='item_con'>
					<span class='title'>
						<span class='ko'>".$main_new_item->it_maker_kor."</span>
						<span class='e'>".$main_new_item->it_maker."</span>
					</span>
					<span class='price'>￦ ".number_format($main_new_item->item_data->it_amount)." ($ ".number_format($main_new_item->item_data->it_amount_usd,2).")</span>
				</div>
			";
        break;
    case 'B' :
        $link = $g4['shop_path']."/search.php?it_maker=".urlencode($main_new_item->type_value);
        $main_new_item->it_maker = $main_new_item->item_data->it_maker;
        $main_new_item->it_maker_kor = $main_new_item->item_data->it_maker_kor;
        break;
}
$new_item_html = "
    <li class='new_list'>
        <a href='".$link."'>
            <span class='brand'>[".$main_new_item->it_maker."] ".$main_new_item->it_maker_kor."</span>
            <span class='list_title'>
                <span class='b_title'>".nl2br($main_new_item->title)."</span>
                <span class='s_title'>".nl2br($main_new_item->title_desc)."</span>
            </span>
            ".$item_info."
            <span class='img'><img src='".$main_new_item->img_url."'/></span>
        </a>
    </li>
";
*/
?>
<style type="text/css">
    .planning_banner {overflow:hidden;width:715px;}
    .planning_banner_mask{
        position:absolute;
        height:420px;
        top:0px;
        left:0px;
        z-index:1;}
    .planning_banner_contents{float:left;}
    .User_Review{
        overflow:hidden;
        width:819px;
        height:253px;
    }
    .User_Review > ul {
        position:absolute;
        left:0px;
        top:0px;
        z-index:3;
    }
    .User_Review > ul > li {height:253px;float:left;}
    .User_Review > ul > li > .titles > strong {cursor:pointer;}
    .move_btn_wrap {}
    .move_btn_wrap > div{
        background-image:url(http://115.68.20.84/mall6/main/slide_point.png);
        width:25px;
        height:25px;
        position: absolute;
        z-index: 3;
        cursor: pointer;
        top:185px;
    }
    .move_btn_prev{left:5px;background-position: 0 0;}
    .move_btn_next{right:5px;background-position: 0 -25px;}
    .move_btn_prev:hover{background-position: -25px 0;}
    .move_btn_next:hover{background-position: -25px -25px;}

    .Main_bannerArea {position:relative;}
</style>

<!--mainSpot_banner-->
<div class="mainSpotArea">
    <!--EventArea-->
    <div class="Main_bannerArea">
        <div class="planning_banner">
            <?
            # 롤링배너 캐시파일 로드 2014-09-29 홍민기
            # 캐시 생성은 banner_config_new.php?mode=banner_update 로 처리 #
            include $g4['full_path']."/cache/rolling_banner_".$_SESSION['s_id'].'.htm';
            ?>
            <?/*
			<div class="planning_banner_mask">
				<div class='planning_banner_contents'>
					<a href="">
						<img src="http://115.68.20.84/mall6/main/main_spot_avalon.jpg" alt="메인기획전">
					</a>
				</div>
				<div class='planning_banner_contents'>
					<a href="">
						<img src="http://115.68.20.84/mall6/main/main_spot_avalon.jpg" alt="메인기획전">
					</a>
				</div>
				<div class='planning_banner_contents'>
					<a href="">
						<img src="http://115.68.20.84/mall6/main/main_spot_avalon.jpg" alt="메인기획전">
					</a>
				</div>
			</div>
			<ul class="listing_button">
				<li class="Select" banner_id='1' onclick="planning_banner_change(this.getAttribute('banner_id'));"><a>1</a></li>
				<li banner_id='2' onclick="planning_banner_change(this.getAttribute('banner_id'));"><a>2</a></li>
				<li banner_id='3' onclick="planning_banner_change(this.getAttribute('banner_id'));"><a>3</a></li>
			</ul>
		    */?>
            <?php
            ?>
            <div class="move_btn_wrap">
                <div class="move_btn_prev" onclick="planning_banner_prev();"></div>
                <div class="move_btn_next" onclick="planning_banner_next();"></div>
            </div>
        </div>
        <?php if(date('Ymd') >= '20150216' && date('Ymd') <= '20150430'){ ?>
      <p class="event_banner"><img src="http://115.68.20.84/mall6/main/main_spot_eventR_mastercard.jpg" alt="메인이벤트" border="0" usemap="#event">
         <map name="event" id="event">
      		<area shape="rect" coords="-3,280,382,419" onFocus="blur()" target="_self" href="<?php echo $g4['shop_path'];?>/master_shinhancard_event.php" />
        	<area shape="rect" coords="0,-1,385,142" onFocus="blur()" target="_self" href="<?php echo $g4['shop_path'];?>/master_shinhancard_ihappy.php" />
        	<area shape="rect" coords="1,142,382,280" onFocus="blur()" target="_blank" href="https://customer.kbcard.com/CXCEVZZC0012.cms?mainCC=a&이벤트일련번호=260694" />
        </map>
       </p>
	<p style="position: absolute;right: -192px;"><a href="<?php echo $g4['shop_path'];?>/master_hanacard_event.php"><img src="http://115.68.20.84/mall6/main/man_Spot_bannerR_hanacard.png" alt="카드이벤트"></a></p>
        <?php }else{?>
            <p class="event_banner"><img src="http://115.68.20.84/mall6/main/main_spot_eventR_mastercard_0430.jpg" alt="메인이벤트" border="0" usemap="#event2">
              <map name="event2" id="event2">
                <area shape="rect" coords="3,2,382,139" onFocus="blur()" target="_self"  href="<?php echo $g4['shop_path'];?>/master_hanacard_event.php" />
                <area shape="rect" coords="2,140,382,279" onFocus="blur()" target="_self"  href="<?php echo $g4['shop_path'];?>/master_shinhancard_ihappy.php" />
                <area shape="rect" coords="3,280,382,421" onFocus="blur()" target="_self"  href="https://customer.kbcard.com/CXCEVZZC0012.cms?mainCC=a&이벤트일련번호=260694" />
              </map>
      		</p>
        <?php }?>
    </div>
    <!--EventSpotArea-->
    <?php if(date('Ymd') >= '20150401' && date('Ymd') <= '20150419'){?>
        <style type="text/css">
            .hotdealzoneArea {margin-top:150px !important;}
        </style>
        <div class="eventSpotArea" style="background-color:#7ac5cc;">
            <p><img src="http://115.68.20.84/mall6/main/event_full_banner_freeshipping.jpg" alt="무료배송이벤트" /></p>
        </div>
    <?php }?>
    <!--HotDealZpne-->
    <div class="hotdealzoneArea">
        <ul>
            <?php
            include $g4['full_path']."/cate_menu/hotdeal_zone.php";
            ?>

        </ul>
    </div>
</div>

<?php if($new_item_html){?>
    <style>
        .main_list_wrap{
            overflow:hidden;
            width:100%;
            height:291px;
            position: relative;
        }
        .main_list_wrap>.icon_new_hot{
            position: absolute;
        }
        .main_list{
            position:absolute;
        }
        .new_list{
            float:left;
        }

        .main_list_btn {
            position: absolute;
            top: 135px;
            width: 26px;
            height: 40px;
            text-indent: -5000px;
            cursor: pointer;

        }
        .main_list_btn.main_list_btn_prev{
            background: url(http://115.68.20.84/main/btn_next_prev_orange.png) no-repeat 0 0;
        }
        .main_list_btn.main_list_btn_next{
            right:0;
            background: url(http://115.68.20.84/main/btn_next_prev_orange.png) no-repeat -26px 0;
        }
    </style>
    <div class="main_list_wrap">
        <div class="icon_new_hot"><span><img src="http://115.68.20.84/mall6/ico_new_hot.png" alt="신규상품"/></span></div>
        <ul class="main_list">
            <?php echo $new_item_html;?>
        </ul>
        <span class="main_list_btn main_list_btn_prev" onclick="main_new_item_rolling('prev');"> < </span>
        <span class="main_list_btn main_list_btn_next" onclick="main_new_item_rolling('next');"> > </span>
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

<!--SpotBannerArea-->
<div class="SpotBanner">
    <p><a href="<?php echo $g4['shop_path'];?>/event.php?ev_id=1424920190"><img data-original="http://115.68.20.84/mall6/main/banner/banner_clearanceSale.jpg" alt="클리어란스"></a></p>
</div>

<?php

$st_best_item_arr = array();
foreach($main_json->main_station_item as $s => $val){

    $rand_key = rand(0,7);
    if(count($val)>0){
        $i=0;
        foreach($val as $key => $val2){
            if($i == $rand_key || $i == 7){
                $val2->it_id = $key;
                $st_best_item_arr[$s] = $val2;
                break;
            }
            $i++;
        }
    }
}
$tmp_arr = array('3'=>'건강', '4'=>'생활', '5'=>'출산육아', '1'=>'뷰티', '2'=>'식품');
$s_first = false;
$st_best_item_html = "";
foreach($tmp_arr as  $s => $val){
    if(in_array($s,array(2,3))){
        $s_first = true;
    }else{
        $s_first = false;
    }
    $st_it_name_arr = explode(' ',$st_best_item_arr[$s]->it_name);
    $st_it_maker = $st_it_name_arr[0];
    unset($st_it_name_arr[0]);
    $st_it_name = implode(' ',$st_it_name_arr);
    $st_best_item_html .= "
<li ".($s_first ? "class='first'":"").">
    <strong class='title_point blue'>".$val."</strong>
    <span class='titles'>
        <a href='".$g4['shop_path']."/item.php?it_id=".$st_best_item_arr[$s]->it_id."'>
            <span class='titles_brand'>".$st_it_maker."</span>
            <span class='titles_item'>".$st_it_name."</span>
        </a>
    </span>
    <span class='prices'>￦ ".number_format($st_best_item_arr[$s]->it_amount)."<em>($ ".usd_convert($st_best_item_arr[$s]->it_amount).")</em></span>
    <span class='titles_images'><a href='".$g4['shop_path']."/item.php?it_id=".$st_best_item_arr[$s]->it_id."'>
        ".get_it_image($st_best_item_arr[$s]->it_id.'_s',150,150,null,null,null,null,true)."

    </a></span>
</li>

        ";

}

?>
<!--ProductArea-->
<div class="recommend_productArea">
    <ul>
        <?php echo $st_best_item_html;?>

    </ul>
</div>


<!--ProductArea
<div class="recommend_productArea">
    <ul>
        <li class="first">
            <strong class="title_point blue">HOT</strong>
			<span class="titles">
				<a href="<?php echo $g4['shop_path'];?>/item.php?it_id=<?php echo $main['item']['H']['it_id'];?>">
                    <span class="titles_brand"><?php echo $main['item']['H']['it_maker_kor'];?></span>
                    <span class="titles_item"><?php echo $main['item']['H']['it_name_kor'];?></span>
                </a>
			</span>
            <span class="prices">￦ <?php echo number_format($main['item']['H']['it_amount']);?><em>($ <?php echo $main['item']['H']['it_amount_usd'];?>)</em></span>
            <span class="titles_images"><a href="<?php echo $g4['shop_path'];?>/item.php?it_id=<?php echo $main['item']['H']['it_id'];?>"><img data-original="<?php echo $main['item']['H']['img_link'];?>" alt="Product1"></a></span>
        </li>
        <li>
            <strong class="title_point blue">NEW</strong>
			<span class="titles">
            	<a href="<?php echo $g4['shop_path'];?>/item.php?it_id=<?php echo $main['item']['N']['it_id'];?>">
                    <span class="titles_brand"><?php echo $main['item']['N']['it_maker_kor'];?></span>
                    <span class="titles_item"><?php echo $main['item']['N']['it_name_kor'];?></span>
                </a>
			</span>
            <span class="prices">￦ <?php echo number_format($main['item']['N']['it_amount']);?><em>($ <?php echo $main['item']['N']['it_amount_usd'];?>)</em></span>
            <span class="titles_images"><a href="<?php echo $g4['shop_path'];?>/item.php?it_id=<?php echo $main['item']['N']['it_id'];?>"><img data-original="<?php echo $main['item']['N']['img_link'];?>" alt="Product2"></a></span>
        </li>
        <li>
            <strong class="title_point red">BEST</strong>
			<span class="titles">
				<a href="<?php echo $g4['shop_path'];?>/item.php?it_id=<?php echo $main['item']['B']['it_id'];?>">
                    <span class="titles_brand"><?php echo $main['item']['B']['it_maker_kor'];?></span>
                    <span class="titles_item"><?php echo $main['item']['B']['it_name_kor'];?></span>
                </a>
			</span>
            <span class="prices">￦ <?php echo number_format($main['item']['B']['it_amount']);?><em>($ <?php echo $main['item']['B']['it_amount_usd'];?>)</em></span>
            <span class="titles_images"><a href="<?php echo $g4['shop_path'];?>/item.php?it_id=<?php echo $main['item']['B']['it_id'];?>"><img data-original="<?php echo $main['item']['B']['img_link'];?>" alt="Product3"></a></span>
        </li>
        <li>
            <strong class="title_point blue">만원의행복 No.1</strong>
			<span class="titles">
				<a href="<?php echo $g4['shop_path'];?>/item.php?it_id=<?php echo $main['item']['M']['it_id'];?>">
                    <span class="titles_brand"><?php echo $main['item']['M']['it_maker_kor'];?></span>
                    <span class="titles_item"><?php echo $main['item']['M']['it_name_kor'];?></span>
                </a>
			</span>
            <span class="prices">￦ <?php echo number_format($main['item']['M']['it_amount']);?><em>($ <?php echo $main['item']['M']['it_amount_usd'];?>)</em></span>
            <span class="titles_images"><a href="<?php echo $g4['shop_path'];?>/item.php?it_id=<?php echo $main['item']['M']['it_id'];?>"><img data-original="<?php echo $main['item']['M']['img_link'];?>" alt="Product4"></a></span>
        </li>
    </ul>
</div>-->

<!--PlanningArea-->
<div class="planningArea">
    <!--p class="planning_eventA"><a href="<?php echo $g4['shop_path'];?>/event.php?ev_id=1424920190"><img data-original="http://115.68.20.84/mall6/main/planning_clearance.png" alt="클리어란스"></a></p-->
    <!--후기영역-->
    <div class="User_Review">
        <p class="titlesA"><strong class="title_point blue">BEST REVIEW</strong></p>
        <p class="prev_button"><a href="#" onclick="review_banner_prev(); return false;">prev</a></p>
        <ul>
            <?php
            if(count($main_json->ps)>0) {

                shuffle($main_json->ps);
                foreach($main_json->ps as $key => $val){

                    list($is_it_maker,$is_it_name) = explode('||',$val->it_name);
                    echo "
						<li>
							<a href='".$g4['path']."/sjsjin/hoogi_list.php#is_id_".$val->is_id."'><span class='review_cont'>".conv_subject($val->is_content,'500','…')."</span></a>
							<span class='titles'>
								<a href='".$g4['path']."/sjsjin/hoogi_list.php#is_id_".$val->is_id."'>
								<span class='titles_brand'>".$is_it_maker."</span>
								<span class='titles_item'>".$is_it_name."</span>
								</a>
								<strong onclick=\"location.href='".$g4['path']."/sjsjin/hoogi_list.php#is_id_".$val->is_id."'\">More</strong>
							</span>
							<span class='titles_images'><a href='".$g4['path']."/sjsjin/hoogi_list.php#is_id_".$val->is_id."'><img src='".$val->img_link."' alt='review'></a></span>
						</li>";


                }


            }elseif(is_array($main['ps'])){
                if(!isset($main)) {
                    include_once $g4['full_path'] . '/cache/main_cache.php';
                }
                # 롤링 순서 랜덤으로 변경 #
                shuffle($main['ps']);

                foreach($main['ps'] as $key => $val){

                    list($is_it_maker,$is_it_name) = explode('||',$val['it_name']);
                    echo "
						<li>
							<a href='".$g4['path']."/sjsjin/hoogi_list.php#is_id_".$val['is_id']."'><span class='review_cont'>".conv_subject($val['is_content'],'500','…')."</span></a>
							<span class='titles'>
								<a href='".$g4['path']."/sjsjin/hoogi_list.php#is_id_".$val['is_id']."'>
								<span class='titles_brand'>".$is_it_maker."</span>
								<span class='titles_item'>".$is_it_name."</span>
								</a>
								<strong onclick=\"location.href='".$g4['path']."/sjsjin/hoogi_list.php#is_id_".$val['is_id']."'\">More</strong>
							</span>
							<span class='titles_images'><a href='".$g4['path']."/sjsjin/hoogi_list.php#is_id_".$val['is_id']."'><img src='".$val['img_link']."' alt='review'></a></span>
						</li>";


                }
            }
            /*
            ?>
            <li>
                <span class="review_cont">누비안헤리티지 블랙솝은 비누상자케이스를 오픈하면 바로 비주가 나온ㄴ 타입이더라구요. 향은 생각보다 진하더라고요, 머리가 아플정도라고 하는분도 있던데 저는 그정돈 아닌것 같아요...</span>
                <span class="titles">
                    <span class="titles_brand">[Nubian Heritage] 누비안 헤리티지</span>
                    <span class="titles_item">아프리칸 블랙 비누, 5 oz</span>
                    <strong>More</strong>
                </span>
                <span class="titles_images"><a href=""><img src="http://115.68.20.84/mall6/main/review_product_1.jpg" alt="review"></a></span>
            </li>
            */?>
        </ul>
        <p class="next_button"><a href="#" onclick="review_banner_next(); return false;">next</a></p>
    </div>
    <!--p class="planning_eventB"><a href="<?php echo $g4['shop_path'];?>/list.php?ca_id=131113"><img src="http://115.68.20.84/mall6/main/planning_oplehealth_info.png" alt="event"></a></p-->
</div>

<!---BrandArea--->
<div class="BrandArea">
    <!--ul class="brand_commend">
    	<li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Nordic+Naturals"><img data-original="http://115.68.20.84/mall6/main/brand_nordic.png" alt="오플브랜드1"/></a></li>
        <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Nellie%27s&sh_s_id%5B%5D=1&ca_id%5B%5D=40&ca_id%5B%5D=41&sh_s_id%5B%5D=4&ca_id%5B%5D=21&ca_id%5B%5D=22&ca_id%5B%5D=24"><img data-original="http://115.68.20.84/mall6/main/brand_nellies.png" alt="오플브랜드2"/></a></li>
        <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Noodle+%26+Boo&sh_s_id%5B%5D=5&ca_id%5B%5D=31&ca_id%5B%5D=30&ca_id%5B%5D=32&sh_s_id%5B%5D=1&ca_id%5B%5D=40"><img data-original="http://115.68.20.84/mall6/main/brand_noodlenboo.png" alt="오플브랜드3"/></a></li>
        <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Crabtree+%26+Evelyn&sh_s_id%5B%5D=1&ca_id%5B%5D=40&ca_id%5B%5D=41&ca_id%5B%5D=42&ca_id%5B%5D=44&sh_s_id%5B%5D=4&ca_id%5B%5D=22&sh_s_id%5B%5D=5&ca_id%5B%5D=32"><img data-original="http://115.68.20.84/mall6/main/brand_crabtree.png" alt="오플브랜드4"/></a></li>
    </ul-->
    <div class="OpleBrand_list">
        <h3>OpleBrand</h3>
        <p class="more_link"><a href="<?php echo $g4['shop_path'];?>/brand_list.php">MORE</a></p>
        <ul>
            <li style="width:130px;text-align:right;"><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Now+foods"><img data-original="http://115.68.20.84/mall6/main/small_now.jpg" alt="Now" /></a></li>
            <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Source+Naturals&sh_s_id%5B%5D=3&ca_id%5B%5D=10&ca_id%5B%5D=11&ca_id%5B%5D=12&ca_id%5B%5D=13&ca_id%5B%5D=14&ca_id%5B%5D=15&ca_id%5B%5D=16&ca_id%5B%5D=17&ca_id%5B%5D=18&ca_id%5B%5D=19&sh_s_id%5B%5D=1&ca_id%5B%5D=40&sh_s_id%5B%5D=5&ca_id%5B%5D=30&ca_id%5B%5D=33&sh_s_id%5B%5D=2&ca_id%5B%5D=51&sh_s_id%5B%5D=4&ca_id%5B%5D=23"><img data-original="http://115.68.20.84/mall6/main/small_sourceNatural.jpg" alt="sourceNaturals" /></a></li>
            <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Method&sh_s_id%5B%5D=4&ca_id%5B%5D=20&ca_id%5B%5D=21&ca_id%5B%5D=22&sh_s_id%5B%5D=5&ca_id%5B%5D=31&ca_id%5B%5D=30&ca_id%5B%5D=33&sh_s_id%5B%5D=1&ca_id%5B%5D=41"><img data-original="http://115.68.20.84/mall6/main/small_method.jpg" alt="Method" /></a></li>
            <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=WoodWick&sh_s_id%5B%5D=4&ca_id%5B%5D=20&ca_id%5B%5D=22&ca_id%5B%5D=23"><img data-original="http://115.68.20.84/mall6/main/small_woodwick.jpg" alt="Woodwick" /></a></li>
            <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Philips+AVENT&sh_s_id%5B%5D=4&ca_id%5B%5D=20&sh_s_id%5B%5D=5&ca_id%5B%5D=31"><img data-original="http://115.68.20.84/mall6/main/small_avent.jpg" alt="avent" /></a></li>
            <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Fisher-Price&sh_s_id%5B%5D=5&ca_id%5B%5D=31"><img data-original="http://115.68.20.84/mall6/main/small_fisherprice.jpg" alt="FisherPrice" /></a></li>
            <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Jason"><img data-original="http://115.68.20.84/mall6/main/small_jason.jpg" alt="Jason" /></a></li>
            <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Nuxe&sh_s_id%5B%5D=1&ca_id%5B%5D=40&ca_id%5B%5D=41&ca_id%5B%5D=44"><img data-original="http://115.68.20.84/mall6/main/small_nuxe.jpg" alt="Nuxe" /></a></li>
            <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Quest+Nutrition&sh_s_id%5B%5D=2&ca_id%5B%5D=52&ca_id%5B%5D=54&sh_s_id%5B%5D=3&ca_id%5B%5D=19"><img src="http://115.68.20.84/mall6/main/small_quest.jpg" alt="Quest" /></a></li>
            <li style="width:130px;"><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Nordic+Naturals&sh_s_id%5B%5D=3&ca_id%5B%5D=10&ca_id%5B%5D=11&ca_id%5B%5D=12&ca_id%5B%5D=13&ca_id%5B%5D=14&ca_id%5B%5D=15&ca_id%5B%5D=16&ca_id%5B%5D=17&sh_s_id%5B%5D=5&ca_id%5B%5D=33&sh_s_id%5B%5D=4&ca_id%5B%5D=24"><img data-original="http://115.68.20.84/mall6/main/small_nordicnaturals.jpg" alt="NordicNaturals" /></a></li>
            <li style="width:130px;text-align:right;"><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Nellie%27s&sh_s_id%5B%5D=1&ca_id%5B%5D=40&ca_id%5B%5D=41&sh_s_id%5B%5D=4&ca_id%5B%5D=21&ca_id%5B%5D=22&ca_id%5B%5D=24"><img data-original="http://115.68.20.84/mall6/main/small_nellies.jpg" alt="Nellie's" /></a></li>
            <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Joseph+Joseph&sh_s_id%5B%5D=4&ca_id%5B%5D=20"><img data-original="http://115.68.20.84/mall6/main/small_joseph.jpg" alt="JosephJoseph" /></a></li>
            <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Noodle+%26+Boo&sh_s_id%5B%5D=5&ca_id%5B%5D=31&ca_id%5B%5D=30&ca_id%5B%5D=32&sh_s_id%5B%5D=1&ca_id%5B%5D=40"><img data-original="http://115.68.20.84/mall6/main/small_noodlenboo.jpg" alt="Noodle & Boo" /></a></li>
            <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Crabtree+%26+Evelyn&sh_s_id%5B%5D=1&ca_id%5B%5D=40&ca_id%5B%5D=41&ca_id%5B%5D=42&ca_id%5B%5D=44&sh_s_id%5B%5D=4&ca_id%5B%5D=22&sh_s_id%5B%5D=5&ca_id%5B%5D=32"><img data-original="http://115.68.20.84/mall6/main/small_crabtreene.jpg" alt="Crabtree & Evelyn" /></a></li>
            <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Aden+and+Anais&sh_s_id%5B%5D=5&ca_id%5B%5D=31&ca_id%5B%5D=30&sh_s_id%5B%5D=1&ca_id%5B%5D=40&ca_id%5B%5D=41"><img data-original="http://115.68.20.84/mall6/main/small_adenanais.jpg" alt="Aden + Anais" /></a></li>
            <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Doctor%27s+Best&sh_s_id%5B%5D=3&ca_id%5B%5D=10&ca_id%5B%5D=11&ca_id%5B%5D=12&ca_id%5B%5D=13&ca_id%5B%5D=16&ca_id%5B%5D=17&ca_id%5B%5D=19"><img data-original="http://115.68.20.84/mall6/main/small_doctorsbest.jpg" alt="Doctor's Best" /></a></li>
            <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Rosebud&sh_s_id%5B%5D=1&ca_id%5B%5D=40"><img data-original="http://115.68.20.84/mall6/main/small_rosebud.jpg" alt="Rosebud" /></a></li>
            <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Bob%27s+Red+Mill&sh_s_id%5B%5D=2&ca_id%5B%5D=51&ca_id%5B%5D=52&ca_id%5B%5D=65"><img data-original="http://115.68.20.84/mall6/main/small_bobsredmaill.jpg" alt="Bob's Red Mills" /></a></li>
            <li><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Rainbow+Light&sh_s_id%5B%5D=3&ca_id%5B%5D=10&ca_id%5B%5D=11&ca_id%5B%5D=12&ca_id%5B%5D=13&ca_id%5B%5D=14&ca_id%5B%5D=15&ca_id%5B%5D=16&ca_id%5B%5D=17&ca_id%5B%5D=19&sh_s_id%5B%5D=5&ca_id%5B%5D=33&sh_s_id%5B%5D=4&ca_id%5B%5D=24"><img data-original="http://115.68.20.84/mall6/main/small_raninbowright.jpg" alt="Rainbow Light" /></a></li>
            <li style="width:130px;"><a href="<?php echo $g4['shop_path'];?>/search.php?it_maker=Simply+Organic&sh_s_id%5B%5D=2&ca_id%5B%5D=51&ca_id%5B%5D=52"><img data-original="http://115.68.20.84/mall6/main/small_simplyorganic.jpg" alt="Simply Organic" /></a></li>
        </ul>
    </div>
</div>



<script type="text/javascript">
    now_planning_banner_id = 1;
    now_review_banner_id = 1;
    review_hover = false;
    planning_hover = false;
    $(function(){
        var planning_banner_cnt = $('.planning_banner_mask > .planning_banner_contents').length;
        var planning_banner_width = $('.planning_banner').width();

        $('.planning_banner_mask').width(planning_banner_cnt * planning_banner_width);

        /*
         후기 롤링 처리
         */
        var review_count = $('.User_Review > ul > li').length;

        for(var i=0; i<review_count; i++){
            $('.User_Review > ul > li:eq('+i+')').css('width',$('.User_Review').outerWidth());
        }
        $('.User_Review > ul').width($('.User_Review > ul > li').outerWidth() * review_count);

    });

    function planning_banner_change(num){
        var left = $('.planning_banner').width() * (num-1);
        now_planning_banner_id = num;
        $('.listing_button > li.Select').removeClass('Select');
        $('.listing_button > li[banner_id='+num+']').addClass('Select');
        $('.planning_banner_mask').stop().animate({'left':'-'+left+'px'});
    }

    function planning_banner_change2(){
        now_planning_banner_id += 1;
        if($('.listing_button > li[banner_id='+now_planning_banner_id+']').length<1){
            now_planning_banner_id = 1;
        }
        planning_banner_change(now_planning_banner_id);
    }

    function planning_banner_prev(){
        now_planning_banner_id -= 1;
        if($('.listing_button > li[banner_id='+now_planning_banner_id+']').length<1){
            now_planning_banner_id = $('.listing_button > li').length;
        }
        planning_banner_change(now_planning_banner_id);
    }
    function planning_banner_next(){
        now_planning_banner_id += 1;
        if($('.listing_button > li[banner_id='+now_planning_banner_id+']').length<1){
            now_planning_banner_id = 1;
        }
        planning_banner_change(now_planning_banner_id);
    }


    function review_banner_change(num){
        var left = $('.User_Review').width() * (num-1) + ((num-1)*60) ;
        $('.User_Review > ul').stop().animate({'left' : '-'+left+'px'});
        now_review_banner_id = num;
    }
    function review_banner_change2(){
        now_review_banner_id += 1;
        if($('.User_Review > ul > li').length<=now_review_banner_id){
            now_review_banner_id = 1;
        }
        review_banner_change(now_review_banner_id);
    }

    function review_banner_next(){
        now_review_banner_id += 1;

        if($('.User_Review > ul > li').length<now_review_banner_id){
            now_review_banner_id = 1;
        }
        review_banner_change(now_review_banner_id);
    }
    function review_banner_prev(){
        now_review_banner_id -= 1;
        if(now_review_banner_id < 1){
            now_review_banner_id = $('.User_Review > ul > li').length;
        }
        review_banner_change(now_review_banner_id);
    }


    function main_rolling_change(){
        if(planning_hover == false){
            planning_banner_change2();
        }
        if(review_hover == false){
            review_banner_change2();
        }
    }

    $('.planning_banner').hover(function( result ){ // 마우스 오버시 자동롤링 되지 않도록
        var type = result.type;

        switch(type){
            case 'mouseenter' : planning_hover = true; break;
            case 'mouseleave' : planning_hover = false;  break;
        }
    });
    $('.User_Review').hover(function( result ){ // 마우스 오버시 자동롤링 되지 않도록
        var type = result.type;

        switch(type){
            case 'mouseenter' : review_hover = true; break;
            case 'mouseleave' : review_hover = false;  break;
        }
    });


    setInterval(function(){main_rolling_change()},3000);




</script>