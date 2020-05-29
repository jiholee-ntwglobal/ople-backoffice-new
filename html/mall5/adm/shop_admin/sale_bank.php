<?
$sub_menu = "800750";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");
$search_from= '';
$search_to ='';
/*if($to_date-$fr_date > 32 || $to_date-$fr_date < 1){
    alert('한달이상 되지않습니다.','');
}*/
if($fr_date){
    $search_from =$fr_date;
    $fr_date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $fr_date);
}

if(!$to_date ){
    $search_to = $to_date ='';
    $to_date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $to_date);

}

$data = array();
$data2 = array();
if($to_date && $fr_date){
    $sql = "
select
    date_format(c.cd_time ,'%Y-%m-%d') as ymd ,count(distinct k.od_id) as od_id ,sum(cd_amount) as amount
from
    yc4_card_history c
inner join
    yc4_kcp_noti_history k
        on k.od_id = c.od_id and k.success_fg ='Y' and c.cd_method_type = '가상계좌'
where
    date_format(c.cd_time ,'%Y-%m-%d') between '$fr_date' and  '$to_date'
group by date_format(c.cd_time ,'%Y-%m-%d')
order by cd_trade_ymd desc
    ";
    $result = sql_query($sql);
    while($row =sql_fetch_array($result)){
        $data[] =$row;
    }

    $sql2 = "
select
    date_format(k.dt ,'%Y-%m-%d') as ymd,count(distinct k.od_id) as od_id,sum(cd_amount) as amount
from
    yc4_card_history c
inner join
    yc4_kcp_noti_history k
        on k.od_id = c.od_id and k.success_fg ='Y' and c.cd_method_type = '가상계좌'
where
    date_format(k.dt ,'%Y-%m-%d') between '$fr_date' and  '$to_date'
group by date_format(k.dt ,'%Y-%m-%d')
order by cd_trade_ymd desc

    ";
    $result2 = sql_query($sql2);
    while($row2 =sql_fetch_array($result2)){
        $data2[] =$row2;
    }
}
$g4[title] = "가상계좌 입금액 조회";
include_once("$g4[admin_path]/admin.head.php");
?>

<?= subtitle($g4[title]) ?>

<table cellpadding=0 cellspacing=0 border=0>
    <colgroup width=150></colgroup>
    <colgroup width='' bgcolor=#ffffff></colgroup>
    <tr>
        <td colspan=2 height=2 bgcolor=#0E87F9></td>
    </tr>
    <tr height=40>
        <form name=frm_sale_date >
            <td>조회 기간</td>
            <td align=right>
                <input type=text name=fr_date size=8 maxlength=8 value='<? echo $search_from==''?date("Ym01", $g4['server_time']):$search_from; ?>' class=ed>
                일 부터
                <input type=text name=to_date size=8 maxlength=8 value='<? echo $search_to==''?date("Ymd", $g4['server_time']):$search_to; ?>' class=ed>
                일 까지
                <input type=submit class=btn1 value='  확  인  '>
            </td>
        </form>
    </tr>
    <tr>
        <td colspan=2 height=2 bgcolor=#0E87F9></td>
    </tr>
</table>
<br>
<br>
<?php if(!empty($data)){
            $tot = array();
            $tot['ordercount'] = 0;
            $tot['receiptvbank'] = 0;
    ?>
    <?= subtitle("가상계좌 발급일 기준 입금액") ?>
    <table cellpadding=0 cellspacing=0 width=100%>
        <tr><td colspan=20 height=2 bgcolor=#0E87F9></td></tr>
        <tr class=ht>
            <td width=85 align=center>가상계좌 발급일</td>
            <td width=80 align=center>입금확인 주문건수</td>
            <td width=80 align=right>입금금액</td>
        </tr>
        <tr><td colspan=20 height=1 bgcolor=#CCCCCC></td></tr>
        <?php foreach($data as $value) {
                    $tot['ordercount']  += $value['od_id'];
                    $tot['receiptvbank'] += $value['amount'];
            ?>
            <tr >
                <td align=center><?php echo $value['ymd'];?></td>
                <td align=right><?php echo number_format($value['od_id']);?></td>
                <td align=right><?php echo number_format($value['amount']);?></td>
            </tr>
            <tr><td colspan=20 height=1 bgcolor=#CCCCCC></td></tr>
        <?php } ?>
        <tr><td colspan=20 height=2 bgcolor=#0E87F9></td></tr>
        <tr class=ht>
            <td align=center>합 계</td>
            <td align=right><?=number_format($tot[ordercount])?></td>
            <td align=right ><?=number_format($tot[receiptvbank])?></td>

        </tr>
        <tr>
            <td colspan=8 height=2 bgcolor=#0E87F9></td>
        </tr>
    </table>
    <br>
    <br>
<?php } ?>
<?php if(!empty($data2)){
    $tot = array();
    $tot['ordercount'] = 0;
    $tot['receiptvbank'] = 0;
    ?>
    <?= subtitle("가상계좌 입금일 기준 입금액 ") ?>
    <table cellpadding=0 cellspacing=0 width=100%>
        <tr><td colspan=20 height=2 bgcolor=#0E87F9></td></tr>
        <tr class=ht>
            <td width=85 align=center>가상계좌 입급일</td>
            <td width=80 align=center>입금확인 주문건수</td>
            <td width=80 align=right>입금금액</td>
        </tr>
        <tr><td colspan=20 height=1 bgcolor=#CCCCCC></td></tr>
        <?php foreach($data2 as $value) {
            $tot['ordercount']  += $value['od_id'];
            $tot['receiptvbank'] += $value['amount'];
            ?>
            <tr >
                <td align=center><?php echo $value['ymd'];?></td>
                <td align=right><?php echo number_format($value['od_id']);?></td>
                <td align=right><?php echo number_format($value['amount']);?></td>
            </tr>
            <tr><td colspan=20 height=1 bgcolor=#CCCCCC></td></tr>
        <?php } ?>
        <tr><td colspan=20 height=2 bgcolor=#0E87F9></td></tr>
        <tr class=ht>
            <td align=center>합 계</td>
            <td align=right><?=number_format($tot[ordercount])?></td>
            <td align=right ><?=number_format($tot[receiptvbank])?></td>

        </tr>
        <tr>
            <td colspan=8 height=2 bgcolor=#0E87F9></td>
        </tr>
    </table>
    <br>
    <br>
<?php } ?>
<?
include_once("$g4[admin_path]/admin.tail.php");
?>
