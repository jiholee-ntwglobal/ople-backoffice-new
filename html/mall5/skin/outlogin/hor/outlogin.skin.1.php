<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include("$g4[path]/config.php");

if ($g4['https_url']) {
    $outlogin_url = $_GET['url'];
    if ($outlogin_url) {
        if (preg_match("/^\.\.\//", $outlogin_url)) {
            $outlogin_url = urlencode($g4[url]."/".preg_replace("/^\.\.\//", "", $outlogin_url));
        }
        else {
            $purl = parse_url($g4[url]);
            if ($purl[path]) {
                $path = urlencode($purl[path]);
                $urlencode = preg_replace("/".$path."/", "", $urlencode);
            }
            $outlogin_url = $g4[url].$urlencode;
        }
    }
    else {
        $outlogin_url = $g4[url];
    }
}
else {
    $outlogin_url = $urlencode;
}

?>

<script type="text/javascript" src="<?=$g4[path]?>/js/capslock.js"></script>
<script type="text/javascript">
// 엠파스 로긴 참고
var bReset = true;
function chkReset(f)
{
    if (bReset) { if ( f.mb_id.value == '아이디' ) f.mb_id.value = ''; bReset = false; }
    document.getElementById("pw1").style.display = "none";
    document.getElementById("pw2").style.display = "";
}
</script>


<!-- 로그인 전 외부로그인 시작 -->
<form name="fhead" method="post"  autocomplete="off" style="margin:0px;">
<input type="hidden" name="url" value="<?=$outlogin_url?>">
<table width="400" border="0" align="center" cellpadding="0" cellspacing="0">
<input name="mb_id" type="text" class="top2" id="mb_id" size="16"  value="아이디" onFocus="this.value=''"/>                      
<input name="mb_password" type="password" class="top2" id="mb_password" size="16" value="패스워드" onFocus="this.value=''" />
<input type="checkbox" name="auto_login" value="1" onclick="if (this.checked) { if (confirm('자동로그인을 사용하시면 다음부터 회원아이디와 패스워드를 입력하실 필요가 없습니다.\n\n\공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?')) { this.checked = true; } else { this.checked = false; } }">
<a href="#" onclick='return fhead_submit();'><img src="<?=$g4['opskin_path']?>/img/bt_login_off.gif" name="Image3" width="51" height="21" border="0" id="Image3" /></a>
<a href="<?=$subpage_url?>&subcon=Login3"><img src="<?=$g4['opskin_path']?>/img/bt_pw_off.gif" name="Image2" width="66" height="21" border="0" id="Image2" /></a>
<a href="<?=$subpage_url?>&subcon=Login2" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image1','','<?=$g4['opskin_path']?>/img/bt_join_on.gif',1)"><img src="<?=$g4['opskin_path']?>/img/bt_join_off.gif" name="Image1" width="54" height="21" border="0" id="Image1" /></a>
</form>                  

<script type="text/javascript">
function fhead_submit()
{
	var f = eval("document.fhead");
	
    if (!document.getElementById("mb_id").value) {
        alert("회원아이디를 입력하십시오.");
        f.mb_id.focus();
        return false;
    }

    if (document.getElementById('mb_password').style.display!='none' && !document.getElementById('mb_password').value) {
        alert("패스워드를 입력하십시오.");
        f.mb_password.focus();
        return false;
    }


        f.action = "/bbs/login_check.php";
        f.submit();

    return true;
}
</script>
<!-- 로그인 전 외부로그인 끝 -->
