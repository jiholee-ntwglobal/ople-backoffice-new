<?php
/**
 * Created by PhpStorm.
 * File name : promotion_item_sort.php.
 * Comment :
 * Date: 2016-05-19
 * User: Minki Hong
 */

$sub_menu = "500500";
include './_common.php';
auth_check($auth[$sub_menu], "r");

if(!$_GET['pr_id']){
    alert('잘못된 경로로 접근하였습니다.');
}

$pr = sql_fetch("select * from yc4_promotion where pr_id = '{$_GET['pr_id']}'");
if(!$pr){
    alert('잘못된 경로로 접근하였습니다.');
}

$pr_ca_sql = sql_query("select pr_ca_id,pr_ca_name from yc4_promotion_category where pr_id = '{$pr['pr_id']}' order by ifnull(sort,99999) asc");
$pr_ca_arr = array();
while ($row = sql_fetch_array($pr_ca_sql)){
    $pr_ca_arr[] = $row;
}

$where = '';

if($_GET['pr_ca_id']){
    $where .= " and ci.pr_ca_id = '{$_GET['pr_ca_id']}'";
}

$pr_item_sql = sql_query("
    select 
        ci.*,
        c.pr_ca_name,
        p.pr_name,
        i.it_name,
        i.it_amount,
        i.it_amount_usd
    from 
        yc4_promotion_item ci
        left join
        yc4_item i on ci.it_id = i.it_id
        left join
        yc4_promotion p on ci.pr_id = p.pr_id
        left join
        yc4_promotion_category c on ci.pr_id = c.pr_id and ci.pr_ca_id = c.pr_ca_id 
    where 
        ci.pr_id = '{$pr['pr_id']}'
        {$where}
    order by 
        ci.pr_id,ci.pr_ca_id,
        ifnull(c.sort,999) asc
");
$pr_item_list = array();
while ($row = sql_fetch_array($pr_item_sql)){
    $pr_item_list[] = $row;
}

$qstr = $pr_ca_qstr = $_GET;
unset($qstr['pr_id'],$pr_ca_qstr['pr_ca_id']);
$qstr = http_build_query($qstr);
$pr_ca_qstr = http_build_query($pr_ca_qstr);




define('bootstrap', true);
$g4['title'] = "프로모션 상품 관리";
include '../admin.head.php';
?>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <div class="panel panel-default">
    	<div class="panel-body">
            <ul class="nav nav-pills">
                <li role="presentation" <?php echo $_GET['pr_ca_id'] == '' ? 'class="active"':'';?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $pr_ca_qstr;?>">전체</a></li>
                <?php foreach ($pr_ca_arr as $pr_ca_row) { ?>
                <li role="presentation" <?php echo $_GET['pr_ca_id'] == $pr_ca_row['pr_ca_id'] ? 'class="active"':'';?>>
                    <a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $pr_ca_qstr;?>&pr_ca_id=<?php echo $pr_ca_row['pr_ca_id'];?>">
                        <?php echo $pr_ca_row['pr_ca_name'];?>
                    </a>
                </li>
                <?php }?>
            </ul>
            <table class="table table-hover table-bordered table-condensed table-striped">
                <thead>
                <tr>
                    <td class="text-center">프로모션(코드)</td>
                    <td class="text-center">프로모션 카테고리(코드)</td>
                    <td class="text-center">상품이미지</td>
                    <td class="text-center">상품명</td>
                    <td class="text-center">상품가격</td>
                    <td class="text-center">할인정보</td>
                    <td class="text-center">아이콘</td>
                    <td class="text-center">등록일</td>
                    <td class="text-center"></td>
                </tr>
                </thead>
                <tbody id="sortable">
                <?php foreach ($pr_item_list as $row) { ?>
                <tr class="ui-state-default">
                    <td><?php echo $row['pr_name'];?> (<?php echo $row['pr_id']?>)</td>
                    <td><?php echo $row['pr_ca_id'] ? $row['pr_ca_name'] .'('.$row['pr_ca_id'].')':'';?></td>
                    <td><?php echo get_it_image($row['it_id'].'_s',100,100,null,null,false,false,false);?></td>
                    <td><?php echo get_item_name($row['it_name'],'list');?></td>
                    <td>$ <?php echo usd_convert($row['it_amount']);?> (￦ <?php echo number_format($row['it_amount']);?>)</td>
                    <td></td>
                    <td></td>
                    <td><?php echo $row['create_dt'];?></td>
                </tr>
                <?php }?>
                </tbody>
            </table>
    	</div>
        <div class="panel-footer text-center">
            <a href="promotion_list.php?<?php echo $qstr;?>" class="btn btn-default">프로모션 목록</a>
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#excel_modal">엑셀 업로드</button>
            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#excel_modal">엑셀 다운로드</button>
            <button type="button" class="btn btn-success">진열 순서 변경</button>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="excel_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content">
                <input type="hidden" name="pr_id" value="<?php echo $pr['pr_id'];?>">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">엑셀 업로드</h4>
                </div>
                <div class="modal-body form-horizontal">
                    <div class="row">
                        <label class="control-label col-lg-4">엑셀 파일 업로드</label>
                        <div class="col-lg-8"><input type="file" name="excel_file" class="form-control"/></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary">저장</button>
                    <a href="#" class="btn btn-info">샘플 엑셀파일 다운로드</a>
                    <button type="button" class="btn btn-default" data-dismiss="modal">닫기</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $( "#sortable" ).sortable();
        $( "#sortable" ).disableSelection();
    </script>
<?php
include '../admin.tail.php';

