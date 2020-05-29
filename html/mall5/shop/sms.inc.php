<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
if (!defined("_SMS_")) exit; // 개별 페이지 접근 불가

// 데모라면
if (file_exists("./DEMO")) return;

// 회사명
$sms_contents = preg_replace("/{회사명}/", $default[de_admin_company_name], $sms_contents);

// SMS 일괄전송을 위하여 변수명에 _$microtime 을 붙임
$microtime = md5(uniqid(rand(), true));
?>
<html>
<head>
<title>SMS</title>
<meta http-equiv="content-type" content="text/html; charset=<?=$g4['charset']?>">
</head>
<body>
<iframe name='hiddenFrame_<?=$microtime?>' width=0 height=0></iframe>

<form name="smsform_<?=$microtime?>" action="http://biz.xonda.net/biz/sms/process_F.asp" method="post" target="hiddenFrame_<?=$microtime?>"> <?//<%'sms 전송 Xonda URL%>?>

<input type="hidden" name="send_number" value="<?=$send_number?>">          <?//<%'발송자 핸드폰 번호(숫자만기입) 15자이내%>?>
<input type="hidden" name="receive_number" value="<?=$receive_number?>">    <?//<%'수신자 핸드폰 번호(동보일 경우 0123456789,0123456789 / 단문일경우 15자 이내 )%>?>
<input type="hidden" name="biz_id" value="<?=$default[de_xonda_id]?>">      <?//<%'Xonda/Biz ID %>?>
<input type="hidden" name="return_url" value="<?=$g4[shop_url]?>/smsresult.php">               <?//<%'sms 전송후 돌아올 URL %>?>
<input type="hidden" name="sms_contents" value="<?=$sms_contents?>">        <?//<%'전송할 메세지 80자 이내'%>?>

<input type="hidden" name="reserved_flag" value="false">    <?//<%' true : 예약, false : 즉시%>?>
<input type="hidden" name="reserved_year" value="" >        <?//<%'예약년도 4자 (숫자만 기입, 현재년 ~ 현재년 + 1) %>?>
<input type="hidden" name="reserved_month" value="" >       <?//<%'예약월 2자 (숫자만 기입, 1 ~ 12) %>?>
<input type="hidden" name="reserved_day" value="" >         <?//<%'예약일 2자 (숫자만 기입, 1 ~ 31) %>?>
<input type="hidden" name="reserved_hour" value="" >        <?//<%'예약시간 2자 (숫자만 기입, 0 ~ 23) %>?>
<input type="hidden" name="reserved_minute" value="" >      <?//<%'예약분 2자 (숫자만 기입, 0 ~ 59) %>?>

<input type="hidden" name="usrdata1" value="<?=$usrdata1?>">    <?//<%'기타 되돌려받을값%>?>
<input type="hidden" name="usrdata2" value="<?=$usrdata2?>">    <?//<%'기타 되돌려받을값%>?>
<input type="hidden" name="usrdata3" value="<?=$usrdata3?>">    <?//<%'기타 되돌려받을값%>?>

</form>

<script language="JavaScript">
document.smsform_<?=$microtime?>.submit();
</script>
</body>
</html>