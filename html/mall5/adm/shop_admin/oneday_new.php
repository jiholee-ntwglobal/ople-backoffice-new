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
$search = array();
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

$orderby =  "a.create_dt DESC";
switch($_GET['fg'])
{
	 case 'Y' :
	     $search[] = "DATE_FORMAT(NOW(),'%Y%m%d') BETWEEN a.st_dt AND a.en_dt";
	     $orderby = "a.sort ASC, a.uid ASC";
	     break;
	 case 'N' : $search[] = "DATE_FORMAT(NOW(),'%Y%m%d') NOT BETWEEN a.st_dt AND a.en_dt"; break;
}

$search_string = '';
if(count($search) > 0)
{
	$search_string = "AND " . implode(" AND ", $search);
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
            1 = 1 ".$search_string."
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
            1 = 1 ".$search_string."
        ORDER BY
            ".$orderby."
        LIMIT 
            ".$from_record.", ".$config['cf_page_rows'];
$sql = sql_query($qry);
while($oneday_item = mysql_fetch_assoc($sql))
{
	$it_name_ = explode("||", $oneday_item['it_name']);
	$it_name = $it_name_[0] . " " . $it_name_[1];
	$l_it_name_ = explode("||", $oneday_item['l_it_name']);
	$l_it_name = $l_it_name_[0] . " " . $l_it_name_[1];
	$list_li .= '
        <li class="list">
            <div style="width:50px;">
                <div>'.$oneday_item['uid'].'</div>
                <input type="hidden" name="sort['.$oneday_item['uid'].']" value="'.$oneday_item['sort'].'" class="sort">
            </div>
            <div style="width:100px;">
                <div><a href="'.$g4['shop_path'].'/item.php?it_id='.$oneday_item['it_id'].'" target="_blank">'.$oneday_item['it_id'].'</a></div>
                <div><a href="'.$g4['shop_path'].'/item.php?it_id='.$oneday_item['l_it_id'].'" target="_blank">'.$oneday_item['l_it_id'].'</a></div>
            </div>
            <div style="width:60px;"><img src="http://115.68.184.248/ople/item/'.$oneday_item['l_it_id'].'_l1" width="50" height="50"></div>
            <div style="width:calc(100% - 570px); text-align:left;">
                <div title="'.$oneday_item['it_name'].'">'.mb_substr($it_name, 0,30, mb_internal_encoding()).'...</div>
                <div title="'.$oneday_item['l_it_name'].'">'.mb_substr($l_it_name,0, 30, mb_internal_encoding()).'...</div>
            </div>
            <div style="width:100px;">
                <div>'. $oneday_item['real_qty'] .' / '.( $oneday_item['real_qty'] * $oneday_item['multiplication'] ).'</div>
                <div><b>'.( $oneday_item['real_qty'] - $oneday_item['order_cnt']).'</b> / '.( ($oneday_item['real_qty'] * $oneday_item['multiplication']) - ($oneday_item['order_cnt'] * $oneday_item['multiplication']) ).'</div>
            </div>
            <div style="width:70px;">
                <div>'.$oneday_item['order_cnt'].'</div>
                <div>'.number_format($oneday_item['total_amount']).'</div>
            </div>
            <div style="width:70px;">
                <div>'.$oneday_item['st_dt'].'</div>
                <div>'.$oneday_item['en_dt'].'</div>
            </div>
             <div style="width:60px;">
                <div>
                '.number_format($oneday_item['price']).'
              <br/>
                 ( $ '.$oneday_item['price_usd'].' )
              </div>
            </div>
            <div style="width:60px;">
                <div>
                    '.icon("수정", "./oneday_write_new.php?uid=".$oneday_item['uid']).' '.
		icon("삭제", "javascript:list_delete(".$oneday_item['uid'].")").'
                </div>
            </div>
        </li>
	';
}

$is_sortable = 1;
if(!$list_li)
{
    $is_sortable = 0;
	$list_li = "
        <li style='text-align:center; height:100px; line-height:100px;'>데이터가 존재하지 않습니다.</li>
    ";
}

include_once $g4['admin_path']."/admin.head.php";
?>

<style>
    .list_tab {
        list-style : none;
        margin-left: 5px;
    }
    .list_tab > li {
        border:1px solid #dddddd;
        border-bottom: 0;
        padding:8px 20px;
        float: left;
        border-radius: 5px 5px 0 0;
        margin-right: 5px;
    }
    .list_tab > li.active {
        font-weight: bold;
        background-color: #ddd;
    }
    .list_tab > li.active > a {
        color: #565656;
    }


    .list_ul > li{
        overflow: hidden;
        clear:both;
        border:1px solid #dddddd;
        padding:5px 0;
        margin-bottom: 2px;
        border-radius: 5px;
        background-color: #fff;
    }
    .list_ul > li.hidden_item{
        background-color:#d9dfe8;
    }
    .list_ul > li > div {
        float: left;
        text-align: center;
    }
    .list_ul > li > div > div:first-child {
        padding-top: 3px;
        padding-bottom: 10px;
    }

    .tbl-head th {
        border-top: 2px solid #ccc;
    }
    .tbl-head th, .tbl-head td {
        font-weight: normal;
        font-size: 12px;
        text-align: center;
        height: 25px;
    }
</style>

<table width='100%'>
    <tr>
        <td>
            시작일 : <input type="text" name='st_dt' size="10" value='<?=$_GET['st_dt'];?>'/>
            ~
            종료일 : <input type="text" name='en_dt' size="10" value='<?=$_GET['en_dt'];?>'/>
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

<ul class="list_tab" style="margin-top:10px;">
    <li <?php echo $_GET['fg'] == 'Y' ? "class='active'":""?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $qstr2;?>&fg=Y">진행</a></li>
    <li <?php echo $_GET['fg'] == 'N' ? "class='active'":""?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $qstr2;?>&fg=N">종료</a></li>
    <li <?php echo $_GET['fg'] == 'ALL' ? "class='active'":""?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $qstr2;?>&fg=ALL">전체</a></li>
</ul>
<div style="float:right; padding:10px 5px 0 0; ">
    건수 : <?=number_format($total_count);?>건
</div>

<table class="tbl-head" cellpadding='0' cellspacing='0' width='100%'>
    <colgroup>
        <col width="50" />
        <col width="100" />
        <col width="60" />
        <col />
        <col width="100" />
        <col width="70" />
        <col width="70" />
        <col width="60" />
        <col width="60" />
    </colgroup>
    <thead>
    <tr>
        <th rowspan='2'>코드</th>
        <th>상품코드</th>
        <th rowspan='2'></th>
        <th>상품명</th>
        <th>총재고(실/가)</th>
        <th>주문수량</th>
        <th>시작일</th>
        <th rowspan="2">판매금액</th>
        <th rowspan='2'><?=icon("입력", "./oneday_write_new.php");?></th>
    </tr>
    <tr>
        <td>참고 상품코드</td>
        <td>참고 상품명</td>
        <td>잔여재고(실/가)</td>
        <td>주문금액</td>
        <td>종료일</td>
    </tr>
    </thead>
</table>

<!--<form name="oneday_sort_update" method="post">-->
<form name="sort_update">
    <ul class="list_ul">
		<?php echo $list_li ?>
    </ul>
    <p style="text-align:center; padding-top:5px;"><input type="button" class="sort-update-start" value="순서 변경" style="padding:5px 10px; cursor:pointer;"></p>
</form>

<?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&fg=$_GET[fg]&page=");?>

<script src="//code.jquery.com/ui/1.11.0/jquery-ui.js"></script>
<script type="text/javascript">
    var is_sortable = "<?php echo $is_sortable?>";
	<?php if($_GET['fg'] != 'Y'){?>
    var sort_fg = false;
	<?php }else{?>
    var sort_fg = is_sortable == 1 ? true : false;
    var sort_change = false;
	<?php }?>
    $(function() {
        if(sort_fg == true) {
            $("ul.list_ul").sortable({
                connectWith: "ul",
                update: function (event, ui) {
                    sorting_fnc();
                }
            });

            $("ul.dropfalse").sortable({
                connectWith: "ul",
                //dropOnEmpty: false, // 여길로 못들어오게
                update: function (event, ui) {
                    sorting_fnc();
                }
            });
        }
        else
        {
            $('input.sort-update-start').parent().remove();
        }

        $('input.sort-update-start').on('click', function() {
            if($('.list_ul > li.list').length < 1)
            {
                alert('순서를 변경할 상품이 존재하지 않습니다.');
                return false;
            }
            if(sort_fg == false)
            {
                alert('진행 탭 에서만 순서를 변경할 수 있습니다.');
                return false;
            }

            if(sort_change === true)
            {
                var frm = $('form[name=sort_update]')[0];
                var form_data = new FormData(frm);

                $.ajax({
                    type: "POST",
                    url: "oneday_new_sort_update.php",
                    data: form_data,
                    dataType: "json",
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        switch (response.result) {
                            case true :
                                alert(response.message);
                                break;

                            case false :
                                alert(response.message);
                                window.location.reload();
                                break;
                        }
                    },
                    error: function (request, status, error) {
                        if (request && error) {
                            alert(request.status + " " + error + "\n잠시 후 다시 시도하세요.");
                        }
                        else {
                            alert('잠시 후 다시 시도하세요.');
                        }
                    }
                });
            }
            else
            {
                alert('순서 변경된 상품이 없습니다.')
            }
        });
    });

    function sorting_fnc()
    {
        for(var i=0; i<$('.list_ul > li').length; i++)
        {
            var sort = i + 1;

            $('.list_ul > li:eq('+i+') > div > .sort').val(sort);
            // $('.list_ul > li:eq('+i+') > .no > .number').text(sort);
        }
        sort_change = true;
    }

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
