<?php
/**
 * Created by PhpStorm.
 * File name : promotion_list.php.
 * Comment :
 * Date: 2016-05-19
 * User: Minki Hong
 */
$sub_menu = "500500";
include './_common.php';
auth_check($auth[$sub_menu], "r");

if ($_GET['mode'] == 'delete') { // 프로모션 삭제
    if (!$_GET['pr_id']) {
        alert('잘못된 경로로 접근하였습니다.');
    }
    // 프로모션 삭제
    sql_query("delete from yc4_promotion where pr_id = '{$_GET['pr_id']}'");
    // 프로모션 카테고리 삭제
    sql_query("delete from yc4_promotion_category where pr_id = '{$_GET['pr_id']}'");
    // 프로모션 상품정보 삭제
    sql_query("delete from yc4_promotion_item where pr_id = '{$_GET['pr_id']}'");
    // 프로모션 할인정보 삭제
    sql_query("delete from yc4_promotion_item_dc where pr_id = '{$_GET['pr_id']}'");
    sql_query("delete from yc4_promotion_item_dc_cache where pr_id = '{$_GET['pr_id']}'");
    // 프로모션 링크 삭제
    sql_query("delete from yc4_promotion_link where pr_id = '{$_GET['pr_id']}'");
    sql_query("delete from yc4_promotion_link where link_pr_id = '{$_GET['pr_id']}'");

    $return_url = $_SERVER['PHP_SELF'];
    if ($_GET['return_url']) {
        $return_url = $_GET['return_url'];
        if ($_GET['qstr']) {
            $return_url .= '?' . $_GET['qstr'];
        }
    }
    alert('삭제가 완료되었습니다.', $return_url);
}


$where = '';
// 진행 및 대기 조건
if ($_GET['fg'] == 'Y') {
    $where .= ($where ? ' and ' : ' where ') . " date_format(now(),'%Y-%m-%d') between ifnull(p.st_dt,date_format(now(),'%Y-%m-%d')) and ifnull(p.en_dt,date_format(now(),'%Y-%m-%d')) ";
} elseif ($_GET['fg'] == 'N') {
    $where .= ($where ? ' and ' : ' where ') . " date_format(now(),'%Y-%m-%d') not between ifnull(p.st_dt,date_format(now(),'%Y-%m-%d')) and ifnull(p.en_dt,date_format(now(),'%Y-%m-%d')) ";
}

if ($_GET['promotion_type']) {
    $where .= ($where ? ' and ' : ' where ') . "promotion_type = '{$_GET['promotion_type']}'";
}

$sql = sql_query("
    select
        p.pr_id, p.pr_name, p.st_dt, p.en_dt, p.comment, p.list_skin, p.banner_url, p.create_dt,p.promotion_type,
        count(distinct c.pr_ca_id) as ca_cnt,
        count(distinct i.it_id) as item_cnt,
        i.create_dt as item_create_dt
    from 
        yc4_promotion p
        LEFT JOIN
        yc4_promotion_category c on p.pr_id = c.pr_id
        left join
        yc4_promotion_item i on p.pr_id = i.pr_id
    {$where}
    group by p.pr_id, p.pr_name, p.st_dt, p.en_dt, p.comment, p.list_skin, p.banner_url, p.create_dt,promotion_type
    order by p.pr_id desc
");
$list_arr = array();
while ($row = sql_fetch_array($sql)) {
    $list_arr[] = $row;
}

// 프로모션 타입 데이터 로드
$p_type_stmt = sql_query("select code_value, code_name from yc4_code where code_type = 'p_type'  ");
$p_type_arr = array();
while ($row = sql_fetch_array($p_type_stmt)) {
    $p_type_arr[$row['code_value']] = $row['code_name'];
}

$qstr = http_build_query($_GET);

$fg_qstr = $_GET;
unset($fg_qstr['fg']);
$fg_qstr = http_build_query($fg_qstr);

$p_type_qstr = $_GET;
unset($p_type_qstr['promotion_type']);
$p_type_qstr = http_build_query($p_type_qstr);

define('bootstrap', true);
$g4['title'] = "프로모션 리스트";
include '../admin.head.php';
?>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>


    <ul class="nav nav-pills pull-left">
        <li role="presentation" <?php echo $_GET['fg'] == '' ? 'class="active"' : ''; ?>><a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $fg_qstr; ?>">전체</a></li>
        <li role="presentation" <?php echo $_GET['fg'] == 'Y' ? 'class="active"' : ''; ?>><a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $fg_qstr; ?>&fg=Y">진행</a></li>
        <li role="presentation" <?php echo $_GET['fg'] == 'N' ? 'class="active"' : ''; ?>><a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $fg_qstr; ?>&fg=N">종료 및 대기</a></li>
    </ul>
    <ul class="nav nav-pills pull-right">
        <li role="presentation" <?php echo $_GET['promotion_type'] == '' ? 'class="active"' : ''; ?>><a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $p_type_qstr; ?>">전체</a></li>
        <?php foreach ($p_type_arr as $p_type_key => $p_type_nm) { ?>
        <li role="presentation" <?php echo $_GET['promotion_type'] == $p_type_key ? 'class="active"' : ''; ?>><a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $p_type_qstr; ?>&promotion_type=<?php echo $p_type_key;?>"><?php echo $p_type_nm;?></a></li>
        <?php } ?>

    </ul>
    <table class="table table-hover table-bordered table-condensed table-striped">
        <thead>
        <tr>
            <td class="text-center">코드</td>
            <td class="text-center">타입</td>
            <td class="text-center">이름</td>
            <td class="text-center">기간</td>
            <td class="text-center">생성시간</td>
            <td class="text-center">최종 업데이트 시간</td>
            <td class="text-center">카테고리 갯수</td>
            <td class="text-center">등록상품 갯수</td>
            <td class="text-center">
                <a href="promotion_write.php"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></a>
            </td>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($list_arr as $row) { ?>
            <tr>
                <td><?php echo $row['pr_id'] ?></td>
                <td><?php echo $p_type_arr[$row['promotion_type']] ?></td>
                <td><?php echo $row['pr_name'] ?></td>
                <td><?php echo $row['st_dt'] ? $row['st_dt'] : '미지정'; ?> ~ <?php echo $row['en_dt'] ? $row['en_dt'] : '미지정'; ?></td>
                <td><?php echo $row['create_dt']; ?></td>
                <td><?php if($row['item_create_dt']) echo $row['item_create_dt']; else echo "-";?></td>
                <td class="text-right">
                    <a href="promotion_category_list.php?<?php echo $qstr; ?>&pr_id=<?php echo $row['pr_id']; ?>">
                        <strong>
                            <?php echo number_format($row['ca_cnt']); ?>
                        </strong>
                    </a>
                </td>
                <td class="text-right">
                    <a href="promotion_item_list.php?<?php echo $qstr; ?>&pr_id=<?php echo $row['pr_id']; ?>">
                        <strong><?php echo number_format($row['item_cnt']); ?></strong>
                    </a>
                </td>
                <td>
                    <a href="promotion_write.php?<?php echo $qstr; ?>&pr_id=<?php echo $row['pr_id'] ?>"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>
                    <a href="#" onclick="promotion_delete('<?php echo $row['pr_id'] ?>'); return false;"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a>
                    <a href="http://ople.com/mall5/shop/promotion_preview.php?pr_id=<?php echo $row['pr_id']; ?>&preview=1" target="_blank"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <style>
        a.btn.btn-primary:link {
            color: #ffffff;
        }
    </style>

    <script>
        /**
         * 프로모션 삭제
         * @param pr_id {int}
         * @returns {boolean}
         */
        function promotion_delete(pr_id) {
            if (!confirm('해당 프로모션을 삭제하시겠습니까?\n해당 프로모션으로 등록한 상품 할인정보도 함께 삭제됩니다.')) {
                return false;
            }
            var get_str = location.search;
            var qstr = get_str.substr(2, get_str.length - 2);
            qstr = encodeURIComponent(qstr);

            var submit_url = location.origin + location.pathname + '?mode=delete&pr_id=' + pr_id + '&qstr=' + qstr + '&return_url=' + encodeURIComponent(location.pathname);

            location.href = submit_url;
            /*var $a = $('a').attr('href',submit_url);
             $('body').append($a);
             $($a)[0].click();
             $($a).remove();*/

        }
    </script>
<?php
include '../admin.tail.php';

