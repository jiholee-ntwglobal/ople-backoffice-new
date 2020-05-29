<?php
/**
 * Created by PhpStorm.
 * File name : promotion_item_list.php.
 * Comment :
 * Date: 2016-05-19
 * User: Minki Hong
 */

$sub_menu = "500500";
include './_common.php';
auth_check($auth[$sub_menu], "r");


// 엑셀 파일 업로드 시작
if($_POST['mode'] == 'excel_upload'){
    include $g4['full_path'] . '/classes/PHPExcel.php';


    $UpFile = $_FILES["excel_file"];
    $UpFileName = $UpFile["name"];

    $UpFilePathInfo = pathinfo($UpFileName);
    $UpFileExt = strtolower($UpFilePathInfo["extension"]);


    if ($UpFileExt != "xls" && $UpFileExt != "xlsx") {
        echo "엑셀파일만 업로드 가능합니다. (xls, xlsx 확장자의 파일포멧)";
        exit;
    }

    //업로드된 엑셀파일을 서버의 지정된 곳에 옮기기 위해 경로 적절히 설정
    $upload_path = $g4["full_path"] . "/adm/shop_admin/admin_upload";
    $upfile_path = $upload_path . "/promotion_item_{$_POST['pr_id']}_" . date("Ymd_His") . "_" . $UpFileName;

    if (is_uploaded_file($UpFile["tmp_name"])) {

        if (!move_uploaded_file($UpFile["tmp_name"], $upfile_path)) {
            echo "업로드된 파일을 옮기는 중 에러가 발생했습니다.";
            exit;
        }

        //파일 타입 설정 (확자자에 따른 구분)
        $inputFileType = 'Excel2007';
        if ($UpFileExt == "xls") {
            $inputFileType = 'Excel5';
        }

        //엑셀리더 초기화
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);

        //데이터만 읽기(서식을 모두 무시해서 속도 증가 시킴)
        $objReader->setReadDataOnly(true);



        //업로드된 엑셀 파일 읽기
        $objPHPExcel = $objReader->load($upfile_path);

        //첫번째 시트로 고정
        $objPHPExcel->setActiveSheetIndex(0);

        //고정된 시트 로드
        $objWorksheet = $objPHPExcel->getActiveSheet();

        //시트의 지정된 범위 데이터를 모두 읽어 배열로 저장
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
        $total_rows = count($sheetData);

        $i = 0;
        /*
         *
            [A] => 프로모션카테고리코드(선택)
            [B] => 오플상품코드
            [C] => 아이콘코드(선택)
            [D] => 할인가격 USD(선택)
            [E] => 할인시작일(선택)
            [F] => 할인종료일(선택)
         * */
        $pr_it_arr = array();
        $filter_sheetData = array();
        foreach ($sheetData as $rows_key => $rows) {
            if($rows_key <= 1){
                continue;
            }
            array_walk($rows, function (&$item) {
                if(is_string($item)){
                    $item = trim($item);
                }
            });

            if(!$rows['B']){
                continue;
            }
            
            // 상품코드 확인
            $it_id_chk = sql_fetch("select count(*) as cnt from yc4_item where it_id = '{$rows['B']}'");
            if($it_id_chk['cnt'] < 1){
                continue;
            }

            $pr_ca_id = $rows['A'];
            if(!$pr_ca_id){
                $pr_ca_id = 0;
            }else{
                // 카테고리 코드 확인
                $pr_ca_chk = sql_fetch("select count(*) as cnt from yc4_promotion_category where pr_id = '{$_POST['pr_id']}' and pr_ca_id = '{$rows['A']}'");
                if($pr_ca_chk['cnt'] < 1){
                    continue;
                }
            }
            if(!isset($pr_it_arr[$pr_ca_id])){
                $pr_it_arr[$pr_ca_id] = array();
            }
            if(!in_array($rows['B'],$pr_it_arr[$pr_ca_id])){
                $pr_it_arr[$pr_ca_id][] = $rows['B'];
                $filter_sheetData[] = $rows;
            }
        }
        unset($sheetData);
        
        // 기존 데이터 삭제
        $delete_sql = "delete from yc4_promotion_item where pr_id = '{$_POST['pr_id']}'";
        $delete_rs = sql_query($delete_sql);
        $delete_dc_sql = "delete from yc4_promotion_item_dc where pr_id = '{$_POST['pr_id']}'";
        $delete_dc_rs = sql_query($delete_dc_sql);
        foreach ($filter_sheetData as $rows_key => $rows) {

            array_walk($rows, function (&$item) {
                if(is_string($item)){
                    $item = trim($item);
                }
            });
            $sort_no = $rows_key +1;

            $insert_sql = "
                insert into yc4_promotion_item 
                ( pr_id, it_id, sort, create_dt, ip, mb_id )
                VALUES 
                ( '{$_POST['pr_id']}', '{$rows['B']}', '{$sort_no}', now(), '{$_SERVER['REMOTE_ADDR']}', '{$member['mb_id']}' )
            ";
            $insert_rs = sql_query($insert_sql);
            $uid = mysql_insert_id();

            $update_set = '';
            // 카테고리 정보 저장
            if($rows['A']){
                $update_set .= ($update_set ? ',':'')."pr_ca_id = '{$rows['A']}'";
            }
            // 아이콘 정보 저장
            if($rows['C']){
                $update_set .= ($update_set ? ',':'')."icon = '{$rows['C']}'";
            }
            if($update_set){
                $update_sql = "update yc4_promotion_item set {$update_set} where uid = '{$uid}'";
                $update_rs = sql_query($update_sql);
            }

            if($rows['D']){ // 상품 할인정보
                // 동일 프로모션 할인정보 중복등록 방지
                // 중복될 경우 위에있는 할인정보만 저장
                $dc_check = sql_fetch("select count(*) as cnt from yc4_promotion_item_dc where it_id = '{$rows['B']}' and pr_id = '{$_POST['pr_id']}'");
                if($dc_check['cnt'] < 1){
                    $dc_insert_sql = "
                        insert into yc4_promotion_item_dc 
                        ( it_id, amount_usd, pr_id, comment, create_dt, ip, mb_id )
                        VALUES 
                        ( '{$rows['B']}', '{$rows['D']}', '{$_POST['pr_id']}', '프로모션상품등록 엑셀파일업로드 {$g4['time_ymdhis']}', now(), '{$_SERVER['REMOTE_ADDR']}', '{$meber['mb_id']}' )
                    ";
                    $dc_insert_rs = sql_query($dc_insert_sql);
                    $dc_uid = mysql_insert_id();
                    $dc_update_set = '';
                    if($rows['E']){
                        $dc_update_set .= ($dc_update_set ? ',':'') . "st_dt = '{$rows['E']}'";
                    }
                    if($rows['F']){
                        $dc_update_set .= ($dc_update_set ? ',':'') . "en_dt = '{$rows['F']}'";
                    }
                    if($dc_update_set){
                        $dc_update_sql = " update yc4_promotion_item_dc set {$dc_update_set} where uid = '{$dc_uid}' ";
                        $dc_update_rs = sql_query($dc_update_sql);
                    }
                }
                
            }
        }

        get_headers('http://ople.com/mall5/cron/promotion_price_update.php');

        $return_url = $_SERVER['PHP_SELF'].'?pr_id='.$_POST['pr_id'];
        alert('프로모션 상품 등록이 완료되었습니다.',$return_url);
    }



    print_r2($_POST);

    exit;

}
// 엑셀 파일 업로드 끝

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
        distinct 
        ci.uid,
        ci.pr_id,
        ci.pr_ca_id,
        ci.it_id,
        ci.icon,
        ci.sort,
        ci.create_dt,
        ci.ip,
        ci.mb_id,
        c.pr_ca_name,
        p.pr_name,
        i.it_name,
        i.it_amount,
        i.it_amount_usd,
        cidc.st_dt as pr_dc_st_dt,
        cidc.en_dt as pr_dc_en_dt,
        cidc.amount_usd as pr_dc_amount_usd,
        idc.st_dt as it_dc_st_dt,
        idc.en_dt as it_dc_en_dt,
        idc.amount_usd as dc_amount_usd
    from 
        yc4_promotion_item ci
        left join
        yc4_item i on ci.it_id = i.it_id
        left join
        yc4_promotion p on ci.pr_id = p.pr_id
        left join
        yc4_promotion_category c on ci.pr_id = c.pr_id and ci.pr_ca_id = c.pr_ca_id
        left join
        yc4_promotion_item_dc cidc on ci.it_id = cidc.it_id and ci.pr_id = cidc.pr_id
        left join
        yc4_promotion_item_dc idc on ci.it_id = idc.it_id and idc.pr_id is null
    where 
        ci.pr_id = '{$pr['pr_id']}'
        {$where}
    order by 
        ci.pr_id,
        ifnull(ci.sort,999) asc,
        ifnull(c.sort,999) asc,
        ci.pr_ca_id
");
$pr_item_list = array();
while ($row = sql_fetch_array($pr_item_sql)){
    $pr_item_list[] = $row;
}


if($_GET['mode'] == 'excel_down'){
    include $g4['full_path'] . '/classes/PHPExcel.php';
    $objPHPExcel = new PHPExcel();
    $excel_title = '오플 프로모션 상품_'.$_GET['pr_id'].'_'.date('Y-m-d');
    $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
        ->setTitle($excel_title)
        ->setSubject($excel_title)
        ->setDescription($excel_title);

    $objPHPExcel->getActiveSheet()->getCell('A1')->setValueExplicit('프로모션카테고리코드(선택)', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('B1')->setValueExplicit('오플상품코드', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('C1')->setValueExplicit('아이콘코드(선택)', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('D1')->setValueExplicit('할인가격 USD(선택)', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('E1')->setValueExplicit('할인시작일(선택)', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('F1')->setValueExplicit('할인종료일(선택)', PHPExcel_Cell_DataType::TYPE_STRING);
    $no = 1;
    foreach ($pr_item_list as $row) {
        $no++;
        $objPHPExcel->getActiveSheet()->getCell('A'.$no)->setValueExplicit($row['pr_ca_id'] ? $row['pr_ca_id'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('B'.$no)->setValueExplicit($row['it_id'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('C'.$no)->setValueExplicit($row['icon'] ? $row['icon'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('D'.$no)->setValueExplicit($row['pr_dc_amount_usd'] ? $row['pr_dc_amount_usd'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('E'.$no)->setValueExplicit($row['pr_dc_st_dt'] ? $row['pr_dc_st_dt'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('F'.$no)->setValueExplicit($row['pr_dc_en_dt'] ? $row['pr_dc_en_dt'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
    }

    $objPHPExcel->getActiveSheet()->setTitle($excel_title);

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
//$objPHPExcel->setActiveSheetIndex(0);

// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
    $filename = iconv("UTF-8", "EUC-KR", $excel_title);

// Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;

}

$qstr = $pr_ca_qstr = $_GET;
unset($qstr['pr_id'],$pr_ca_qstr['pr_ca_id']);
$qstr = http_build_query($qstr);
$pr_ca_qstr = http_build_query($pr_ca_qstr);


# 프로모션 코드 정보 로드 시작 #
// 프로모션 로드
$pr_info_sql = sql_query("select pr_id,pr_name,st_dt,en_dt from yc4_promotion order by pr_id desc");
$pr_list_arr = array();
while ($row = sql_fetch_array($pr_info_sql)){
    $pr_list_arr[] = $row;
}

// 프로모션 카테고리 로드
$pr_ca_info_sql = sql_query("select pr_id,pr_ca_id,pr_ca_name,sort from yc4_promotion_category order by pr_id desc, sort asc");
$pr_ca_info_arr = array();
while($row = sql_fetch_array($pr_ca_info_sql)){
    if(!isset($pr_ca_info_arr[$row['pr_id']])){
        $pr_ca_info_arr[$row['pr_id']] = array();
    }
    $pr_ca_info_arr[$row['pr_id']][] = $row;
}
# 프로모션 코드 정보 로드 끝 #




define('bootstrap', true);
$g4['title'] = "프로모션 상품 관리";
include '../admin.head.php';
?>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <div class="row">
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
                        <td class="text-center" rowspan="3">순서</td>
                        <td class="text-center" rowspan="3">프로모션<br/>(코드)</td>
                        <td class="text-center" rowspan="3">카테고리<br/>(코드)</td>
                        <td class="text-center" rowspan="3" colspan="2">상품명</td>
                        <td class="text-center" rowspan="3">상품가격</td>
                        <td class="text-center" colspan="4">할인정보</td>
                        <td class="text-center" rowspan="3">아이콘</td>
<!--                        <td class="text-center" rowspan="3">등록일</td>-->
                    </tr>
                    <tr>
                        <td class="text-center" colspan="2">프로모션</td>
                        <td class="text-center" colspan="2">일반할인</td>
                    </tr>
                    <tr>
                        <td class="text-center">할인금액</td>
                        <td class="text-center">기간</td>
                        <td class="text-center">할인금액</td>
                        <td class="text-center">기간</td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pr_item_list as $row) { ?>
                        <tr>
                            <td><?php echo $row['sort'];?></td>
                            <td><?php echo $row['pr_name'];?> <br/>코드:<?php echo $row['pr_id']?></td>
                            <td><?php echo $row['pr_ca_id'] ? $row['pr_ca_name'] .'<br/>코드:'.$row['pr_ca_id']:'';?></td>
                            <td><?php /*echo get_it_image($row['it_id'].'_s',70,70,null,null,false,false,false);*/?>
                                <img src="http://115.68.184.248/ople/item/<?php echo $row['it_id']; ?>_l1" width="70" height="70">
                            </td>
                            <td><?php echo get_item_name($row['it_name'],'list');?></td>
                            <td>$ <?php echo usd_convert($row['it_amount']);?><br/> (￦ <?php echo number_format($row['it_amount']);?>)</td>
                            <td>
                                <?php if($row['pr_dc_amount_usd']){?>
                                    $ <?php echo $row['pr_dc_amount_usd'];?>
                                    <br/>(￦ <?php echo number_format(round($row['pr_dc_amount_usd'] * $default['de_conv_pay']));?>)
                                <?php }?>
                            </td>
                            <td>
                                <?php if($row['pr_dc_st_dt'] || $row['pr_dc_en_dt']){?>
                                    <?php echo $row['pr_dc_st_dt'];?> ~ <?php echo $row['pr_dc_en_dt'];?>
                                <?php }else{ ?>
                                    프로모션 기간 내
                                <?php } ?>
                            </td>
                            <td>
                                <?php if($row['it_dc_amount_usd']){?>
                                    $ <?php echo $row['it_dc_amount_usd'];?>
                                    <br/>(￦ <?php echo number_format(round($row['it_dc_amount_usd'] * $default['de_conv_pay']));?>)
                                <?php }?>
                            </td>
                            <td>
                                <?php if($row['it_dc_st_dt'] || $row['it_dc_en_dt']){?>
                                    <?php echo $row['it_dc_st_dt'];?> ~ <?php echo $row['it_dc_en_dt'];?>
                                <?php }?>
                            </td>
                            <td>
                                <?php if($row['icon']){?>
                                    아이콘 <?php echo $row['icon'];?>
                                <?php }?>
                            </td>
<!--                            <td>--><?php //echo $row['create_dt'];?><!--</td>-->
                        </tr>
                    <?php }?>
                    </tbody>
                </table>
            </div>
            <div class="panel-footer text-center">
                <a href="http://ople.com/mall5/shop/promotion.php?pr_id=<?php echo $pr['pr_id'];?>&preview=1" target="_blank" class="btn btn-default">프로모션 미리보기</a>
                <a href="promotion_list.php?<?php echo $qstr;?>" class="btn btn-default">프로모션 목록</a>
                <a href="ople_promotion_item_manager_excel_sample.xlsx" class="btn btn-default">엑셀 다운로드(샘플)</a>
                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#excel_modal">엑셀 업로드</button>
                <a href="<?php echo $_SERVER['PHP_SELF'].'?'.http_build_query($_GET);?>&mode=excel_down" class="btn btn-warning">엑셀 다운로드</a>
                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#code_info_modal">프로모션 코드 정보 확인</button>
            </div>


        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="excel_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']?>">
                <input type="hidden" name="mode" value="excel_upload">
                <input type="hidden" name="pr_id" value="<?php echo $pr['pr_id'];?>">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">엑셀 업로드</h4>
                </div>
                <div class="modal-body form-horizontal">
                    <div class="row">
                        <label class="control-label col-lg-4">엑셀 파일 업로드</label>
                        <div class="col-lg-8"><input type="file" name="excel_file" class="form-control" required/></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">저장</button>
                    <a href="ople_promotion_item_manager_excel_sample.xlsx" class="btn btn-info">샘플 엑셀파일 다운로드</a>
                    <button type="button" class="btn btn-default" data-dismiss="modal">닫기</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="code_info_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">프로모션 코드 정보</h4>
            </div>
            <div class="modal-body">
                <?php
                if(isset($pr_list_arr) && is_array($pr_list_arr)){
                    foreach ($pr_list_arr as $row) {
                ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?php echo $row['pr_name'];?>
                        </div>
                    	<div class="panel-body">
                            <div class="row form-horizontal">
                                <div class="row">
                                    <label class="col-sm-3 control-label">프로모션 코드</label>
                                    <div class="col-sm-9">
                                        <p class="form-control-static"><?php echo $row['pr_id']?></p>
                                    </div>
                                </div>
                            </div>
                            <?php if($row['st_dt'] && $row['st_dt']){?>
                                <div class="row form-horizontal">
                                    <div class="row">
                                        <label class="col-sm-3 control-label">기간</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static"><?php echo $row['st_dt'];?> ~ <?php echo $row['en_dt']?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if($row['comment']) {?>
                                <div class="row form-horizontal">
                                    <div class="row">
                                        <label class="col-sm-3 control-label">기간</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static"><?php echo $row['comment'];?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php }?>

                    	</div>
                        <?php if(isset($pr_ca_info_arr[$row['pr_id']]) && is_array($pr_ca_info_arr[$row['pr_id']])) {?>
                            <table class="table table-bordered table-condensed table-striped">
                            	<thead>
                            		<tr>
                            			<th class="text-center">카테고리 코드</th>
                                        <th class="text-center">카테고리 명</th>
                                        <th class="text-center">순서</th>
                            		</tr>
                            	</thead>
                            	<tbody>
                                <?php foreach($pr_ca_info_arr[$row['pr_id']] as $pr_ca_row){ ?>
                            		<tr>
                            			<td><?php echo $pr_ca_row['pr_ca_id'];?></td>
                            			<td><?php echo $pr_ca_row['pr_ca_name'];?></td>
                            			<td><?php echo $pr_ca_row['sort'];?></td>
                            		</tr>
                                <?php } ?>
                            	</tbody>
                            </table>
                        <?php }?>
                    </div>

                <?php
                    }
                }?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
    </div>


<?php
include '../admin.tail.php';

