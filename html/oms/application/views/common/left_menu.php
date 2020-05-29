<?php
/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 4:28
 */?>
<div id="wrapper">

    <?php
    if(isset($current_master_id)) {
        if($current_master_id == "fastople") $background_color = "";
        if($current_master_id == "neiko") $background_color = "background-color:#A0EEF1";
        if($current_master_id == "allthatmal") $background_color ="background-color:#FAC3C3";
    }else{
        $background_color = "";
    }
    ?>
    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0;<?php echo $background_color;?>">
        <div class="navbar-header">
<!--            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>-->
            <a class="navbar-brand" href="<?php echo site_url('dashboard'); ?>">Openmarket Manage System 1.0</a>
        </div>
        <!-- /.navbar-header -->

        <?php

        if(isset($master_id_arr)) {
            foreach ($master_id_arr as $master_id){
                if($master_id == "fastople") $btn_color = "primary";
                if($master_id == "neiko") $btn_color = "info";
                if($master_id == "allthatmal") $btn_color ="danger"
        ?>
        <a href="<?php echo site_url('auth/master_id/goMasterId/'.$master_id.'/'.urlencode(uri_string()))?>" class="btn btn-<?php echo $btn_color;?> btn-icon-split">
            <span class="text"><?php echo $master_id?></span>
        </a>
        <?php
            }
        }

        if(isset($current_master_id)) {
                echo "현재 계정 : ". $current_master_id;
         }
         ?>

        <ul class="nav navbar-top-links navbar-right">

            <!-- /.dropdown -->
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                    <i class="fa fa-list fa-fw"></i> <i class="fa fa-caret-down"></i>
                </a>
                <ul class="dropdown-menu dropdown-message">
                    <?php if(isset($current_master_id)) {
                        ?>
                        <li><a href="<?php echo site_url('order/order'); ?>"><i class="fa fa-list-alt fa-fw"></i>
                                주문서 관리</a></li>
                        <?php
                        if($current_master_id=="fastople") {
                            ?>
<!--                            <li>-->
<!--                                <a href="--><?php //echo site_url('order/customer_number'); ?><!--">-->
<!--                                    <div class="text-danger">-->
<!--                                        <i class="fa fa-sign-in fa-fw"></i> 통관고유부호 업로드-->
<!--                                    </div>-->
<!--                                </a>-->
<!--                            </li>-->

<!--                            <li><a href="#"><i class="fa fa-trash-o fa-fw"></i> 취소요청 관리</a></li>-->

                            <li class="divider"></li>

                            <li><a href="<?php echo site_url('item/item_option_manage'); ?>"><i class="fa fa-briefcase fa-fw"></i> 옵션상품 관리</a></li>

                            <li class="divider"></li>

                            <li><a href="<?php echo site_url('item/single_item/updateProductPriceList'); ?>"><i class="fa fa-briefcase fa-fw"></i> 단품상품 일괄가격조정 </a></li>

                            <li class="divider"></li>

                            <li><a href="<?php echo site_url('/item/single_item/insertSingleItemForm'); ?>"><i class="fa fa-won fa-fw"></i> 수동등록 상품 등록</a></li>

                            <li><a href="<?php echo site_url('/item/single_item'); ?>"><i class="fa fa-won fa-fw"></i>단품 상품 리스트</a></li>


                            <?php if($_SERVER['REMOTE_ADDR']=='211.214.213.101') { ?>
                                <li><a href="<?php echo site_url('/item/single_item/apiItemListExcelDownForm'); ?>"><i class="fa fa-won fa-fw"></i>ESM 상품 다운 리스트</a></li>
                            <?php } ?>

                            <!--<li><a href="<?php /*echo site_url('/item/single_item/apiItemListExcelDownForm'); */?>"><i class="fa fa-won fa-fw"></i>
                                    ESM 상품 다운 리스트</a></li>-->
                            <!--                    <li><a href="#"><i class="fa fa-won fa-fw"></i> 상품 가격 업데이트 히스토리</a></li>-->
                            <li><a href="<?php echo site_url('/order/order/compareOrderItemPrice'); ?>"><i class="fa fa-won fa-fw"></i> 준비 주문 상품 가격정보</a></li>

                            <li><a href="<?php echo site_url('/item/ople_mapping_change'); ?>"><i class="fa fa-won fa-fw"></i> 오플매핑변경 관리</a></li>

                            <li class="divider"></li>

                            <li><a href="<?php echo site_url('item/soldout_exclude_item'); ?>"><i class="fa fa-tag fa-fw"></i> 품절 예외상품 관리</a></li>

                            <li><a href="<?php echo site_url('item/soldout_history'); ?>"><i class="fa fa-tags fa-fw"></i>단품 품절, 품절해제 히스토리</a></li>

                            <li><a href="<?php echo site_url('item/soldout_option_history'); ?>"><i class="fa fa-tags fa-fw"></i>옵션 품절, 품절해제 히스토리</a></li>

                            <?php }else{ ?>
                                <li><a href="<?php echo site_url('item/single_item/updateProductPriceList'); ?>"><i class="fa fa-briefcase fa-fw"></i> 단품상품 일괄가격조정 </a></li>

                                <li class="divider"></li>

                                <li><a href="<?php echo site_url('/item/single_item/insertSingleItemForm'); ?>"><i class="fa fa-won fa-fw"></i> 수동등록 상품 등록</a></li>

                                <li><a href="<?php echo site_url('/item/single_item'); ?>"><i class="fa fa-won fa-fw"></i>단품 상품 리스트</a></li>

                            <!--<li><a href="<?php /*echo site_url('/item/single_item/apiItemListExcelDownForm'); */?>"><i class="fa fa-won fa-fw"></i>
                                        ESM 상품 다운 리스트</a></li>-->

                            <li class="divider"></li><li><a href="<?php echo site_url('/item/ople_mapping_change'); ?>"><i class="fa fa-won fa-fw"></i> 오플매핑변경 관리</a></li>

                            <li><a href="<?php echo site_url('item/item_option_manage'); ?>"><i class="fa fa-briefcase fa-fw"></i> 옵션상품 관리</a></li>

                            <li class="divider"></li>

                            <li><a href="<?php echo site_url('item/soldout_exclude_item'); ?>"><i class="fa fa-tag fa-fw"></i> 품절 예외상품 관리</a></li>

                            <li><a href="<?php echo site_url('item/soldout_history'); ?>"><i class="fa fa-tags fa-fw"></i>단품 품절, 품절해제 히스토리</a></li>

                            <li><a href="<?php echo site_url('item/soldout_option_history'); ?>"><i class="fa fa-tags fa-fw"></i>옵션 품절, 품절해제 히스토리</a></li>
                        <?php  }
                        }else{ ?>
                        <li><a href="<?php echo site_url('order/customer_number'); ?>"><div class="text-danger"><i class="fa fa-sign-in fa-fw"></i> 통관고유부호 업로드</div></a></li>

                        <li><a href="<?php echo site_url('order/order'); ?>"><i class="fa fa-list-alt fa-fw"></i> 주문서 관리</a></li>

                        <li><a href="#"><i class="fa fa-trash-o fa-fw"></i> 취소요청 관리</a></li>

                        <li class="divider"></li>

                        <li><a href="<?php echo site_url('item/item_option_manage'); ?>"><i class="fa fa-briefcase fa-fw"></i> 옵션상품 관리</a></li>

                        <li class="divider"></li>

                        <li><a href="<?php echo site_url('item/single_item/updateProductPriceList'); ?>"><i class="fa fa-briefcase fa-fw"></i>  단품상품 일괄가격조정 </a></li>

                        <li class="divider"></li><li><a href="<?php echo site_url('/item/ople_mapping_change'); ?>"><i class="fa fa-won fa-fw"></i> 오플매핑변경 관리</a></li>
                        <li class="divider"></li>

                        <li><a href="<?php echo site_url('/item/single_item/insertSingleItemForm'); ?>"><i class="fa fa-won fa-fw"></i> 수동등록 상품 등록</a></li>

                        <li><a href="<?php echo site_url('/item/single_item'); ?>"><i class="fa fa-won fa-fw"></i> 단품 상품 리스트</a></li>

                        <!--<li><a href="<?php /*echo site_url('/item/single_item/apiItemListExcelDownForm'); */?>"><i class="fa fa-won fa-fw"></i>ESM 상품 다운 리스트</a></li>-->
                        <!--                    <li><a href="#"><i class="fa fa-won fa-fw"></i> 상품 가격 업데이트 히스토리</a></li>-->

                        <li><a href="<?php echo site_url('/order/order/compareOrderItemPrice'); ?>"><i class="fa fa-won fa-fw"></i> 준비 주문 상품 가격정보</a></li>

                        <li class="divider"></li>

                        <li><a href="<?php echo site_url('item/soldout_exclude_item'); ?>"><i class="fa fa-tag fa-fw"></i> 품절 예외상품 관리</a></li>

                        <li><a href="<?php echo site_url('item/soldout_history'); ?>"><i class="fa fa-tags fa-fw"></i>단품 품절, 품절해제 히스토리</a></li>

                        <li><a href="<?php echo site_url('item/soldout_option_history'); ?>"><i class="fa fa-tags fa-fw"></i>옵션 품절, 품절해제 히스토리</a></li>

                    <?php } ?>
                </ul>
                <!-- /.dropdown-user -->
            </li>
            <!-- /.dropdown -->

            <!-- /.dropdown -->
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                    <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
                </a>
                <ul class="dropdown-menu dropdown-message">
                    <li><a href="<?php echo site_url('auth/login/logout'); ?>"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                    </li>
                </ul>
                <!-- /.dropdown-user -->
            </li>

            <!-- /.dropdown -->
        </ul>
        <!-- /.navbar-top-links -->

    </nav>

    <div id="page-wrapper">
