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

if($_POST['mode'] == 'delete'){
    $json = array('error' => false);
    if (!$_POST['pr_id'] || !$_POST['pr_ca_id']){
        $json['error'] = '잘못된 경로로 접근하였습니다.';
    }
    /*$sql = sql_query("
        delete from yc4_promotion_category where pr_id = '{$_POST['pr_id']}' and pr_ca_id = '{$_POST['pr_ca_id']}'
    ");
    if(!$sql){
        alert('처리중 오류가 발생하였습니다. 관리자에게 문의 해 주세요.');
    }*/
    if(!$json['error']){
        //echo "delete from yc4_promotion_category where pr_id = '{$_POST['pr_id']}' and pr_ca_id = '{$_POST['pr_ca_id']}'".PHP_EOL;
        $sql = sql_query("
            delete from yc4_promotion_category where pr_id = '{$_POST['pr_id']}' and pr_ca_id = '{$_POST['pr_ca_id']}'
        ");
        if(!$sql){
            $json['error'] = '처리중 오류가 발생하였습니다. 관리자에게 문의 해 주세요.'.PHP_EOL.'카테고리 삭제오류';
        }

        // 해당 카테고리의 프로모션 상품 리스트 로드
        $sql = sql_query("select distinct it_id from yc4_promotion_item where pr_id = '{$_POST['pr_id']}' and pr_ca_id = '{$_POST['pr_ca_id']}'");
        $it_id_arr = array();
        $where = '';
        while ($row = sql_fetch_array($sql)){
            $it_id_arr[] = $row['it_id'];
        }

        $exists_it_id = array();
        if(count($it_id_arr) > 0){ // 해당 카테고리 상품 중 다른 카테고리에 등록된 상품이 있는지 체크

            $where .= " and it_id in ('".implode("','",$it_id_arr)."')";
            $chk = sql_query("
            select distinct dc.it_id 
            from 
            yc4_promotion_item i
            left join
            yc4_promotion_item_dc dc on i.pr_id = dc.pr_id and i.it_id = dc.it_id
            where i.pr_id = '{$_POST['pr_id']}' 
            and ifnull(i.pr_ca_id,'') <> '{$_POST['pr_ca_id']}' 
            and i.it_id <> '' 
            and i.it_id in ('".implode("','",$it_id_arr)."') 
            and dc.uid is not null
        ");

            while ($row = sql_fetch_array($chk)){
                if($row['it_id']){
                    $exists_it_id[] = $row['it_id'];
                }
            }
            if(count($exists_it_id) > 0){
                $where .= " and it_id not in ('".implode("','",$exists_it_id)."')";
            }

            $sql = sql_query("delete from yc4_promotion_item where pr_id = '{$_POST['pr_id']}' and pr_ca_id = '{$_POST['pr_ca_id']}'");
            if(!$sql){
                $json['error'] = '처리중 오류가 발생하였습니다. 관리자에게 문의 해 주세요.'.PHP_EOL.'대상상품 삭제 오류';
            }
            if(!$json['error']){
                $sql = sql_query("delete from yc4_promotion_item_dc where pr_id = '{$_POST['pr_id']}' {$where}");
                if(!$sql){
                    $json['error'] = '처리중 오류가 발생하였습니다. 관리자에게 문의 해 주세요.'.PHP_EOL.'대상상품 할인정보 삭제 오류';
                }
                if(!$json['error']){
                    $sql = sql_query("delete from yc4_promotion_item_dc_cache where pr_id = '{$_POST['pr_id']}' {$where}");
                    if(!$sql){
                        $json['error'] = '처리중 오류가 발생하였습니다. 관리자에게 문의 해 주세요.'.PHP_EOL.'대상상품 할인정보 캐시 삭재 오류';
                    }
                }
            }


            /*echo "delete from yc4_promotion_item where pr_id = '{$_POST['pr_id']}' and pr_ca_id = '{$_POST['pr_ca_id']}'".PHP_EOL;
            echo "delete from yc4_promotion_item_dc where pr_id = '{$_POST['pr_id']}' {$where}".PHP_EOL;
            echo "delete from yc4_promotion_item_dc_cache where pr_id = '{$_POST['pr_id']}' {$where}".PHP_EOL;*/

        }
    }

    header('Content-Type: application/json');
    echo json_encode($json);
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
    pc.pc_view,pc.mobile_view,
    count(pi.it_id) as item_cnt
    from 
    yc4_promotion_category pc
    left join
    yc4_promotion_item pi on pc.pr_id = pi.pr_id and pc.pr_ca_id = pi.pr_ca_id
    where 
    pc.pr_id = '{$pr['pr_id']}'
    GROUP BY pc.pr_id, pc.pr_ca_id, pc.pr_ca_name, pc.sort, pc.create_dt, pc.pc_view,pc.mobile_view
    order by ifnull(pc.sort,999) asc
");
$pr_category_list = array();
while ($row = sql_fetch_array($pr_category_sql)){
    $pr_category_list[] = $row;
}
$qstr = $pr_ca_id_qstr = $_GET;
unset($qstr['pr_id'],$pr_ca_id_qstr['pr_ca_id'],$pr_ca_id_qstr['pr_id']);
$qstr = http_build_query($qstr);
$pr_ca_id_qstr = http_build_query($pr_ca_id_qstr);



define('bootstrap', true);
$g4['title'] = "프로모션 카테고리 리스트";
include '../admin.head.php';
?>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    
    <div class="panel panel-default">
        <div class="panel-body">
            <table class="table table-hover table-bordered table-condensed table-striped">
                <thead>
                <tr>
                    <td class="text-center">카테고리코드</td>
                    <td class="text-center">카테고리명</td>
                    <td class="text-center">상품 개수</td>
                    <td class="text-center">노출</td>
                    <td class="text-center">순서</td>
                    <td class="text-center">등록일</td>
                    <td class="text-center">
                        <a href="promotion_category_write.php?<?php echo $pr_ca_id_qstr;?>&pr_id=<?php echo $pr['pr_id'];?>" ><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></a>
                    </td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($pr_category_list as $row) {
                    $device = '';
                    if ($row['pc_view'] == 'Y') {
                        $device .= ($device ? ', ':''). 'PC';
                    }
                    if ($row['mobile_view'] == 'Y') {
                        $device .= ($device ? ', ':''). '모바일';
                    }
                    if (!$device) {
                        $device = '미 노출';
                    }
                    ?>
                    <tr>
                        <td><?php echo $row['pr_ca_id'];?></td>
                        <td><?php echo $row['pr_ca_name'];?></td>
                        <td class="text-right">
                            <a href="promotion_item_list.php?<?php echo $pr_ca_id_qstr;?>&pr_id=<?php echo $row['pr_id'];?>&pr_ca_id=<?php echo $row['pr_ca_id']?>">
                                <strong><?php echo number_format($row['item_cnt']);?></strong>
                            </a>

                        </td>
                        <td><?php echo $device;?></td>
                        <td><?php echo $row['sort'] ? $row['sort']:'미지정';?></td>
                        <td><?php echo $row['create_dt'];?></td>
                        <td>
                            <a href="promotion_category_write.php?<?php echo $qstr;?>&pr_id=<?php echo $row['pr_id'];?>&pr_ca_id=<?php echo $row['pr_ca_id'];?>"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>
                            <span class="glyphicon glyphicon-remove btn btn-xs" aria-hidden="true" onclick="delete_promotion_category('<?php echo $row['pr_ca_id'];?>');"></span>
                        </td>
                    </tr>
                <?php }?>
                </tbody>
            </table>
        </div>
        <div class="panel-footer text-center">
            <a href="http://ople.com/mall5/shop/promotion.php?pr_id=<?php echo $pr['pr_id'];?>&preview=1" target="_blank" class="btn btn-default">프로모션 미리보기</a>
            <a href="promotion_list.php?<?php echo $qstr;?>" class="btn btn-default">프로모션 목록</a>
            <a href="promotion_category_sort.php?<?php echo http_build_query($_GET);?>" class="btn btn-primary">카테고리 노출 순서 변경</a>
        </div>
    </div>


    <script>
        function delete_promotion_category(pr_ca_id){
            if(!confirm('해당 프로모션의 카테고리를 삭제하시겠습니까?')){
                return false;
            }
            $.ajax({
                'url' : '<?php echo $_SERVER['PHP_SELF'];?>',
                'type' : 'post',
                'dataType' : 'json',
                'async' : true,
                'data' : {
                    'mode' : 'delete',
                    'pr_id' : '<?php echo $pr['pr_id'];?>',
                    'pr_ca_id' : pr_ca_id
                },
                'success' : function(json){
                    if(json['error']){
                        alert(json['error']);
                        return false;
                    }
                    alert('삭제가 완료되었습니다.');
                    location.reload();
                }
            });
        }
    </script>

<?php
include '../admin.tail.php';
