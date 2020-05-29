<?php
/**
 * Created by PhpStorm.
 * File name : promotion_item_dc_list.php.
 * Comment :
 * Date: 2016-05-24
 * User: Minki Hong
 */
$sub_menu = "500510";
include './_common.php';
auth_check($auth[$sub_menu], "r");

$where = '';
if($_GET['fg'] == 'Y'){ // 진행 중
    $where .= " and cache.uid is not null ";
}elseif($_GET['fg'] == 'N'){ // 종료 및 대기
    $where .= " and cache.uid is null ";
}

if($_GET['is_pr'] == 'Y'){ // 프로모션 할인
    $where .= " and dc.pr_id is not null";
}elseif($_GET['is_pr'] == 'N'){ // 일반 할인
    $where .= " and dc.pr_id is null";
}

$sql = sql_query("
    select 
        dc.*,
        i.it_name,
        i.it_amount as ori_amount,
        i.it_amount_usd as ori_amount_usd,
        pr.pr_name,
        pr.st_dt as pr_st_dt,
        pr.en_dt as pr_en_dt
    from 
        yc4_promotion_item_dc dc
        left join
        yc4_promotion_item_dc_cache cache on dc.uid = cache.uid
        left join
        yc4_item i on dc.it_id = i.it_id
        left join
        yc4_promotion pr on dc.pr_id = pr.pr_id
    where
        i.it_use = 1
        and i.it_discontinued = 0
        {$where}
    order by dc.uid desc
");

$data_arr = array();
while($row = sql_fetch_array($sql)) {
    $data_arr[] = $row;
}

$fg_qstr = $is_pr_qstr = $_GET;
unset($fg_qstr['fg'],$is_pr_qstr['is_pr']);
$fg_qstr = http_build_query($fg_qstr);
$is_pr_qstr = http_build_query($is_pr_qstr);

define('bootstrap', true);
$g4['title'] = "프로모션 할인 상품 리스트";
include '../admin.head.php';
?>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<div class="row">
    <div class="col-lg-6">
        <ul class="nav nav-pills">
            <li role="presentation" <?php echo $_GET['fg'] == '' ? 'class="active"':'';?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $fg_qstr;?>">전체</a></li>
            <li role="presentation" <?php echo $_GET['fg'] == 'Y' ? 'class="active"':'';?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $fg_qstr;?>&fg=Y">진행</a></li>
            <li role="presentation" <?php echo $_GET['fg'] == 'N' ? 'class="active"':'';?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $fg_qstr;?>&fg=N">종료 및 대기</a></li>
        </ul>
    </div>
    <div class="col-lg-6">
        <ul class="nav nav-pills navbar-right">
            <li role="presentation" <?php echo $_GET['is_pr'] == '' ? 'class="active"':'';?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $is_pr_qstr;?>">전체</a></li>
            <li role="presentation" <?php echo $_GET['is_pr'] == 'Y' ? 'class="active"':'';?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $is_pr_qstr;?>&is_pr=Y">프로모션 할인</a></li>
            <li role="presentation" <?php echo $_GET['is_pr'] == 'N' ? 'class="active"':'';?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $is_pr_qstr;?>&is_pr=N">일반 할인</a></li>
        </ul>
    </div>
</div>

<table class="table table-hover">
	<thead>
		<tr>
			<td class="text-center">상품코드</td>
			<td class="text-center" colspan="2">상품명</td>
			<td class="text-center">프로모션</td>
			<td class="text-center">정상가</td>
			<td class="text-center">할인가</td>
			<td class="text-center">기간</td>
		</tr>
	</thead>
	<tbody>
		<?php
        if(is_array($data_arr)) {
            foreach ($data_arr as $row) {
            ?>
        <tr>
            <td>
                <a href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $row['it_id']?>" target="_blank"> <?php echo $row['it_id']?> </a>
            </td>
            <td><?php echo get_it_image($row['it_id'].'_s',80,80,null,null,false,false,false);?></td>
            <td><?php echo get_item_name($row['it_name'],'list'); ?></td>
            <td><?php echo $row['pr_name']?></td>
            <td>$ <?php echo number_format(usd_convert($row['ori_amount']),2);?><br/>(￦ <?php echo number_format($row['ori_amount']);?>)</td>
            <td>$ <?php echo number_format($row['amount_usd'],2);?><br/>(￦ <?php echo number_format(round($row['amount_usd'] * $default['de_conv_pay']));?>)</td>
            <td><?php
                if($row['st_dt'] || $row['en_dt']){
                    echo $row['st_dt'] .' ~ '.$row['en_dt'];
                }elseif($row['pr_st_dt'] || $row['pr_en_dt']){
                    echo '프로모션 기간 내<br/>('.$row['pr_st_dt'] .' ~ '.$row['pr_en_dt'].')';
                }
                ?></td>
        </tr>

        <?php
            }
        }
        ?>
	</tbody>
</table>

<?php
include '../admin.tail.php';


