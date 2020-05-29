<?
    /* ============================================================================== */
    /* =   PAGE : 일반결제 지불 처리 PAGE                                           = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2006   KCP Inc.   All Rights Reserverd.                   = */
    /* ============================================================================== */

include "./_common.php";
?>
<? include "./configure/site.conf"; ?>
<?
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
    $soc_no     = $_POST[ "soc_no"     ];                             // 주문자 주민등록번호
    $req_tx     = $_POST[ "req_tx"     ];                             // 요청 종류
    $currency   = $_POST[ "currency"   ];                             // 화폐단위 (WON/USD)
    $uiva_uniq_key = $_POST[ "va_uniq_key"];                          // 유니크키 값(고정식 가상계좌 고유키값)
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   02. 가상계좌 결제 페이지 구현                                            = */
    /* ============================================================================== */
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$g4['charset']?>">
<title>*** KCP Online Payment System [PHP Version] ***</title>
<link href="css/sample.css" rel="stylesheet" type="text/css">
<script language='javascript'>

    function  jsf__show_progress( show )
    {
        if ( show == true )
        {
            window.show_pay_btn.style.display  = 'none';
            window.show_progress.style.display = 'inline';
        }
        else
        {
            window.show_pay_btn.style.display  = 'inline';
            window.show_progress.style.display = 'none';
        }
    }

    function  jsf__chk_ssl_vcnt( form )
    {
        if (form.ipgm_name.value == "")
        {
            alert("입금자명을 입력해 주시기 바랍니다.");
            form.ipgm_name.focus();
            return false;
        }
        else if (form.ipgm_bank.value == "XXXX")
        {
            alert("입금은행을 선택해 주시기 바랍니다.");
            form.ipgm_bank.focus();
            return false;
        }
        else
        {
            return true;
        }
    }

    function  jsf__pay_vcnt( form )
    {

        jsf__show_progress(true);

        if ( jsf__chk_ssl_vcnt( form ) == false )
        {
            jsf__show_progress(false);
            return;
        }

        form.submit();
    }

</script>
</head>
<body>
<form name="ssl_form" action="./pp_cli_hub.php" method="post">
<table border='0' cellpadding='0' cellspacing='1' width='500' align='center'>
    <tr>
        <td align="left" height="25"><img src="./img/KcpLogo.jpg" border="0" width="65" height="50"></td>
        <td align='right' class="txt_main">KCP Online Payment System [PHP HUB Version]</td>
    </tr>
    <tr>
        <td bgcolor="CFCFCF" height='3' colspan='2'></td>
    </tr>
    <tr>
        <td align='center' valign="middle" colspan='2' height="30">
            <font style="font-size:12px;font-weight:bold">이 페이지는 결제을 요청하는 페이지로써 샘플(예시) 페이지입니다.</font>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <table width="90%" align="center">
                <tr>
                    <td bgcolor="CFCFCF" height='2'></td>
                </tr>
                <tr>
                    <td align="center"><B>주문 정보 확인</B></td>
                </tr>
                <tr>
                    <td bgcolor="CFCFCF" height='2'></td>
                </tr>
            </table>
            <table width="90%" align="center">
                <tr>
                    <td>주문 번호</td>
                    <td><?=$ordr_idxx?></td>
                </tr>
                <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                <tr>
                    <td>상품 정보</td>
                    <td><?=$good_name?></td>
                </tr>
                <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                <tr>
                    <td>결제 금액</td>
                    <td><?=$good_mny?></td>
                </tr>
                <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                <tr>
                    <td>주문자 이름</td>
                    <td><?=$buyr_name?></td>
                </tr>
                <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                <tr>
                    <td>주문자 E-Mail</td>
                    <td><?=$buyr_mail?></td>
                </tr>
                <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                <tr>
                    <td>주문자 전화번호</td>
                    <td><?=$buyr_tel1?></td>
                </tr>
                <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                <tr>
                    <td>주문자 휴대폰번호</td>
                    <td><?=$buyr_tel2?>
                    </td>
                </tr>
                <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td bgcolor="CFCFCF" height='2' colspan='2'></td>
                </tr>
                <tr>
                    <td align="center" colspan='2'><B>결제정보</B></td>
                </tr>
                <tr>
                    <td bgcolor="CFCFCF" height='2' colspan='2'></td>
                </tr>
                <tr>
                    <td><B>결제방법</B></td>
                    <td><b>가상계좌</b></td>
                </tr>
                <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                <tr>
                    <td><B>결제금액</B></td>
                    <td><B><?=$good_mny?> 원</B></td>
                </tr>
                <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                <tr>
                    <td><B>입금자명</B></td>
                    <td><input type='text' name='ipgm_name' value="개발자" maxlength='10'>
                    </td>
                </tr>
                <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                <tr>
                    <td><B>입금은행</B></td>
                    <td>
                        <select name="ipgm_bank">
                            <option value="XXXX" selected>선택</option>
                            <option value="BK03">기업은행</option>
                            <option value="BK04">국민은행</option>
                            <option value="BK05">외환은행</option>
                            <option value="BK07">수협은행</option>
                            <option value="BK11">농협중앙회</option>
                            <option value="BK20">우리은행</option>
							<option value="BK23">SC제일은행</option>
                            <option value="BK32">부산은행</option>
							<option value="BK34">광주은행</option>
                            <option value="BK71">우체국</option>
							<option value="BK81">하나은행</option>
                            <option value="BK26">신한은행</option>
                        </select>
                    </td>
                </tr>
                <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                <tr>
                    <td><B>입금 예정일</B></td>
                    <td><input type='text' name='ipgm_date' maxlength='8' value="20140405"><br>(예: 2006년 3월 5일의 경우 : "20060305" 와 같이 입력)
                    </td>
                </tr>

                <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                <tr>
                    <td colspan="2" align="center">
                        <span id='show_pay_btn'>
                            <input type='button' value='결제 하기' onclick='jsf__pay_vcnt( this.form )' class="box">
                        </span>
                        <span id='show_progress' style='display:none'>
                            <b>결제진행중입니다. 잠시만 기다려주십시오</b>
                        </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td bgcolor="CFCFCF" height='3' colspan='2'></td>
    </tr>
    <tr>
        <td colspan='2' align="center" height='25'>ⓒ Copyright 2006. KCP Inc.  All Rights Reserved.</td>
    </tr>
</table>

<!-- KCP 관련 셋팅 -->
<input type="hidden" name="pay_method" value="<?=$pay_method?>">
<input type="hidden" name="ordr_idxx"  value="<?=$ordr_idxx?>">
<input type="hidden" name="good_name"  value="<?=$good_name?>">
<input type="hidden" name="good_mny"   value="<?=$good_mny?>">
<input type="hidden" name="buyr_name"  value="<?=$buyr_name?>">
<input type="hidden" name="buyr_mail"  value="<?=$buyr_mail?>">
<input type="hidden" name="buyr_tel1"  value="<?=$buyr_tel1?>">
<input type="hidden" name="buyr_tel2"  value="<?=$buyr_tel2?>">
<input type="hidden" name="soc_no"     value="<?=$soc_no?>">
<input type="hidden" name="req_tx"     value="<?=$req_tx?>">
<input type="hidden" name="currency"   value="<?=$currency?>">
<input type="hidden" name="va_uniq_key" value="<?=$va_uniq_key?>">

</form>
</body>
</html>