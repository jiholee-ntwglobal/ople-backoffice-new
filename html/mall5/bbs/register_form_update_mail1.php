<?
// 회원가입축하 메일 (회원님께 발송)
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$g4[charset]?>">
<title>회원가입 축하 메일</title>
</head>

<body style="padding:30px; font-size: 13px; font-family:굴림; margin: 0; color:#313131">
<div style="font-family: arial, verdana, sans-serif; width:100%; margin: 0 auto;">
<div style="width:600px;height: auto;padding: 10px;border: 1px solid #999;position:relative;">
	<div style="height: 100px;border-bottom: 4px solid #ff931e;">
		<div style="float: left; margin-top: 50px;"><a href="http://ople.com"><img src="http://115.68.20.84/main/email_logo.gif"></a></div>
		<div style="float: right; margin-top: 15px;"><a href="http://ople.com/mall5/shop/25m.php"><img src="http://115.68.20.84/main/email_banner.gif"></a></div>
		</div>
	<div style="padding: 90px 0px 0px 20px; margin-bottom: 200px;text-align: left;">
		<img src="http://115.68.20.84/main/email_welcome.gif"><br><br>
		<span style="font-family:Arial, Helvetica, sans-serif, NanumGothic, 돋움, Apple SD Gothic Neo; font-size: 18px;color:#fa5a00;"><b><?=$mb_name?></b> 님의 회원가입을 진심으로 축하합니다.</span><br>
		<? if ($config[cf_use_email_certify]) { ?>
        아래의 주소를 클릭하시면 회원가입이 완료됩니다.<br>
        <a href='<?=$certify_href?>'><b><?=$certify_href?></b></a><br>
         <? } ?>

         회원님의 성원에 보답하고자 더욱 더 열심히 하겠습니다.<br>
         감사합니다.
	</div>
<div style="font-size: 10px; text-align: center; margin-left: auto;">(C) 2003~2012 OPLE.COM & STOKOREA. All rights Reserved.<br>
고객상담 전화번호 : 070-7678-7004</div>
</div>
</div>
</body>
</html>
