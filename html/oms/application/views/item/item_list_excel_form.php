<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-06-20
* Time : 오후 3:49
*/
?>
<div class="row">
    <div class="col-md-12">
        <h4>ESM 상품 다운 리스트</h4>
    </div>
</div>
<div class="alert alert-danger">
    ※ 지마켓 (Active)의 경우 ESM에서 거래상태, 지마켓(Restricted)의 경우 거래제한된 상품 리스트가 다운됩니다.
</div>
<div class="row">

    <button type="button" class="btn btn-success" onclick="location.href='<?php echo site_url('/item/single_item/gmarketItemListExcel/Active') ?>';">
        지마켓(Active)다운로드
    </button>
    <button type="button" class="btn btn-success" onclick="location.href='<?php echo site_url('/item/single_item/gmarketItemListExcel/Restricted') ?>';">
        지마켓(Restricted)다운로드
    </button>
    <button type="button" class="btn btn-danger" onclick="location.href='<?php echo site_url('/item/single_item/autionItemListexcel') ?>';">
        옥션다운로드
    </button>
</div>
