<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-10-10
 * Time: 오후 4:16
 */
$sub_menu = "300124";
include_once "./_common.php";
include_once $g4['full_path'] . '/lib/db.php';
auth_check($auth[$sub_menu], "w");
if(!$_GET['upc'] || trim($_GET['upc'])==''){

    alert('잘못된 접근 방식입니다');
}

$set_upc_chk = sql_fetch("
            SELECT a.it_id
            FROM ople_mapping    a
                 INNER JOIN ople_mapping b
                    ON     a.it_id = b.it_id
                       AND a.upc = '".sql_safe_query(trim($_GET['upc']))."'
                       AND a.ople_type = 's'
                       AND b.ople_type = 's'
            GROUP BY a.it_id
            HAVING count(a.it_id) > 1
            LIMIT 1
            ");
if( !$set_upc_chk['it_id'] || trim($set_upc_chk['it_id']) == ''){
    alert('다중 상품  UPC 가 아닙니다');
}

$db = new db();

$stmt = $db->ntics_db->prepare("
            SELECT uid, single_amount
            FROM ople_set_amount_info a
            where upc =?
            ");
$stmt->bindValue(1,$_GET['upc']);
$stmt->execute();
$save_chk = $stmt->fetch(PDO::FETCH_ASSOC);


$stmt = $db->ntics_db->prepare("
SELECT b.upc,
       c.mfgname,
       concat (
          rtrim (b.item_name),
          CASE
             WHEN rtrim (isnull (b.POTENCY, '')) != ''
             THEN
                concat (' ', rtrim (b.POTENCY))
          END,
          CASE
             WHEN rtrim (isnull (b.POTENCY_UNIT, '')) != ''
             THEN
                concat (' ', rtrim (b.POTENCY_UNIT))
          END,
          CASE
             WHEN rtrim (isnull (b.count, '')) != ''
             THEN
                concat (' ', rtrim (b.count))
          END,
          CASE
             WHEN rtrim (isnull (b.type, '')) != ''
             THEN
                concat (' ', rtrim (b.type))
          END)
          item_name,
       b.currentqty,
       b.location
  FROM NTICS.dbo.N_MASTER_ITEM b
       INNER JOIN NTICS.dbo.n_mfg c ON b.MfgCD = c.mfgcd
 WHERE upc = ?
            ");
$stmt->bindValue(1,$_GET['upc']);
$stmt->execute();
$upc_info = $stmt->fetch(PDO::FETCH_ASSOC);



$g4[title] = "다중세트상품 단품가";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<form action="set_item_amount_save.php" method="post">
    <? if( $save_chk['uid']){ ?>
        <input name="uid" value="<?php echo $save_chk['uid'];?>" type="hidden">
    <?php } ?>
    <div class="list-group">
        <div class="list-group-item">
            <div class="list-group-item-heading"><h4>세트상품 관련 UPC 단품가 등록</h4></div>
            <div class="list-group-item-text">
                <table class="table">
                    <tr>
                        <th>UPC</th>
                        <td><?php echo $upc_info['upc']; ?>
                            <input name="upc" value="<?php echo $upc_info['upc'];?>" type="hidden">
                        </td>
                    </tr>
                    <tr>
                        <th>브랜드</th>
                        <td><?php echo $upc_info['mfgname']; ?></td>
                    </tr>
                    <tr>
                        <th>제품명</th>
                        <td><?php echo $upc_info['item_name']; ?></td>
                    </tr>
                    <tr>
                        <th>단품가</th>
                        <td>
                            <div class="input-group">
                                <div class="input-group-addon">$</div>
                                <input type="text" class="form-control" name="single_amount" value="<?php echo $save_chk['single_amount']; ?>">
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="list-group-item-text text-right">
                <button type="submit" class="btn btn-success" >저장</button>
                <button type="button" class="btn btn-primary" onclick="history.back();">목록</button>
            </div>
        </div>
    </div>
</form>
