<?php
/**
 * Created by PhpStorm.
 * File name : promotion_item_dc_cache_update.php.
 * Comment :
 * Date: 2016-07-04
 * User: Minki Hong
 */
include '_common.php';
file_get_contents('http://ople.com/mall5/cron/promotion_price_update.php');
alert('캐시 재 생성이 완료되었습니다.');