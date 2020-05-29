<?
//--------------------------------------------------
// 온라인견적
//--------------------------------------------------
// 참고 : 옵션이 있는 상품은 제외됩니다.
//--------------------------------------------------
include_once("./_common.php");

if (!$oc_id)
{
    $sql = " select max(oc_id) as max_oc_id from $g4[yc4_onlinecalc_table] ";
    $row = sql_fetch($sql);
    $oc_id = $row[max_oc_id];
}

$sql = " select * from $g4[yc4_onlinecalc_table] 
          where oc_id = '$oc_id' ";
$oc = sql_fetch($sql);
if (!$oc[oc_id]) 
    alert("등록된 온라인견적이 없습니다.");

$g4[title] = $oc[oc_subject];
include_once("./_head.php");
?>

<img src="<?=$g4[shop_img_path]?>/top_onlinecalc.gif" border=0>

<?
$himg = "$g4[path]/data/onlinecalc/{$oc_id}_h";
if (file_exists($himg)) 
    echo "<img src='$himg' border=0><BR>";

// 상단 HTML
echo stripslashes($oc[oc_head_html]);

if ($is_admin)
    echo "<p align=center><a href='$g4[shop_admin_path]/onlinecalcform.php?w=u&oc_id=$oc[oc_id]'><img src='$g4[shop_img_path]/btn_admin_modify.gif' border=0></a></p>";
?>

<form name=fonlinecalc method=post action="./cartupdate.php" style="padding:0px;">
<input type=hidden name=w value="multi">
<input type=hidden name=sw_direct value=''>
<table width=100% align=center cellpadding=0 cellspacing=0>
<tr>
    <td>
        <table width=100% cellpadding=0 cellspacing=0>
        <colgroup width=150>
        <colgroup width='' align=right>
        <colgroup width=60>
        <colgroup width=90>
        <colgroup width=30>
        <tr>
            <td colspan=3 align=right style='padding-top:7px; padding-bottom:7px;'>합 계 </td>
            <td><input type=text id='tot_amount_0' name='tot_amount[0]' size=10 class=ed readonly style='text-align:right;'> 원</td>
            <td>&nbsp;</td>
        </tr>
        <tr><td class=c1 height=2 colspan=5></td></tr>
        <?
        $hidden = array(); 

        // 분류 리스트를 나누고 $cat 배열 생성
        $cat = explode("\n", trim($oc[oc_category]));

        for ($i=0; $i<count($cat); $i++)
        {
            if ($i > 0)
                echo "<tr><td height=1 colspan=5 background='$g4[shop_img_path]/dot_line.gif'></td></tr>\n";

            $ca_id = $cat[$i];
            
            $sql = " select ca_name from $g4[yc4_category_table] where ca_id = '$ca_id' ";
            $row = sql_fetch($sql);
            $ca_name = $row[ca_name];
            
            echo "<tr $g_tr_mouse>\n";
            echo "<td width=80>&nbsp;$ca_name</td>\n";
            // 상품 (하위 분류의 상품을 모두 포함한다.)
            // 1.02.00 
            // it_order 추가
            $sql = " select *
                       from $g4[yc4_item_table]
                      where ca_id like '$ca_id%'
                        and it_use = 1
                        and it_tel_inq = 0
                        and it_opt1 not like '%;%'
                        and it_opt2 not like '%;%'
                        and it_opt3 not like '%;%'
                        and it_opt4 not like '%;%'
                        and it_opt5 not like '%;%'
                        and it_opt6 not like '%;%'
                      order by ca_id, it_order, it_id desc ";
            $result = sql_query($sql);
            echo "<td>\n";
            echo "<select id='it_id_{$i}' name='it_id[{$i}]' onchange=\"compute_line(this.form, $i);\">\n";
            echo "<option value=''>------------------------------ 선택하십시오. ------------------------------\n";
            for ($k=0; $row=mysql_fetch_array($result); $k++) 
            {
				if($row['it_name']){
					$row['it_name'] = get_item_name($row['it_name']);
				}
                // 재고가 없다면 제외
                if (get_it_stock_qty($row[it_id]) <= 0)
                    continue;

                echo "<option value='$row[it_id]'>[$ca_name] $row[it_name] (".display_amount(get_amount($row)).")\n";

                $hidden[$row[it_id]]->name = $row[it_name];
                $hidden[$row[it_id]]->amount = $row[it_amount];
                $hidden[$row[it_id]]->point = $row[it_point];
            }

            echo "</select>\n";
            // 실제 넘기는 값
            echo "<input type=hidden id='it_name_{$i}' name='it_name[{$i}]' value=''>\n";
            echo "<input type=hidden id='it_amount_{$i}' name='it_amount[{$i}]' value=''>\n";
            echo "<input type=hidden id='it_point_{$i}' name='it_point[{$i}]' value=''>\n";
            echo "</td>\n";
            echo "<td style='padding-top:7px; padding-bottom:7px;'><input type=text id='ct_qty_{$i}' name='ct_qty[{$i}]' size=3 maxlength=4 class=ed value='1' onblur=\"change_qty(this.form, this, $i); compute_total(this.form);\" style='text-align:right;'> 개</td>\n";
            echo "<td><input type=text id='amount_{$i}' name='amount[{$i}]' size=10 class=ed readonly style='text-align:right;'> 원</td>\n";
            echo "<td><a href='#detail' onclick=\"item_detail(document.fonlinecalc.elements['it_id['+$i+']']);\">";
            echo "<img src='$g4[shop_img_path]/icon_onlinecalc.gif' border=0 align=absmiddle alt='상품 상세보기' width=20 height=20></a></td>\n";
            echo "</tr>\n";
        }

        // $length 는 자바스크립트에서 사용
        $length = $i;


        // 저장해놓은 hidden 배열 출력
        foreach($hidden as $key=>$value)
        {
            echo "<input type=hidden id='tmp_name_{$key}'   name='tmp_name[{$key}]'   value='{$value->name}'>\n";
            echo "<input type=hidden id='tmp_amount_{$key}' name='tmp_amount[{$key}]' value='{$value->amount}'>\n";
            echo "<input type=hidden id='tmp_point_{$key}'  name='tmp_point[{$key}]'  value='{$value->point}'>\n";
        }
        ?>
        <tr><td class=c1 height=2 colspan=5></td></tr>
        <tr>
            <td colspan=3 align=right style='padding-top:7px; padding-bottom:7px;'>합 계 </td>
            <td>
                <input type=text id='tot_amount_1' name='tot_amount[1]' size=10 class=ed readonly style='text-align:right;'> 원</td>
            <td>&nbsp;</td>
        </tr>
        </table></td>
</tr>
</table>

<p align=center>
    <a href="javascript:fonlinecalc_check('buy');"><img src="<?=$g4[shop_img_path]?>/btn_buy.gif" border=0 title="주문하기"></a>&nbsp;
    <a href="javascript:fonlinecalc_check('cart');"><img src="<?=$g4[shop_img_path]?>/btn_cart_in.gif" border=0 title="장바구니 담기"></a>&nbsp;                                                                         
    <a href="javascript:fonlinecalc_check('print');"><img src="<?=$g4[shop_img_path]?>/btn_print.gif" border=0 title="인쇄하기"></a>
<input type=hidden id='element_length' value='<?=(int)$length;?>'>
</form>

<script language='javascript'>
var delimiter = ""; // 구분자

function fonlinecalc_check(act)
{
    var f = document.fonlinecalc;

    if (act == 'buy') // 바로 구매
        f.sw_direct.value = '1';
    else  // 장바구니에 담기
        f.sw_direct.value = '0';

    checked = false;
    for (i=0; i<document.getElementById('element_length').value; i++) 
    {
        //if (f.elements('it_id['+i+']').value != "") 
        if (document.getElementById('it_id_'+i).value != "") 
        {
            checked = true;
            break;
        }
    }

    if (checked == false) 
    {
        alert("상품을 한개 이상 선택해 주십시오.");
        return;
    }

    if (act == "print")   
    {
        f.action = "./onlinecalcprint.php";
        f.target = "_new";
    } 
    else 
    {
        f.target = "_parent";
        f.action = "./cartupdate.php";
    }
    f.submit();
}

function change_qty(f, fld, idx)
{
    var qty = parseInt(fld.value);
    if( (fld.value.search(/[^0-9]+/) != -1) || (qty < 1) || (isNaN(qty) == true) )
    {
        alert('수량을 바르게 입력해 주십시오.');
        fld.focus();
        return false;
    }

    compute_line(f, idx);
}

function compute_line(f, idx)
{
    var tmp_name = "";
    var tmp_amount = 0;
    var tmp_point = 0;
    //var qty = parseInt(f.elements('ct_qty['+idx+']').value);
    //var selidx = f.elements("it_id["+idx+"]").value;
    var qty = parseInt(document.getElementById('ct_qty_'+idx).value);
    var selidx = document.getElementById('it_id_'+idx).value;

    if (selidx == "") 
    {
        amount = 0;
    } 
    else 
    {
        //tmp_name = f.elements("tmp_name["+selidx+"]").value;
        //tmp_amount = parseInt(f.elements("tmp_amount["+selidx+"]").value);
        //tmp_point = parseInt(f.elements("tmp_point["+selidx+"]").value);
        tmp_name = document.getElementById('tmp_name_'+selidx).value;
        tmp_amount = document.getElementById('tmp_amount_'+selidx).value;
        tmp_point = document.getElementById('tmp_point_'+selidx).value;

        amount =  tmp_amount * qty;
    }

    //f.elements('it_name['+idx+']').value = tmp_name;
    //f.elements('it_amount['+idx+']').value = tmp_amount;
    //f.elements('it_point['+idx+']').value = tmp_point;
    document.getElementById('it_name_'+idx).value = tmp_name;
    document.getElementById('it_amount_'+idx).value = tmp_amount;
    document.getElementById('it_point_'+idx).value = tmp_point;

    //f.elements('amount['+idx+']').value = number_format(String(amount));
    document.getElementById('amount_'+idx).value = number_format(String(amount));

    compute_total(f);
}

function compute_total(f)
{
    tot_amount = 0;
    for (i=0; i<document.getElementById('element_length').value; i++)
    {
        //amount = f.elements('amount['+i+']').value;
        amount = document.getElementById('amount_'+i).value;
        if (amount == "") amount = "0";
        amount = no_comma(String(amount));
        tot_amount += parseInt(amount);
    }
    //f.elements('tot_amount[0]').value = f.elements('tot_amount[1]').value = number_format(String(tot_amount));
    document.getElementById('tot_amount_0').value = document.getElementById('tot_amount_1').value = number_format(String(tot_amount));
}

// 상품 상세보기
function item_detail(fld)
{
    // delimiter 구분자로 나누고
    str = fld.value.split(delimiter);
    if (str[0] == "") return false;
    window.open('./item.php?it_id='+str[0], "itemdetail");
}
</script>

<?
// 하단 HTML
echo stripslashes($oc[oc_tail_html]);

$timg = "$g4[path]/data/onlinecalc/{$oc[oc_id]}_t";
if (file_exists($timg))
    echo "<img src='$timg' border=0><br>";

include_once("./_tail.php");
?>
