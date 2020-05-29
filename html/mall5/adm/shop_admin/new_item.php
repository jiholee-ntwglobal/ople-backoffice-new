<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-03-31
 * Time: 오후 2:10
 */


$sub_menu = "600500";
include "_common.php";
auth_check($auth[$sub_menu], "r");

if($_POST['mode'] == 'sort_update'){
    $sort = $_POST['sort'];
    if(is_array($sort)) {
        foreach ($sort as $uid => $sort) {
            $sql = "UPDATE yc4_item_new SET sort = '" . sql_safe_query($sort) . "' WHERE uid = '" . sql_safe_query($uid) . "'" . PHP_EOL;
            sql_query($sql);
        }
    }
    alert('순서 변경이 완료되었습니다.',$_SERVER['PHP_SELF']);
    exit;
}

# 제품관 로드 #
$s_sql = sql_query("select s_id,name from yc4_station where s_id<>6 order by sort asc");
$st_li = "";
if(!$_GET['s_id']){
    $_GET['s_id'] = 3;
}
while($row = sql_fetch_array($s_sql)){
    $st_li.= "<li ".($_GET['s_id'] == $row['s_id'] ? "class='active'":"")."><a href='".$_SERVER['PHP_SELF']."?s_id=".$row['s_id']."'>".$row['name']."</a></li>";
}

//$st_li.= "<li ".($_GET['s_id'] == 6 ? "class='active'":"")."><a href='".$_SERVER['PHP_SELF']."?s_id=6'>리스트 순서변경</a></li>";

$sql_search = "";

if($_GET['s_id'] && $_GET['s_id'] != 6){
    $sql_search .= ($sql_search ? " and ":" where ")."a.s_id = '".sql_safe_query($_GET['s_id'])."'";
}

# 데이터 로드 #
$sql = sql_query("
    select
        a.*,b.name
    from
      yc4_item_new a
      left join yc4_station b on a.s_id = b.s_id
    {$sql_search}
    order by a.sort asc
");
/*echo " select
        a.*,b.name
    from
      yc4_item_new a
      left join yc4_station b on a.s_id = b.s_id
    {$sql_search}
    order by a.sort asc";*/
$no = 1;
$list_li = '';
while($row = sql_fetch_array($sql)){

    $type = $row['type'] == 'I' ? '상품' : '브랜드';

    $list_tr .= "
		<tr>
			<td>".$row['sort']."</td>
			<td>$type</td>
			<td>$row[type_value]</td>
			<td>$row[title]</td>
			<td>$row[end_dt]</td>
			<td>$row[use_fg]</td>
			<td><a href='new_item_write.php?uid=$data[uid]'><img src=\"http://209.216.56.103/mall5/adm/img/icon_modify.gif\"></img></a></td>
		</tr>";

    $link = '';
    $item_con = '';
    switch($row['type']){
        case 'I' :
            $link = $g4['shop_path']."/item.php?it_id=".$row['type_value'];
            $it_info = sql_fetch("select it_name,it_amount,it_maker,it_maker_kor from ".$g4['yc4_item_table']." where it_id = '".$row['type_value']."'");
            $item_con = "
                <div class='item_con'>
                    <span class='title'>
                        <span class='ko'>".$row['it_name_kor']."</span>
                        <span class='e'>".$row['it_name_eng']."</span>
                    </span>
                    <span class='price'>￦ ".number_format($it_info['it_amount'])." ($ ".number_format(usd_convert($it_info['it_amount']),2).")</span>
                </div>
            ";
            break;
        case 'B' :
            $link = $g4['shop_path']."/search.php?it_maker=".urlencode($row['type_value']);
            $it_info = sql_fetch("select it_maker,it_maker_kor from ".$g4['yc4_item_table']." where it_maker = '".sql_safe_query($row['type_value'])."' and (it_maker_kor is not null) limit 1");
            break;
    }
    $list_li .= "
        <li class='new_list'>
            <div class='no'>
                <p class='number' style='display: none;'>".$row['sort']."</p>
                <input type='hidden' name='sort[".$row['uid']."]' value='".$row['sort']."' class='sort'/>
            </div>
			<a href='".$link."' target='_blank'>
				<span class='brand'>[".$it_info['it_maker']."] ".$it_info['it_maker_kor']."</span>
				<span class='list_title'>
					<span class='b_title'>".nl2br($row['title'])."</span>
					<span class='s_title'>".nl2br($row['title_desc'])."</span>
				</span>

			".$item_con."

				<span class='img'><img src='".$row['img_url']."'></span>
			</a>

			<br/>
			<p>순서 : ".$row['sort']." 타입 : ".$type." / ".$row['name']." / 종료일 : ".date('Y-m-d',strtotime($row['end_dt']))." / 사용 여부 : ".$row['use_fg']." / ".icon('수정',"new_item_write.php?uid=".$row['uid'])."</p>
		</li>
    ";
    $no++;

}
$sort_btn = "<input type='submit' value='순서 저장'/>";
if(!$list_li){
    $list_li = "<li>데이터가 존재하지 않습니다.</li>";
    $sort_btn = '';
}
/*if($sort_btn && $_GET['s_id'] != 6){
    $sort_btn = '';
}*/

include_once $g4['admin_path']."/admin.head.php";
?>
    <style>
        .list_tab{
            list-style: none;
            overflow: hidden;
        }
        .list_tab>li{
            padding:5px;
            float:left;
            border: 1px solid #dddddd;
        }
        .list_tab>li.active{
            font-weight: bold;
        }
    </style>

    <div style="overflow: hidden;">
        <ul class="list_tab" style="float: left">
            <?php echo $st_li;?>
        </ul>
        <ul class="list_tab" style="float: right">
            <li><a href="<?php echo $g4['shop_admin_path'];?>/new_item_write.php">신상품 등록 + </a></li>
        </ul>
    </div>
    <p align="right" style="font-weight: bold; clear: both;">
        순서번경<?php echo help('순서 변경은 드래그 & 드랍 후 순서 저장 버튼을 눌러 주세요');?>
    </p>
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
        <input type="hidden" name="mode" value="sort_update"/>
        <?php echo $sort_btn?>
        <ul class="list_ul">
            <?php echo $list_li;?>
        </ul>
        <?php echo $sort_btn?>
    </form>
    <?/*
    <table width="100%">
        <thead>
        <tr>
            <td>순서</td>
            <td>타입</td>
            <td>상품코드/브랜드명</td>
            <td>제목</td>
            <td>종료일</td>
            <td>노출여부</td>
            <td><?php echo icon('입력',$g4['shop_admin_path'].'/new_item_write.php');?></td>
        </tr>
        </thead>
        <tbody>
        <?php echo $list_tr;?>
        </tbody>
    </table>
    */?>

    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/ui/1.11.0/jquery-ui.js"></script>
    <script>

        $(function() {

            $("ul.list_ul").sortable({
                connectWith: "ul",
                update: function (event, ui) {
                    sorting_fnc();
                }
            });



        });

        function sorting_fnc(){
            for(var i=0; i<$('.list_ul > li').length; i++){
                var sort = i+1;

                $('.list_ul > li:eq('+i+') > .no > .sort').val(sort);
                $('.list_ul > li:eq('+i+') > .no > .number').text(sort);
            }

        }

    </script>

<?php
include_once $g4['admin_path']."/admin.tail.php";
?>