<?php
/**
 * Created by PhpStorm.
 * User: DEV_4
 * Date: 2016-12-01
 * Time: 오후 3:06
 */

include_once("./_common.php");
include_once $g4['full_path'] . '/lib/ople_mapping.php';

$ople_mapping = new ople_mapping();
//upc_chk

$escape=array("'",'"','\\');

$it_name = trim($_POST['it_name']) != '' ?str_replace($escape,'',trim($_POST['it_name'])):''; // 상품명
$qty_k = trim($_POST['qty_k'])!='' ? str_replace($escape,'',trim($_POST['qty_k'])):''; //가격\
$qty_u = trim($_POST['qty_u']) !=''? str_replace($escape,'',trim($_POST['qty_u'])):'';//가격$
$health = $_POST['health'] !='' ? str_replace($escape,'',trim($_POST['health'])):'0'; //건기식
$stock_qty = isset($_POST['stock_qty']) ? '0':'9999'; //품절 여부
$clearance = isset($_POST['clearance']) ? 'Y':''; //목록통관
$it_origin = trim($_POST['it_origin']) !='' ? str_replace($escape,'',trim($_POST['it_origin'])):''; //원산지
$onetime_limit_cnt = is_numeric(trim($_POST['onetime_limit_cnt']))  ? str_replace($escape,'',trim($_POST['onetime_limit_cnt'])):0; //1회구매수량

if(!$it_name){
//튕기기  상품명
    alert('상품명을 입력해주세요','');
}
if(!$qty_k || !$qty_u){
//튕기기 가격 원
    alert('가격을 입력해주세요','');
}

if( !is_numeric($qty_k) || !is_numeric($qty_u)){
    alert('가격을 입력해주시기 바랍니다');
}
if($clearance =='Y' && $health >0 ){
    alert('목록통관 체크및 건기식 병수가 수량이 입력 되었습니다 확인 부탁드립니다.');;
}
if( !is_numeric($health)){
    $health = '0';
}
$stock_qty=$stock_qty != '9999' ?'0':$stock_qty;
$max_itid = sql_fetch("select max(it_id ) m from yc4_item limit 1");
if(!isset($max_itid['m'])){
    //튕기기 맥스값이 없다면
    alert('오플상품코드를 불러오지 못했습니다','');
}
$new_it_id = trim($max_itid['m']) +100;
// 새로운 it_id 있는지 없는지 검사
$new_cnt = sql_fetch("select count(*) cnt from yc4_item where it_id ='{$new_it_id}'");
if($new_cnt['cnt']>0){
    //튕기거나 다시 새로운 it_id 생성
    alert('새로운 상품코드가 존재합니다 다시해주세요','');
}else{
   /* echo "INSERT INTO yc4_item
(

it_id, it_name, it_amount, it_use, it_time,
it_ip, it_discontinued, it_health_cnt, it_amount_usd, it_stock_qty,
it_gallery , it_type1 ,	it_type2 ,	it_type3 ,	it_type4 ,
it_type5 ,	it_explan ,	it_explan_html ,	it_cust_amount ,	it_amount2 ,
it_amount3 ,	it_point ,	it_hit , it_order ,	it_tel_inq ,
SKU ,	it_order_onetime_limit_cnt ,	it_bottle_count ,	it_cust_amount_usd ,	ps_cnt
,list_clearance, it_origin,it_create_time
)
VALUES
(
'{$new_it_id}', '{$it_name}', '{$qty_k}', 1, now(),
'{$_SERVER['REMOTE_ADDR']}', 0, '{$health}', '{$qty_u}', {$stock_qty},
0,  0,	0,	0,	0,
0, '',	0,	0,	0,
0,	0,	0,  0,	0,
'',	{$onetime_limit_cnt},	0,	0,	0,
'{$clearance}', '{$it_origin}',now()
)";*/
    //insert
 sql_query("INSERT INTO yc4_item
(

it_id, it_name, it_amount, it_use, it_time,
it_ip, it_discontinued, it_health_cnt, it_amount_usd, it_stock_qty,
it_gallery , it_type1 ,	it_type2 ,	it_type3 ,	it_type4 ,
it_type5 ,	it_explan ,	it_explan_html ,	it_cust_amount ,	it_amount2 ,
it_amount3 ,	it_point ,	it_hit , it_order ,	it_tel_inq ,
SKU ,	it_order_onetime_limit_cnt ,	it_bottle_count ,	it_cust_amount_usd ,	ps_cnt
,list_clearance, it_origin,it_create_time
)
VALUES
(
'{$new_it_id}', '{$it_name}', '{$qty_k}', 1, now(),
'{$_SERVER['REMOTE_ADDR']}', 0, '{$health}', '{$qty_u}', {$stock_qty},
0,  0,	0,	0,	0,
0, '',	0,	0,	0,
0,	0,	0,  0,	0,
'',	{$onetime_limit_cnt},	0,	0,	0,
'{$clearance}', '{$it_origin}',now()
)");
    //insert 한 it_id 확인 및 데이터 가지고 와서
    $insert_it_id_cnt = sql_fetch("select count(*) cnt from yc4_item where it_id ='{$new_it_id}'");
    if($insert_it_id_cnt<=0){
        // it_id 가 만들어지지 않았으니 튕기기
        alert('오플상품코드가 만들어지지 않았습니다. 다시해주세요','');
    }
    $it_id=$new_it_id;

   /* echo "

INSERT INTO set_item
(
it_id,mb_id
)
VALUES
(
'{$it_id}', '{$_SESSION['ss_mb_id']}'
)";*/
    sql_query("

INSERT INTO set_item
(
it_id,mb_id
)
VALUES
(
'{$it_id}', '{$_SESSION['ss_mb_id']}'
)");
    $insert_it_id_cnt = sql_fetch("select count(*) cnt from set_item where it_id ='{$it_id}'");
    if($insert_it_id_cnt<=0){
        // it_id 가 만들어지지 않았으니 튕기기
        alert('오플상품코드가 만들어지지 않았습니다. 다시해주세요.','');
    }
    // ople _mapping
    $upc_arr = array();
    if(is_array($upc)){
        foreach ($upc as $key => $row_upc) {
            $upc_arr[$key]['upc'] = trim($row_upc);
        }
    }
    if(is_array($qty)){
        foreach ($qty as $key => $row_qty) {
            if(!$row_qty){
                $row_qty = 1;
            }
            $upc_arr[$key]['qty'] = trim($row_qty);
        }
    }
    // 생성한 it_id
    $result = $ople_mapping->ople_mapping($it_id,$upc_arr);
    if($result === true){
        echo "
            <script>
                var url = '".$g4['shop_admin_path']."/set_item_view.php.php';
                if(confirm('상품이 생성 되었습니다. 확인하시겠습니까?')){
                    url = '".$g4['shop_admin_path']."/item_mapping_edit.php?it_id=".$it_id."';
                }
                location.href=url;
            </script>
        ";
    }else{
        alert('처리중 오류 발생! 관리자에게 문의해 주세요.');
    }
    alert('상품이 생성 되었습니다.','./set_item_view.php');
    exit;
}
exit;
