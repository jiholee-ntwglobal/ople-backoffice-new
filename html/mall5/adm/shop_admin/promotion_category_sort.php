<?php
/**
 * Created by PhpStorm.
 * File name : promotion_category_list.php.
 * Comment :
 * Date: 2016-05-19
 * User: Minki Hong
 */
$sub_menu = "500500";
include './_common.php';
auth_check($auth[$sub_menu], "r");

if($_POST['mode'] == 'sort_update'){
    if(!$_POST['sort']){
        alert('잘못된 경로로 접근하였습니다.');
    }
    if(!is_array($_POST['sort'])){
        alert('잘못된 경로로 접근하였습니다.');
    }
    if(count($_POST['sort']) < 1){
        alert('잘못된 경로로 접근하였습니다.');
    }

    /*
     * $_POST['sort'][pr_ca_id] = sort
     * */
    asort($_POST['sort']);
    foreach ($_POST['sort'] as  $pr_ca_id => $sort) {
        $update_rs = sql_query($a="update yc4_promotion_category set sort = '{$sort}' where pr_ca_id = '{$pr_ca_id}' and pr_id = '{$_POST['pr_id']}'");
        if(!$update_rs){
            alert('처리중 오류 발생! 관라자에게 문의해 주세요.');
        }
    }
    $qstr = '';
    if($_POST['qstr']){
        parse_str($_POST['qstr'],$qstr);
        unset($qstr['pr_id']);
        $qstr = http_build_query($qstr);
    }
    if($qstr){
        $qstr = '&'.$qstr;
    }

    $return_url = $_SERVER['PHP_SELF'].'?pr_id='.$_POST['pr_id'].$qstr;

    alert('카테고리 순서 변경이 완료되었습니다.',$return_url);

    exit;
}


if(!$_GET['pr_id']){
    alert('잘못된 경로로 접근하였습니다.');
}

$pr = sql_fetch("select * from yc4_promotion where pr_id = '{$_GET['pr_id']}'");
if(!$pr){
    alert('잘못된 경로로 접근하였습니다.');
}

$pr_category_sql = sql_query("
    select 
    pc.pr_id, pc.pr_ca_id, pc.pr_ca_name, pc.sort, pc.create_dt,
    count(pi.it_id) as item_cnt
    from 
    yc4_promotion_category pc
    left join
    yc4_promotion_item pi on pc.pr_id = pi.pr_id and pc.pr_ca_id = pi.pr_ca_id
    where 
    pc.pr_id = '{$pr['pr_id']}'
     GROUP BY pc.pr_id, pc.pr_ca_id, pc.pr_ca_name, pc.sort, pc.create_dt
    order by ifnull(pc.sort,999) asc
");
$pr_category_list = array();
while ($row = sql_fetch_array($pr_category_sql)){
    $pr_category_list[] = $row;
}
$qstr = $pr_ca_id_qstr = $srot_qstr = $_GET;
unset($qstr['pr_id'],$pr_ca_id_qstr['pr_ca_id'],$pr_ca_id_qstr['pr_id'],$srot_qstr['pr_ca_id']);
$qstr = http_build_query($qstr);
$pr_ca_id_qstr = http_build_query($pr_ca_id_qstr);
$srot_qstr = http_build_query($srot_qstr);



define('bootstrap', true);
$g4['title'] = "프로모션 카테고리 순서 변경";
include '../admin.head.php';
?>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <style>
        .ui-state-highlight {
            height: 1.5em; line-height: 1.2em;
            border: 1px dotted black;
        }
        .ui-state-default{
            cursor: move;
        }
    </style>
    
    <form class="panel panel-default" method="post">
        <input type="hidden" name="mode" value="sort_update">
        <input type="hidden" name="pr_id" value="<?php echo $pr['pr_id'];?>">
        <input type="hidden" name="qstr" value="<?php echo htmlspecialchars($srot_qstr);?>">
        <div class="panel-body">
            <table class="table table-hover table-bordered table-condensed table-striped">
                <thead>
                <tr>
                    <td class="text-center">카테고리코드</td>
                    <td class="text-center">카테고리명</td>
                    <td class="text-center">상품 개수</td>
                    <td class="text-center">순서</td>
                    <td class="text-center">등록일</td>
                    
                </tr>
                </thead>
                <tbody id="sortable">
                <?php foreach ($pr_category_list as $row) { ?>
                    <tr class="ui-state-default">
                        <td class="pr_ca_id"><?php echo $row['pr_ca_id'];?></td>
                        <td><?php echo $row['pr_ca_name'];?></td>
                        <td class="text-right">
                            <a href="promotion_item_list.php?<?php echo $pr_ca_id_qstr;?>&pr_id=<?php echo $row['pr_id'];?>&pr_ca_id=<?php echo $row['pr_ca_id']?>">
                                <strong><?php echo number_format($row['item_cnt']);?></strong>
                            </a>

                        </td>
                        <td class="sortno">
                            <?php echo $row['sort'] ? $row['sort'] : '미지정';?>
                        </td>
                        <td><?php echo $row['create_dt'];?></td>

                    </tr>
                <?php }?>
                </tbody>
            </table>
        </div>
        <div class="panel-footer text-center">
            <!--<a href="promotion_list.php?<?php /*echo $qstr;*/?>" class="btn btn-default">프로모션 목록</a>-->
            <a href="promotion_category_list.php?<?php echo http_build_query($_GET);?>" class="btn btn-default">프로모션 카테고리 목록</a>
            <button type="submit" class="btn btn-primary">저장</button>
        </div>
    </form>

    <script>
        $(function(){
            sort_execute();
        });
        $( "#sortable" ).sortable({
            placeholder: "ui-state-highlight",
            update: function(){
                sort_execute();
            }
        });

        $( "#sortable" ).disableSelection();

        function sort_execute(){
            $('#sortable > tr.ui-state-default > td.sortno').each(function(k){
                var sort_no = k+1;
                var ca_pr_id = $(this).parent().find('.pr_ca_id').text().trim();
                var html = sort_no+'<input type="hidden" name="sort['+ca_pr_id+']" value="'+sort_no+'">';
                $(this).html(html);
            });
        }
    </script>
    

<?php
include '../admin.tail.php';
