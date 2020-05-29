<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<style type="text/css">
<!--
.m_title    { BACKGROUND-COLOR: #F7F7F7; PADDING-LEFT: 15px; PADDING-top: 5px; PADDING-BOTTOM: 5px; }
.m_padding  { PADDING-LEFT: 15px; PADDING-BOTTOM: 5px; PADDING-TOP: 5px; }
.m_padding2 { PADDING-LEFT: 0px; PADDING-top: 5px; PADDING-BOTTOM: 0px; }
.m_padding3 { PADDING-LEFT: 0px; PADDING-top: 5px; PADDING-BOTTOM: 5px; }
.m_text     { BORDER: #D3D3D3 1px solid; HEIGHT: 18px; BACKGROUND-COLOR: #ffffff; }
.m_text2    { BORDER: #D3D3D3 1px solid; HEIGHT: 18px; BACKGROUND-COLOR: #dddddd; }
.m_textarea { BORDER: #D3D3D3 1px solid; BACKGROUND-COLOR: #ffffff; WIDTH: 100%; WORD-BREAK: break-all; }
.w_message  { font-family:돋움; font-size:9pt; color:#4B4B4B; }
.w_norobot  { font-family:돋움; font-size:9pt; color:#BB4681; }
.w_hand     { cursor:pointer; }
-->
</style>

<script type="text/javascript" src="<?php echo $g4['path'];?>/js/md5.js"></script>
<script type="text/javascript" src="<?php echo $g4['path'];?>/js/sideview.js"></script>

<form id="fregisterform" name=fregisterform method=post onsubmit="return fregisterform_submit(this);" enctype="multipart/form-data" autocomplete="off">
<table width=100% cellspacing=0 cellspacing=0 align=center>

<input type=hidden name='w'                value="<?=$w?>">
<input type=hidden name='url'              value="<?=$urlencode?>">
<input type=hidden name='mb_jumin'         value="<?=$jumin?>">
<input type=hidden name='mb_jumin2'        value="<?=$mb_jumin?>"> <? // 김선용 200811 : 주민번호 별도저장 ?>
<input type=hidden name='mb_id_enabled'    value="" id="mb_id_enabled">
<input type=hidden name="mb_open" value="1">
<?/*
<!--<input type=hidden name=mb_nick_enabled  value="" id="mb_nick_enabled"> 김선용 200805 : 이름=별명 통합사용 -->
*/?>
<input type=hidden name=mb_email_enabled value="" id="mb_email_enabled">
<!-- // 김선용 201309 : 회원가입 프로모션 코드 -->
<input type="hidden" name="mp_event_id" value="<?=$mp_event_id?>" />
<tr><td>

<!--<img src="<?=$member_skin_path?>/img/join_form_title.gif" width="624" height="72">-->
<?
$type_num = ($w == 'u') ? "3" : "2";
$type_str = ($w == 'u') ? "회원정보수정" : "회원가입";
?>

<div class='PageTitle'>
<img src="<?=$g4['path']?>/images/member/member_title0<?=$type_num?>.gif" alt="회원가입" />
</div>

<table width="100%" cellspacing="0" cellpadding="0">
<tr>
    <td bgcolor="#CCCCCC">
        <TABLE cellSpacing=1 cellPadding=0 width=100%>
        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>아이디</TD>
            <TD class=m_padding>
                <input class=m_text maxLength=20 size=20 name="mb_id" minlength="3" alphanumericunderline itemname="아이디" required value="<?=$member[mb_id]?>" <?= ($w == '') ? 'required' : "readonly style='background-color:#dddddd;'";?> onchange="fregisterform.mb_id_enabled.value='';">
                &nbsp;<? if ($w == "") { ?><a onclick="mb_id_check(); return false;" href="#"><img src="<?=$member_skin_path?>/img/join_check_btn.gif" border=0 align=absmiddle></a><? } ?>
                <table height=25 cellspacing=0 cellpadding=0 border=0>
                <tr><td><font color="#66A2C8">※ 영문자, 숫자, _ 만 입력 가능. 최소 3자이상 입력하세요.</font></td></tr>
                </table>
            </TD>
        </TR>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>패스워드</TD>
            <TD class=m_padding><INPUT class=m_text type=password name="mb_password" size=20 maxlength=20 <?=($w=="")?"required":"";?> itemname="패스워드"></TD>
        </TR>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>패스워드 확인</TD>
            <TD class=m_padding><INPUT class=m_text type=password name="mb_password_re" size=20 maxlength=20 <?=($w=="")?"required":"";?> itemname="패스워드 확인"></TD>
        </TR>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>패스워드 분실시 질문</TD>
            <TD class=m_padding>
                <select name=mb_password_q_select onchange="this.form.mb_password_q.value=this.value;">
                    <option value="">선택하십시오.</option>
                    <option value="내가 좋아하는 캐릭터는?">내가 좋아하는 캐릭터는?</option>
                    <option value="타인이 모르는 자신만의 신체비밀이 있다면?">타인이 모르는 자신만의 신체비밀이 있다면?</option>
                    <option value="자신의 인생 좌우명은?">자신의 인생 좌우명은?</option>
                    <option value="초등학교 때 기억에 남는 짝꿍 이름은?">초등학교 때 기억에 남는 짝꿍 이름은?</option>
                    <option value="유년시절 가장 생각나는 친구 이름은?">유년시절 가장 생각나는 친구 이름은?</option>
                    <option value="가장 기억에 남는 선생님 성함은?">가장 기억에 남는 선생님 성함은?</option>
                    <option value="친구들에게 공개하지 않은 어릴 적 별명이 있다면?">친구들에게 공개하지 않은 어릴 적 별명이 있다면?</option>
                    <option value="추억하고 싶은 날짜가 있다면?(예:1994/04/20)">추억하고 싶은 날짜가 있다면?(예:1994/04/20)</option>
                    <option value="다시 태어나면 되고 싶은 것은?">다시 태어나면 되고 싶은 것은?</option>
                    <option value="가장 감명깊게 본 영화는?">가장 감명깊게 본 영화는?</option>
                    <option value="읽은 책 중에서 좋아하는 구절이 있다면?">읽은 책 중에서 좋아하는 구절이 있다면?</option>
                    <option value="기억에 남는 추억의 장소는?">기억에 남는 추억의 장소는?</option>
                    <option value="인상 깊게 읽은 책 이름은?">인상 깊게 읽은 책 이름은?</option>
                    <option value="자신의 보물 제1호는?">자신의 보물 제1호는?</option>
                    <option value="받았던 선물 중 기억에 남는 독특한 선물은?">받았던 선물 중 기억에 남는 독특한 선물은?</option>
                    <option value="자신이 두번째로 존경하는 인물은?">자신이 두번째로 존경하는 인물은?</option>
                    <option value="아버지의 성함은?">아버지의 성함은?</option>
                    <option value="어머니의 성함은?">어머니의 성함은?</option>
                    <option value="가장 여행하고 싶은 나라는?">가장 여행하고 싶은 나라는?</option>
                </select>

                <table width="350" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td class=m_padding2><input class=m_text type=text name="mb_password_q" size=55 required itemname="패스워드 분실시 질문" value="<?=$member[mb_password_q]?>"></td>
                </tr>
                </table>
            </TD>
        </TR>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>패스워드 분실시 답변</TD>
            <TD class=m_padding><input class=m_text type=text name='mb_password_a' size=38 required itemname='패스워드 분실시 답변' value='<?=$member[mb_password_a]?>'></TD>
        </TR>
        </TABLE>
    </td>
</tr>
</table>

<table width="100%" cellspacing="0" cellpadding="0">
<tr>
    <td height="1" bgcolor="#ffffff"></td>
</tr>
</table>

<table width="100%" cellspacing="0" cellpadding="0">
<tr>
    <td bgcolor="#CCCCCC">
        <TABLE cellSpacing=1 cellPadding=0 width=100%>
        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>이름</TD>
            <TD class=m_padding>
                <!-- 한글만 입력받을 경우 <INPUT name=mb_name itemname="이름" required minlength="2" nospace hangul value="<?=$member[mb_name]?>" <?=$member[mb_name]?"readonly class=m_text2":"class=m_text";?>> -->
                <INPUT name=mb_name id=mb_name itemname="이름" required minlength="2" value="<?=$member[mb_name]?>" <?=$member[mb_name]?"readonly class=m_text2":"class=m_text";?>>
            </TD>
        </TR>

		<?// 김선용 200805 : 이름=별명 통합사용 ?>
		<!--
        <?// if ($member[mb_nick_date] <= date("Y-m-d", $g4[server_time] - ($config[cf_nick_modify] * 86400))) { // 별명수정일이 지났다면 수정가능 ?>
        <input type=hidden name=mb_nick_default value='<?//=$member[mb_nick]?>'>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>별명</TD>
            <TD class='m_padding lh'>
                <input class=m_text type=text name='mb_nick' maxlength=20 minlength="2" required itemname="별명" value='<?//=$member[mb_nick]?>' onchange="fregisterform.mb_nick_enabled.value='';">
                &nbsp;<a href="javascript:mb_nick_check();"><img src="<?//=$member_skin_path?>/img/join_check_btn.gif" border=0 align=absmiddle></a>
                <br>별명을 바꾸시면 앞으로 <?//=(int)$config[cf_nick_modify]?>일 이내에는 변경이 안됩니다.
            </TD>
        </TR>
        <?// } else { ?>
        <input type=hidden name="mb_nick" value="<?//=$member[mb_nick]?>">
        <?// } ?>
		-->
		<input type="hidden" name="mb_nick" id="mb_nick" value="<?=$member['mb_nick']?>">

        <input type=hidden name='old_email' value='<?=$member[mb_email]?>'>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>E-mail</TD>
            <TD class='m_padding lh'>
                <INPUT class=m_text type=text name='mb_email' size=38 maxlength=100 email required itemname='E-mail' value='<?=$member[mb_email]?>'>
                &nbsp;<a onclick="mb_email_check();return false;" href="#"><img src="<?=$member_skin_path?>/img/join_check_btn.gif" border=0 align=absmiddle></a>
                <? if ($config[cf_use_email_certify]) { ?>
                    <? if ($w=='') { echo "<br>E-mail 로 발송된 내용을 확인한 후 인증하셔야 회원가입이 완료됩니다."; } ?>
                    <? if ($w=='u') { echo "<br>E-mail 주소를 변경하시면 다시 인증하셔야 합니다."; } ?>
                <? } ?>
            </TD>
        </TR>

        <? if ($w=="") { ?>
            <TR bgcolor="#FFFFFF">
                <TD class=m_title>생년월일</TD>
                <TD class=m_padding><input class=m_text type=text name='mb_birth' size=8 maxlength=8 minlength=8 required numeric itemname='생년월일' value='<?=$member[mb_birth]?>' title='8자리 숫자'><font color="#66A2C8">※ 8자리 숫자만 입력하세요.(예:1980년2월2일생일시 19800202)</font>
                    <!--<a href="javascript:win_calendar('mb_birth', document.getElementById('mb_birth').value, '');"><img src='<?=$member_skin_path?>/img/calendar.gif' border=0 align=absmiddle title='달력 - 날짜를 선택하세요'></a>--></TD>
            </TR>
        <? } else { ?>
            <input type=hidden name=mb_birth value='<?=$member[mb_birth]?>'>
        <? } ?>

        <? if ($member[mb_sex]) { ?>
            <input type=hidden name=mb_sex value='<?=$member[mb_sex]?>'>
        <? } else { ?>
            <TR bgcolor="#FFFFFF">
                <TD class=m_title>성별</TD>
                <TD class=m_padding>
                    <select id=mb_sex name=mb_sex required itemname='성별'>
                    <option value=''>선택하세요
                    <option value='F'>여자
                    <option value='M'>남자
                    </select>
                    <script language="JavaScript">//document.getElementById('mb_sex').value='<?=$member[mb_sex]?>';</script>
                    </td>
            </TR>
        <? } ?>
	</table>

	<TABLE cellSpacing=1 cellPadding=0 width=100%>
		<tr bgcolor="#FFFFFF">
			<td colspan='2' style='padding:20px;'>※ 아래의 사항은 선택사항 입니다.</td>
		</tr>

        <? if ($config[cf_use_homepage]) { ?>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>홈페이지</TD>
            <TD class=m_padding><input class=m_text type=text name='mb_homepage' size=38 maxlength=255 <?=$config[cf_req_homepage]?'required':'';?> itemname='홈페이지' value='<?=$member[mb_homepage]?>'></TD>
        </TR>
        <? } ?>

        <? if ($config[cf_use_tel]) { ?>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>전화번호</TD>
            <TD class=m_padding><input class=m_text type=text name='mb_tel' size=21 maxlength=20 <?=$config[cf_req_tel]?'required':'';?> itemname='전화번호' value='<?=$member[mb_tel]?>'></TD>
        </TR>
        <? } ?>

        <? if ($config[cf_use_hp]) { ?>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>핸드폰번호</TD>
            <TD class=m_padding><input class=m_text type=text name='mb_hp' size=21 maxlength=20 <?=$config[cf_req_hp]?'required':'';?> itemname='핸드폰번호' value='<?=$member[mb_hp]?>'></TD>
        </TR>
        <? } ?>

        <? if ($config[cf_use_addr]) { ?>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>주소</TD>
            <TD valign="middle" class=m_padding>
                <table width="330" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td height="25"><input class=m_text type=text name='mb_zip1' size=4 maxlength=3 readonly <?=$config[cf_req_addr]?'required':'';?> itemname='우편번호 앞자리' value='<?=$member[mb_zip1]?>'>
                         -
                        <input class=m_text type=text name='mb_zip2' size=4 maxlength=3 readonly <?=$config[cf_req_addr]?'required':'';?> itemname='우편번호 뒷자리' value='<?=$member[mb_zip2]?>'>
                        &nbsp;<a href="#" onclick="win_zip('fregisterform', 'mb_zip1', 'mb_zip2', 'mb_addr1', 'mb_addr2', 'mb_addr_jibeon','mb_zonecode'); return false;"><img src="<?=$member_skin_path?>/img/post_search_btn.gif" border=0 align=absmiddle></a></td>
                </tr>
                <tr>
                    <td height="25" colspan="2"><input class=m_text type=text name='mb_addr1' size=60 readonly <?=$config[cf_req_addr]?'required':'';?> itemname='주소' value='<?=$member[mb_addr1]?>'></td>
                </tr>
                <tr>
                    <td height="25" colspan="2"><input class=m_text type=text name='mb_addr2' size=60 <?=$config[cf_req_addr]?'required':'';?> itemname='상세주소' value='<?=$member[mb_addr2]?>'></td>
 				</tr>
                <tr>
                    <td height="25" colspan="2">
                        <input type="hidden" name="mb_addr_jibeon" value="<?=$member['mb_addr_jibeon']; ?>">
						<input type="hidden" name='mb_zonecode' value="<?php echo $member['mb_zonecode'];?>" />
                        <span id="mb_addr_jibeon"><?=($member['mb_addr_jibeon'] ? '지번주소 : '.$member['mb_addr_jibeon'] : ''); ?></span>
                    </td>
                </tr>
                </table>
            </TD>
        </TR>
        <? } ?>

        </TABLE>
    </td>
</tr>
</table>



<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td bgcolor="#CCCCCC">
        <TABLE cellSpacing=1 cellPadding=0 width=100%>

        <?/*
		<? if ($config[cf_use_signature]) { ?>
        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>서명</TD>
            <TD class=m_padding><textarea name=mb_signature class=m_textarea rows=3 style='width:95%;' <?=$config[cf_req_signature]?'required':'';?> itemname='서명'><?=$member[mb_signature]?></textarea></TD>
        </TR>
        <? } ?>

        <? if ($config[cf_use_profile]) { ?>
        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>자기소개</TD>
            <TD class=m_padding><textarea name=mb_profile class=m_textarea rows=3 style='width:95%;' <?=$config[cf_req_profile]?'required':'';?> itemname='자기 소개'><?=$member[mb_profile]?></textarea></TD>
        </TR>
        <? } ?>

        <? if ($member[mb_level] >= $config[cf_icon_level]) { ?>
        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>회원아이콘</TD>
            <TD class=m_padding><INPUT class=m_text type=file name='mb_icon' size=30>
                <table width="350" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class=m_padding3>* 이미지 크기는 가로(<?=$config[cf_member_icon_width]?>픽셀)x세로(<?=$config[cf_member_icon_height]?>픽셀) 이하로 해주세요.<br>&nbsp;&nbsp;(gif만 가능 / 용량:<?=number_format($config[cf_member_icon_size])?>바이트 이하만 등록됩니다.)
                            <? if ($w == "u" && file_exists($mb_icon)) { ?>
                                <br><img src='<?=$mb_icon?>' align=absmiddle> <input type=checkbox name='del_mb_icon' value='1'>삭제
                            <? } ?>
                        </td>
                    </tr>
                </table>
			</TD>
        </TR>
        <? } ?>
		*/?>

        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>메일링서비스</TD>
            <TD class=m_padding><input type=checkbox name=mb_mailling value='1' <?=($w=='' || $member[mb_mailling])?'checked':'';?>>정보 메일을 받겠습니다.</TD>
        </TR>
        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>SMS 수신여부</TD>
            <TD class=m_padding><input type=checkbox name=mb_sms value='1' <?=($w=='' || $member[mb_sms])?'checked':'';?>>핸드폰 문자메세지를 받겠습니다.</TD>
        </TR>



        <? if ($w == "" && $config[cf_use_recommend]) { ?>
        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>추천인아이디</TD>
            <TD class=m_padding><input type=text name=mb_recommend class=m_text></TD>
        </TR>
        <? } ?>

        </TABLE>
    </td>
</tr>
</table>

<? if ($w == "" && $config[cf_use_norobot]) { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td height="1" bgcolor="#ffffff"></td>
</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td bgcolor="#CCCCCC">
        <TABLE cellSpacing=1 cellPadding=0 width=100%>
        <TR bgcolor="#FFFFFF">
            <td width="160" height="28" class=m_title><?=$norobot_str?></td>
            <td class=m_padding><input class=m_text type=text name='wr_key' required itemname='자동등록방지' size=15>&nbsp;&nbsp;* 왼쪽의 글자중 <FONT COLOR="red">빨간글자</font>만 순서대로 입력하세요.</td>
        </tr>
        </table>
    </td>
</tr>
</table>
<? } ?>


<p style='text-align:center;padding:30px 0;'>
    <INPUT type=image src="<?=$member_skin_path?>/img/join_ok_btn.gif" border=0 accesskey='s'>
</p>
</td></tr>

</table>
</form>

<script type="text/Javascript">
with (document.fregisterform) {
    if (w.value == "")
        mb_id.focus();
    else {
        mb_password.focus();
        //mb_nick_enabled.value = 1;
    }
}

// submit 최종 폼체크
function fregisterform_submit(f)
{

    if (f.w.value == "") {
        if (f.mb_id_enabled.value == "") {
            alert("회원아이디 중복확인을 해주십시오.");
            f.mb_id.focus();
            return false;
        } else if (f.mb_id_enabled.value == -1) {
            alert("'"+f.mb_id.value+"'은(는) 이미 가입된 회원아이디이므로 사용하실 수 없습니다.");
            f.mb_id.focus();
            return false;
        }
    }
	/* 김선용 200805 : 이름=별명 통합사용
    if ((f.w.value == "" && f.mb_nick_enabled.value == "") ||
        (f.w.value == "u" && f.mb_nick_enabled.value == "" && f.mb_nick.defaultValue != f.mb_nick.value)) {
        alert("별명 중복확인을 해주십시오.");
        f.mb_nick.focus();
        return false;
    } else if (f.mb_nick_enabled.value == -1) {
        alert("'"+f.mb_nick.value+"'은(는) 이미 등록된 별명이므로 사용하실 수 없습니다.");
        f.mb_nick.focus();
        return false;
    }
	*/

    var id = prohibit_id_check(f.mb_id.value);
    if (id) {
        alert("'"+id+"'은(는) 사용하실 수 없는 회원아이디입니다.");
        f.mb_id.focus();
        return false;
    }

    if (f.mb_password.value != f.mb_password_re.value) {
        alert("패스워드가 같지 않습니다.");
        f.mb_password_re.focus();
        return false;
    }

	// 김선용 201205 : 이름. 한글,영문, 공백포함
	var pattern = /([^가-힣\x20^a-z^A-Z^\s])/i;
	if(f.mb_name.value == ''){
		alert("이름을 입력해 주십시오.");
		f.mb_name.focus();
		return false;
	}
	if(pattern.test(f.mb_name.value)){
		alert("이름은 한글,영문,공백만 가능합니다.");
		f.mb_name.focus();
		return false;
	}

    /*
    // 사용할 수 없는 별명을 사용하고자 하는 경우에는 이 주석을 제거하십시오.
    if (!prohibit_id_check(f.mb_nick.value))
    {
        alert("'"+f.mb_nick.value + "'은(는) 사용하실 수 없는 별명입니다.");
        f.mb_nick.focus();
        return false;
    }
    */

    var domain = prohibit_email_check(f.mb_email.value);
    if (domain) {
        alert("'"+domain+"'은(는) 사용하실 수 없는 메일입니다.");
        f.mb_email.focus();
        return false;
    }

    if ((f.w.value == "" && f.mb_email_enabled.value == "") ||
        (f.w.value == "u" && f.mb_email_enabled.value == "" && f.mb_email.defaultValue != f.mb_email.value)) {
        alert("E-mail 중복확인을 해주십시오.");
        f.mb_email.focus();
        return false;
    } else if (f.mb_email_enabled.value == -1) {
        alert("'"+f.mb_email.value+"'은(는) 이미 다른 회원이 사용하는 E-mail이므로 사용하실 수 없습니다.");
        return false;
    }

	if(typeof f.mb_tel != 'undefined'){
		if(f.mb_tel.value.replace(/[^a-zA-Zㄱ-힣]/g,'').length>0){
			alert('올바른 전화번호를 입력해 주세요.');
			f.mb_tel.focus();
			return false;
		}
	}

	if(typeof f.mb_hp != 'undefined'){
		if(f.mb_hp.value.replace(/[^a-zA-Zㄱ-힣]/g,'').length>0){
			alert('올바른 전화번호를 입력해 주세요.');
			f.mb_hp.focus();
			return false;
		}
	}

	if (typeof f.mb_birth != 'undefined'){
		if(isValidDate(f.mb_birth.value) == false){
			alert('올바르지 않은 날짜 입니다.');
			return false;
		}
	}


    if (typeof f.mb_icon != "undefined")
    {
        if (f.mb_icon.value)
        {
            if (!f.mb_icon.value.toLowerCase().match(/.(gif)$/i))
            {
                alert("회원아이콘이 gif 파일이 아닙니다.");
                f.mb_icon.focus();
                return false;
            }
        }
    }

    if (typeof(f.mb_recommend) != 'undefined')
    {
        if (f.mb_id.value == f.mb_recommend.value)
        {
            alert("본인을 추천할 수 없습니다.");
            f.mb_recommend.focus();
            return false;
        }
    }

    if (typeof(f.wr_key) != "undefined") {
        if (hex_md5(f.wr_key.value) != md5_norobot_key) {
            alert("자동등록방지용 빨간글자가 순서대로 입력되지 않았습니다.");
            f.wr_key.focus();
            return false;
        }
    }

    f.action = "./register_form_update.php";
    return true;
}

// 회원아이디 검사
function mb_id_check()
{
    var f = document.fregisterform;

    if (f.mb_id.value == "") {
        alert("회원 아이디를 입력하세요.");
        f.mb_id.focus();
        return false;
    }

	if (f.mb_id.value.replace(/[0-9a-zA-Z_]/g,'').length > 0) {
        alert("회원 아이디는 영문자,숫자,_ 만 입력 가능합니다.");
        f.mb_id.focus();
        return false;
    }

	if (f.mb_id.value.length < 3) {
        alert("회원 아이디는 최소 3자 이상 입력해 주세요");
        f.mb_id.focus();
        return false;
    }

    var id = prohibit_id_check(f.mb_id.value);
    if (id) {
        alert("'"+id + "'은(는) 사용하실 수 없는 회원아이디입니다.");
        f.mb_id.focus();
        return false;
    }

    win_open(g4_path+"/"+g4_bbs+"/member_id_check.php?mb_id="+document.fregisterform.mb_id.value, "hiddenframe");
}

// 별명 검사
/* 김선용 200805 : 이름=별명 통합사용
function mb_nick_check()
{
    var f = document.fregisterform;

    if (f.mb_nick.value == "") {
        alert("별명을 입력하세요.");
        f.mb_nick.focus();
        return false;
    }

    var id = prohibit_id_check(f.mb_nick.value);
    if (id) {
        alert("'"+id + "'은(는) 사용하실 수 없는 별명입니다.");
        f.mb_nick.focus();
        return false;
    }

    if (f.mb_nick.defaultValue == f.mb_nick.value && f.mb_nick.value != "") {
        alert("별명이 바뀌지 않았으므로 중복확인 하실 필요가 없습니다.");
        return false;
    }

    //win_open(g4_path+"/"+g4_bbs+"/member_nick_check.php?mb_nick="+document.fregisterform.mb_nick.value, "hiddenframe");
    win_open(g4_path+"/"+g4_bbs+"/member_nick_check.php?mb_nick="+encodeURIComponent(document.fregisterform.mb_nick.value), "hiddenframe");
}
*/

// E-mail 검사
function mb_email_check()
{
    if (document.fregisterform.mb_email.value == "") {
        alert("E-mail을 입력하세요.");
        return false;
    }

    win_open(g4_path+"/"+g4_bbs+"/member_email_check.php?mb_email="+document.fregisterform.mb_email.value, "hiddenframe");
}

function mb_id_change()
{
    if (document.fregisterform.mb_id.value != document.fregisterform.mb_id.defaultValue)
        document.fregisterform.mb_id_enabled.value = "";
}

// 금지 아이디, 별명 검사
function prohibit_id_check(id)
{
    id = id.toLowerCase();

    var prohibit_id = "<?=trim(strtolower($config[cf_prohibit_id]))?>";
    var s = prohibit_id.split(",");

    for (i=0; i<s.length; i++) {
        if (s[i] == id)
            return id;
    }
    return "";
}

// 금지 메일 도메인 검사
function prohibit_email_check(email)
{
    email = email.toLowerCase();

    var prohibit_email = "<?=trim(strtolower(preg_replace("/(\r\n|\r|\n)/", ",", $config[cf_prohibit_email])));?>";
    var s = prohibit_email.split(",");
    var tmp = email.split("@");
    var domain = tmp[tmp.length - 1]; // 메일 도메인만 얻는다

    for (i=0; i<s.length; i++) {
        if (s[i] == domain)
            return domain;
    }
    return "";
}

// 날짜 유효성검사
function isValidDate(param) {
	try
	{
		param = param.replace(/-/g,'');


		// 자리수가 맞지않을때
		if( isNaN(param) || param.length!=8 ) {
			return false;
		}


		var now_date = new Date();
		var now_year = now_date.getFullYear();
		var now_month = now_date.getMonth() + 1;
		var now_day = now_date.getDate();
		if(String(now_month).length<2){
			now_month = '0'+now_month;
		}
		if(String(now_day).length<2){
			now_day = '0'+now_day;
		}


		var year = param.substring(0, 4);
		var month = param.substring(4, 6);
		var day = param.substring(6, 8);

		var dd = day / 0;



		// 년도가 현재보다 미래이거나 1900년도 이하일 경우는 false
		if( year > now_year || year < 1900 ){
			return false;
		}

		if( month<1 || month>12 ) {
			return false;
		}

		var maxDaysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
		var maxDay = maxDaysInMonth[month-1];

		// 윤년 체크
		if( month==2 && ( year%4==0 && year%100!=0 || year%400==0 ) ) {
			maxDay = 29;
		}

		if( day<=0 || day>maxDay ) {
			return false;
		}




		// 미래는 생일이 될수 없다
		var now_sum = Number(String(now_year)+String(now_month)+String(now_day));
		var date_sum = Number(String(year)+String(month)+String(day));



		if(now_sum < date_sum){
			return false;
		}



		return true;

	} catch (err) {
		return false;
	}
}
</script>
