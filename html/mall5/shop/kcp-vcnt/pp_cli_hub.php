<?
    /* ============================================================================== */
    /* =   PAGE : 지불 요청 PAGE                                                    = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2006   KCP Inc.   All Rights Reserverd.                   = */
    /* ============================================================================== */
	include "./_common.php";

    ////////////////////////////////////////////////////////////////////////////////////
    /*
    08.01.30
    무통장을 제외한 결제시 shop/settleresult.php에서 포인트를 차감하게 되는데
    주문시 새로운 창을 여러개 띄우고 결제를 하게 되면 포인트가 - (마이너스)로
    처리되며, 할인된 금액으로 정상 결제가 됨.
    이런 오류를 방지하고자 아래의 코드를 추가 함
    포인트로 결제하는 내역이 있으면서 회원의 포인트가 - (마이너스) 포인트라면 오류메세지 출력
    */
    $sql = " select od_temp_point from $g4[yc4_order_table] where od_id = '$_POST[ordr_idxx]' ";
    $row = sql_fetch($sql);
    if ($row[od_temp_point] > 0 && $member[mb_point] < 0) {
        alert("결제 오류 : 담당자에게 문의하시기 바랍니다.");
    }

    ////////////////////////////////////////////////////////////////////////////////////
?>
<?
    /* ============================================================================== */
    /* = 라이브러리 및 사이트 정보 include                                          = */
    /* = -------------------------------------------------------------------------- = */

    require "./pp_cli_hub_lib.php";
    include "./configure/site.conf";
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   01. 지불 요청 정보 설정                                                  = */
    /* = -------------------------------------------------------------------------- = */
    $pay_method = $_POST[ "pay_method" ];                             // 결제 방법
    $ordr_idxx  = $_POST[ "ordr_idxx"  ];                             // 주문 번호
	$good_name  = $_POST[ "good_name"  ];                             // 상품 정보
    $good_mny   = $_POST[ "good_mny"   ];                             // 결제 금액
	$buyr_name  = $_POST[ "buyr_name"  ];                             // 주문자 이름
    $buyr_mail  = $_POST[ "buyr_mail"  ];                             // 주문자 E-Mail
    $buyr_tel1  = $_POST[ "buyr_tel1"  ];                             // 주문자 전화번호
    $buyr_tel2  = $_POST[ "buyr_tel2"  ];                             // 주문자 휴대폰번호
    $req_tx     = $_POST[ "req_tx"     ];                             // 요청 종류
    $currency   = $_POST[ "currency"   ];                             // 화폐단위 (WON/USD)
    $va_uniq_key= $_POST[ "va_uniq_key"];                             // 유니크 키값
    /* = -------------------------------------------------------------------------- = */
    $soc_no     = $_POST[ "soc_no"  ];                                // 주민등록번호
    $tx_cd      = "";                                                 // 트랜잭션 코드
    $cash_yn    = $_POST[ "cash_yn" ];                                // 현금영수증 발행 여부
    $bSucc      = "";                                                 // DB 작업 성공 여부
    /* = -------------------------------------------------------------------------- = */
    $res_cd     = "";                                                 // 결과코드
    $res_msg    = "";                                                 // 결과메시지
    $tno        = "";                                                 // 거래번호
    /* = -------------------------------------------------------------------------- = */
    $bankname   = "";                                                 // 입금은행
    $bankcode   = "";                                                 // 은행코드
    $depositor  = "";                                                 // 가상계좌주명
    $account    = "";                                                 // 가상계좌번호
    $app_time   = "";                                                 // 승인시간
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   02. 인스턴스 생성 및 초기화                                              = */
    /* = -------------------------------------------------------------------------- = */

    $c_PayPlus  = new C_PAYPLUS_CLI;
    $c_PayPlus->mf_clear();
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   03. 처리 요청 정보 설정, 실행                                            = */
    /* = -------------------------------------------------------------------------- = */

    /* = -------------------------------------------------------------------------- = */
    /* =   03-1. 승인 요청                                                          = */
    /* = -------------------------------------------------------------------------- = */
        // 업체 환경 정보
        $cust_ip = getenv( "REMOTE_ADDR" );

        if ( $req_tx == "pay" )
        {
            $tx_cd = "00100000";

        $common_data_set = "";

        $common_data_set .= $c_PayPlus->mf_set_data_us( "amount",   $good_mny  );
        $common_data_set .= $c_PayPlus->mf_set_data_us( "currency", $currency  );

        if (!soc_no == "")
        {
            $common_data_set .= $c_PayPlus->mf_set_data_us( "soc_no",  $soc_no );
        }
        $common_data_set .= $c_PayPlus->mf_set_data_us( "cust_ip",  $cust_ip   );
        $common_data_set .= $c_PayPlus->mf_set_data_us( "escw_mod", "N"        );

        $c_PayPlus->mf_add_payx_data( "common", $common_data_set );

            // 주문 정보
            $c_PayPlus->mf_set_ordr_data( "ordr_idxx",  $ordr_idxx );
            $c_PayPlus->mf_set_ordr_data( "good_name",  $good_name );
            $c_PayPlus->mf_set_ordr_data( "good_mny",   $good_mny  );
            $c_PayPlus->mf_set_ordr_data( "buyr_name",  $buyr_name );
            $c_PayPlus->mf_set_ordr_data( "buyr_tel1",  $buyr_tel1 );
            $c_PayPlus->mf_set_ordr_data( "buyr_tel2",  $buyr_tel2 );
            $c_PayPlus->mf_set_ordr_data( "buyr_mail",  $buyr_mail );

            if ( $pay_method == "VCNT" )
            {
                $vcnt_data_set;

                //$vcnt_data_set .= $c_PayPlus->mf_set_data_us( "va_txtype",   "41100000" );            // 일반식 가상계좌
                //$vcnt_data_set .= $c_PayPlus->mf_set_data_us( "va_txtype",   "41110000" );            // 일반식 가상계좌(va_uniq_key 사용)
                $vcnt_data_set .= $c_PayPlus->mf_set_data_us( "va_txtype",   "41210000" );            // 고정식 가상계좌(va_uniq_key 사용)
                $vcnt_data_set .= $c_PayPlus->mf_set_data_us( "va_mny",      $good_mny  );            // 결제 금액
                $vcnt_data_set .= $c_PayPlus->mf_set_data_us( "va_bankcode", $_POST[ "ipgm_bank" ] ); // 입금은행
                $vcnt_data_set .= $c_PayPlus->mf_set_data_us( "va_name",     $_POST[ "ipgm_name" ] ); // 입금자명
                $vcnt_data_set .= $c_PayPlus->mf_set_data_us( "va_date",     $_POST[ "ipgm_date" ] ); // 입금 예정일
                $vcnt_data_set .= $c_PayPlus->mf_set_data_us( "va_uniq_key", $_POST[ "va_uniq_key"]); // 유니크 키 값

                $c_PayPlus->mf_add_payx_data( "va", $vcnt_data_set );
            }
        }

    /* = -------------------------------------------------------------------------- = */
    /* =   03-2. 취소/매입 요청                                                     = */
    /* = -------------------------------------------------------------------------- = */
        else if ( $req_tx == "mod" )
        {
            $mod_type = $_POST[ "mod_type" ];

            $tx_cd = "00200000";


            $c_PayPlus->mf_set_modx_data( "tno",      $_POST[ "tno" ]      );      // KCP 원거래 거래번호
            $c_PayPlus->mf_set_modx_data( "mod_type", $mod_type            );      // 원거래 변경 요청 종류
            $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip             );      // 변경 요청자 IP
            $c_PayPlus->mf_set_modx_data( "mod_desc", $_POST[ "mod_desc" ] );      // 변경 사유
        }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   03-3. 실행                                                               = */
    /* ------------------------------------------------------------------------------ */
        if ( strlen($tx_cd) > 0 )
        {
            $c_PayPlus->mf_do_tx( "",                $g_conf_home_dir, $g_conf_site_cd,
                                  $g_conf_site_key,  $tx_cd,           "",
                                  $g_conf_pa_url,    $g_conf_pa_port,  "payplus_cli_slib",
                                  $ordr_idxx,        $cust_ip,         $g_conf_log_level,
                                  "",                $g_conf_tx_mode );
        }
        else
        {
            $c_PayPlus->m_res_cd  = "9562";
            $c_PayPlus->m_res_msg = "연동 오류";
        }
        $res_cd  = $c_PayPlus->m_res_cd;                      // 결과 코드
        $res_msg = $c_PayPlus->m_res_msg;                     // 결과 메시지
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   04. 승인 결과 처리                                                       = */
    /* = -------------------------------------------------------------------------- = */
        if ( $req_tx == "pay" )
        {
            if ( $res_cd == "0000" )
            {
                $tno = $c_PayPlus->mf_get_res_data( "tno" );       // KCP 거래 고유 번호

    /* = -------------------------------------------------------------------------- = */
    /* =   04-1. 신용카드 승인 결과 처리                                            = */
    /* = -------------------------------------------------------------------------- = */
                if ( $pay_method == "VCNT" )
                {
                    $bankname  = $c_PayPlus->mf_get_res_data( "bankname"  ); // 입금할 은행 이름
                    $bankcode  = $c_PayPlus->mf_get_res_data( "bankcode"  ); // 은행코드 예) BK04
                    $depositor = $c_PayPlus->mf_get_res_data( "depositor" ); // 입금할 계좌 예금주
                    $account   = $c_PayPlus->mf_get_res_data( "account"   ); // 입금할 계좌 번호
                    $app_time  = $c_PayPlus->mf_get_res_data( "app_time"  ); // 시간

                    if (strtolower($g4[charset]) == "utf-8") {
                        $bankname = iconv("cp949", "utf8", $bankname);
						$depositor = iconv("cp949", "utf8", $depositor);
						$res_msg = iconv("cp949", "utf8", $res_msg);
                    }

                    $on_uid = $_POST[on_uid];
                    $trade_ymd = date("Y-m-d", time());
                    $trade_hms = date("H:i:s", time());

                    // 가상계좌내역 INSERT
                    $sql = "insert $g4[yc4_card_history_table]
                               set od_id = '$ordr_idxx',
                                   on_uid = '$on_uid',
                                   cd_mall_id = '{$default['de_kcp_mid' ]}',
                                   cd_amount = '0',
								   cd_amount_temp = '$good_mny', /* 입금예정액 */
                                   cd_app_no = '$tno',
                                   cd_app_rt = '$res_cd',
                                   cd_trade_ymd = '$trade_ymd',
                                   cd_trade_hms = '$trade_hms',
                                   cd_opt01 = '$buyr_name',
                                   cd_time = NOW(),
                                   cd_ip = '$cust_ip',
								   cd_method_type = '가상계좌' ";
                    sql_query($sql, TRUE);

                    // 주문서 UPDATE
                    $sql = " update $g4[yc4_order_table]
                                set od_bank_account = '$bankname $account $depositor',
                                    od_receipt_bank = '0',
                                    od_bank_time = '',
                                    od_escrow1 = '$tno'
                              where od_id = '$ordr_idxx'
                                and on_uid = '$on_uid' ";
                    sql_query($sql, TRUE);

					// 회원테이블에 가상계좌 고유키 및 가상계좌 정보 저장. 미사용
					/*
					if($member['mb_id']){
						sql_query("update {$g4['member_table']}
							set mb_kcp_vcnt_code = '$va_uniq_key',
								mb_kcp_vcnt_account = '$bankname $account $depositor' where mb_id='{$member['mb_id']}' ");
					}
					*/

					// 김선용 2014.03 : 사용포인트 여기서 차감처리.
					$od = sql_fetch("select mb_id, od_receipt_point, od_temp_point, od_name, od_hp from $g4[yc4_order_table] where od_id='$ordr_idxx' and on_uid='$on_uid' ");
					if ($od[od_receipt_point] == '0' && $od[od_temp_point] != '0' && $od['mb_id'])
					{
						sql_query("update $g4[yc4_order_table] set od_receipt_point = od_temp_point where od_id='$ordr_idxx' and on_uid='$on_uid' ");
						insert_point($od[mb_id], (-1) * $od[od_temp_point], "주문번호:$ordr_idxx 결제", "@order", $od[mb_id], "$ordr_idxx");
					}

					// SMS 발송 2014-11-06 홍민기
					if ($default['de_sms_use']){

						$receive_number = preg_replace("/[^0-9]/", "", $od['od_hp']); // 수신자번호
						$send_number = preg_replace("/[^0-9]/", "", $default['de_sms_hp']); // 발신자번호

						$sms_contents = "{이름}님 입금계좌번호\n{계좌번호}\n{입금액}원\n{회사명}";
						$sms_contents = preg_replace("/{이름}/", $od['od_name'], $sms_contents);
						$sms_contents = preg_replace("/{입금액}/", number_format($good_mny), $sms_contents);
						$sms_contents = preg_replace("/{계좌번호}/", $bankname."\n".$account , $sms_contents);
						$sms_contents = preg_replace("/{회사명}/", $default['de_admin_company_name'], $sms_contents);

						include_once("$g4[full_path]/lib/icode.sms.lib.php");
						$SMS = new SMS; // SMS 연결
						$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
						$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
						$SMS->Send();
					}

                    $action = "../settleresult.php?on_uid=$on_uid";

                }

    /* = -------------------------------------------------------------------------- = */
    /* =   04-2. 승인 결과를 업체 자체적으로 DB 처리 작업하시는 부분입니다.         = */
    /* = -------------------------------------------------------------------------- = */
    /* =         승인 결과를 DB 작업 하는 과정에서 정상적으로 승인된 건에 대해      = */
    /* =         DB 작업을 실패하여 DB update 가 완료되지 않은 경우, 자동으로       = */
    /* =         승인 취소 요청을 하는 프로세스가 구성되어 있습니다.                = */
    /* =         DB 작업이 실패 한 경우, bSucc 라는 변수(String)의 값을 "false"     = */
    /* =         로 세팅해 주시기 바랍니다. (DB 작업 성공의 경우에는 "false" 이외의 = */
    /* =         값을 세팅하시면 됩니다.)                                           = */
    /* = -------------------------------------------------------------------------- = */
                $bSucc = "";             // DB 작업 실패일 경우 "false" 로 세팅

    /* = -------------------------------------------------------------------------- = */
    /* =   04-3. DB 작업 실패일 경우 자동 승인 취소                                 = */
    /* = -------------------------------------------------------------------------- = */
                if ( $bSucc == "false" )
                {
                    $c_PayPlus->mf_clear();

                    $tx_cd = "00200000";

                    $c_PayPlus->mf_set_modx_data( "tno",      $tno     );                       // KCP 원거래 거래번호
                    $c_PayPlus->mf_set_modx_data( "mod_type", "STSC"   );                       // 원거래 변경 요청 종류
                    $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip );                       // 변경 요청자 IP
                    $c_PayPlus->mf_set_modx_data( "mod_desc", "결과 처리 오류 - 자동 취소" );   // 변경 사유
                    $c_PayPlus->mf_do_tx( "",                $g_conf_home_dir, $g_conf_site_cd,
                                          $g_conf_site_key,  $tx_cd,           "",
                                          $g_conf_pa_url,    $g_conf_pa_port,  "payplus_cli_slib",
                                          $ordr_idxx,        $cust_ip,         $g_conf_log_level,
                                          "",                $g_conf_tx_mode );

                    $res_cd  = $c_PayPlus->m_res_cd;
                    $res_msg = $c_PayPlus->m_res_msg;
                }

            }    // End of [res_cd = "0000"]

    /* = -------------------------------------------------------------------------- = */
    /* =   04-4. 승인 실패를 업체 자체적으로 DB 처리 작업하시는 부분입니다.         = */
    /* = -------------------------------------------------------------------------- = */
            else
            {
            }
        }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   05. 취소/매입 결과 처리                                                  = */
    /* = -------------------------------------------------------------------------- = */
        else if ( $req_tx == "mod" )
        {
        }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   06. 폼 구성 및 결과페이지 호출                                           = */
    /* ============================================================================== */
?>

    <html>
    <head>
    <script>
        function goResult()
        {
			document.getElementById('_dis_kcp_progress_').style.display = 'block';
            document.pay_info.submit();
        }
    </script>
    </head>
    <body onload="goResult()">

<div id="_dis_kcp_progress_" class=kcpwin style="display:none; width:420px; height:300px; top:400px; left:300px; position:absolute; index:-1;">
<table width="400" border="0" cellspacing="1" cellpadding="0" align="center" bgcolor="#E0D6AD">
  <tr>
    <td align="center" class="bblack1" height="60" bgcolor="#FBFAF4">
      가상계좌 발급처리중 입니다. 잠시만 기다려 주십시오.<br>
      <img src="./processing.gif" name="pro" width="295" height="10">
    </td>
  </tr>
</table>
</div>

    <form name="pay_info" method="post" action="<?=$action?>">
        <input type="hidden" name="req_tx"            value="<?=$req_tx?>">            <!-- 요청 구분 -->
        <input type="hidden" name="pay_method"        value="<?=$pay_method?>">        <!-- 사용한 결제 수단 -->
        <input type="hidden" name="bSucc"             value="<?=$bSucc?>">             <!-- 쇼핑몰 DB 처리 성공 여부 -->

        <input type="hidden" name="res_cd"            value="<?=$res_cd?>">            <!-- 결과 코드 -->
        <input type="hidden" name="res_msg"           value="<?=$res_msg?>">           <!-- 결과 메세지 -->
        <input type="hidden" name="ordr_idxx"         value="<?=$ordr_idxx?>">         <!-- 주문번호 -->
        <input type="hidden" name="va_uniq_key"       value="<?=$va_uniq_key?>">         <!-- 주문번호 -->
        <input type="hidden" name="tno"               value="<?=$tno?>">               <!-- KCP 거래번호 -->
        <input type="hidden" name="good_mny"          value="<?=$good_mny?>">          <!-- 결제금액 -->
        <input type="hidden" name="good_name"         value="<?=$good_name?>">         <!-- 상품명 -->
        <input type="hidden" name="buyr_name"         value="<?=$buyr_name?>">         <!-- 주문자명 -->
        <input type="hidden" name="buyr_tel1"         value="<?=$buyr_tel1?>">         <!-- 주문자 전화번호 -->
        <input type="hidden" name="buyr_tel2"         value="<?=$buyr_tel2?>">         <!-- 주문자 휴대폰번호 -->
        <input type="hidden" name="buyr_mail"         value="<?=$buyr_mail?>">         <!-- 주문자 E-mail -->

        <input type="hidden" name="bankname"          value="<?=$bankname?>">          <!-- 카드코드 -->
        <input type="hidden" name="bankcode"          value="<?=$bankcode?>">          <!-- 카드명 -->
        <input type="hidden" name="depositor"         value="<?=$depositor?>">         <!-- 승인시간 -->
        <input type="hidden" name="account"           value="<?=$account?>">           <!-- 승인번호 -->
        <input type="hidden" name="app_time"          value="<?=$app_time?>">          <!-- 할부개월 -->

    </form>
    </body>
    </html>
