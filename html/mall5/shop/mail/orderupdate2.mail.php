<?
// 회원가입축하 메일 (회원님께 발송)
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$g4[charset]?>">
<title>고객님께 주문서 메일 드리기</title>
</head>

<body style="padding:30px; font-size: 14px; font-family:돋움,Dotum; margin: 0; color:#313131">

<div style="font-family: arial, verdana, sans-serif; width:100%; margin: 0 auto;">

<div style="width:700px;height: auto;padding:10px;border: 1px solid #999999 ;position:relative;">
	<div style="height: 100px;border-bottom: 4px solid #ff931e;">
		<div style="float: left; margin-top: 50px;"><a href="http://ople.com"><img src="http://115.68.20.84/main/email_logo.gif"></a></div>
		<div style="float: right; margin-top: 15px;"><a href="http://ople.com/mall5/shop/25m.php"><img src="http://115.68.20.84/main/email_banner.gif"></a></div>
		</div>
        <div style="width:660px;padding:20px;border-bottom:1px solid #ff931e;margin-bottom:20px"><img src="http://115.68.20.84/main/thankyou.gif"></div>
	<div style="padding:20px; margin-bottom:0;text-align: left;">
		<table width="100%" cellspacing="0" cellpadding="0" border=0>
    <td>
        <!-- 주문내역  -->
        <table width="100%" cellpadding="0" cellspacing="0">
        <colgroup width=200>
        <colgroup width=110>
        <colgroup width=150>
        <colgroup width=1>
        <colgroup width=''>
        <? for ($i=0; $i<count($list); $i++) { ?>
        <tr>
            <td width="9%" rowspan=12 align=center><a href='<?="$g4[shop_url]/item.php?it_id={$list[$i][it_id]}"?>' target=_blank><?=$list[$i][it_simg]?></a></td>
            <td width="18%" height=22>주문제품명</td>
            <td colspan=3><B><?=$list[$i][it_name]?></B></td>
        </tr>
        <tr><td colspan=4 bgcolor=#FFFFFF height=1 style="border-top:1px solid #DDDDDD"></td></tr>
        <tr>
            <td height=22>주문번호</td>
            <td width="41%">: <font color=#CC3300><B><?=$od_id?></B></font></td>
            <td width="31%"> 선택옵션 </td>
        </tr>
        <tr><td colspan=2 bgcolor=#FFFFFF height=1 style="border-top:1px solid #DDDDDD"></td></tr>
        <tr>
            <td height=22>판매가격</td>
            <td>: <?=display_amount($list[$i][ct_amount])?></td>
            <td rowspan=9 valign=top style='padding-left:10px; padding-top:5px'><?=$list[$i][it_opt]?></td>
        </tr>
        <tr><td colspan=2 bgcolor=#FFFFFF height=1 style="border-top:1px solid #DDDDDD"></td></tr>
        <tr>
            <td height=22> 수량</td>
            <td>: <b><?=number_format($list[$i][ct_qty])?></b>개</td>
        </tr>
        <tr><td colspan=2 bgcolor=#FFFFFF height=1 style="border-top:1px solid #DDDDDD"></td></tr>
        <tr>
            <td height=22> 소계</td>
            <td>: <?=display_amount($list[$i][stotal_amount])?></td>
        </tr>
        <tr><td colspan=2 bgcolor=#FFFFFF height=1 style="border-top:1px solid #DDDDDD"></td></tr>
        <tr>
            <td height=22>포인트</td>
            <td>: <?=display_point($list[$i][stotal_point])?></td>
        </tr>
        <tr><td colspan=3 bgcolor=#FFFFFF height=1 style="border-top:1px solid #DDDDDD"></td></tr>
        <tr><td colspan=3 height=20></td></tr>
        <? } ?>

        <? if ($od_send_cost > 0) { // 배송비가 있다면 ?>
        <tr>
            <td></td>
            <td height=22>배송비</td>
            <td colspan=3 bgcolor=#F6F6F6><?=display_amount($od_send_cost)?></td>
        </tr>
        <? } ?>

        <tr>
            <td></td>
            <td height=22>주문합계</td>
            <td colspan=3 bgcolor=#F6F6F6><font color=#0066CC><b><?=display_amount($ttotal_amount)?></b></font></td>
        </tr>
        <tr>
            <td></td>
            <td height=22>포인트합계</td>
            <td colspan=3 bgcolor=#F6F6F6><b><?=display_point($ttotal_point)?></b></td>
        </tr>
        </table><br>
        <!-- 주문내역 END -->

        <!-- 결제정보 -->
        <table width="100%" align="center" cellpadding="0" cellspacing="0">
        <tr><td height=30><B>결제정보</B></td></tr>
        <tr>
            <td>
                <table width=100% cellpadding=4 cellspacing=0>
                <colgroup width=110>
                <colgroup width=''>
                <tr><td colspan=2 height=1 bgcolor=#fa5a00></td></tr>

                <? if ($od_receipt_point > 0) { ?>
                <tr bgcolor="#F2F7FA">
                    <td>포인트 입금액</td>
                    <td>: <font color=#0066CC><B><?=display_point($od_receipt_point)?></B></font></td>
                </tr>
                <? } ?>

                <? if ($od_receipt_card > 0) { ?>
                <tr bgcolor="#F2F7FA">
                    <td>신용카드 입금액</td>
                    <td>: <font color=#0066CC><B><?=display_amount($od_receipt_card)?></B><!--  (승인전 금액입니다.) --></font></td>
                </tr>
                <? } ?>

                <? if ($od_receipt_bank > 0) { ?>
                <tr bgcolor="#F2F7FA" style="height:30px">
                    <td><?=$od_settle_case?> 입금액</td>
                    <td>: <font color=#0066CC><B><?=display_amount($od_receipt_bank)?></B></font></td>
                </tr>
                <tr bgcolor="#F2F7FA" style="height:30px">
                    <td>계좌번호</td>
                    <td>: <?=$od_bank_account?></td>
                </tr>
                <tr bgcolor="#F2F7FA" style="height:30px">
                    <td>입금자 이름</td>
                    <td>: <?=$od_deposit_name?></td>
                </tr>
                <? } ?>

                <tr><td colspan=2 height=1 bgcolor=#FFFFFF></td></tr>
                </table>
            </td>
        </tr>
        </table><br>
       <!-- 결제정보 END-->

<!-- 주문자 정보 -->
        <table width="100%" align="center" cellpadding="0" cellspacing="0">
        	<tr>
                <td height=30><B>주문하신 분 정보</B></td>
                <td align=right valign=bottom style='color:#fa5a00; font-size=11px; font-family:돋움'>상세한 내용은 주문서 조회 화면에서 확인하실 수 있습니다.  [<a href='<?="$g4[shop_url]/orderinquiry.php"?>'>바로가기</a>]</td>
            </tr>
            <tr>
                <td colspan=2>
                    <table width="100%" cellpadding="4" cellspacing="0">
                     <colgroup width=110>
                     <colgroup width=''>
                        <tr><td colspan=2 height=1 bgcolor=#fa5a00></td></tr>
                        <tr bgcolor="#F8F7F2" style="height:30px">
                            <td> 이름</td>
                            <td>: <?=$od_name?></td>
                        </tr>
                        <tr bgcolor="#F8F7F2" style="height:30px">
                            <td> 전화번호</td>
                            <td>: <?=$od_tel?></td>
                        </tr>
                        <tr bgcolor="#F8F7F2" style="height:30px">
                            <td> 핸드폰</td>
                            <td>: <?=$od_hp?></td>
                        </tr>
                        <tr bgcolor="#F8F7F2" style="height:30px">
                            <td> 주소</td>
                            <td>: <?=sprintf("(%s-%s) %s %s", $od_zip1, $od_zip2, $od_addr1, $od_addr2)?></td>
                        </tr>

                        <? if ($od_hope_date) { ?>
                        <tr bgcolor="#F8F7F2" style="height:30px">
                            <td> 희망배송일</td>
                            <td>: <?=$od_hope_date?> (<?=get_yoil($od_hope_date)?>)</td>
                        </tr>
                        <? } ?>
                        <tr><td colspan=2 height=1 bgcolor=#FFFFFF></td></tr>
                    </table>
                </td>
            </tr>
        </table><br>
        <!-- 주문자 정보 END-->



        <!-- 배송지 정보 -->
        <!-- 배송지 정보 -->
        <table width="100%" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td height=30><B>배송지 정보</B></td>
            <td align=right valign=bottom style='color:#fa5a00; font-size=11px; font-family:돋움'>배송지를 변경하실 고객님은 <B><?= $default[de_admin_company_tel] ?></B>로 연락주시기 바랍니다.</td></tr>
        <tr>
            <td colspan=2>

			<?if($od_ship == '1'){ // 김선용 201211 : 복수배송?>
				<? echo get_fui_ship_item($tmp_on_uid, $member['mb_id'], 0, false, true);?>
			<?}else{ // 단수배송?>

                <table width="100%" cellpadding="4" cellspacing="0">
                <colgroup width=110>
                <colgroup width=''>
                <tr><td colspan=2 height=1 bgcolor=#fa5a00></td></tr>
                <tr bgcolor="#F8F7F2" style="height:30px">
                    <td> 이 름</td>
                    <td>: <?=$od_b_name?></td>
                </tr>
                <tr bgcolor="#F8F7F2" style="height:30px">
                    <td> 전화번호</td>
                    <td>: <?=$od_b_tel?></td>
                </tr>
                <tr bgcolor="#F8F7F2" style="height:30px">
                    <td> 핸드폰</td>
                    <td>: <?=$od_b_hp?></td>
                </tr>
                <tr bgcolor="#F8F7F2" style="height:30px">
                    <td> 주 소</td>
                    <td>: <?=sprintf("(%s-%s) %s %s", $od_b_zip1, $od_b_zip2, $od_b_addr1, $od_b_addr2)?></td>
                </tr>
                <tr bgcolor="#F8F7F2" style="height:30px">
                    <td>  전하실 말씀</td>
                    <td>: <?=$od_memo?></td>
                </tr>
                <tr><td colspan=2 height=1 bgcolor=#FFFFFF></td></tr>
                </table>
			<?}?>
            </td>
        </tr>
        </table><br>
        <!-- 배송지정보 END-->
	</div>
<div style="font-size: 10px; text-align: center; margin-left: auto;">본 메일은 <?=$g4[time_ymdhis]?> (<?=get_yoil($g4[time_ymdhis])?>)을 기준으로 작성되었습니다.<br>(C) 2003~2012 OPLE.COM & STOKOREA. All rights Reserved.<br>
고객상담 전화번호 : 070-7678-7004</div>
</div>
</div>
</body>
</html>
