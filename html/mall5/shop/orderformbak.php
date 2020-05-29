<?
include_once("./_common.php");

// 장바구니가 비어있는가?
$tmp_on_uid = get_session('ss_on_uid');
if (get_cart_count($tmp_on_uid) == 0) 
    alert("장바구니가 비어 있습니다.", "./cart.php");

// 포인트 결제 대기 필드 추가
//sql_query(" ALTER TABLE `$g4[yc4_order_table]` ADD `od_temp_point` INT NOT NULL AFTER `od_temp_card` ", false);

$g4[title] = "주문서 작성";

include_once("./_head.php");
?>
<!--<img src="<?=$g4[shop_img_path]?>/top_orderform.gif" border="0"><p>-->
<div style="padding-top:20px;"></div>
<table width="672" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td width="319"><img src="<?=$g4['path']?>/images/category/category_title01_a.gif" width="319" height="26"></td>
	<td width="353" align="right" class="font11">HOME &gt; <span class="font11_orange">주문서 작성</span></td>
</tr>
<tr><td height="2" colspan="2" bgcolor="#fa5a00"></td></tr>
</table><p>

<?
$s_page = 'orderform.php';
$s_on_uid = $tmp_on_uid;
include_once("./cartsub.inc.php");
?>

<form name=forderform method=post action="./orderreceipt.php" onsubmit="return forderform_check(this);" autocomplete=off>
<input type=hidden name=od_amount    value='<?=$tot_sell_amount?>'>
<input type=hidden name=od_send_cost value='<?=$send_cost?>'>

<!-- 주문하시는 분 -->
<table width=100% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
    <td bgcolor=#F3F2FF align=center><img src='<?=$g4[shop_img_path]?>/t_data01.gif'></td>
    <td bgcolor=#FAFAFA style='padding-left:10px'>
        <table cellpadding=3>
        <colgroup width=100>
        <colgroup width=''>
        <tr>
            <td>이름</td>
            <td><input type=text id=od_name name=od_name value='<?=$member[mb_name]?>' maxlength=20 class=ed></td>
        </tr>

        <? if (!$is_member) { // 비회원이면 ?>
        <tr>
            <td>비밀번호</td>
            <td><input type=password name=od_pwd class=ed maxlength=20>
                영,숫자 3~20자 (주문서 조회시 필요)</td>
        </tr>
        <? } ?>

        <tr>
            <td>전화번호</td>
            <td><input type=text name=od_tel value='<?=$member[mb_tel]?>' maxlength=20 class=ed></td>
        </tr>
        <tr>
            <td>핸드폰</td>
            <td><input type=text name=od_hp value='<?=$member[mb_hp]?>' maxlength=20 class=ed></td>
        </tr>
        <tr>
            <td rowspan=2>주 소</td>
            <td>
                <input type=text name=od_zip1 size=3 maxlength=3 value='<?=$member[mb_zip1]?>' class=ed readonly>
                -
                <input type=text name=od_zip2 size=3 maxlength=3 value='<?=$member[mb_zip2]?>' class=ed readonly>
                <a href="javascript:;" onclick="win_zip('forderform', 'od_zip1', 'od_zip2', 'od_addr1', 'od_addr2');"><img 
                    src="<?=$g4[shop_img_path]?>/btn_zip_find.gif" border="0" align=absmiddle></a>
            </td>
        </tr>
        <tr>
            <td>
                <input type=text name=od_addr1 size=35 maxlength=50 value='<?=$member[mb_addr1]?>' class=ed readonly>
                <input type=text name=od_addr2 size=15 maxlength=50 value='<?=$member[mb_addr2]?>' class=ed> (상세주소)
            </td>
        </tr>
        <tr>
            <td>E-mail</td>
            <td><input type=text name=od_email size=35 maxlength=100 value='<?=$member[mb_email]?>' class=ed></td>
        </tr>

        <? if ($default[de_hope_date_use]) { // 배송희망일 사용 ?>
        <tr>
            <td>희망배송일</td>
            <td><select name=od_hope_date>
                <option value=''>선택하십시오.
                <? 
                for ($i=0; $i<7; $i++) {
                    $sdate = date("Y-m-d", time()+86400*($default[de_hope_date_after]+$i));
                    echo "<option value='$sdate'>$sdate (".get_yoil($sdate).")\n";
                }
                ?>
                </select></td>
        </tr>
        <? } ?>
        </table>
    </td>
</tr>
</table>

<!-- 받으시는 분 -->
<table width=100% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
    <td bgcolor=#F3F2FF align=center><img src='<?=$g4[shop_img_path]?>/t_data03.gif'></td>
    <td bgcolor=#FAFAFA style='padding-left:10px'>
        <table cellpadding=3>
        <colgroup width=100>
        <colgroup width=''>
        <tr height=30>
            <td colspan=2>
                <input type=checkbox id=same name=same onclick="javascript:gumae2baesong(document.forderform);"> 
                <label for='same'><b>주문하시는 분과 받으시는 분의 정보가 동일한 경우 체크하세요.</b></label></td></tr>
        <tr>
        <tr>
            <td>이름</td>
            <td><input type=text name=od_b_name class=ed maxlength=20></td>
        </tr>
        <tr>
            <td>전화번호</td>
            <td><input type=text name=od_b_tel class=ed
                maxlength=20></td>
        </tr>
        <tr>
            <td>핸드폰</td>
            <td><input type=text name=od_b_hp class=ed
                maxlength=20></td>
        </tr>
        <tr>
            <td rowspan=2>주 소</td>
            <td>
                <input type=text name=od_b_zip1 size=3 maxlength=3 class=ed readonly>
                -
                <input type=text name=od_b_zip2 size=3 maxlength=3 class=ed readonly>
                <a href="javascript:;" onclick="win_zip('forderform', 'od_b_zip1', 'od_b_zip2', 'od_b_addr1', 'od_b_addr2');"><img 
                    src="<?=$g4[shop_img_path]?>/btn_zip_find.gif" border="0" align=absmiddle></a>
                </a>
            </td>
        </tr>
        <tr>
            <td>
                <input type=text name=od_b_addr1 size=35 maxlength=50 class=ed readonly>
                <input type=text name=od_b_addr2 size=15 maxlength=50 class=ed> (상세주소)
            </td>
        </tr>
        <tr>
            <td>전하실말씀</td>
            <td><textarea name=od_memo rows=4 cols=60 class=ed></textarea></td>
        </tr>
        </table>
    </td>
</tr>
</table>

<!-- 결제 정보 -->
<table width=100% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
    <td bgcolor=#FFEFFD align=center><img src='<?=$g4[shop_img_path]?>/t_data04.gif'></td>
    <td bgcolor=#FAFAFA style='padding-left:10px'>
        <table cellpadding=3>
        <tr>
            <td height=50>
                <?
                $multi_settle == 0;
                $checked = "";

                // 무통장입금 사용
                if ($default[de_bank_use]) {
                    $multi_settle++;
                    echo "<input type='radio' id=od_settle_bank name='od_settle_case' value='무통장' $checked><label for='od_settle_bank'>무통장입금</label> &nbsp;&nbsp;&nbsp;";
                    $checked = "";
                }

                // 가상계좌 사용
                if ($default[de_vbank_use]) {
                    $multi_settle++;
                    echo "<input type='radio' id=od_settle_vbank name=od_settle_case value='가상계좌' $checked><label for='od_settle_vbank'>가상계좌</label> &nbsp;&nbsp;&nbsp;";
                    $checked = "";
                }

                // 계좌이체 사용
                if ($default[de_iche_use]) {
                    $multi_settle++;
                    echo "<input type='radio' id=od_settle_iche name=od_settle_case value='계좌이체' $checked><label for='od_settle_iche'>계좌이체</label> &nbsp;&nbsp;&nbsp;";
                    $checked = "";
                }

                // 신용카드 사용
                if ($default[de_card_use]) {
                    $multi_settle++;
                    echo "<input type='radio' id=od_settle_card name=od_settle_case value='신용카드' $checked><label for='od_settle_card'>신용카드</label> &nbsp;&nbsp;&nbsp;";

					// 김선용 200801
					if($default['de_card_pg'] == 'authorize')
					{
						echo "
						<div style='padding-top:10px;'></div>
						<div style='line-height:150%; border:2px solid blue; padding:5px;'>
							<font color='red'><u>※ 카드결제 안내</u></font><br>
							<b>1)</b> 카드사용시 할부는 사용할 수 없고, 일시불로만 결제가 됩니다.<br>사용하신 일시불은 한국의 해당 카드사에 문의하시면 10만원 이상건에 대해 할부로 전환이 됩니다.(해당 카드사별로 상이함)<br>
							<b>2)</b> 한국에서 사용하는 <u>할부/공인인증서</u>와 같은 부분은 한국에서만 사용하고 있는 기능입니다.&nbsp;&nbsp;미국등과 같은 한국이외 국가에서는 할부/공인인증서와 같은 기능은 사용하지 않습니다.<br>저희 <u>http://{$_SERVER['HTTP_HOST']}</u> 에서는 카드결제 보안을 위해서 미국내 최고 카드결제사인 authorize 의 최고 보안 모듈인 AIM 모듈방식을 사용하고 있으며, 회원에게만 카드결제가 가능토록 하고 있습니다.&nbsp;&nbsp;또한 <u>카드결제시 회원의 어떠한 카드정보도 저장하거나 남지 않으며</u> 결제정보 전송은 <u>최고 보안방식인 128비트 SSL 로 암호화하여 전송하는 방식을 이용하며, 한국의 웹링크방식이 아닌 다른 방식으로 전송하므로 중간에서 해킹이나 크랙킹이 전혀 불가능</u>합니다.<br>따라서 회원들께서는 안심하고 카드결제를 이용하실 수 있습니다.<br>
							<b>3)</b> 카드결제는 해외 사용이 가능한 비자/마스터/아멕스(American express) 카드만 가능합니다.&nbsp;&nbsp;(카드에 비자/마스터/아멕스 로고가 있어야 합니다.)<br>
							<b>4)</b> 한국 국내전용 카드는 결제하실 수 없습니다.(Local Card)<br>
							<b>5)</b> 비자/마스터 카드라해도 해외사용 신청을 하지 않았으면 결제가 불가능합니다.<br>
							(카드사에 해외 사용신청이 되어있는지 확인하시기 바랍니다.)<br>
							<b>6)</b> 한국에서 사용하는 <u>체크카드</u>의 경우, 해외사용이 가능한 체크카드라면 결제가 가능합니다.
						</div>";
					}
                    $checked = "";
                }

                // 회원이면서 포인트사용이면
                if ($is_member && $config[cf_use_point]) 
                {
                    // 포인트 결제 사용 포인트보다 회원의 포인트가 크다면
                    if ($member[mb_point] >= $default[de_point_settle])
                    {
                        $temp_point = $tot_amount * ($default[de_point_per] / 100); // 포인트 결제 % 적용
                        $temp_point = (int)(($temp_point / 100) * 100); // 100점 단위

                        // 계산된 포인트가 보유포인트보다 크다면 보유포인트 전체로 결제
                        if ($temp_point > $member[mb_point]) 
                            $temp_point = $member[mb_point];

                        echo "<br><br><input type=checkbox id=od_temp_point name=od_temp_point value='$temp_point' checked>";
                        echo "<label for='od_temp_point'>보유포인트 ".display_point($temp_point)." 사용 : 주문금액의 {$default[de_point_per]}% 내에서 포인트 결제가 가능합니다.</label> &nbsp;";
                        $multi_settle++;
                    }
                }

                if ($multi_settle == 0)
                    echo "<br><span class=point>결제할 방법이 없습니다.<br>운영자에게 알려주시면 감사하겠습니다.</span>";

                if (!$default[de_card_point])
                    echo "<br><br>· '무통장입금' 이외의 결제 수단으로 결제하시는 경우 포인트를 적립해드리지 않습니다.";
                ?>
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

<p align=center>
    <input type="image" src="<?=$g4[shop_img_path]?>/btn_next2.gif" border=0 alt="다음">&nbsp;
    <a href='javascript:history.go(-1);'><img src="<?=$g4[shop_img_path]?>/btn_back1.gif" alt="뒤로" border=0></a>
</form>

<!-- <? if ($default[de_card_use] || $default[de_iche_use]) { echo "결제대행사 : $default[de_card_pg]"; } ?> -->
 
<script language='javascript'>
function forderform_check(f) 
{
    errmsg = "";
    errfld = "";
    var deffld = "";

    check_field(f.od_name, "주문하시는 분 이름을 입력하십시오.");
    if (typeof(f.od_pwd) != 'undefined')
    {
        clear_field(f.od_pwd);
        if( (f.od_pwd.value.length<3) || (f.od_pwd.value.search(/([^A-Za-z0-9]+)/)!=-1) )
            error_field(f.od_pwd, "회원이 아니신 경우 주문서 조회시 필요한 비밀번호를 3자리 이상 입력해 주십시오.");
    }
    check_field(f.od_tel, "주문하시는 분 전화번호를 입력하십시오.");
    check_field(f.od_addr1, "우편번호 찾기를 이용하여 주문하시는 분 주소를 입력하십시오.");
    check_field(f.od_addr2, " 주문하시는 분의 상세주소를 입력하십시오.");
    check_field(f.od_zip1, "");
    check_field(f.od_zip2, "");

    clear_field(f.od_email);
    if(f.od_email.value=='' || f.od_email.value.search(/(\S+)@(\S+)\.(\S+)/) == -1)
        error_field(f.od_email, "E-mail을 바르게 입력해 주십시오.");

    if (typeof(f.od_hope_date) != "undefined") 
    {
        clear_field(f.od_hope_date);
        if (!f.od_hope_date.value) 
            error_field(f.od_hope_date, "희망배송일을 선택하여 주십시오.");
    }

    check_field(f.od_b_name, "받으시는 분 이름을 입력하십시오.");
    check_field(f.od_b_tel, "받으시는 분 전화번호를 입력하십시오.");
    check_field(f.od_b_addr1, "우편번호 찾기를 이용하여 받으시는 분 주소를 입력하십시오.");
    check_field(f.od_b_addr2, "받으시는 분의 상세주소를 입력하십시오.");
    check_field(f.od_b_zip1, "");
    check_field(f.od_b_zip2, "");

    // 배송비를 받지 않거나 더 받는 경우 아래식에 + 또는 - 로 대입
    f.od_send_cost.value = parseInt(f.od_send_cost.value);

    if (errmsg) 
    {
        alert(errmsg);
        errfld.focus();
        return false;
    }

    var settle_case = document.getElementsByName("od_settle_case");
    var settle_check = false;
    for (i=0; i<settle_case.length; i++)
    {
        if (settle_case[i].checked)
        {
            settle_check = true;
            break;
        }
    }
    if (!settle_check)
    {
        alert("결제방식을 선택하십시오.");
        return false;
    }

    return true;
}

// 구매자 정보와 동일합니다.
function gumae2baesong(f)
{
    f.od_b_name.value = f.od_name.value;
    f.od_b_tel.value  = f.od_tel.value;
    f.od_b_hp.value   = f.od_hp.value;
    f.od_b_zip1.value = f.od_zip1.value;
    f.od_b_zip2.value = f.od_zip2.value;
    f.od_b_addr1.value = f.od_addr1.value;
    f.od_b_addr2.value = f.od_addr2.value;
}
</script>

<?
include_once("./_tail.php");
?>