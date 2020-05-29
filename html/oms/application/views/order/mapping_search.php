<?php
/**
 * Created by PhpStorm.
 * User: 강소진
 * Date : 2018-07-26
 * Time : 오전 11:40
 */

?>
<div class="table-responsive">
    <table class="table table-hover" style="font-size:10px;">
        <thead>
        <tr>
            <th>VCODE</th>
            <th>상품정보</th>
            <th>매핑UPC</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if(is_array($product_info) && count($product_info) > 0){ ?>
            <tr>
                <td><?php  echo 'V'.str_pad($virtual_item_id, 9, '0', STR_PAD_LEFT); ?></td>
                <td>
                    <?php
                    foreach ($product_info as $key=>$val) {
                        echo element('mfgname', $val) . " " . element('item_name', $val) . "<br><br>";
                    }
                    ?>
                </td>
                <td>
                    <?php
                    foreach ($product_info as $key=>$val) {
                        echo element('upc', $val, '') . ' X ' . element('quantity', $mapping_info[$key]) . "<br><br>";
                    }
                    ?>
                </td>
                <td><button type="button" onclick="addMappingProduct('<?php  echo 'V'.str_pad($virtual_item_id, 9, '0', STR_PAD_LEFT); ?>')" class="btn btn-primary">추가</button></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
