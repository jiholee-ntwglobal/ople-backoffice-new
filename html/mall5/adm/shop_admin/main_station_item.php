<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-03-30
 * Time: 오후 2:14
 */

$sub_menu = "600400";
include "_common.php";
auth_check($auth[$sub_menu], "r");

if($_POST['mode'] == 'sort_update'){
    if(count($_POST['sort']) < 1){
        alert('순서를 변경할 상품이 존재하지 않습니다.');
        exit;
    }
    foreach($_POST['sort'] as $uid => $sort){
        sql_query("update yc4_station_main_item set sort = '".$sort."' where uid = '".$uid."'");
    }
    alert('순서 변경이 완료되었습니다.',$_SERVER['PHP_SELF'].'?s_id='.$_POST['s_id']);
    exit;
}

if(!$_GET['fg']){
    $_GET['fg'] = 'Y';
}

$sql_search = '';

switch($_GET['fg']){
    case 'Y' : $sql_search .= " and useyn = 'Y'"; break;
    case 'N' : $sql_search .= " and useyn = 'N'"; break;
}
if(!$_GET['s_id']){
    $_GET['s_id'] = 3;
}

if($_GET['s_id']){
    $sql_search .= " and a.s_id = '".mysql_real_escape_string($_GET['s_id'])."'";
}

# 메인 진열상품 데이터 로드 #
$sql = sql_query("
    select
        a.*,b.it_amount,b.it_amount_usd
    from
        yc4_station_main_item a,
        ".$g4['yc4_item_table']." b
    where
        a.it_id = b.it_id
        ".$sql_search."
    order by a.sort asc
");
$items_array = array();
$currentqty_it_id = array();
while($row = sql_fetch_array($sql)){
    array_push($items_array,$row);
    if(isset($row['it_id'])){
        array_push($currentqty_it_id,$row['it_id']);
    }
}

//22server currentqty
include $g4['full_path'].'/lib/db.php';
$inQuery = implode(',', array_fill(0, count($currentqty_it_id), '?'));
$sql_query = "
    SELECT a.it_id, b.upc,a.qty,b.currentqty
    FROM ople_mapping a INNER JOIN N_MASTER_ITEM b ON a.upc = b.upc
    WHERE a.it_id in ($inQuery)
    group by a.it_id, b.upc,a.qty,b.currentqty
";
$db = new db();
$currentqty_stmt=$db->ntics_db->prepare($sql_query);
$currentqty_stmt->execute($currentqty_it_id);
$currentqty_item_data =array();
foreach( $currentqty_stmt->fetchAll() as $currentqty_item){
    if(isset($currentqty_item['it_id'])){
        $currentqty_item_data[$currentqty_item['it_id']][] = $currentqty_item;
    }
}

//$stm= $db->ntics_db->prepare($sql_query);
//$stm= $stm->execute($currentqty_it_id);
//$data= $stm->fetchAll();

//var_dump($data);


$list_tr = $list_li = '';
$total_cnt = mysql_num_rows($sql);
$view_cnt = floor($total_cnt / 4) * 4;
if($view_cnt > 16){
    $view_cnt = 16;
}
foreach ($items_array as $row){
    switch($row['useyn']){
        case 'Y' : $useyn = 'O'; break;
        case 'N' : $useyn = 'X'; break;

    }
    $NTICS_td ='';
    if(is_array($currentqty_item_data[$row['it_id']])){
        foreach ($currentqty_item_data[$row['it_id']] as $upc_item){
            $NTICS_td .= "<p>UPC : ".$upc_item['upc']." x ".$upc_item['qty']."ea NTICS QTY : ".$upc_item['currentqty']."</p>";
        }
    }
    $list_li .= "
        <li>
            <div class='no'>
                <div class='number'>".$row['sort']."</div>
                <input type='hidden' name='sort[".$row['uid']."]' value='".$row['sort']."' class='sort'/>
            </div>
            <div class='it_img'>"
        //곽범석 수정
        ."<img src=\"http://115.68.20.84/item/".$row['it_id']."_l1\" width=\"100\" height=\"100\">"./*<img id='{$row['it_id']}_s' src='https://uvaxnqcpaepy770580.gcdn.ntruss.com/mall5/data/item/{$row['it_id']}_s' width='80' height='80' border='0'>*/"
            </div>
            <div class='it_info'>
                <p>오플 상품코드 : <a href='".$g4['shop_path']."/item.php?it_id=".$row['it_id']."' target='_blank'>".$row['it_id']."</a></p>
                <p>출력 상품명 : ".$row['it_name']."</p>
                <p>MSRP : $ ".number_format($row['msrp'],2)." / ￦ ".number_format(round($row['msrp'] * $default['de_conv_pay'],-2))."</p>
                <p>가격 : $ ".usd_convert($row['it_amount'])."(".get_dc_percent(usd_convert($row['it_amount']),$row['msrp'] )."%) / ￦ ".number_format($row['it_amount'])."</p>
                ".$NTICS_td ."
            </div>
            <div class='list_btn'>
                ".
                icon('수정',$g4['shop_admin_path'].'/main_station_item_write.php?uid='.$row['uid']).
                icon('삭제',$g4['shop_admin_path'].'/main_station_item_write.php?uid='.$row['uid'].'&mode=delete')
                ."
            </div>

        </li>
    ";
    $NTICS_td='';
    /*
    $list_tr .= "
        <tr>
            <td align='center'>".$row['uid']."</td>
            <td align='center'>".$row['sort']."</td>
            <td align='center'><a href='".$g4['shop_path']."/item.php?it_id=".$row['it_id']."' target='_blank'>".$row['it_id']."</a></td>
            <td>".get_it_image($row['it_id'].'_s',80,80,null,null,null,true,false)."</td>
            <td>".$row['it_name']."</td>
            <td align='right'>
                $ ".number_format($row['msrp'],2)."<br/>
                ￦ ".number_format(round($row['msrp'] * $default['de_conv_pay'],-2))."
            </td>
            <td align='right'>
                $ ".number_format(round($row['it_amount'] / $default['de_conv_pay'],2))."(".get_dc_percent(round($row['it_amount'] / $default['de_conv_pay']),$row['msrp'] )."%)<br/>
                ￦ ".number_format($row['it_amount'])."
            </td>
            <td align='center'>".$useyn."</td>
            <td align='center'>".
                icon('수정',$g4['shop_admin_path'].'/main_station_item_write.php?uid='.$row['uid']).
                icon('삭제',$g4['shop_admin_path'].'/main_station_item_write.php?uid='.$row['uid'].'&mode=delete').
            "</td>
        </tr>
    ";
    */
}

# 제품관로드 #
$s_sql = sql_query("select s_id,name from yc4_station where s_id <> 6 order by sort");

$st_qstr = $st_qstr2 = $_GET;
unset($st_qstr['s_id']);
$st_qstr = http_build_query($st_qstr);

unset($st_qstr2['fg']);
$st_qstr2 = http_build_query($st_qstr2);

$station_tab = "";

while($row = sql_fetch_array($s_sql)){
    $station_tab .= "
        <li ".($_GET['s_id'] == $row['s_id'] ? " class='active'":"")."><a href='".$_SERVER['PHP_SELF']."?".$st_qstr."&s_id=".$row['s_id']."'>".$row['name']."</a></li>
    ";
}

include_once $g4['admin_path']."/admin.head.php";
?>
<style>
.list_tab_wrap{
    overflow: hidden;
}
.list_tab{
    overflow: hidden;
    list-style: none;

}
.list_tab>li{
    float:left;
    padding:5px;
    border: 1px solid #dddddd;
}
.list_tab>li.active{
    font-weight: bold;
}
.it_info,.it_img,.no,.list_btn{
    float:left;
    height:100px;
}
.it_info{
    width:500px;
    margin-left:10px;
}
.list_ul > li{
    overflow: hidden;
    clear:both;
    border:1px solid #dddddd;
    padding:5px;
}
.list_ul > li.hidden_item{
    background-color:#d9dfe8;
}
</style>
<div class="list_tab_wrap">
    <ul class="list_tab" style="float: left;">
        <li <?php echo $_GET['fg'] == 'Y' ? "class='active'":""?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $st_qstr2;?>&fg=Y">진행</a></li>
        <li <?php echo $_GET['fg'] == 'N' ? "class='active'":""?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $st_qstr2;?>&fg=N">종료</a></li>
        <li <?php echo $_GET['fg'] == 'ALL' ? "class='active'":""?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $st_qstr2;?>&fg=ALL">전체</a></li>
    </ul>
    <ul class="list_tab" style="float: right;">
        <?php echo $station_tab;?>
    </ul>
</div>
<?php /*
<table width="100%">
    <thead>
        <tr align="center">
            <td>번호</td>
            <td>순서</td>
            <td>오플상품코드</td>
            <td></td>
            <td>출력상품명</td>
            <td>MSRP</td>
            <td>가격</td>
            <td>진열여부</td>
            <td><?php echo icon('입력',$g4['shop_admin_path'].'/main_station_item_write.php');?></td>
        </tr>
    </thead>
    <tbody>
        <?php echo $list_tr;?>
    </tbody>
</table>
 */?>
<h2>총 <?php echo $total_cnt;?>개 등록 / <?php echo $view_cnt;?>개 노출 예정</h2>
<h3>상품은 4개단위로 출력됩니다.(최대 16개 까지 출력)</h3>
<p align="right"><?php echo icon('입력',$g4['shop_admin_path'].'/main_station_item_write.php');?></p>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return frm_chk(this);">
    <input type="hidden" name="mode" value="sort_update"/>
    <input type="hidden" name="s_id" value="<?php echo $_GET['s_id'];?>"/>
    <ul class="list_ul">
        <?php echo $list_li;?>
    </ul>
    <p align="center"><input type="submit" value="순서 변경"/></p>
</form>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.11.0/jquery-ui.js"></script>
<script type="text/javascript">
    <?php if($_GET['fg'] != 'Y'){?>
    var sort_fg = false;
    <?php }else{?>
    var sort_fg = true;
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

    });

    function sorting_fnc(){
        for(var i=0; i<$('.list_ul > li').length; i++){
            var sort = i+1;

            $('.list_ul > li:eq('+i+') > .no > .sort').val(sort);
            $('.list_ul > li:eq('+i+') > .no > .number').text(sort);
            <?php $view_cnt?>
        }

    }
    function frm_chk(){
        if(sort_fg == false){
            alert('진행 탭 에서만 순서를 변경할 수 있습니다.');
            return false;
        }
        if($('.list_ul > li').length < 1){
            alert('순서를 변경할 상품이 존재하지 않습니다.');
            return false;
        }
        return true;
    }
</script>
<?php
include_once $g4['admin_path']."/admin.tail.php";
?>