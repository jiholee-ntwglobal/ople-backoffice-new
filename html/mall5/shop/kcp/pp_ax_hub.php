<?
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

    if ($_POST['site_cd'] == 'T0007') {
        $default['de_kcp_site_key'] = '2.mDT7R4lUIfHlHq4byhYjf__';
    }

    ////////////////////////////////////////////////////////////////////////////////////


    /* ============================================================================== */
    /* =   PAGE : 지불 요청 및 결과 처리 PAGE                                       = */
    /* = -------------------------------------------------------------------------- = */
    /* =   연동시 오류가 발생하는 경우 아래의 주소로 접속하셔서 확인하시기 바랍니다.= */
    /* =   접속 주소 : http://testpay.kcp.co.kr/pgsample/FAQ/search_error.jsp       = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2010.02   KCP Inc.   All Rights Reserved.                 = */
    /* ============================================================================== */
?>
<?
    /* ============================================================================== */
    /* =   01. 지불 데이터 셋업 (업체에 맞게 수정)                                  = */
    /* = -------------------------------------------------------------------------- = */
    //$g_conf_home_dir    = "/home/jobs/yc4_es2/shop/kcp/payplus";   // BIN 절대경로 입력
    $g_conf_home_dir    = dirname($_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF']) . '/payplus';
    $g_conf_log_level   = "3";                                  // 변경불가
    if ($_POST['site_cd'] == 'T0007')
        $g_conf_pa_url  = "testpaygw.kcp.co.kr";                    // real url : paygw.kcp.co.kr , test url : testpaygw.kcp.co.kr
    else
        $g_conf_pa_url  = "paygw.kcp.co.kr";
    $g_conf_pa_port = "8090";                                   // 포트번호 , 변경불가
    $g_conf_mode    = 0;                                        // 변경불가

    require "pp_ax_hub_lib.php";                                // library [수정불가]
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   02. 지불 요청 정보 설정                                                  = */
    /* = -------------------------------------------------------------------------- = */
    $site_cd        = $_POST[  "site_cd"         ];             // 사이트 코드
    $site_key       = $default['de_kcp_site_key' ];             // 사이트 키
    $req_tx         = $_POST[  "req_tx"          ];             // 요청 종류
    $cust_ip        = getenv(  "REMOTE_ADDR"     );             // 요청 IP
    $ordr_idxx      = $_POST[  "ordr_idxx"       ];             // 쇼핑몰 주문번호
    $good_name      = $_POST[  "good_name"       ];             // 상품명
    /* = -------------------------------------------------------------------------- = */
    $good_mny       = $_POST[  "good_mny"        ];             // 결제 총금액
    $tran_cd        = $_POST[  "tran_cd"         ];             // 처리 종류
    /* = -------------------------------------------------------------------------- = */
    $res_cd         = "";                                       // 응답코드
    $res_msg        = "";                                       // 응답메시지
    $tno            = $_POST[  "tno"             ];             // KCP 거래 고유 번호
    /* = -------------------------------------------------------------------------- = */
    $buyr_name      = $_POST[  "buyr_name"       ];             // 주문자명
    $buyr_tel1      = $_POST[  "buyr_tel1"       ];             // 주문자 전화번호
    $buyr_tel2      = $_POST[  "buyr_tel2"       ];             // 주문자 핸드폰 번호
    $buyr_mail      = $_POST[  "buyr_mail"       ];             // 주문자 E-mail 주소
    /* = -------------------------------------------------------------------------- = */
    $bank_name      = "";                                       // 은행명
    $bank_issu      = $_POST[  "bank_issu"       ];             // 계좌이체 서비스사
    /* = -------------------------------------------------------------------------- = */
    $mod_type       = $_POST[  "mod_type"        ];             // 변경TYPE VALUE 승인취소시 필요
    $mod_desc       = $_POST[  "mod_desc"        ];             // 변경사유
    /* = -------------------------------------------------------------------------- = */
    $use_pay_method = $_POST[  "use_pay_method"  ];             // 결제 방법
    $bSucc          = "";                                       // 업체 DB 처리 성공 여부
    $acnt_yn        = $_POST[  "acnt_yn"         ];             // 상태변경시 계좌이체, 가상계좌 여부
    /* = -------------------------------------------------------------------------- = */
    $card_cd        = "";                                       // 신용카드 코드
    $card_name      = "";                                       // 신용카드 명
    $app_time       = "";                                       // 승인시간 (모든 결제 수단 공통)
    $app_no         = "";                                       // 신용카드 승인번호
    $noinf          = "";                                       // 신용카드 무이자 여부
    $quota          = "";                                       // 신용카드 할부개월
    $bankname       = "";                                       // 은행명
    $depositor      = "";                                       // 입금 계좌 예금주 성명
    $account        = "";                                       // 입금 계좌 번호
    /* = -------------------------------------------------------------------------- = */
    $escw_used      = $_POST[  "escw_used"       ];             // 에스크로 사용 여부
    $pay_mod        = $_POST[  "pay_mod"         ];             // 에스크로 결제처리 모드
    $deli_term      = $_POST[  "deli_term"       ];             // 배송 소요일
    $bask_cntx      = $_POST[  "bask_cntx"       ];             // 장바구니 상품 개수
    $good_info      = $_POST[  "good_info"       ];             // 장바구니 상품 상세 정보
    $rcvr_name      = $_POST[  "rcvr_name"       ];             // 수취인 이름
    $rcvr_tel1      = $_POST[  "rcvr_tel1"       ];             // 수취인 전화번호
    $rcvr_tel2      = $_POST[  "rcvr_tel2"       ];             // 수취인 휴대폰번호
    $rcvr_mail      = $_POST[  "rcvr_mail"       ];             // 수취인 E-Mail
    $rcvr_zipx      = $_POST[  "rcvr_zipx"       ];             // 수취인 우편번호
    $rcvr_add1      = $_POST[  "rcvr_add1"       ];             // 수취인 주소
    $rcvr_add2      = $_POST[  "rcvr_add2"       ];             // 수취인 상세주소
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   03. 인스턴스 생성 및 초기화 (단, 계좌이체 및 교통카드는 제외)            = */
    /* = -------------------------------------------------------------------------- = */
    /* =       결제에 필요한 인스턴스를 생성하고 초기화 합니다. 단, 계좌이체 및     = */
    /* =       교통카드의 경우는 결제 모듈을 통한 전문통신을 하지 않기 때문에       = */
    /* =       결제 모듈을 사용하는 과정에서 제외됩니다.                            = */
    /* = -------------------------------------------------------------------------- = */
    // 동방시스템 계좌이체가 아니면
    //if ( $bank_issu != "SCOB" )
    // 동방시스템 제외 : 100823
    if (1)
    {
        $c_PayPlus = new C_PP_CLI;
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   04. 처리 요청 정보 설정, 실행                                            = */
    /* = -------------------------------------------------------------------------- = */

    /* = -------------------------------------------------------------------------- = */
    /* =   04-1. 승인 요청                                                          = */
    /* = -------------------------------------------------------------------------- = */
        if ( $req_tx == "pay" )
        {
            if ( ( $use_pay_method == "000000000100" ) || ( $bank_issu == "SCOB" ) ) // 동방시스템 계좌이체, 교통카드의 경우
            {
                $tran_cd = "00200000";

                $c_PayPlus->mf_set_modx_data( "tno",           $tno       ); // KCP 원거래 거래번호
                $c_PayPlus->mf_set_modx_data( "mod_type",      "STAQ"     ); // 원거래 변경 요청 종류
                $c_PayPlus->mf_set_modx_data( "mod_ip",        $cust_ip   ); // 변경 요청자 IP
                $c_PayPlus->mf_set_modx_data( "mod_ordr_idxx", $ordr_idxx ); // 주문번호
            }
            else
            {
                $c_PayPlus->mf_set_encx_data( $_POST[ "enc_data" ], $_POST[ "enc_info" ] );
            }
        }

    /* = -------------------------------------------------------------------------- = */
    /* =   04-2. 매입 요청                                                          = */
    /* = -------------------------------------------------------------------------- = */
        else if ( $req_tx == "mod" )
        {
            $tran_cd = "00200000";

            $c_PayPlus->mf_set_modx_data( "tno",        $tno            );          // KCP 원거래 거래번호
            $c_PayPlus->mf_set_modx_data( "mod_type",   $mod_type       );          // 원거래 변경 요청 종류
            $c_PayPlus->mf_set_modx_data( "mod_ip",     $cust_ip        );          // 변경 요청자 IP
            $c_PayPlus->mf_set_modx_data( "mod_desc",   $mod_desc       );          // 변경 사유
        }

    /* = -------------------------------------------------------------------------- = */
    /* =   04-3. 에스크로 상태변경 요청                                              = */
    /* = -------------------------------------------------------------------------- = */
        else if ( $req_tx == "mod_escrow" )
        {
            $tran_cd = "00200000";

            $c_PayPlus->mf_set_modx_data( "tno",        $tno            );          // KCP 원거래 거래번호
            $c_PayPlus->mf_set_modx_data( "mod_type",   $mod_type       );          // 원거래 변경 요청 종류
            $c_PayPlus->mf_set_modx_data( "mod_ip",     $cust_ip        );          // 변경 요청자 IP
            $c_PayPlus->mf_set_modx_data( "mod_desc",   $mod_desc       );          // 변경 사유
            if ($mod_type == "STE1")                                                // 상태변경 타입이 [배송요청]인 경우
            {
                $c_PayPlus->mf_set_modx_data( "deli_numb",   $_POST[ "deli_numb" ] );          // 운송장 번호
                $c_PayPlus->mf_set_modx_data( "deli_corp",   $_POST[ "deli_corp" ] );          // 택배 업체명
            }
            else if ($mod_type == "STE2" || $mod_type == "STE4")                    // 상태변경 타입이 [즉시취소] 또는 [취소]인 계좌이체, 가상계좌의 경우
            {
                if ($acnt_yn == "Y")
                {
                    $c_PayPlus->mf_set_modx_data( "refund_account",   $_POST[ "refund_account" ] );      // 환불수취계좌번호
                    $c_PayPlus->mf_set_modx_data( "refund_nm",        $_POST[ "refund_nm"      ] );      // 환불수취계좌주명
                    $c_PayPlus->mf_set_modx_data( "bank_code",        $_POST[ "bank_code"      ] );      // 환불수취은행코드
                }
            }

            $action = "{$g4['shop_admin_path']}/escrow/kcp_result.php";
        }


    // 결제금액을 조작하여 넘어오는 경우에는 pp_cli 실행전에 에러를 출력한다. 그러므로 에러 출력시 결제는 되지 않는다.
    $site_cd   = $_POST['site_cd'];
    $timestamp = $_POST['timestamp'];
    $serverkey = $_SERVER['SERVER_SOFTWARE'].$_SERVER['SERVER_ADDR']; // 사용자가 알수 없는 고유한 값들
    $hashdata  = $_POST['hashdata']; // 넘어온값
    $hashdata2 = md5($site_cd.$ordr_idxx.$good_mny.$timestamp.$serverkey);
    if ($hashdata !== $hashdata2)
        die("DATA Error!!!");


    /* = -------------------------------------------------------------------------- = */
    /* =   04-4. 실행                                                               = */
    /* = -------------------------------------------------------------------------- = */
        if ( $tran_cd != "" )
        {
            $c_PayPlus->mf_do_tx( $trace_no, $g_conf_home_dir, $site_cd, $site_key, $tran_cd, "",
                                  $g_conf_pa_url, $g_conf_pa_port, "payplus_cli_slib", $ordr_idxx,
                                  $cust_ip, $g_conf_log_level, 0, $g_conf_mode );
        }
        else
        {
            $c_PayPlus->m_res_cd  = "9562";
            $c_PayPlus->m_res_msg = "연동 오류 TRAN_CD[" . $tran_cd . "]";
        }

        $res_cd    = $c_PayPlus->m_res_cd;
        $res_msg   = $c_PayPlus->m_res_msg;

        if ($res_cd != '0000')
        {
            echo "<script>
            var openwin = window.open( './proc_win.html', 'proc_win', '' );
            openwin.close();
            </script>";
            alert("$res_cd : $res_msg");
            exit;
        }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   05. 승인 결과 처리                                                       = */
    /* = -------------------------------------------------------------------------- = */
        if ( $req_tx == "pay" )
        {
            if( $res_cd == "0000" )
            {
                $tno    = $c_PayPlus->mf_get_res_data( "tno"    ); // KCP 거래 고유 번호
                $amount = $c_PayPlus->mf_get_res_data( "amount" ); // KCP 실제 거래 금액

    /* = -------------------------------------------------------------------------- = */
    /* =   05-1. 신용카드 승인 결과 처리                                            = */
    /* = -------------------------------------------------------------------------- = */
                if ( $use_pay_method == "100000000000" )
                {
                    $card_cd   = $c_PayPlus->mf_get_res_data( "card_cd"   ); // 카드 코드
                    $card_name = $c_PayPlus->mf_get_res_data( "card_name" ); // 카드 종류
                    $app_time  = $c_PayPlus->mf_get_res_data( "app_time"  ); // 승인 시간
                    $app_no    = $c_PayPlus->mf_get_res_data( "app_no"    ); // 승인 번호
                    $noinf     = $c_PayPlus->mf_get_res_data( "noinf"     ); // 무이자 여부 ( 'Y' : 무이자 )
                    $quota     = $c_PayPlus->mf_get_res_data( "quota"     ); // 할부 개월

                    $on_uid = $_POST[on_uid];
                    $trade_ymd = substr($app_time,0,4)."-".substr($app_time,4,2)."-".substr($app_time,6,2);
                    $trade_hms = substr($app_time,8,2).":".substr($app_time,10,2).":".substr($app_time,12,2);

                    // 카드내역 INSERT
                    $sql = "insert $g4[yc4_card_history_table]
                               set od_id = '$ordr_idxx',
                                   on_uid = '$on_uid',
                                   cd_mall_id = '$site_cd',
                                   cd_amount = '$good_mny',
                                   cd_app_no = '$app_no',
                                   cd_app_rt = '$res_cd',
                                   cd_trade_ymd = '$trade_ymd',
                                   cd_trade_hms = '$trade_hms',
                                   cd_opt01 = '$buyr_name',
                                   cd_time = NOW(),
                                   cd_ip = '$cust_ip',
								   cd_method_type = '신용카드' ";
                    sql_query($sql, TRUE);

                    // 주문서 UPDATE
                    $sql = " update $g4[yc4_order_table]
                                set od_receipt_card = '$good_mny',
                                    od_card_time = NOW(),
                                    od_escrow1 = '$tno',
									od_shop_memo=concat(od_shop_memo, '\\n', 'KCP 신용카드 결제')
                              where od_id = '$ordr_idxx'
                                and on_uid = '$on_uid' ";
                    sql_query($sql, TRUE);

					// 장바구니
					sql_query("update {$g4['yc4_cart_table']} set ct_status='준비' where on_uid='{$on_uid}' and ct_status in('주문') ");


					// 김선용 2014.03 : 사용포인트 여기서 차감처리.
					$od = sql_fetch("select mb_id, od_receipt_point, od_temp_point from $g4[yc4_order_table] where od_id='$ordr_idxx' and on_uid='$on_uid' ");
					if ($od[od_receipt_point] == '0' && $od[od_temp_point] != '0' && $od['mb_id'])
					{
						sql_query("update $g4[yc4_order_table] set od_receipt_point = od_temp_point where od_id='$ordr_idxx' and on_uid='$on_uid' ");
						insert_point($od[mb_id], (-1) * $od[od_temp_point], "주문번호:$ordr_idxx 결제", "@order", $od[mb_id], "$ordr_idxx");
					}

                    $action = "../settleresult.php?on_uid=$on_uid";
                }

    /* = -------------------------------------------------------------------------- = */
    /* =   05-2. 계좌이체 승인 결과 처리                                            = */
    /* = -------------------------------------------------------------------------- = */
                if ( $use_pay_method == "010000000000" )
                {
                    $bank_name = $c_PayPlus->mf_get_res_data( "bank_name"  );  // 은행명
                    $bank_code = $c_PayPlus->mf_get_res_data( "bank_code"  );  // 은행코드

                    $on_uid = $_POST[on_uid];
                    $trade_ymd = date("Y-m-d", time());
                    $trade_hms = date("H:i:s", time());

                    // 계좌이체내역 INSERT
                    $sql = "insert $g4[yc4_card_history_table]
                               set od_id = '$ordr_idxx',
                                   on_uid = '$on_uid',
                                   cd_mall_id = '$site_cd',
                                   cd_amount = '$good_mny',
                                   cd_app_no = '$tno',
                                   cd_app_rt = '$res_cd',
                                   cd_trade_ymd = '$trade_ymd',
                                   cd_trade_hms = '$trade_hms',
                                   cd_opt01 = '$buyr_name',
                                   cd_time = NOW(),
                                   cd_ip = '$cust_ip',
								   cd_method_type = '계좌이체' ";
                    sql_query($sql, TRUE);

                    // 주문서 UPDATE
                    $sql = " update $g4[yc4_order_table]
                                set od_receipt_bank = '$good_mny',
                                    od_bank_time = NOW(),
                                    od_escrow1 = '$tno'
                              where od_id = '$ordr_idxx'
                                and on_uid = '$on_uid' ";
                    sql_query($sql, TRUE);

                    $action = "../settleresult.php?on_uid=$on_uid";
                }

    /* = -------------------------------------------------------------------------- = */
    /* =   05-3. 가상계좌 승인 결과 처리                                            = */
    /* = -------------------------------------------------------------------------- = */
                if ( $use_pay_method == "001000000000" )
                {
                    $bankname  = $c_PayPlus->mf_get_res_data( "bankname"  ); // 입금할 은행 이름
                    $depositor = $c_PayPlus->mf_get_res_data( "depositor" ); // 입금할 계좌 예금주
                    $account   = $c_PayPlus->mf_get_res_data( "account"   ); // 입금할 계좌 번호

                    if (strtolower($g4[charset]) == "utf-8") {
                        $bankname = iconv("cp949", "utf8", $bankname);
                    }

                    $on_uid = $_POST[on_uid];
                    $trade_ymd = date("Y-m-d", time());
                    $trade_hms = date("H:i:s", time());

                    // 가상계좌내역 INSERT
                    $sql = "insert $g4[yc4_card_history_table]
                               set od_id = '$ordr_idxx',
                                   on_uid = '$on_uid',
                                   cd_mall_id = '$site_cd',
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

					// 김선용 2014.03 : 사용포인트 여기서 차감처리.
					$od = sql_fetch("select mb_id, od_receipt_point, od_temp_point from $g4[yc4_order_table] where od_id='$ordr_idxx' and on_uid='$on_uid' ");
					if ($od[od_receipt_point] == '0' && $od[od_temp_point] != '0' && $od['mb_id'])
					{
						sql_query("update $g4[yc4_order_table] set od_receipt_point = od_temp_point where od_id='$ordr_idxx' and on_uid='$on_uid' ");
						insert_point($od[mb_id], (-1) * $od[od_temp_point], "주문번호:$ordr_idxx 결제", "@order", $od[mb_id], "$ordr_idxx");
					}

                    $action = "../settleresult.php?on_uid=$on_uid";
                }

    /* = -------------------------------------------------------------------------- = */
    /* =   05-4. 휴대폰 승인 결과 처리                                              = */
    /* = -------------------------------------------------------------------------- = */
                if ( $use_pay_method == "000010000000" )
                {
                    $app_time = $c_PayPlus->mf_get_res_data( "hp_app_time"  ); // 승인 시간
                }

    /* = -------------------------------------------------------------------------- = */
    /* =   05-5. 상품권 승인 결과 처리                                              = */
    /* = -------------------------------------------------------------------------- = */
                if ( $use_pay_method == "000000001000" )
                {
                    $app_time = $c_PayPlus->mf_get_res_data( "tk_app_time"  ); // 승인 시간
                }

    /* = -------------------------------------------------------------------------- = */
    /* =   05-6. ARS 승인 결과 처리                                                 = */
    /* = -------------------------------------------------------------------------- = */
                if ( $use_pay_method == "000000000010" )
                {
                    $app_time = $c_PayPlus->mf_get_res_data( "ars_app_time"  ); // 승인 시간
                }

    /* = -------------------------------------------------------------------------- = */
    /* =   05-6. 승인 결과를 업체 자체적으로 DB 처리 작업하시는 부분입니다.         = */
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
    /* =   05-7. DB 작업 실패일 경우 자동 승인 취소                                 = */
    /* = -------------------------------------------------------------------------- = */
                if ( $bSucc == "false" )
                {
                    $c_PayPlus->mf_clear();

                    $tran_cd = "00200000";

                    $c_PayPlus->mf_set_modx_data( "tno",      $tno                  );         // KCP 원거래 거래번호
                    $c_PayPlus->mf_set_modx_data( "mod_type", "STE2"                );         // 원거래 변경 요청 종류
                    $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip              );         // 변경 요청자 IP
                    $c_PayPlus->mf_set_modx_data( "mod_desc", "결과 처리 오류 - 자동 취소" );  // 변경 사유

                    $c_PayPlus->mf_do_tx( $tno,  $g_conf_home_dir, $site_cd,
                                          $site_key,  $tran_cd,    "",
                                          $g_conf_pa_url,  $g_conf_pa_port,  "payplus_cli_slib",
                                          $ordr_idxx, $cust_ip,    $g_conf_log_level,
                                          0,    $g_conf_mode );

                    $res_cd  = $c_PayPlus->m_res_cd;
                    $res_msg = $c_PayPlus->m_res_msg;
                }

            }    // End of [res_cd = "0000"]

    /* = -------------------------------------------------------------------------- = */
    /* =   05-8. 승인 실패를 업체 자체적으로 DB 처리 작업하시는 부분입니다.         = */
    /* = -------------------------------------------------------------------------- = */
            else
            {
            }
        }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   06. 매입 결과 처리                                                       = */
    /* = -------------------------------------------------------------------------- = */
        else if ( $req_tx == "mod" )
        {
        }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   07. 에스크로 상태변경 결과 처리                                          = */
    /* = -------------------------------------------------------------------------- = */
        else if ( $req_tx == "mod_escrow" )
        {
        }
    } // End of Process
    /* ============================================================================== */

    /* ============================================================================== */
    /* =   08. 동방시스템 계좌이체 결과 처리 (승인, 취소)                                      = */
    /* = -------------------------------------------------------------------------- = */
    else
    {
        // 동방시스템 DB 에 업데이트 되지 않도록 처리 : 100823
        /*
        $res_cd    = $_POST[ "res_cd"    ];                     // 응답코드
        $res_msg   = $_POST[ "res_msg"   ];                     // 응답메시지
        $bank_name = $_POST[ "bank_name" ];                     // 은행명

        $on_uid = $_POST[on_uid];
        $trade_ymd = date("Y-m-d", time());
        $trade_hms = date("H:i:s", time());

        // 계좌이체내역 INSERT
        $sql = "insert $g4[yc4_card_history_table]
                   set od_id = '$ordr_idxx',
                       on_uid = '$on_uid',
                       cd_mall_id = '$site_cd',
                       cd_amount = '$good_mny',
                       cd_app_no = '$tno',
                       cd_app_rt = '$res_cd',
                       cd_trade_ymd = '$trade_ymd',
                       cd_trade_hms = '$trade_hms',
                       cd_opt01 = '$buyr_name',
                       cd_time = NOW(),
                       cd_ip = '$cust_ip' ";
        sql_query($sql, TRUE);

        // 주문서 UPDATE
        $sql = " update $g4[yc4_order_table]
                    set od_receipt_bank = '$good_mny',
                        od_bank_time = NOW(),
                        od_escrow1 = '$tno'
                  where od_id = '$ordr_idxx'
                    and on_uid = '$on_uid' ";
        sql_query($sql, TRUE);

        $action = "../settleresult.php?on_uid=$on_uid";
        */
    }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   09. 폼 구성 및 결과페이지 호출                                           = */
    /* ============================================================================== */
?>
    <html>
    <head>
    <script type="text/javascript">
       function goResult() {
		    document.getElementById('_dis_kcp_progress_').style.display = 'block';
            //var openwin = window.open( 'proc_win.php', 'proc_win', 'width=420, height=100, top=300, left=300' );
			document.pay_info.submit();
            //openwin.close();
		}
    </script>
    </head>
    <body onload="goResult()">

<style type="text/css">
.kcpwin{font-size:9pt; line-height:160%}
.bblack1 {FONT-WEIGHT: bold; FONT-SIZE: 9pt; COLOR: #000000; LINE-HEIGHT: 12pt; FONT-STYLE: normal; FONT-FAMILY: "돋움"; TEXT-DECORATION: none
}
</style>
<div id="_dis_kcp_progress_" class=kcpwin style="display:none; width:420px; height:300px; top:400px; left:300px; position:absolute; index:-1;">
<table width="400" border="0" cellspacing="1" cellpadding="0" align="center" bgcolor="#E0D6AD">
  <tr>
    <td align="center" class="bblack1" height="60" bgcolor="#FBFAF4">
      결제 진행중입니다. 잠시만 기다려 주십시오.<br>
      <img src="./processing.gif" name="pro" width="295" height="10">
    </td>
  </tr>
</table>
</div>

    <form name="pay_info" method="post" action="<?=$action?>">
        <input type="hidden" name="req_tx"            value="<?=$req_tx?>">            <!-- 요청 구분 -->
        <input type="hidden" name="use_pay_method"    value="<?=$use_pay_method?>">    <!-- 사용한 결제 수단 -->
        <input type="hidden" name="bSucc"             value="<?=$bSucc?>">             <!-- 쇼핑몰 DB 처리 성공 여부 -->

        <input type="hidden" name="res_cd"            value="<?=$res_cd?>">            <!-- 결과 코드 -->
        <input type="hidden" name="res_msg"           value="<?=$res_msg?>">           <!-- 결과 메세지 -->
        <input type="hidden" name="ordr_idxx"         value="<?=$ordr_idxx?>">         <!-- 주문번호 -->
        <input type="hidden" name="tno"               value="<?=$tno?>">               <!-- KCP 거래번호 -->
        <input type="hidden" name="good_mny"          value="<?=$good_mny?>">          <!-- 결제금액 -->
        <input type="hidden" name="good_name"         value="<?=$good_name?>">         <!-- 상품명 -->
        <input type="hidden" name="buyr_name"         value="<?=$buyr_name?>">         <!-- 주문자명 -->
        <input type="hidden" name="buyr_tel1"         value="<?=$buyr_tel1?>">         <!-- 주문자 전화번호 -->
        <input type="hidden" name="buyr_tel2"         value="<?=$buyr_tel2?>">         <!-- 주문자 휴대폰번호 -->
        <input type="hidden" name="buyr_mail"         value="<?=$buyr_mail?>">         <!-- 주문자 E-mail -->

        <input type="hidden" name="escw_used"         value="<?=$escw_used?>">         <!-- 에스크로 사용 여부 -->
        <input type="hidden" name="pay_mod"           value="<?=$pay_mod?>">           <!-- 에스크로 결제처리 모드 -->
        <input type="hidden" name="deli_term"         value="<?=$deli_term?>">         <!-- 배송 소요일 -->
        <input type="hidden" name="bask_cntx"         value="<?=$bask_cntx?>">         <!-- 장바구니 상품 개수 -->
        <input type="hidden" name="good_info"         value="<?=$good_info?>">         <!-- 장바구니 상품 상세 정보 -->
        <input type="hidden" name="rcvr_name"         value="<?=$rcvr_name?>">         <!-- 수취인 이름 -->
        <input type="hidden" name="rcvr_tel1"         value="<?=$rcvr_tel1?>">         <!-- 수취인 전화번호 -->
        <input type="hidden" name="rcvr_tel2"         value="<?=$rcvr_tel2?>">         <!-- 수취인 휴대폰번호 -->
        <input type="hidden" name="rcvr_mail"         value="<?=$rcvr_mail?>">         <!-- 수취인 E-Mail -->
        <input type="hidden" name="rcvr_zipx"         value="<?=$rcvr_zipx?>">         <!-- 수취인 우편번호 -->
        <input type="hidden" name="rcvr_add1"         value="<?=$rcvr_add1?>">         <!-- 수취인 주소 -->
        <input type="hidden" name="rcvr_add2"         value="<?=$rcvr_add2?>">         <!-- 수취인 상세주소 -->

        <input type="hidden" name="card_cd"           value="<?=$card_cd?>">           <!-- 카드코드 -->
        <input type="hidden" name="card_name"         value="<?=$card_name?>">         <!-- 카드명 -->
        <input type="hidden" name="app_time"          value="<?=$app_time?>">          <!-- 승인시간 -->
        <input type="hidden" name="app_no"            value="<?=$app_no?>">            <!-- 승인번호 -->
        <input type="hidden" name="quota"             value="<?=$quota?>">             <!-- 할부개월 -->

        <input type="hidden" name="bank_name"         value="<?=$bank_name?>">         <!-- 은행명 -->

        <input type="hidden" name="bankname"          value="<?=$bankname?>">          <!-- 입금 은행 -->
        <input type="hidden" name="depositor"         value="<?=$depositor?>">         <!-- 입금계좌 예금주 -->
        <input type="hidden" name="account"           value="<?=$account?>">           <!-- 입금계좌 번호 -->

    </form>
    </body>
    </html>

