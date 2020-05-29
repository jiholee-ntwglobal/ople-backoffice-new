<?
$sub_menu = "400960";

include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "엑셀 주문일괄등록";
include_once("$g4[admin_path]/admin.head.php");


if($_POST['mode'] == 'excel_upload') {

    if (isset($_FILES['upfile'])) {

        $file_nm_split = explode('.', $_FILES['upfile']['name']);

        $file_ext = strtolower($file_nm_split[count($file_nm_split) - 1]);


        if ($file_ext == 'xlsx' || $file_ext == 'xls') {

            $uploaded_file = $g4['full_path'] . '/adm/shop_admin/admin_upload/' . date('YmdHis') . '.' . $file_ext;

            if (move_uploaded_file($_FILES['upfile']['tmp_name'], $uploaded_file)) {

                require_once $g4['full_path'] . '/classes/PHPExcel.php';
                //엑셀 정보 불러들이기
                $objPHPExcel = PHPExcel_IOFactory::load($uploaded_file);

                $sheetIndex = $objPHPExcel->setActiveSheetIndex(0);
                $sheet = $objPHPExcel->getActiveSheet();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = "AA"; // $sheet->getHighestColumn();

                //주문자 정보 불러들이기
                $od_member = get_member("opleorder");

                //엑셀 데이터 읽어서 배열로 만들기
                for ($row = 3; $row <= $highestRow; $row++) {
                    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
                    $rowData = array_filter(call_user_func_array('array_merge', ($rowData)));
                    $item_array = array(); //상품 초기화

                    //상품 있는만큼 배열로 따로 만들기
                    foreach ($rowData as $key => $val){
                        if($key>6 && ($key%2==1)){
                            $item_array[] = array("it_id"=>$val, "ct_qty"=>$rowData[($key+1)]);
                        }
                    }
                    $rowData[item] = $item_array;
                    $rowArray[] = $rowData;
                }

                //배열로 된 데이터 주문서로 테이블에 각각 insert하기
                $ok_cnt = 0; //성공 개수
                $order_fail = array(); //실패한 애들
                $total_cnt = 0; //총 개수
                foreach ($rowArray as $val){

                    if($val[0] !="" && $val[1] !="" && $val[2] !="" && $val[3] !="" && $val[4] !=""){

                        $od_id = get_new_od_id();
                        $tmp_on_uid = get_unique_id();

                        //우편번호 길이가 6개일땐 3개로 나누고 아니면 3개 / 2개로
                        $zonecode = preg_replace("/[^0-9]/", "", trim($val[3]));
                        $zip1 = substr($zonecode, 0, 3);
                        $zip2 = (strlen($zonecode)==6) ? substr($zonecode, -3) : substr($zonecode, -2);
                        //통관or주민
                        $od_b_jumin = preg_replace("/([^a-zA-Z0-9])/", "", trim($val[6]));
                        //$customs_clearance_type = (strtoupper(substr($customs_clearance_code,0,1))=="P") ? "c" : "j";

                        $tel = preg_replace("/[^0-9]/", "", trim($val[1]));
                        $hp = preg_replace("/[^0-9]/", "" , trim($val[2]));

                        /**
                        $od_deposit_name 입금자이름 case 가상계좌일땐 od_name과 같음
                         * $od_shop_memo 결제할때나 그럴때 남는 히스토리인듯
                         * $od_hope_date 모르겠음 걍 지움
                         * od_send_code 배송비 : 0원
                         * od_receipt_bank 입금액
                         **/
                        //카트 insert item에서 불러오면 됨
                        /** ct_amount = 판매가
                         * ct_qty = 수량 *
                         * ct_amount_usd
                         */
                        $tot_amount = 0;
                        foreach ($val[item] as $item_val) {
                            $item = sql_fetch("select it_amount from yc4_item WHERE it_id = '" . $item_val[it_id] . "'");
                            //$add_column = (defined('PRICE_DOLLAR')) ? ",ct_amount_usd = '" . usd_convert($item['it_amount']) . "'" : "";
                            $sql = " insert $g4[yc4_cart_table]
                                        set on_uid       = '$tmp_on_uid',
                                            it_id        = '$item_val[it_id]',
                                            ct_status    = '준비',
                                            ct_status_update_dt = '{$g4['time_ymdhis']}',
                                            ct_amount    = '{$item[it_amount]}',
                                            ct_point     = '{$item[it_point]}',
                                            ct_point_use = '0',
                                            ct_stock_use = '0',
                                            ct_qty       = '" . $item_val[ct_qty] . "',
                                            ct_time      = '{$g4['time_ymdhis']}',
                                            ct_ip        = '" . getenv('REMOTE_ADDR') . "',
                                            ct_mb_id = '{$od_member['mb_id']}',
                                            ct_amount_usd = '" . usd_convert($item['it_amount']) . "'
                                            ";
                            sql_query($sql);
                            //입금액에 넣으려고 총금액 구함
                            $tot_amount += ($item[it_amount]*$item_val[ct_qty]);
                        }

                        //주문서 insert
                        $sql = " insert $g4[yc4_order_table]
                                set od_id             = '$od_id',
                                    on_uid            = '$tmp_on_uid',
                                    mb_id             = '$od_member[mb_id]',
                                    od_pwd            = '$od_member[mb_password]',
                                    od_name           = '$od_member[mb_name]',
                                    od_email          = '$od_member[mb_email]',
                                    od_tel            = '$od_member[mb_tel]',
                                    od_hp             = '$od_member[mb_hp]',
                                    od_zonecode		  = '$od_member[mb_zonecode]',
                                    od_zip1           = '$od_member[mb_zip1]',
                                    od_zip2           = '$od_member[mb_zip2]',
                                    od_addr1          = '$od_member[mb_addr1]',
                                    od_addr2          = '$od_member[mb_addr2]',
                                    od_addr_jibeon	  = '$od_member[mb_addr_jibeon]',
                                    od_b_name         = '".trim(addslashes(htmlspecialchars($val[0])))."',
                                    od_b_tel          = '".(addslashes(htmlspecialchars($tel)))."',
                                    od_b_hp           = '".(addslashes(htmlspecialchars($hp)))."',
                                    od_b_zonecode	  = '$zonecode',
                                    od_b_zip1         = '".$zip1."',
                                    od_b_zip2         = '".$zip2."',
                                    od_b_addr1        = '".trim(addslashes(htmlspecialchars($val[4])))."',
                                    od_b_addr_jibeon  = '".trim(addslashes(htmlspecialchars($val[4])))."',
                                    od_deposit_name   = '$od_member[od_name]',
                                    od_memo           = '".trim(addslashes(htmlspecialchars($val[5])))."',
                                    od_send_cost      = '',
                                    od_temp_bank      = '$tot_amount',
                                    od_temp_card      = '',
                                    od_temp_point     = '',
                                    od_receipt_bank   = '$tot_amount',
                                    od_receipt_card   = '0',
                                    od_receipt_point  = '',
                                    od_bank_account   = '',
                                    od_shop_memo      = '엑셀 주문 일괄 등록',
                                    od_bank_time = '$g4[time_ymdhis]',
                                    od_status_update_dt = '$g4[time_ymdhis]',
                                    od_pay_time = '$g4[time_ymdhis]',
                                    od_auto_pay_fg = 'Y',
                                    od_hope_date      = '',
                                    od_time           = '$g4[time_ymdhis]',
                                    od_ip             = '" . getenv('REMOTE_ADDR') . "',
                                    od_settle_case    = '가상계좌',
                                    exchange_rate	  = '$default[de_conv_pay]',
                                    /* // 김선용 200908 : */
                                    od_b_jumin		= '" . $od_b_jumin . "',
                                    od_recommend_off_sale = '0',
                                    /* // 김선용 2014.03 : 카드 복합결제 구분 처리 */
                                    card_settle_case ='',
                                    kcp_escrow_point = '0'
                                    /* 고정가상계좌 구분. 미사용 kcp_vbank_fix = '0' */
                                    ";

                        sql_query($sql);

                        //주민번호||통관고유부호 insert

                        if ($od_b_jumin) {
                            sql_query("DELETE FROM yc4_customs_clearance_agreement WHERE od_id = '" . $od_id . "' ");
                            sql_query("
                                        INSERT INTO
                                            yc4_customs_clearance_agreement
                                       (
                                            od_id,od_b_name,flag,code,create_dt,create_id
                                       )
                                        VALUES(
                                            '" . $od_id . "','" . trim($val[0]) . "','c','" . trim($od_b_jumin) . "','" . $g4['time_ymdhis'] . "','" . $od_member['mb_id'] . "'
                                        )
                                   ");
                        }
                        $or_ok_id[] = $od_id;
                        $ok_cnt++;
                        $total_cnt++;
                    }else{
                        if($val[0]!="") {
                            $order_fail[] = $val[0];
                            $total_cnt++;
                        }
                    }

                } //엑셀 내용 배열 foreach-end
                $order_ok_id_list = implode("," , $or_ok_id);

                $CONTENTS = "
                                <tr>
                                    <td colspan='2' height='50'>총 ".$total_cnt."개 중 ".$ok_cnt."개 주문서 업로드에 성공하였습니다.</td>
                                </tr>
                                <tr>
                                    <td colspan='2' height='50'>주문번호 : ".$order_ok_id_list."</td>                                    
                                </tr>
                                ";
                if(count($order_fail)>0){
                    $order_fail_list = implode("," , $order_fail);
                    $CONTENTS.= "<tr>
                                       <td colspan='2' height='50'>실패한 사람 리스트 : ".$order_fail_list."</td>     
                                    </tr>";
                }
            } else {
                alert("네트워크 장애가 있습니다. 잠시후 다시 시도해주세요.");
                exit;
            }

        } else {
            alert("Excel파일을 업로드하세요.");
            exit;
        }


    } else {
        alert("Excel파일을 업로드하세요.");
        exit;
    }

} else {
    $CONTENTS = "
	<tr>
		<td colspan='4' height='50'>일괄 처리할 대상을 업로드해 주세요.</td>
	</tr>";
}

?>
<table width=100% cellpadding=0 cellspacing=0>
    <tr>
        <td colspan=12 height=2>
            <h2>엑셀 주문일괄등록&nbsp;</h2>
            <br>
            <div style="border:1px solid black;padding:10px 20px;">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="mode" value="excel_upload">
                    <table width="100%">
                        <tbody><tr>
                            <td align="left" width="140"><a href="/mall5/adm/shop_admin/admin_upload/order_sample.xlsx">Download Sample File</a></td>
                            <td align="left" width="300"><input type="file" name="upfile">&nbsp;<input type="submit" value="Upload File"></td>
                            <td align="right"></td>
                        </tr>
                        </tbody></table>
                </form>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan=12 height=1 bgcolor=#CCCCCC></td>
    </tr>
</table>

<table width=100% cellpadding="5" cellspacing="2" style="table-layout:fixed;word-break:break-all;">
    <tbody align="center" class="data_tbody">
    <?php echo $CONTENTS; ?>
    </tbody>
</table>
<?php
include_once("$g4[admin_path]/admin.tail.php");
?>
