<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$g4['charset']?>">
<title>판매자님께 주문서 메일 드리기</title>
</head>

<style>
body, th, td, form, input, select, text, textarea, caption { font-size: 12px; font-family:굴림;}
.line {border: 1px solid #868F98;}
</style>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" cellspacing="0" cellpadding="0" border=0>
<tr><td width="25" height="25" colspan=3>&nbsp;</td></tr>
<tr>
    <td width="25" valign="top"><img src="<?=$g4['shop_url']?>/mail/img/mail_left.gif" width="25" height="281"></td>
    <td class="line" align=center>
        <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td height="59" background="<?=$g4['shop_url']?>/mail/img/mail_bg2.gif" style='padding-left:20px'>
                <strong><font color="#02253A">본 메일은 <?=$g4[time_ymdhis]?> (<?=get_yoil($g4[time_ymdhis])?>)을 기준으로 작성되었습니다.</font></strong>
            </td>
        </tr>
        </table>
        <p>

        <!-- 주문내역  -->
        <table width="95%" cellpadding="0" cellspacing="0">  
        <colgroup width=200>                    
        <colgroup width=110>
        <colgroup width=150>
        <colgroup width=1>
        <colgroup width=''>
        <tr>
            <td rowspan=6 align=center><a href='<?="$g4[shop_url]/item.php?it_id={$list[it_id]}"?>' target=_blank><?=$list[it_simg]?></a></td>
            <td height=22> ▒  주문제품명</td>
            <td colspan=3>: <B><?=$list[it_name]?></B></td>
        </tr>
        <tr><td colspan=4 bgcolor=#DDDDDD height=1></td></tr>
        <tr>
            <td height=22> ▒  주문번호</td>
            <td>: <font color=#CC3300><B><?=$od_id?></B></font></td>
            <td rowspan=4 bgcolor=#DDDDDD></td>
            <td>&nbsp; ▒  선택옵션 </td>
        </tr>
        <tr><td colspan=2 bgcolor=#DDDDDD height=1></td></tr>
        <tr>
            <td height=22> ▒  수량</td>
            <td>: <b><?=number_format($list[ct_qty])?></b>개</td>
            <td style='padding-left:10px; padding-top:0px'><?=$list[it_opt]?></td>
        </tr>
        <tr><td colspan=4 bgcolor=#DDDDDD height=1></td></tr>
        </table><p>
        <!-- 주문내역 END -->

        <!-- 주문자 정보 -->
        <table width="95%" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td height=30><B>주문하신 분 정보</B></td>
        </tr>
        <tr>
            <td>
                <table width="100%" cellpadding="4" cellspacing="0">
                <colgroup width=110>                    
                <colgroup width=''>
                <tr><td colspan=2 height=2 bgcolor=#DFDED9></td></tr>
                <tr bgcolor="#F8F7F2">
                    <td>▒ 이 름</td>
                    <td>: <?=$od_name?></td>
                </tr>
                <tr bgcolor="#F8F7F2">
                    <td>▒ 전화번호</td>
                    <td>: <?=$od_tel?></td>
                </tr>
                <tr bgcolor="#F8F7F2">
                    <td>▒ 핸드폰</td>
                    <td>: <?=$od_hp?></td>
                </tr>
                <tr bgcolor="#F8F7F2">
                    <td>▒ 주 소</td>
                    <td>: <?=sprintf("(%s-%s) %s %s", $od_zip1, $od_zip2, $od_addr1, $od_addr2)?></td>
                </tr>

                <? if ($od_hope_date) { ?>
                <tr bgcolor="#F8F7F2">
                    <td>▒ 희망배송일</td>
                    <td>: <?=$od_hope_date?> (<?=get_yoil($od_hope_date)?>)</td>
                </tr>
                <? } ?>
                <tr><td colspan=2 height=2 bgcolor=#DFDED9></td></tr>
                </table>
            </td>
        </tr>
        </table>   
        <!-- 주문자 정보 END-->
        
        <!-- 배송지 정보 -->
        <p>
        <table width="95%" align="center" cellpadding="0" cellspacing="0">
        <tr><td height=30><B>배송지 정보</B></td></tr>
        <tr>
            <td>
                <table width="100%" cellpadding="4" cellspacing="0">
                <colgroup width=110>                    
                <colgroup width=''>
                <tr><td colspan=2 height=2 bgcolor=#DFDED9></td></tr>
                <tr bgcolor="#F8F7F2">
                    <td>▒ 이 름</td>
                    <td>: <?=$od_b_name?></td>
                </tr>
                <tr bgcolor="#F8F7F2">
                    <td>▒ 전화번호</td>
                    <td>: <?=$od_b_tel?></td>
                </tr>
                <tr bgcolor="#F8F7F2">
                    <td>▒ 핸드폰</td>
                    <td>: <?=$od_b_hp?></td>
                </tr>
                <tr bgcolor="#F8F7F2">
                    <td>▒ 주 소</td>
                    <td>: <?=sprintf("(%s-%s) %s %s", $od_b_zip1, $od_b_zip2, $od_b_addr1, $od_b_addr2)?></td>
                </tr>
                <tr bgcolor="#F8F7F2">
                    <td> ▒ 전하실 말씀</td>
                    <td>: <?=$od_memo?></td>
                </tr>
                <tr><td colspan=2 height=2 bgcolor=#DFDED9></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td height=30 align=right style='color:#A26217; font-size=11px; font-family:돋움'>이 주문과 관련된 내용은 <B><?= $default[de_admin_company_tel] ?></B>로 연락주시기 바랍니다.</td>            
        </tr>
        </table> 
        <!-- 배송지정보 END-->
       
        <table width=95%>
        <tr><td height=30 align=right></td></tr>
        </table>
    </td>
    <td width="25" valign="top"><img src="<?=$g4['shop_url']?>/mail/img/mail_right.gif" width="25" height="281"></td>
</tr>
</table>

</body>
</html>
