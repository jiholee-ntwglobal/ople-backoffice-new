<?
include_once("./_common.php");

session_check();


// 김선용 2014.03 : 카드 결제시 변수 고정처리. 내부처리를 위해 신용카드 국내/해외 구분변수를 따로 할당
if($_POST['od_settle_case'] == 'kcp' || $_POST['od_settle_case'] == 'authorize'){
	$card_settle_case = ($_POST['od_settle_case'] == 'kcp' ? 'kcp' : 'authorize');
	$_POST['od_settle_case'] = '신용카드';
	$od_settle_case = '신용카드';
}


// 김선용 201006 : 전역변수 XSS/인젝션 보안강화 및 방어
include_once "sjsjin.shop_guard.php";

// 장바구니가 비어있는가?
$tmp_on_uid = get_session('ss_on_uid');
if (get_cart_count($tmp_on_uid) == 0)
    alert("장바구니가 비어 있습니다.", "./cart.php");

// 희망배송일 사용한다면
if ($default[de_hope_date_use])
{
    ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})", $od_hope_date, $hope_date);
    if ($od_hope_date == "") ; // 통과
    else if (checkdate($hope_date[2], $hope_date[3], $hope_date[1]) == false)
        alert("희망배송일을 올바르게 입력해 주십시오.");
    else if ($od_hope_date < date("Y-m-d", time()+86400*$default[de_hope_date_after]))
        alert("희망배송일은 오늘부터 {$default[de_hope_date_after]}일 후 부터 입력해 주십시오.");
}

// 회원 로그인 중이라면 회원비밀번호를 주문서에 넣어줌
if ($is_member)
    $_POST['od_pwd'] = $member['mb_password'];
else
    $_POST['od_pwd'] = sql_password($_POST['od_pwd']);

// 김선용 201208 : 주민번호 미사용
// 김선용 200811 : 주민번호 처리
//if($_POST['temp_jumin'] && $is_member)
	//sql_query("update {$g4['member_table']} set mb_jumin2='{$_POST['temp_jumin']}' where mb_id='{$member['mb_id']}' ");


$g4[title] = "주문확인 및 결제";
include_once("./_head.php");
?>
<!--<img src="<?=$g4[shop_img_path]?>/top_orderreceipt.gif" border="0"><p>-->
<div style="padding-top:20px;"></div>
<table width="755" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td width="319"><img src="<?=$g4['path']?>/images/category/category_title01_b.gif"></td>
	<td width="353" align="right" class="font11">HOME &gt; <span class="font11_orange">주문확인 및 결제내역</span></td>
</tr>
<tr><td height="2" colspan="2" bgcolor="#fa5a00"></td></tr>
</table><p>

<?
$s_page = '';
$s_on_uid = $tmp_on_uid;
include_once("./cartsub.inc.php");
?>

<br>
<table width=97% align=center cellpadding=0 cellspacing=10 border=0>
<tr>
    <td><b><span class="font11_orange">입력하신 내용이 맞는지 다시 한번 확인하여 주십시오.</span></b></td>
</tr>
</table>

<!-- 주문하시는 분 -->
<table width=97% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
    <td class="c3" align=center>주문하시는 분</td>
    <td bgcolor=#FAFAFA style='padding-left:10px'>
        <table cellpadding=3>
        <colgroup width=120>
        <colgroup width=''>
        <tr height=25>
            <td>이름</td>
            <td><? echo $_POST['od_name'] ?></td>
        </tr>
        <tr height=25>
            <td>전화번호</td>
            <td><? echo $_POST['od_tel'] ?></td>
        </tr>
        <tr height=25>
            <td>핸드폰</td>
            <td><? echo $_POST['od_hp'] ?></td>
        </tr>
        <tr height=25>
            <td>주소</td>
            <td><? echo sprintf("(%s-%s) %s %s", $_POST['od_zip1'], $_POST['od_zip2'], $_POST['od_addr1'], $_POST['od_addr2']); ?></td>
        </tr>
        <tr height=25>
            <td>E-mail</td>
            <td><? echo $_POST['od_email'] ?></td>
        </tr>
        <?
        // 희망배송일 사용한다면
        if ($default[de_hope_date_use]) {
            echo "
            <tr height=25>
                <td>희망배송일</td>
                <td>$od_hope_date (".get_yoil($od_hope_date).")</td>
            </tr> ";
        }
        ?>
        </table>
    </td>
</tr>
</table>

<!-- 받으시는 분 -->
<table width=97% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
    <td class="c3" align=center>받으시는 분</td>
    <td bgcolor=#FAFAFA style='padding-left:10px'>
        <table cellpadding=3>
        <colgroup width=120>
        <colgroup width=''>
        <tr height=25>
            <td>이름</td>
            <td colspan=3><? echo $_POST['od_b_name']; ?></td>
        </tr>
        <tr height=25>
            <td>전화번호</td>
            <td><? echo $_POST['od_b_tel'] ?></td>
        </tr>
        <tr height=25>
            <td>핸드폰</td>
            <td><? echo $_POST['od_b_hp'] ?></td>
        </tr>
        <tr height=25>
            <td>주소</td>
            <td><? echo sprintf("(%s-%s) %s %s", $_POST['od_b_zip1'], $_POST['od_b_zip2'], $_POST['od_b_addr1'], $_POST['od_b_addr2']); ?></td>
        </tr>
        <tr height=25>
            <td>전하실말씀</td>
            <td><? echo htmlspecialchars2(stripslashes($_POST['od_memo'])) ?></td>
        </tr>
        </table>
    </td>
</tr>
</table>

<!-- 결제 정보 -->
<table width=97% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
        <td class="c3" align=center>결제 정보</td>
    <td bgcolor=#FAFAFA style='padding-left:10px'>
        <table cellpadding=3>
        <form name=frmorderreceipt method=post action='./orderupdate.php' onsubmit="return frmorderreceipt_check(this)" autocomplete="off">
        <input type=hidden name=de_card_point value='<? echo $default[de_card_point] ?>'>
        <input type=hidden name=od_settle_case value='<? echo $_POST['od_settle_case'] ?>'>
        <input type=hidden name=od_amount    value='<? echo $tot_sell_amount ?>'>
        <input type=hidden name=od_send_cost value='<? echo $_POST['od_send_cost'] ?>'>
        <input type=hidden name=od_name      value='<? echo $_POST['od_name'] ?>'>
        <input type=hidden name=od_pwd       value='<? echo $_POST['od_pwd'] ?>'>
        <input type=hidden name=od_tel       value='<? echo $_POST['od_tel'] ?>'>
        <input type=hidden name=od_hp        value='<? echo $_POST['od_hp'] ?>'>
        <input type=hidden name=od_zip1      value='<? echo $_POST['od_zip1'] ?>'>
        <input type=hidden name=od_zip2      value='<? echo $_POST['od_zip2'] ?>'>
        <input type=hidden name=od_addr1     value='<? echo $_POST['od_addr1'] ?>'>
        <input type=hidden name=od_addr2     value='<? echo $_POST['od_addr2'] ?>'>
        <input type=hidden name=od_email     value='<? echo $_POST['od_email'] ?>'>
        <input type=hidden name=od_b_name    value='<? echo $_POST['od_b_name'] ?>'>
        <input type=hidden name=od_b_tel     value='<? echo $_POST['od_b_tel'] ?>'>
        <input type=hidden name=od_b_hp      value='<? echo $_POST['od_b_hp'] ?>'>
        <input type=hidden name=od_b_zip1    value='<? echo $_POST['od_b_zip1'] ?>'>
        <input type=hidden name=od_b_zip2    value='<? echo $_POST['od_b_zip2'] ?>'>
        <input type=hidden name=od_b_addr1   value='<? echo $_POST['od_b_addr1'] ?>'>
        <input type=hidden name=od_b_addr2   value='<? echo $_POST['od_b_addr2'] ?>'>
        <input type=hidden name=od_hope_date value='<? echo $_POST['od_hope_date'] ?>'>
        <input type=hidden name=od_memo      value='<? echo htmlspecialchars2(stripslashes($_POST['od_memo'])) ?>'>
		<input type="hidden" name="od_recommend_off_sale" value="<?=$order_save_amount?>" /> <? // 김선용 201209 : 추천인시스템 할인금액?>

		<!-- // 김선용 200908 : -->
		<input type="hidden" name="od_b_jumin" value="<?=(int)$_POST['od_b_jumin']?>">

		<!-- 김선용 2014.03 :  -->
		<input type="hidden" name="card_settle_case" value="<?=$card_settle_case?>" />


        <colgroup width=120>
        <colgroup width=''>
        <input type=hidden name=od_settle_amount value='<?=$tot_amount?>'>
        <tr>
            <td>결제금액</td>
            <td><?=display_amount($tot_amount)?></td>
        </tr>
        <?
        $receipt_amount = $tot_amount - $od_temp_point;

        if ($od_temp_point != "")
        {
            $temp_point = number_format($od_temp_point);
            echo "<input type=hidden name=od_temp_point value='$od_temp_point'>";
            echo "<tr><td>포인트결제</td><td>".display_point($od_temp_point)."</td></tr>";
        }

        if ($od_settle_case == "무통장")
        {
            // 은행계좌를 배열로 만든후
            $str = explode("\n", $default[de_bank_account]);
            if (count($str) <= 1)
            {
                $bank_account = "<input type=hidden name='od_bank_account' value='$str[0]'>$str[0]\n";
            }
            else
            {
                $bank_account = "\n<select name=od_bank_account>\n";
                $bank_account .= "<option value=''>--------------- 선택하십시오 ---------------\n";
                for ($i=0; $i<count($str); $i++)
                {
                    $str[$i] = str_replace("\r", "", $str[$i]);
                    $bank_account .= "<option value='$str[$i]'>$str[$i] \n";
                }
                $bank_account .= "</select> ";
            }

            echo "<input type=hidden name=od_receipt_bank value='$receipt_amount'>";
            echo "<tr><td>무통장입금액</td><td>".display_amount($receipt_amount)." (결제하실 금액)</td></tr>";
            echo "<tr><td>계좌번호</td><td>$bank_account</td></tr>";
            echo "<tr><td>입금자 이름</td><td><input type=text name=od_deposit_name class=ed size=10 maxlength=20 value='$od_name'> (주문하시는분과 입금자가 다를 경우)</td></tr>";
            $receipt_amount = 0;
        }

        if ($od_settle_case == "가상계좌")
        {
            $border_style = "";
            if ($od_receipt_bank == "") $border_style = " border-style:none;";
            echo "<input type=hidden name=od_bank_account value='가상계좌'>";
            echo "<input type=hidden name=od_deposit_name value='$od_name'>";
            echo "<input type=hidden name=od_receipt_bank value='$receipt_amount'>";
			//echo "<input type=hidden name=vbank_fix value='{$_POST['vbank_fix']}' />"; // 김선용 2014.04 : 고정계좌 구분
            echo "<tr><td>가상계좌</td><td>".display_amount($receipt_amount)." (결제하실 금액)</td></tr>";
            $receipt_amount = 0;
        }

        if ($od_settle_case == "계좌이체")
        {
            $border_style = "";
            if ($od_receipt_bank == "") $border_style = " border-style:none;";
            echo "<input type=hidden name=od_bank_account value='계좌이체'>";
            echo "<input type=hidden name=od_deposit_name value='$od_name'>";
            echo "<input type=hidden name=od_receipt_bank value='$receipt_amount'>";
            echo "<tr><td>계좌이체</td><td>".display_amount($receipt_amount)." (결제하실 금액)</td></tr>";
            $receipt_amount = 0;
        }

		// 김선용 2014.03 :  kcp 복합결제에 따른 처리
        if ($od_settle_case == "신용카드")
        {
			//if($default['de_card_pg'] == 'authorize'){
			if($card_settle_case == 'authorize')
			{
				$temp_pay = ($receipt_amount / $default['de_conv_pay']);
				$x_amount = ceil($temp_pay * 100)/100;
				$dis_usd = "<span style='font-size:9pt; font-family:tahoma; color:#6666FF;'>(\$".number_format($x_amount,2).")</span>";

				$border_style = "";
				if ($od_receipt_bank == "") $border_style = " border-style:none;";
				echo "<input type=hidden name=od_receipt_card value='$receipt_amount'>";
				echo "<tr><td>신용카드</td><td>".display_amount($receipt_amount)." {$dis_usd} (결제하실 금액)</td></tr>";
				$receipt_amount = 0;
			}
			else if($card_settle_case == 'kcp')
			{
				$border_style = "";
				if ($od_receipt_bank == "") $border_style = " border-style:none;";
				echo "<input type=hidden name=od_receipt_card value='$receipt_amount'>";
				echo "<tr><td>신용카드</td><td>".display_amount($receipt_amount)." (결제하실 금액)</td></tr>";
				$receipt_amount = 0;
			}

        }
        ?>
        </table>
    </td>
</tr>
</table>

<p align=center>
    <span id='id_submit'>
    <a href="javascript:history.go(-1);"><img src="<?=$g4[shop_img_path]?>/btn_back1.gif" border=0 title="뒤로"></a>&nbsp;&nbsp;&nbsp;
    <a href="javascript:frmorderreceipt_check(document.frmorderreceipt);"><img src='<?=$g4[shop_img_path]?>/btn_next2.gif' border=0 title='다음'></a>
    </span>
    <span id='id_saving' style='display:none;'><img src='<?=$g4[shop_img_path]?>/saving.gif' border=0></span>
</form>


<script type='text/javascript'>
function frmorderreceipt_check(f)
{
    errmsg = "";
    errfld = "";

    settle_amount = parseFloat(f.od_amount.value) + parseFloat(f.od_send_cost.value);
    od_receipt_bank = 0;
    od_receipt_card = 0;
    od_temp_point = 0;

    if (typeof(f.od_temp_point) != 'undefined')
    {
        od_temp_point = parseFloat(no_comma(f.od_temp_point.value));
        if (od_temp_point > 0)
        {
            /*
            // 포인트 최소 결제점수
            if (od_temp_point < <?=(int)($default[de_point_settle] * $default[de_point_per] / 100)?>)
            {
                //alert("포인트 결제액은 <?=display_point($default[de_point_settle])?> 이상 가능합니다.");
                alert("포인트 결제액은 <?=display_point($default[de_point_settle] * $default[de_point_per] / 100)?> 이상 가능합니다.");
                return;
            // 가지고 있는 포인트 보다 많이 입력했다면
            }
            else
            */
            if (od_temp_point > <? echo (int)$od_temp_point ?>)
            {
                alert("포인트 결제액은 <? echo display_point($od_temp_point) ?> 까지 가능합니다.");
                return;
            }
        }
    }

    if (typeof(f.od_receipt_card) != 'undefined')
    {
        od_receipt_card = parseFloat(no_comma(f.od_receipt_card.value));
        if (od_receipt_card < <?=(int)($default[de_card_max_amount])?>)
        {
            alert("신용카드 결제액은 <?=number_format($default[de_card_max_amount])?> 이상 가능합니다.");
            return;
        }
    }

    if (typeof(f.od_receipt_bank) != 'undefined')
    {
        od_receipt_bank = parseFloat(no_comma(f.od_receipt_bank.value));
        if (f.od_bank_account.value == "" && od_receipt_bank > 0)
        {
            alert("무통장으로 입금하실 은행 계좌번호를 선택해 주십시오.");
            f.od_bank_account.focus();
            return;
        }

        if (f.od_deposit_name.value.length < 2)
        {
            alert("입금자분 이름을 입력해 주십시오.");
            f.od_deposit_name.focus();
            return;
        }
    }

    sum = od_receipt_bank + od_receipt_card + od_temp_point;
    if (settle_amount != sum)
    {
        alert("입력하신 입금액 합계와 결제금액이 같지 않습니다.");
        return;
    }

    // 음수일 경우 오류
    if (od_temp_point < 0 || od_receipt_card < 0 || od_receipt_bank < 0)
    {
        alert("금액은 음수가 될 수 없습니다.");
        return;
    }

    str_card = "";
    str = "총 결제하실 금액 " + number_format(f.od_settle_amount.value) + "원 중에서\n\n";
    if (typeof(f.od_temp_point) != 'undefined')
        str += "포인트 : " + number_format(f.od_temp_point.value) + "점\n\n";
    if (typeof(f.od_receipt_card) != 'undefined')
    {
        str += "신용카드 : " + number_format(f.od_receipt_card.value) + "원\n\n";
        if (parseFloat(f.od_receipt_card.value) > 0)
        {
            // 카드, 계좌이체 결제시 포인트부여 여부
            if (!f.de_card_point.value)
                str_card += "\n\n---------------------------------------\\n\\n카드, 계좌이체 결제시 적립포인트는 부여하지 않습니다.";
         }
    }
    if (typeof(f.od_receipt_bank) != 'undefined')
        str += "<?=$od_settle_case?> : " + number_format(f.od_receipt_bank.value) + "원\n\n";
    str += "으로 주문 하셨습니다.\n\n"+
           "금액이 올바른지 확인해 주십시오."+str_card;


    sw_submit = confirm(str);
    if (sw_submit == false)
        return;

    document.getElementById('id_submit').style.display = 'none';
    document.getElementById('id_saving').style.display = '';

    f.submit();
}
</script>

<?
include_once("./_tail.php");
?>