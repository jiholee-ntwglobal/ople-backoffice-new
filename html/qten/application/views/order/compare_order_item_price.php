<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2018-09-11
* Time : 오후 2:13
*/
?>
        <div class="row">
            <h4>준비 주문 상품 가격정보</h4>
        </div>
        <form method="GET">
            <div class="row">
                <div class="col-md-9"></div>
                <div class="col-md-2 form-group">
                    <label>오차 표시 기준</label>
                    <input type="text" class="form-control" name="set_gap" value="<?php echo $set_gap ?>"/>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary">검색</button>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="table-responsive">
                <table class="table table-hover" style="font-size:10px;">
                    <thead>
                    <tr>
                        <th>상품코드</th>
                        <th>상품명</th>
                        <th>상품옵션명</th>
                        <th>수집상품가</th>
                        <th>오플상품가</th>
                        <th colspan="2">가격오차</th>
                        <th colspan="3">매핑정보</th>
                        <th>주문정보 바로가기</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($order_product_prices as $order_product_price){

                        $product_tag = '';
                        if(element('option_name', $order_product_price, '') != '') $product_tag = '옵션상품';
                        if(element('addPrdYn', $order_product_price, '') == 'Y' && element('addPrdNo', $order_product_price, '0') > 0) $product_tag = '추가구성상품';

                        $percent = (element('ople_price', $order_product_price)>0) ? round( (element('price_gap', $order_product_price) * 100) / element('ople_price', $order_product_price),2) : "0";

                        $line_class = ($set_gap - $percent) > 0 ? 'bg-danger' : '';

                        if($percent > 0) $percent = '+' . $percent;


                        $current_upc = element('upc', $upc_info[element('master_item_id',$master_item_info[element('virtual_item_id',$order_product_price)][0])]);
                        $current_qty = element('quantity',$master_item_info[element('virtual_item_id',$order_product_price)][0]);

//                        $current_mapping_info = element($current_upc, $mapping_info);
                        $current_mapping_info = element(element('virtual_item_id',$order_product_price), $master_item_info);


                        $row_cnt = max(1, count($current_mapping_info));

                        $first_mapping_product = array();

                        if(is_array($current_mapping_info)) {
                            $first_mapping_product = element(element('upc', $upc_info[element('master_item_id', $current_mapping_info[0])]), $mapping_info);// $current_mapping_info[0];
                            $first_mapping_product = $first_mapping_product[0];
                        }

                        $first_product_name = $first_product_price = '';

                        if(element('it_id', $first_mapping_product) && element('it_name', $first_mapping_product)){
                            $first_product_name .= element('it_id', $first_mapping_product). " X " . $current_qty . '<br/>';
                            if(strpos(element('it_name', $first_mapping_product),'||') !== false){
                                $tmp = explode('||', element('it_name', $first_mapping_product));
                                $first_product_name .= $tmp[0];
                                if($tmp[1]) $first_product_name .= '<br/>' . $tmp[1];
                                if($tmp[2]) $first_product_name .= '<br/>' . $tmp[2];
                            } else {
                                $first_product_name .= element('it_name', $first_mapping_product);
                            }

                            $first_product_price = number_format(element(element('it_id', $first_mapping_product), $ople_price));

                        }

                        ?>
                        <tr class="<?php echo $line_class; ?>">
                            <td rowspan="<?php echo $row_cnt; ?>"><a href="<?php echo site_url('item/channel_item/openChannelUrl/' . element('channel_id', $order_product_price). '/' . element('channel_product_no', $order_product_price)); ?>" target="_blank"><?php echo element('channel_product_no', $order_product_price); ?></a></td>
                            <td rowspan="<?php echo $row_cnt; ?>"><?php echo element('product_name', $order_product_price); ?> <?php echo $product_tag; ?></td>
                            <td rowspan="<?php echo $row_cnt; ?>"><?php echo element('option_name', $order_product_price); ?></td>
                            <td rowspan="<?php echo $row_cnt; ?>"><?php echo number_format(element('product_price', $order_product_price)); ?></td>
                            <td rowspan="<?php echo $row_cnt; ?>"><?php echo number_format(element('ople_price', $order_product_price)); ?></td>
                            <td rowspan="<?php echo $row_cnt; ?>"><?php echo $percent. '%'; ?></td>
                            <td rowspan="<?php echo $row_cnt; ?>"><?php echo number_format(element('price_gap', $order_product_price)); ?></td>

                            <td><img src="http://115.68.20.84/item/<?php echo element('it_id', $first_mapping_product,''); ?>_s" width="70"/></td>
                            <td><a href="http://www.ople.com/mall5/shop/item.php?it_id=<?php echo element('it_id', $first_mapping_product); ?>" target="_blank"><?php echo $first_product_name; ?></a></td>
                            <td><?php echo $first_product_price; ?></td>

                            <td rowspan="<?php echo $row_cnt; ?>"><a href="<?php echo site_url('order/order/'); ?>?status=all&search_type=channel_order_no&search_value=<?php echo element('channel_order_no', $order_product_price); ?>" class="btn btn-warning">바로가기</a></td>
                        </tr>
                        <?php
                        if(count($current_mapping_info) > 1){

                            for($k=1;$k<count($current_mapping_info); $k++){

                               // $mapping_product = $current_mapping_info[$k];
                                $mapping_product = element(element('upc', $upc_info[element('master_item_id', $current_mapping_info[$k])]), $mapping_info);// $current_mapping_info[0];
                                $mapping_product = $mapping_product[0];


                                $product_name = '';

                                if(element('it_id', $mapping_product) && element('it_name', $mapping_product)){
                                    $product_name .= element('it_id', $mapping_product) . " X " . $current_qty.  '<br/>';
                                    if(strpos(element('it_name', $mapping_product),'||') !== false){
                                        $tmp = explode('||', element('it_name', $mapping_product));
                                        $product_name .= $tmp[0];
                                        if($tmp[1]) $product_name .= '<br/>' . $tmp[1];
                                        if($tmp[2]) $product_name .= '<br/>' . $tmp[2];
                                    } else {
                                        $product_name .= element('it_name', $mapping_product);
                                    }
                                }

                                $product_price = number_format(element(element('it_id', $mapping_product), $ople_price));

                                echo "
                                    <tr>
                                        <td><img src=\"http://115.68.20.84/item/" . element('it_id', $mapping_product) . "_s\"
                                         width=\"70\"/></td>
                                        <td><a href=\"http://www.ople.com/mall5/shop/item.php?it_id=" . element('it_id', $mapping_product) . "\" target=\"_blank\">$product_name</a></td>
                                        <td>$product_price</td>
                                    </tr>";


                            }
                        }

                    } ?>
                    </tbody>
                </table>
            </div>
        </div>
<script type="text/javascript">
    $(document).ready(function () {

        $(".dropdown").removeClass("open");

    });
</script>


