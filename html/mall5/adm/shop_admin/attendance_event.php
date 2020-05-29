<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-06-15
 * Time: 오후 6:21
 */


$sub_menu = "400940";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4['title'] = "출석체크 이벤트 참여정보 확인";
include_once $g4['admin_path']."/admin.head.php";

$search_result = '';

if($_GET['mb_id'] && $_GET['mode'] == 'search'){

	$info = sql_fetch("select count(*) as cnt from attendance where mb_id='$_GET[mb_id]'");

	$search_result = '이벤트 참여 : '.$info['cnt'].'회<br/>';


	$rs = sql_query($a="
				select 
					o.od_id
				from yc4_order o
					left outer join yc4_cart c on c.on_uid = o.on_uid
				where c.ct_status in ('준비','배송','완료')
				-- and if(o.od_receipt_bank>0,c.ct_amount * c.ct_qty,0) + if(o.od_receipt_card>0,c.ct_amount * c.ct_qty,0) > 50000
				and o.od_receipt_bank + o.od_receipt_card >= 50000
					and date_format(o.od_time,'%Y%m%d') between '20150803' and '20150828'
					and mb_id='$_GET[mb_id]'
				group by o.od_id"); // 0803~0828


	$buy_cnt = 0;

	while($data = sql_fetch_array($rs)){
		$buy_cnt++;
	}//echo $a;

	$search_result .= '이벤트 기간 내 5만원 이상 구매내역: '.$buy_cnt.'회<br/>';




}


?>
<style>
    ul.list_tab{
        list-style: none;
        overflow: hidden;
        margin: 0;
        padding: 0;
    }
    ul.list_tab > li {
        float: left;
        padding: 5px;
        border: 1px solid #DDDDDD;
    }
    ul.list_tab > li.active{
        font-weight: bold;
    }
</style>

<h3>출석체크 이벤트 참여정보 확인</h3>
<a href="attendance_event_point.php">포인트 지급 내역 확인</a>
<form method="get">
<input type="hidden" name="mode" value="search"/>
오플ID <input type="text" name="mb_id" />
<input type="submit" value="조회"/>
</form>
<?php
echo $search_result;
?>

<?php
include_once $g4['admin_path']."/admin.tail.php";
?>
