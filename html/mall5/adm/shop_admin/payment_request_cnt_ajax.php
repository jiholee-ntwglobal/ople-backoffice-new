<?php
include_once("./_common.php");
define('bootstrap', true);?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<?php
$result = sql_query($sql = "select
            order_id,
            rtrim(date_format(create_date,'%H:%i:%s'))times,
            pay_method
            from
            payment_request_order
            where
            date_format(create_date,'%Y-%m-%d') = '" . $_POST['days'] . "'
            and flag ='Y'
            order by create_date desc");?>
    <table class="<?php echo $_POST['days']; ?> table table-bordered table-hover table-condensed" >
        <thead>
        <tr>
            <th rowspan="2" style="text-align: center;"><span style="color: #EE5A00;">시간</span></th>
            <th colspan="2" style="text-align: center"><span style="color: #EE5A00;">주문번호</span></th>
        </tr>
        <tr>
            <th style="text-align: center;"><span style="color: #EE5A00;">가상계좌</span></th>
            <th style="text-align: center;"><span style="color: #EE5A00;">신용카드</span></th>
        </tr>
        </thead>
<?php
while ($value = sql_fetch_array($result)) {
    ?>

    <tr class="<?php echo $_POST['days']; ?>">
        <td style="text-align: center;"><?php echo $value['times']; ?></td>
        <?php
        if ($value['pay_method'] == 'kcp-vcnt') { ?>
            <td style="text-align: center;"><a href="./orderform.php?od_id=<?php echo $value['order_id']; ?>" target="_blank"><?php echo $value['order_id']; ?></a></td>
            <td></td>
        <?php } else { ?>
            <td></td>
            <td style="text-align: center;"><a href="./orderform.php?od_id=<?php echo $value['order_id']; ?>" target="_blank"><?php echo $value['order_id']; ?></a></td>

        <?php }
        ?>
    </tr>
<?php } ?>
    </table>
