<?php
/*
----------------------------------------------------------------------
file name	 : hotdeal_zone.php
comment		 : 핫딜존 영역
date		 : 2015-02-03
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

if(count($main_json->hotdeal) > 0){

    $i = 0;
    foreach($main_json->hotdeal as $sort => $val){
        $i++;

        if($i == 3 || $i ==  7){
            $hotdeal_first = true;
        }else{
            $hotdeal_first = false;
        }

        if($val->end_fg == true){
            $end_mask = "
                <div class='hotdel_end_mask'>

                </div>
						";
        }else{
            $end_mask = '';
        }
        echo "
            <li".($hotdeal_first ? " class='first'":"").">
                ".$end_mask."
                <p class='discount_rate_box'><strong>".$val->dc_per."</strong></p>
                <p class='banner'><a href='".$g4['shop_path']."/item.php?it_id=".$val->it_id."'><img src='".$val->img_link."' alt='hotdeal1'></a></p>
                <p class='hotdeal_price'>
                    <span class='street_price'>￦ ".number_format($val->it_amount_msrp_krw)." <em>($ ".number_format($val->it_amount_msrp,2).")</em></span>
                    <strong class='discount_price'>￦ ".number_format($val->it_event_amount)." <em>($ ".number_format(usd_convert($val->it_event_amount),2).")</em></strong>
                </p>
            </li>
        ";
    }
}else {
    if(!isset($main)) {
        include_once $g4['full_path'] . '/cache/main_cache.php';
    }
    # 핫딜존 시작 #
    if (is_array($main['hotdel'])) {

        $i = 0;
        foreach ($main['hotdel'] as $sort => $val) {
            $i++;

            if ($i == 3 || $i == 7) {
                $hotdeal_first = true;
            } else {
                $hotdeal_first = false;
            }

            if ($val['end_fg'] == true) {
                $end_mask = "
                <div class='hotdel_end_mask'>

                </div>
						";
            } else {
                $end_mask = '';
            }
            echo "
            <li" . ($hotdeal_first ? " class='first'" : "") . ">
                " . $end_mask . "
                <p class='discount_rate_box'><strong>" . $val['dc_per'] . "</strong></p>
                <p class='banner'><a href='" . $g4['shop_path'] . "/item.php?it_id=" . $val['it_id'] . "'><img src='" . $val['img_link'] . "' alt='hotdeal1'></a></p>
                <p class='hotdeal_price'>
                    <span class='street_price'>￦ " . number_format($val['it_amount_msrp_krw']) . " <em>($ " . number_format($val['it_amount_msrp'], 2) . ")</em></span>
                    <strong class='discount_price'>￦ " . number_format($val['it_event_amount']) . " <em>($ " . number_format(usd_convert($val['it_event_amount']), 2) . ")</em></strong>
                </p>
            </li>
        ";
        }
    }
}



?>