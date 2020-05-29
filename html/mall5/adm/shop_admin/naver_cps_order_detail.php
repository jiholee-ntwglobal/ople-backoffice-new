<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-11-08
* Time : 오후 4:54
*/

$sub_menu = "800780";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

//$g4['title'] = $html_title;
$g4['title'] = "네이버CPS 주문건정보 검색 내부용 > 주문상세";
include_once $g4['admin_path']."/admin.head.php";

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from $g4[yc4_order_table] where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    alert($alt_msg1);
}

$sql = " select a.ct_id,
                a.it_id,
                a.ct_qty,
                a.ct_amount,
                a.ct_point,
                a.ct_status,
                a.ct_time,
                a.ct_point_use,
                a.ct_stock_use,
                a.it_opt1,
                a.it_opt2,
                a.it_opt3,
                a.it_opt4,
                a.it_opt5,
                a.it_opt6,
                b.it_name,
                b.it_health_cnt,
                a.ct_amount_usd
           from $g4[yc4_cart_table] a, $g4[yc4_item_table] b
          where a.on_uid = '$od[on_uid]'
            and a.it_id  = b.it_id
          order by a.ct_id ";
$result = sql_query($sql);


?>
    <style type="text/css">
        .item_name_etc_deatil{
            font-size: 13px;
            font-weight:normal;
        }
    </style>
    <p>
    <table width=100% cellpadding=0 cellspacing=0>
        <tr>
            <td><?php echo subtitle("주문상품(주문번호:".$_GET['od_id'].")")?>
            <td align=right>
            </td>
        </tr>
    </table>


    <form name=frmorderform method=post action='' style="margin:0px;">


        <table width=100% cellpadding=0 cellspacing=0 class='list_styleAD'>
            <thead>
            <tr>
                <td width="100">상품코드</td>
                <td>상품명</td>
                <td width=60>상태</td>
                <td width=75>상품가</td>
                <td width=35>수량</td>
                <td width=75>소계(상품가*수량)</td>
            </tr>
            </thead>
            <tbody>
            <?
            $image_rate = 2.5;
            $tot_health_cnt = 0;
            $t_amount_usd	=0;
            for ($i=0; $row=sql_fetch_array($result); $i++)
            {

                $it_id = $row[it_id];
                $ct_amount_usd = round($row['ct_amount'] / $od['exchange_rate'],2);


                $it_name = "<a href='https://www.ople.com/mall5/shop/item.php?it_id=$row[it_id]' target='_blank'>".stripslashes(get_item_name($row[it_name],'detail'))."</a><br>";
                $it_name .= print_item_options($row['it_id'], $row['it_opt1'], $row['it_opt2'], $row['it_opt3'], $row['it_opt4'], $row['it_opt5'], $row['it_opt6']);

                $ct_amount['소계'] = $row['ct_amount'] * $row['ct_qty'];
                $ct_amount['소계_usd'] = $ct_amount_usd * $row['ct_qty'];
                $ct_point['소계'] = $row['ct_point'] * $row['ct_qty'];
                if ($row['ct_status']=='주문' || $row['ct_status']=='준비' || $row['ct_status']=='배송' || $row['ct_status']=='완료')
                    $t_ct_amount['정상'] += $row['ct_amount'] * $row['ct_qty'];
                else if ($row['ct_status']=='취소' || $row['ct_status']=='반품' || $row['ct_status']=='품절'){
                    $t_ct_amount['취소'] += $row['ct_amount'] * $row['ct_qty'];
                    $t_ct_amount['취소Usd'] += $ct_amount['소계_usd'];
                }


                $image = get_it_image("$row[it_id]_s", (int)($default['de_simg_width'] / $image_rate), (int)($default['de_simg_height'] / $image_rate), $row['it_id']);

                $list = $i%2;
                echo "
    <tr>
        <td>$it_id</td>        
        <td class='ADlist_itemBox' style='padding:0'>
			<table width='100%'>
				<tr>
					<td width='80' style='padding:0'>$image</td>
					<td style='padding:0; text-align:left;'>$it_name</td>
				</tr>
			</table>
		</td>		
        <td>$row[ct_status]</td>
        <td>
			$ ".number_format($ct_amount_usd,2)."
		</td>
        <td>$row[ct_qty]</td>
        <td>
			$ ".number_format($ct_amount['소계_usd'],2)."
		</td>
        ";
                echo "</tr>";
                $tot_health_cnt += $row['it_health_cnt'] * $row['ct_qty'];
                if ($row['ct_status']=='준비' || $row['ct_status']=='배송' || $row['ct_status']=='완료') {
                    $t_amount_usd += $ct_amount['소계_usd'];
                }
                }
            ?>
            <tr class='ADlist_resultBox'>
                <td colspan=2>주문일시 : <?php echo substr($od[od_time],0,16)?> (<?php echo get_yoil($od[od_time]);?>)</td>
                <td colspan=6>
                    <input type=hidden name="chk_cnt" value="<? echo $i ?>">

                    <?php
                    // 2011.07.01 이후 주문서는 배송비 출력. 1107130207
                    // 김선용 201107 : root 계정일때 배송비제로. 상품합계에 합산
                    $edit_ct_amount = ($member['mb_id'] == 'root' && substr($od_id,0,6) < 110701 ? $od['od_send_cost'] : 0);
                    ?>
                    <b>주문합계 : $ <?php echo number_format($t_amount_usd,2); ?></B>

                </td>
            </tr>
            </tbody>
        </table>
    </form>
<br/>
    <div align="center">
        <input type=button class=btn1 value='  목  록  ' onclick="document.location.href='./naver_cps_order_test.php';">
    </div>
<br/>

<?php
include_once("$g4[admin_path]/admin.tail.php");
