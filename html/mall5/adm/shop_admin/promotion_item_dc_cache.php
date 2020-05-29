<?php
/**
 * Created by PhpStorm.
 * File name : promotion_item_dc_cache.php.
 * Comment :
 * Date: 2016-07-04
 * User: Minki Hong
 */
$sub_menu = "500511";
include '_common.php';
auth_check($auth[$sub_menu], "r");
?>
<script>
    if(!confirm('프로모션 상품 할인 가격 캐시를 재 생성 하시겠습니까?')){
       history.back();
    }else{
        location.href='promotion_item_dc_cache_update.php';
    }
</script>
