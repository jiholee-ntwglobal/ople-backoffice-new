<?
include_once("./_common.php");

if (!$is_member)
    alert_close('회원만 메일을 발송할 수 있습니다.');

// 스팸을 발송할 수 없도록 세션에 아무값이나 저장하여 hidden 으로 넘겨서 다음 페이지에서 비교함
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);

$sql = " select it_name from $g4[yc4_item_table] where it_id='$it_id' ";
$it = sql_fetch($sql);
if($it['it_name']){
	$it['it_name'] = get_item_name($it['it_name']);
}
if (!$it[it_name])
    alert_close("등록된 상품이 아닙니다.");

$g4[title] =  "$it[it_name] - 추천하기";
include_once("$g4[path]/head.sub.php");
?>

<div class="pop_title">
	<p><?=get_text($g4[title])?></p>
</div>

<div class="pop_style">
<form name="fitemrecommend" method="post" action="./itemrecommendmail.php" onsubmit="return fitemrecommend_check(this);" style='margin:0px;' autocomplete='off'>
<input type=hidden name=token value='<?=$token?>'>
<input type=hidden name=it_id value='<?=$it_id?>'>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='list_styleC'>
<colgroup>
	<col width='130px' />
	<col />
</colgroup>
<tbody>
	<tr>
		<th>추천하실 분 E-mail</th>
        <td><input type=text id='to_email' name='to_email' required itemname='추천하실 분 E-mail' class=ed style="width:97%;"></td>
    </tr>
    <!-- <tr align=center>
		<th></th>
         <td>※ 추천하실 분이 여러명인 경우 E-mail을 컴마(,)로 구분하세요. 최대 3명</td>
    </tr> -->
    <tr>
         <th>제목</th>
         <td><input type=text name='subject' class=ed style='width:97%;' required itemname='제목'></td>
    </tr>
    <tr>
         <th>내용</th>
         <td><textarea name='content' rows=10 style='width:97%;' required itemname='내용' class=ed></textarea></td>
    </tr>
</tbody>
</table>
<p style='height:50px;text-align:center;line-height:50px;'>  
    <input id=btn_submit type=image src="<?=$g4[shop_img_path]?>/btn_confirmS.gif" border=0 style='padding:0;margin:0;'>
    <a href="javascript:window.close();"><img src="<?=$g4[shop_img_path]?>/btn_close.gif" border="0"></a>
</p>
</form>
</div>

<script type="text/javascript">
function fitemrecommend_check(f)
{
    return true;
}

document.getElementById('to_email').focus();
</script>

<?
include_once("$g4[path]/tail.sub.php");
?>