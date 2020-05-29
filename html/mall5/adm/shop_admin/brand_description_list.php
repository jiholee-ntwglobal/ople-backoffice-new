<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-06-01
 * Time: 오후 5:12
 */
$sub_menu = "600971";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

//페이징
function paging($total_list, $now_page, $list_page, $num_page, $searchdata)
{
    $total_page = ceil($total_list / $list_page); // 전체 페이지
    $total_block = ceil($total_page / $num_page); // 전체 블록
    $now_block = ceil($now_page / $num_page); //현재 블록
    $start_page = (((int)(($now_page - 1) / $num_page)) * $num_page) + 1; //시작페이지
    $end_page = $start_page + $num_page - 1; //끝페이지
    $next_page = (((int)(($now_page - 1 + $num_page) / $num_page)) * $num_page) + 1; //다음페이지
    $prev_page = (((int)(($now_page - 1 - $num_page) / $num_page)) * $num_page) + $num_page; //이전페이지
    $server = $_SERVER['PHP_SELF'];
    $pages = "";
    unset($searchdata['page']);
    $search = "";
    $search = http_build_query($searchdata);
    if ($total_page == 0 || $now_page > $total_page) {
        $pages .= "검색한 결과는 없습니다.";
    } else {
        if ($now_page > 1) {
            $pages .= "<a class=\"btn btn-primary\" href=" . $server . "?page=1" . "&" . $search . "> 처음 </a>";
        }
        if ($now_block > 1) {
            $pages .= "<a class=\"btn btn-primary\" z href=" . $server . "?page=" . $prev_page . "&" . $search . "> 이전 </a>";
        }
        if ($end_page >= $total_page) {
            $end_page = $total_page;
        }
        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $now_page) {
                $pages .= "<b class=\"btn btn-info\" disabled=\"disabled\"> " . $i . " </b>";
            } else {
                $pages .= "<a class=\"btn btn-info\" href=" . $server . "?page=" . $i . "&" . $search . "> " . $i . " </a>";
            }
        }
        if ($now_page >= 1) {
            if ($total_block != $now_block) {
                $pages .= "<a class=\"btn btn-primary\" href=" . $server . "?page=" . $next_page . "&" . $search . "> 다음 </a>";
            }
            if ($now_page != $total_page) {
                $pages .= "<a class=\"btn btn-primary\" href=" . $server . "?page=" . $total_page . "&" . $search . "> 마지막 </a>";
            }
        }
    }
    return $pages;
}

$now_page = isset($_GET['page']) ? $_GET['page'] : "1";
$now_page = $now_page == 0 ? 1 : $now_page;
$now_page = is_numeric($now_page) ? $now_page : "1";

//검색
$_GET['name'] = trim($_GET['name']) ? trim($_GET['name']) : '';

$_GET['tab'] = trim($_GET['tab']) ? trim($_GET['tab']) : 'all';

$where = '';

if ($_GET['name'] != '') {
    $where .= ($where == '' ? " where ":' and ')." a.it_maker like '%".sql_safe_query($_GET['name'] )."%'";
}

if($_GET['tab']== 'ing'){
    $where .= ($where == '' ? " where ":' and ')." date_format(now(),'%Y%m%d')between date_format(ifnull(start_date,'1999-01-01'),'%Y%m%d')  and date_format(ifnull(end_date,'2100-01-01'),'%Y%m%d')  ";
}elseif ($_GET['tab']== 'wait'){
    $where .= ($where == '' ? " where ":' and ')." (date_format(now(),'%Y%m%d') < date_format(ifnull(start_date,'1999-01-01'),'%Y%m%d')  or date_format(now(),'%Y%m%d')> date_format(ifnull(end_date,'2100-01-01'),'%Y%m%d')  ) ";
}

//paging
$list_page = 20;//보여줄 컬럼
$num_page = 5;//표시할 페이지

//리스트
$sql =
    "
select uid,it_maker,use_flag_pc,use_flag_mo,start_date,end_date 
from brand_description a
$where
order by uid desc 
limit  ".($now_page - 1) * $list_page." ,{$list_page}
   ";

$brand_description_result = sql_query($sql);
$brand_description_result_list = array();

while ($row = sql_fetch_array($brand_description_result)){

    array_push($brand_description_result_list ,$row);

}

//갯수
$sql =
    "
SELECT count(*) cnt
FROM brand_description a
".$where."
   ";
$brand_description_cnt = sql_fetch($sql);
$total_list = $brand_description_cnt['cnt'];

//url
$get_data = $_GET;
unset($get_data['mode']);
$excel_url =http_build_query($get_data);
unset($get_data['tab']);
unset($get_data['page']);
$get_data = http_build_query($get_data);



$g4[title] = "브랜드별 배너 관리";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div class="row">
    <div class="col-lg-12">
        <h4>브랜드별 배너 관리</h4>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <p><span class="glyphicon glyphicon-search"></span>해당 버튼은 적용,미적용 상관없이 볼수있는 테스트페이지</p>
        <p>브랜드 클릭시 라이브서버로 이동</p>
        <p>모바일기능은 미구현(모바일 기능 필요할시 개발팀 문의)</p>
    </div>
</div>
<form class="form-inline text-right">
    <input type="hidden" value="<?php echo $_GET['tab']; ?>" name="tab">
    <div class="form-group">
        <label>브랜드명</label>
        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($_GET['name']); ?>">
    </div>
    <div class="form-group">
    </div>
    <button class="btn btn-primary" type="submit">검색</button>
</form>
<br>
<div class='row'>
    <div class="col-lg-10 text-right">
        <ul class="nav nav-tabs">
            <li role="presentation" id="tab_upc" class='<?php echo $_GET['tab'] == '' || $_GET['tab'] == 'all' ? 'active' : ''; ?>'>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $get_data; ?>">등록된 전체 브랜드</a>
            </li>
            <li role="presentation" id="tab_it_id" class='<?php echo $_GET['tab'] == 'ing' ? 'active' : ''; ?>'>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $get_data; ?>&tab=ing">진행중인 전체 브랜드</a>
            </li>
            <li role="presentation" id="tab_it_id" class='<?php echo $_GET['tab'] == 'wait' ? 'active' : ''; ?>'>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $get_data; ?>&tab=wait">종료된 전체 브랜드</a>
            </li>
        </ul>
    </div>
    <div class="col-lg-2 text-right">
        <button class="btn btn-success" type="button" onclick="location.href='./brand_description_form.php'" >생성</button>
    </div>
</div>
<table class="table table-hover">
    <thead>
    <tr>
        <td><strong>브랜드</strong></td>
        <td><strong>브랜드배너 PC</strong></td>
        <td><strong>브랜드배너 MO</strong></td>
        <td><strong>기간</strong></td>
        <td></td>
    </tr>
    </thead>
    <?php if (!empty($brand_description_result_list)) { ?>
        <tbody>
        <?php foreach ($brand_description_result_list as $value){ ?>
            <tr>
                <td><a href="http://ople.com/mall5/shop/search.php?it_maker=<?php echo $value['it_maker'];?>" target="_blank"><?php echo $value['it_maker'];?></a></td>
                <td><?php echo $value['use_flag_pc']=='1'?'적용':'미적용';?><a target="_blank" href="http://ople.com/mall5/shop/search_brand_description.php?it_maker=<?php echo $value['it_maker'];?>"><span class="glyphicon glyphicon-search"></span></a></td>
                <td><?php echo $value['use_flag_mo']=='1'?'적용':'미적용';?><a target="_blank" href="http://www.ople.com/m/shop/brand_description.php?it_maker=<?php echo $value['it_maker'];?>"><span class="glyphicon glyphicon-search"></span></a></td>
                <td><?php echo $value['start_date'] ? $value['start_date'] : '무기한';?>  ~ <?php echo $value['end_date'] ? $value['end_date'] : '무기한';?> </td>
                <td>
                    <button type="button" onclick="location.href='./brand_description_form.php?uid=<?php echo $value['uid']; ?>'" class="btn btn-info btn-sm">수정</button>
                    <button type="button" onclick="delete_data('<?php echo $value['uid']; ?>','<?php echo $value['it_maker'];?>');" class="btn btn-danger btn-sm">삭제</button>
                </td>
            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
        <tr>
            <td class="text-center" colspan="9"><?php echo paging($total_list, $now_page, $list_page, $num_page, $_GET); ?></td>
        </tr>
        </tfoot>
    <? } ?>
</table>

<form action="./brand_description_save.php" method="post"id="delete_from">
    <input type="hidden" name="mode" value="delete">
    <input type="hidden" name="uid" value="">
    <input type="hidden" name="brand" value="">
</form>
<script>
    function delete_data(id,brand) {
        $('input[name=uid]').val('');
        if(confirm('삭제하시겠습니까?')){
            $('input[name=uid]').val(id);
            $('#delete_from').submit();
        }



    }
</script>
<?
include_once("$g4[admin_path]/admin.tail.php");
?>

