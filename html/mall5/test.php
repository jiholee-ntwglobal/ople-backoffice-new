<?php

//include "_common.php";
include_once './_common.php';
include_once './lib/db.php';


print_r($_SESSION);

$it_id = '1320764100';
$fg = 'Y';
$mb_id = $member['mb_id'];
$soldout_history_fnc = function($it_id,$fg,$mb_id){
    if(!in_array($fg,array('Y','N'))){
        return false;
    }

    if(!$mb_id){
        $mb_id = $_SESSION['ss_mb_id'];
    }
    if(!$mb_id){
        return false;
    }

    $db = new db();
    $ntics_stmt =  $db->ntics_db->prepare("select a.upc,b.currentqty from ople_mapping a left join N_MASTER_ITEM b on a.upc = b.upc where a.it_id = ? and b.upc is not null");
    $ntics_stmt->execute(array($it_id));
    if($ntics_stmt === false){
        return false;
    }
    $ntics_data = $ntics_stmt->fetch(PDO::FETCH_ASSOC);
    if(!trim($ntics_data['upc'])){
        return false;
    }
    $params = array('OPLE',$ntics_data['upc'],$it_id,$fg,'OPLE-'.$mb_id,$ntics_data['currentqty']);
    $db->ntics_db->beginTransaction();
    $insert_stmt = $db->ntics_db->prepare("insert into soldout_proc_history (channel,upc,it_id,flag,create_dt,create_id,ntics_qty) VALUES (?,?,?,?,FORMAT ( GETDATE(), 'yyyyMMddHHmmss'),?,?)");
    if($insert_stmt->execute($params) === false){
        return false;
    }
    $uid = $db->ntics_db->lastInsertId();
    if(!$uid){
        return false;
    }

    $db->ntics_db->rollBack();
    return true;
};

var_dump($soldout_history_fnc($it_id,$fg,$mb_id));