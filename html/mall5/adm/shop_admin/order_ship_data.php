<?php
/**
 * Created by PhpStorm.
 * File name : order_ship_data.php.
 * Comment :
 * Date: 2016-07-28
 * User: Minki Hong
 *
 */


$sub_menu = "400901";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

if($_POST){

    include $g4['full_path'].'/lib/db.php';

    if($_POST['od_id_arr']){
        $od_id_arr      =   explode(PHP_EOL, $_POST['od_id_arr']);
    }elseif($_POST['od_id']){
        $od_id_arr[]    =   $_POST['od_id'];
    }else{
        alert('처리할 주문이 존재하지 않습니다.');
        exit;
    }

    if(count($od_id_arr) < 1){
        alert('처리할 주문이 존재하지 않습니다.');
        exit;
    }
    $od_total_cnt   = 0;
    $od_success_cnt = 0;
    $od_fail_cnt    = 0;

    foreach($od_id_arr as $od_id){
        $od_total_cnt++;
        $od_id  = trim($od_id);
        // 주문서 정보 확인
        $od = array();
        $od = sql_fetch("select * from yc4_order where od_id = '".$od_id."'");
        if(!$od){
//            echo 'LINE : '.__LINE__.PHP_EOL;
            $od_fail_cnt++;
            continue;
        }
        // 상품 정보 확인
        $ct_sql = sql_query("select * from yc4_cart where on_uid = '{$od['on_uid']}' and ct_status = '준비'");
        $ct_arr = array();
        while ($row = sql_fetch_array($ct_sql)){
            $ct_arr[] = $row;
        }
        if(count($ct_arr) < 1 ){
            $od_fail_cnt++;
            continue;
        }
        // 배송전산 등록 확인
        $db = new db();
        $chk = $db->ntics_db->query("select count(*) as cnt from ntshipping.dbo.ns_s01 where od_id = '".$od_id."'")->fetchObject()->cnt;
        if($chk > 0){
            $od_fail_cnt++;
            continue;
        }

        $db->ntics_db->beginTransaction();
        // 주문서 확인 및 저장
        $ns_s01_insert_stmt = $db->ntics_db->prepare("
        insert into ntshipping.dbo.ns_s01
        ( site,  type, on_uid, od_id,  mb_id, od_email
        , od_name, od_tel, od_hp,  od_zip1,  od_zip2,  od_addr1, od_addr2
        , od_b_name,  od_b_tel, od_b_hp,  od_b_zip1,  od_b_zip2,  od_b_addr1, od_b_addr2
        , od_memo,  od_time,  od_ip,  od_jumin, od_receipt_card,  od_send_cost, od_receipt_bank,  od_receipt_point, status, cdate,  cuid)
        values
        (?,?,?,?,?,?,N?,?,?,?,?,N?,N?,N?,?,?,?,?,N?,N?,N?,?,?,?,?,?,?,?,?,?,?)
        ");

        $params = array(
            'OKFLEX',   'New',  $od['on_uid'],      $od['od_id'],   $od['mb_id'],       $od['od_email']
        ,   $od['od_name'],     $od['od_tel'],      $od['od_hp'],   $od['od_zip1'],     $od['od_zip2'],     $od['od_addr1'],    $od['od_addr2']
        ,   $od['od_b_name'],   $od['od_b_tel'],    $od['od_b_hp'], $od['od_b_zip1'],   $od['od_b_zip2'],   $od['od_b_addr1'],  $od['od_b_addr2']
        ,   $od['od_memo'],     $od['od_time'],     $od['od_ip'],   $od['od_b_jumin'],  $od['od_receipt_card']
        ,   $od['od_send_cost'],    $od['od_receipt_bank'], $od['od_receipt_point'],    '1',    date('Y-m-d'),  'admin'
        );
        if($ns_s01_insert_stmt->execute($params) === false){
            $db->ntics_db->rollBack();
            $od_fail_cnt++;
            continue;
        }
        // 상품정보 확인 및 저장
        $ct_fail_cnt    = 0;
        foreach ($ct_arr as $row) {
            if($ct_fail_cnt > 0){
                break;
            }
            $ct_chk = $db->ntics_db->query("select count(*) as cnt from ntshipping.dbo.ns_s02 where ct_id = '{$row['ct_id']}' and on_uid = '{$row['on_uid']}'")->fetchObject()->cnt;
            if($ct_chk > 0){
                $ct_fail_cnt++;
                break;
            }
            // s02 처리
            $ns_s02_insert_stmt = $db->ntics_db->prepare("
            insert into ntshipping.dbo.ns_s02 
            (ct_id,on_uid,it_id,ct_qty,ct_amount)
            VALUES
            (?,?,?,?,?)
            ");
            $params = array(
                $row['ct_id'],      $row['on_uid'],     $row['it_id']
            ,  $row['ct_qty'],     $row['ct_amount']
            );
            if($ns_s02_insert_stmt->execute($params) === false){
                $ct_fail_cnt++;
                break;
            }
            // s03 처리
            $s03_fail   = false;
            for($q = 1; $q <= $row['ct_qty']; $q++) {
                $insert_s03_insert_stmt = $db->ntics_db->prepare("
                insert into ntshipping.dbo.ns_s03 
                (ct_id, on_uid, it_id, id, upc, mfgname, itemdesc, size, wp, invoiceokname, invoiceokprice)
                select
                a.ct_id,a.on_uid,a.it_id,
                c.id, c.upc, c.mfgname, 
                isnull(c.itemdesc,'Special Order or Item not match! ask manager for this order') as itemdesc,
                c.size, c.wp, c.invoiceokname, c.invoiceokprice
                FROM
                ntshipping.dbo.ns_s02 a
                left join
                ntshipping.dbo.ns_o02 b on a.it_id = b.it_id
                left join
                ntshipping.dbo.ns_m01 c on b.id = c.id
                where
                a.on_uid = ? and a.ct_id = ?
                ");
                $params = array(
                    $row['on_uid'],
                    $row['ct_id'],
                );
                if ($insert_s03_insert_stmt->execute($params) === false) {
                    $s03_fail   = true;
                    break;
                }
            }
            if($s03_fail){
                break;
            }
        }
        if($ct_fail_cnt > 0){
            $db->ntics_db->rollBack();
            $od_fail_cnt++;
            continue;
        }
        $od_success_cnt++;
        $db->ntics_db->commit();
    }

    alert('배송전산 등록작업이 완료되었습니다.('.$od_total_cnt.'개 주문중 성공 : '.$od_success_cnt.'개, 실패 : '.$od_fail_cnt.'개)',$_SERVER['PHP_SELF']);
}

$insert_fg = false;
$list_html = '';
if($_GET['od_id']){
    include $g4['full_path'].'/lib/db.php';
    $db = new db();
    $od = sql_fetch("select * from yc4_order where od_id = '{$_GET['od_id']}'");
    $ct_data = array();

    $ople_html = '';
    $shipping_html = '';

    if($od){
        $ct_sql = sql_query("select a.ct_id,a.it_id,a.ct_qty,a.ct_amount,a.ct_amount_usd,a.ct_status,b.it_name from yc4_cart a left join yc4_item b on a.it_id = b.it_id where a.on_uid = '{$od['on_uid']}' order by a.ct_id");
        $ct_html = '';
        while ($row = sql_fetch_array($ct_sql)){
            $ct_html .= '
            <tr>
                <td>'.get_it_image($row['ct_id'].'_s',50,50,null,null,null,false).'</td>
                <td>'.$row['it_id'].'</td>
                <td>'.get_item_name($row['it_name'],'list').'</td>
                <td>'.$row['ct_status'].'</td>
                <td>'.$row['ct_qty'].'</td>
                <td> $ '.number_format($row['ct_amount_usd'],2).'<br/>(￦ '.number_format($row['ct_amount']).')</td>
                <td> $ '.number_format($row['ct_amount_usd'] * $row['ct_qty'],2).'<br/>(￦ '.number_format($row['ct_amount'] * $row['ct_qty']).')</td>
            </tr>
            ';
        }
        if(!$ct_html){
            $ct_html = '<td colspan="6" class="text-center">주문 내역이 없습니다.</td>';
        }

        $ople_html .= '
            <div class="panel panel-primary">
                <div class="panel-heading">'.$od['od_id'].'</div>
                <table class="table table-condensed">
                <thead>   
                    <tr>
                        <td></td>
                        <td>IT_ID</td>
                        <td>상품명</td>
                        <td>상태</td>
                        <td>수량</td>
                        <td>가격</td>
                        <td>소계</td>
                    </tr>
                </thead>
                <tbody>
                    '.$ct_html.'
                </tbody>
                </table>
            </div>
        ';
    }

    $shipping_od = $db->ntics_db->query("select * from ntshipping.dbo.ns_s01 where od_id = '{$od['od_id']}'")->fetch(PDO::FETCH_ASSOC);
    if($shipping_od){
        $shipping_ct_data = $db->ntics_db->query("
            select 
                rtrim(a.ct_id) as ct_id, 
                rtrim(a.on_uid) as on_uid, 
                rtrim(a.it_id) as it_id, 
                rtrim(a.upc) as upc, 
                rtrim(a.invoiceokname) as invoiceokname, 
                rtrim(a.shippingcode) as shippingcode,
                c.status,
                c.weight,
                count(*) as qty 
            from 
                ntshipping.dbo.ns_s03 a
                left JOIN 
                ntshipping.dbo.ns_s01 b on a.on_uid = b.on_uid
                left JOIN 
                ntshipping.dbo.ns_invoice c on a.shippingcode = c.cjnum and 'k'+b.od_id = c.ordercode
            where a.on_uid = '{$od['on_uid']}'
            group by a.ct_id, a.on_uid, a.it_id, a.upc, a.invoiceokname, a.shippingcode, c.weight,c.status
            order by a.shippingcode,a.ct_id
        ")->fetchAll(PDO::FETCH_ASSOC);
        $shipping_ct_html = '';
        foreach ($shipping_ct_data as $row) {
            $row_status = '';
            if(!trim($row['shippingcode'])){
                $row_status = '미 작업';
            }elseif(strlen(trim($row['shippingcode'])) != 13){
                $row_status = '취소';
            }elseif($row['weight']){
                $row_status = '무게 측정 완료';
            }else{
                $row_status = '무게 미입력';
            }
            $shipping_ct_html .= "
                <tr>
                    <td>".$row['upc']."</td>
                    <td>".$row['it_id']."</td>
                    <td>".$row['qty']."</td>
                    <td>".$row['invoiceokname']."</td>
                    <td>".$row['shippingcode']."</td>
                    <td>".$row_status."</td>  
                </tr>
            ";
        }

        $shipping_html .= '
            <div class="panel panel-primary">
                <div class="panel-heading">'.$shipping_od['od_id'].'</div>
                <table class="table table-condensed">
                <thead>   
                    <tr>
                        <td>UPC</td>
                        <td>IT_ID</td>
                        <td>수량</td>
                        <td>상품명</td>
                        <td>송장번호</td>
                        <td>상태</td>
                    </tr>
                </thead>
                <tbody>
                    '.$shipping_ct_html.'
                </tbody>
                </table>
            </div>
        ';
    }else{
        $insert_fg = true;
    }


    if(!$shipping_od){
        $shipping_html = '배송 데이터가 존재하지 않습니다.';
    }
    if(!$ople_html){
        $ople_html = '주문 데이터가 존재하지 않습니다.';
    }



    $ople_html = '<td>'.$ople_html.'</td>';
    $shipping_html = '<td>'.$shipping_html.'</td>';

    $row_html = $ople_html . $shipping_html;

    $row_html = '<tr>'.$row_html.'</tr>';

    $list_html .= $row_html;

}



$g4[title] = "주문 배송 데이터 등록";
define('bootstrap', true);
include_once $g4['admin_path']."/admin.head.php";
?>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<div class="panel panel-default">
    <form class="panel-heading form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" onsubmit="return insert_orders_confirm(this);">
        <div class="row">
            <div class="form-group">
                <div class="col-lg-12">
                    <label for="od_id" class="control-label col-lg-2">주문번호<br/><small>(엔터로 구분됩니다.)</small></label>
                    <div class="col-lg-8">
                        <textarea name="od_id_arr" class="form-control" rows="10"></textarea>
                    </div>
                    <div class="col-lg-2">
                        <button class="btn btn-primary">일괄발송처리</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <form class="panel-heading form-horizontal" method="get" action="<?php echo $_SERVER['PHP_SELF'];?>">
        <div class="row">
            <div class="form-group">
                <div class="col-lg-12">
                    <label for="od_id" class="control-label col-lg-2">주문번호</label>
                    <div class="col-lg-10">
                        <div class="input-group">
                            <input type="text" name="od_id" class="form-control" value="<?php echo htmlspecialchars($_GET['od_id'])?>">
                            <div class="input-group-btn">
                                <button class="btn btn-primary">검색</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php if($od) { ?>
	<table class="table table-hover table-condensed">
        <col width="50%">
        <col width="50%">
		<thead>
			<tr>
				<th>오플</th>
				<th>배송</th>
			</tr>
		</thead>
		<tbody>
			<?php echo $list_html;?>
		</tbody>
	</table>
    <?php if($insert_fg) { ?>
    <form class="panel-footer" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" onsubmit="return shipping_insert_confirm(this);">
        <input type="hidden" name="od_id" value="<?php echo $_GET['od_id']?>">
        <button type="submit" class="btn btn-primary">배송 데이터 저장</button>
    </form>
    <?php } ?>
    <?php } ?>
</div>

    <script>
        function shipping_insert_confirm(f){
            if(!confirm('해당 주문을 배송 데이터에 입력하시겠습니까?\n((상태가 \'준비\'인 상품만 입력 됩니다.)')){
                return false;
            }

            return true;
        }
        function insert_orders_confirm(f){
            if(!confirm('해당 주문을 배송 데이터에 입력하시겠습니까?\n((상태가 \'준비\'인 상품만 입력 됩니다.)')){
                return false;
            }
            $(f.)

            return true;
        }
    </script>

<?php include_once $g4['admin_path']."/admin.tail.php";;
