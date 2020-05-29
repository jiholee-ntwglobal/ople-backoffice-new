<?
// 회원가입축하 메일 (회원님께 발송)
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$g4[charset]?>">
<title><?=$wr_subject?> 메일</title>
</head>


<body style="padding:30px; font-size: 13px; font-family:굴림; margin: 0; color:#313131"> 
<div style="font-family: arial, verdana, sans-serif; width:100%; margin: 0 auto;">
<div style="width:600px;height: auto;padding: 10px;border: 1px solid #99999 ;position: relative;">
	<div style="width:580px; height: 100px;border-bottom: 4px solid #ff931e;">
		<div style="float: left; margin-top: 50px;"><a href="http://ople.com"><img src="http://115.68.20.84/main/email_logo.gif"></a></div>
		<div style="float: right; margin-top: 15px;"><a href="http://ople.com/mall5/shop/25m.php"><img src="http://115.68.20.84/main/email_banner.gif"></a></div>
		</div>
	<div style="padding: 20px; margin-bottom: 0 ;text-align: left;">
		<table width="550" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <table width="550" border="0" cellspacing="0" cellpadding="4">
                            <tr>   
                                <td width="20%" height="30" bgcolor="#ffffff">제목</td> 
                                <td width="80%" bgcolor="#ffffff"><?=$wr_subject?></td>
                            </tr>
                            <tr bgcolor="#ffffff"> 
                                <td height="2" colspan="2"></td>
                            </tr>
                            <tr> 
                                <td height="30" bgcolor="#ffffff">게시자</td>
                                <td bgcolor="#ffffff" ><?=$wr_name?></td>
                            </tr>
                        </table>
                <p>

                <table width="550" border="0" align="center" cellpadding="4" cellspacing="0">
                <tr><td height="400" valign="top" style="word-break:break-all;min-height:400px"><div style="border-top: 1px solid #ff931e;padding-top:15px;"><?=$wr_content?></div></td></tr>
                </table>
                <p>

                        <table width="550" border="0" cellpadding="0" cellspacing="0" style="table-layout:fixed">
                            <tr>
                                <td height="2" bgcolor="#E0E0E0" align="center"></td>
                            </tr>
                            <tr> 
                                <td height="25" bgcolor="#ffffff" align="center">홈페이지에서도 게시물을 확인하실 수 있습니다.[<a href='<?=$link_url?>'>바로가기</a>]<br><br>(C) 2003~2012 OPLE.COM & STOKOREA. All rights Reserved.<br>
고객상담 전화번호 : 070-7678-7004</td>
                            </tr>
                        </table>
            </td>
        </tr>
        </table>
	</div>
</div>
</body>
</html>
   