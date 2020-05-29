<?php
$sub_menu = "500423";

include_once("./_common.php");

# 이벤트 삭제처리 #
if($_GET['mode'] == 'del')
{
	if(!$_GET['uid'])
	{
		exit;
	}

	$qry = "
		  delete from yc4_oneday_sale_item where uid = '".$_GET['uid']."'
	    ";

	if(sql_query($qry))
	{
		alert('삭제가 완료되었습니다.',$_SERVER['PHP_SELF']);
	}
	else
	{
		alert('삭제중 오류가 발생했습니다. 다시 시도해 주세요.',$_SERVER['PHP_SELF']);
	}

	exit;
}

if($_GET['mode'] == 'it_order_onetime_limit_cnt')
{
	$end_item_sql = sql_fetch($a="
		  select
			group_concat('''',a.it_id,'''') as it_id,
			count(*) as cnt
		  from
			yc4_oneday_sale_item a,
			".$g4['yc4_item_table']." b
		  where
			a.it_id = b.it_id and
			'".date('Ymd')."' not between a.st_dt and a.en_dt
			and b.it_order_onetime_limit_cnt>0
        ");

	$end_item = $end_item_sql['it_id'];
	if($end_item)
	{
		$end_item = "(".$end_item.")";
		sql_query("
                update
                    ".$g4['yc4_item_table']."
                set
                    it_order_onetime_limit_cnt = 0
                where
                    it_id in ".$end_item."
            ");
	}
	alert('굿데이 이벤트 종료상품 구매제한이 해제되었습니다.',$_SERVER['PHP_SELF']);
	exit;
}

auth_check($auth[$sub_menu], "r");

$g4['title'] = "원데이 이벤트 설정";

# 검색 처리 #
if($_GET['st_dt'])
{
	$search[] = "a.st_dt >= '".$_GET['st_dt']."'";
}
if($_GET['en_dt'])
{
	$search[] = "a.en_dt <= '".$_GET['en_dt']."'";
}
if($_GET['value'])
{
	$search[] = $_GET['key']." like '%".$_GET['value']."%'";
}

if(!isset($_GET['fg']))
{
	$_GET['fg'] = 'Y';
}

switch($_GET['fg'])
{
	case 'Y' : $search[] = "DATE_FORMAT(NOW(),'%Y%m%d') BETWEEN a.st_dt AND a.en_dt"; break;
	case 'N' : $search[] = "DATE_FORMAT(NOW(),'%Y%m%d') NOT BETWEEN a.st_dt AND a.en_dt"; break;
}

if(count($search) > 0)
{
	$search = "AND " . implode(" AND ", $search);
}

# 원데이 이벤트 상품 리스트 로드 #
$count_qry = "
        SELECT
            COUNT(*) AS cnt
        FROM
            yc4_oneday_sale_item a 
              LEFT JOIN yc4_item AS b ON a.it_id = b.it_id
              LEFT JOIN g4_member AS c ON c.mb_id = a.create_id
              LEFT JOIN yc4_item AS d ON a.l_it_id = d.it_id
        WHERE
            1 = 1 ".$search."
        ORDER BY
            a.create_dt DESC
    ";
$count_result = mysql_fetch_assoc(sql_query($count_qry));
$total_count = $count_result['cnt'];

# 페이징 처리 #
$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$qry = "
        SELECT
            a.*,
            b.it_name,
            c.mb_name,
            d.it_name AS l_it_name,
            if(a.price > 0 ,a.price * a.order_cnt , b.it_amount * a.order_cnt ) AS total_amount
        FROM
            yc4_oneday_sale_item a 
              LEFT JOIN yc4_item AS b ON a.it_id = b.it_id
              LEFT JOIN g4_member AS c ON c.mb_id = a.create_id
              LEFT JOIN yc4_item AS d ON a.l_it_id = d.it_id
        WHERE
            1 = 1 ".$search."
        ORDER BY
            a.create_dt DESC
        LIMIT 
            ".$from_record.", ".$config['cf_page_rows'];
$sql = sql_query($qry);

while($oneday_item = mysql_fetch_assoc($sql))
{
	$list_tr .= "
		    <tr class='list$list center ht'>
                <td rowspan='2'>".$oneday_item['uid']."</td>
                <td><a href='".$g4['shop_path']."/item.php?it_id=".$oneday_item['it_id']."'>".$oneday_item['it_id']."</a></td>
                <td rowspan='2'>".get_it_image($oneday_item['l_it_id'].'_s',50,50,$oneday_item['it_id'])."</td>
                <td align='left' title='".$oneday_item['it_name']."'>".conv_subject($oneday_item['it_name'],50,'…')."</td>
                <td>". $oneday_item['real_qty'] ." / ".( $oneday_item['real_qty'] * $oneday_item['multiplication'] )."</td>
    
                <td>".$oneday_item['order_cnt']."</td>
                <td>".$oneday_item['st_dt']."</td>
    
                <td>".number_format($oneday_item['price'])."</td>
                <td rowspan='2'>".icon("수정", "./oneday_write_new.php?uid=".$oneday_item['uid']).'&nbsp;'.icon("삭제", "javascript:list_delete(".$oneday_item['uid'].")")."</td>
            </tr>
            <tr class='list$list center ht'>
                <td><a href='".$g4['shop_path']."/item.php?it_id=".$oneday_item['l_it_id']."'>".$oneday_item['l_it_id']."</a></td>
                <td align='left' title='".$oneday_item['l_it_name']."'>".conv_subject($oneday_item['l_it_name'],50,'…')."</td>
                <td><b>".( $oneday_item['real_qty'] - $oneday_item['order_cnt'])."</b> / ".( ($oneday_item['real_qty'] * $oneday_item['multiplication']) - ($oneday_item['order_cnt'] * $oneday_item['multiplication']) )."</td>
                <td>".number_format($oneday_item['total_amount'])."</td>
                <td rowspan='2'>".$oneday_item['en_dt']."</td>
            </tr>
            <tr><td colspan='9' height='1' bgcolor='#CCCCCC'></td></tr>
	    ";
}

if(!$list_tr)
{
	$list_tr = "
            <tr align='center' class='list$list center ht'>
                <td colspan='8'>데이터가 존재하지 않습니다.</td>
            </tr>
        ";
}

include_once $g4['admin_path']."/admin.head.php";
?>

<style>
	.list_tab {
		list-style : none;
	}
	.list_tab > li {
		border:1px solid #dddddd;
		border-bottom: 0;
		padding:8px 20px;
		float: left;
		border-radius: 5px 5px 0 0;
		margin-right: 1px;
	}
	.list_tab > li.active {
		font-weight: bold;
		background-color: #ddd;
	}
	.list_tab > li.active > a {
		color: #565656;
	}
</style>

<table width='100%'>
	<tr>
		<td>
			시작일 : <input type="text" name='st_dt' value='<?=$_GET['st_dt'];?>'/>
			~
			종료일 : <input type="text" name='en_dt' value='<?=$_GET['en_dt'];?>'/>
			<select name="key">
				<option value="a.it_id" <?=($_GET['key'] == 'a.it_id') ? 'selected':'';?>>상품코드</option>
				<option value="b.it_name" <?=($_GET['key'] == 'b.it_name') ? 'selected':'';?>>상품명</option>
			</select>
			<input type="text" name='value' value='<?=$_GET['value'];?>'/>
			<input type="image" src="../../adm/img/btn_search.gif" align="absmiddle" onclick="oneday_search();">
			<input type="button" value=' 종료된 상품 구매제한 풀기 ' onclick="location.href='<?php echo $_SERVER['PHP_SELF'];?>?mode=it_order_onetime_limit_cnt'"/>
		</td>
	</tr>

</table>

<table width='100%' cellpadding='4' cellspacing='0'>
	<tr>
		<td align='right'>건수 : <?=$total_count;?></td>
	</tr>
</table>


<ul class="list_tab">
	<li <?php echo $_GET['fg'] == 'Y' ? "class='active'":""?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $qstr2;?>&fg=Y">진행</a></li>
	<li <?php echo $_GET['fg'] == 'N' ? "class='active'":""?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $qstr2;?>&fg=N">종료</a></li>
	<li <?php echo $_GET['fg'] == 'ALL' ? "class='active'":""?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $qstr2;?>&fg=ALL">전체</a></li>
</ul>
<table cellpadding='0' cellspacing='0' width='100%'>
	<tr>
		<td height='2' bgcolor='#CCC' colspan='9'></td>
	</tr>
	<tr align='center' class='ht'>
		<td rowspan='2'>코드</td>
		<td>상품코드</td>
		<td rowspan='2'></td>
		<td>상품명</td>
		<td>총 재고 (실/가)</td>
		<td>주문수량</td>
		<td>시작일</td>
		<td>판매금액</td>
		<td rowspan='2'><?=icon("입력", "./oneday_write_new.php");?></td>
	</tr>
	<tr class='ht' align='center'>
		<td>참고 상품 코드</td>
		<td>참고 상품명</td>
		<td>잔여 재고(실/가)</td>
		<td>주문금액</td>
		<td>종료일</td>

	</tr>
	<tr><td colspan="9" height="1" bgcolor="#CCCCCC"></td></tr>
	<?=$list_tr;?>
</table>
<?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&fg=$_GET[fg]&page=");?>

<script type="text/javascript">

    function list_delete(uid)
    {
        if(uid)
        {
            if(confirm('삭제하시겠습니까?'))
            {
                window.location.href = window.location.pathname + '?mode=del&uid=' + uid;
            }
        }
    }
    function oneday_search(){
        var st_dt	= $('input[name=st_dt]').val();
        var en_dt	= $('input[name=en_dt]').val();
        var key		= $('select[name=key]').val();
        var value	= $('input[name=value]').val();
        var	fg		= '<?php echo $_GET['fg']; ?>';

        var err = false;


        if(st_dt != '' && st_dt.length<5){
            alert('시작일은 4자리 이상 입력해 주세요.');
            $('input[name=st_dt]').focus();
            err = true;
        }
        if(en_dt != '' && en_dt.length<5){
            alert('종료일은 4자리 이상 입력해 주세요.');
            $('input[name=en_dt]').focus();
            err = true;
        }

        if(err == true){
            return false;
        }

        location.href="<?=$_SERVER['PHP_SELF']?>?fg="+fg+"&st_dt="+st_dt+"&en_dt="+en_dt+"&key="+key+"&value="+value;

    }
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
