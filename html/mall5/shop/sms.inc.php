<?
if (!defined("_GNUBOARD_")) exit; // ���� ������ ���� �Ұ�
if (!defined("_SMS_")) exit; // ���� ������ ���� �Ұ�

// ������
if (file_exists("./DEMO")) return;

// ȸ���
$sms_contents = preg_replace("/{ȸ���}/", $default[de_admin_company_name], $sms_contents);

// SMS �ϰ������� ���Ͽ� ������ _$microtime �� ����
$microtime = md5(uniqid(rand(), true));
?>
<html>
<head>
<title>SMS</title>
<meta http-equiv="content-type" content="text/html; charset=<?=$g4['charset']?>">
</head>
<body>
<iframe name='hiddenFrame_<?=$microtime?>' width=0 height=0></iframe>

<form name="smsform_<?=$microtime?>" action="http://biz.xonda.net/biz/sms/process_F.asp" method="post" target="hiddenFrame_<?=$microtime?>"> <?//<%'sms ���� Xonda URL%>?>

<input type="hidden" name="send_number" value="<?=$send_number?>">          <?//<%'�߼��� �ڵ��� ��ȣ(���ڸ�����) 15���̳�%>?>
<input type="hidden" name="receive_number" value="<?=$receive_number?>">    <?//<%'������ �ڵ��� ��ȣ(������ ��� 0123456789,0123456789 / �ܹ��ϰ�� 15�� �̳� )%>?>
<input type="hidden" name="biz_id" value="<?=$default[de_xonda_id]?>">      <?//<%'Xonda/Biz ID %>?>
<input type="hidden" name="return_url" value="<?=$g4[shop_url]?>/smsresult.php">               <?//<%'sms ������ ���ƿ� URL %>?>
<input type="hidden" name="sms_contents" value="<?=$sms_contents?>">        <?//<%'������ �޼��� 80�� �̳�'%>?>

<input type="hidden" name="reserved_flag" value="false">    <?//<%' true : ����, false : ���%>?>
<input type="hidden" name="reserved_year" value="" >        <?//<%'����⵵ 4�� (���ڸ� ����, ����� ~ ����� + 1) %>?>
<input type="hidden" name="reserved_month" value="" >       <?//<%'����� 2�� (���ڸ� ����, 1 ~ 12) %>?>
<input type="hidden" name="reserved_day" value="" >         <?//<%'������ 2�� (���ڸ� ����, 1 ~ 31) %>?>
<input type="hidden" name="reserved_hour" value="" >        <?//<%'����ð� 2�� (���ڸ� ����, 0 ~ 23) %>?>
<input type="hidden" name="reserved_minute" value="" >      <?//<%'����� 2�� (���ڸ� ����, 0 ~ 59) %>?>

<input type="hidden" name="usrdata1" value="<?=$usrdata1?>">    <?//<%'��Ÿ �ǵ���������%>?>
<input type="hidden" name="usrdata2" value="<?=$usrdata2?>">    <?//<%'��Ÿ �ǵ���������%>?>
<input type="hidden" name="usrdata3" value="<?=$usrdata3?>">    <?//<%'��Ÿ �ǵ���������%>?>

</form>

<script language="JavaScript">
document.smsform_<?=$microtime?>.submit();
</script>
</body>
</html>