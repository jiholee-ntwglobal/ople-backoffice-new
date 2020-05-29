<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-10-10
 * Time: 오후 4:16
 */
$sub_menu = "300125";
include_once "./_common.php";
include_once $g4['full_path'] . '/lib/db.php';
auth_check($auth[$sub_menu], "w");

if($_POST['mode'] =='update'){

    if(count($_POST['it_ids']) < 1){
        alert('1개 이상 체크 해주세요');
    }

    $update_arr =  array(
        'discontinued' => "it_discontinued = '1'",
        'received' => "it_discontinued = '0'",
        'salestop' => "it_use = '0'",
        'sale' => "it_use = '1'",
        'soldout' => "it_stock_qty = '0'",
        'instock' => "it_stock_qty = '99999'"
    );

    if(!array_key_exists($_POST['status_update'],$update_arr)){
        alert('개발팀의 문의해주세요 1');
    }

    foreach ($_POST['it_ids'] as $it_id){
        $where_in .= ",'".sql_safe_query(trim($it_id))."'";
    }
    $where_in = substr($where_in ,1);

    if($where_in ==''){
        alert('개발팀의 문의 주세요 ');
    }

    //ople
    sql_query("
    update yc4_item set {$update_arr[$_POST['status_update']]} where it_id in ($where_in)
    ");

    //11번가
    $openmarket_11st = new PDO('mysql:host=115.68.114.153;dbname=MANAGE11ST', 'neiko', 'rsmaker@ntwglobal');
    $openmarket_11st->query("update yc4_item set {$update_arr[$_POST['status_update']]} where it_id in ($where_in)");

    foreach ($_POST['it_ids'] as $it_id) {
        //history
        sql_query("
    INSERT INTO item_status_batch_processing(it_id,
                                         status,
                                         date,
                                         id)
    VALUES ('$it_id',
            '{$_POST['status_update']}',
            now(),
            '{$member['mb_id']}'
            )
    ");
    }

    /* 일괄 품절, 품절해제 시 히스토리 저장 로직 추가 - 2018.02.06 rsmaker@ntwglobal.com */
    if(in_array($_POST['status_update'], array('soldout', 'instock'))){

        $soldout_flag = $_POST['status_update'] == 'soldout' ? 'o' : 'i';
        $soldout_fg = $_POST['status_update'] == 'soldout' ? 'Y' : 'N';

        include_once $g4['full_path'].'/lib/db.php';
        $db = new db();

        foreach ($_POST['it_ids'] as $it_id) {

            sql_query("update yc4_soldout_history set current_fg='N' where it_id='".$it_id."' and current_fg='Y'");

            sql_query("
			insert into
				yc4_soldout_history
			(
				it_id,flag,mb_id,time,ip,current_fg
			)values(
				'".$it_id."','".$soldout_flag."','".$member['mb_id']."','".$g4['time_ymdhis']."','".$_SERVER['REMOTE_ADDR']."','Y'
			)
		");

            $ntics_stmt =  $db->ntics_db->prepare("select a.upc,b.currentqty from ople_mapping a left join N_MASTER_ITEM b on a.upc = b.upc where a.it_id = ? and b.upc is not null");
            $ntics_stmt->execute(array($it_id));
            if($ntics_stmt === false){
                continue;
            }
            $ntics_data = $ntics_stmt->fetch(PDO::FETCH_ASSOC);
            if(!trim($ntics_data['upc'])){
                continue;
            }
            $params = array('OPLE',$ntics_data['upc'],$it_id,$soldout_fg,'OPLE-'.$member['mb_id'],$ntics_data['currentqty']);
//		$db->ntics_db->beginTransaction();
            $insert_stmt = $db->ntics_db->prepare("insert into soldout_proc_history (channel,upc,it_id,flag,create_dt,create_id,ntics_qty) VALUES (?,?,?,?,FORMAT ( GETDATE(), 'yyyyMMddHHmmss'),?,?)");
            if($insert_stmt->execute($params) === false){
                continue;
            }
            $uid = $db->ntics_db->lastInsertId();
            if(!$uid){
                continue;
            }

        }

    }

    alert('처리 되었습니다','./item_status_change.php?'.$_POST['get_url']);
}

//alert('개발팀의 문의해주세요 2');
