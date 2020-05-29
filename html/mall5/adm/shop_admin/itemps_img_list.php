<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-07-11
 * Time: 오전 10:41
 */
include_once("./_common.php");

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

$list_page = 20;//보여줄 컬럼
$num_page = 5;//표시할 페이지

//리스트
$sql =
    "
            SELECT date_format(is_time, '%Y') yyyy,
                   date_format(is_time, '%m') mm,
                   date_format(is_time, '%d') dd,
                   is_image0,
                   is_image1,
                   is_image2,
                   is_image3,
                   is_image4
            FROM yc4_item_ps
            WHERE is_image0 IS NOT NULL AND is_image0 != ''
limit 1

   ";
/*limit  " . ($now_page - 1) * $list_page . " ,{$list_page}*/
$coupon_result = sql_query($sql);

$coupon_list = array();
$coupon_data = array();
$int = 1;

while ($row = sql_fetch_array($coupon_result)) {
    array_push($coupon_list, $row);
}

//갯수
$sql =
    "
            SELECT count(*) cnt
            FROM yc4_item_ps
            WHERE is_image0 IS NOT NULL AND is_image0 != ''
   ";
$coupon_cnt = sql_fetch($sql);
$total_list = $coupon_cnt['cnt'];

$get_data = $_GET;
unset($get_data['page']);
$get_data = http_build_query($get_data);

?>
<!-- 합쳐지고 최소화된 최신 CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">

<!-- 부가적인 테마 -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">

<!-- 합쳐지고 최소화된 최신 자바스크립트 -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-lg-12">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>아이디</th>
                        <th>이름</th>
                    </tr>
                    </thead>
                    <?php if (!empty($coupon_list)) {
                        $upload_dir = "http://ople.com/mall5/data/itemps_img";
                        ?>
                        <tbody>
                        <?php foreach ($coupon_list as $row){
                            $file_name_yyyy = $file_name_mm = $file_name_dd = '';

                            if(!$row['yyyy'] || trim($row['yyyy'])== ''){
                                continue;
                            }
                            if(!$row['mm'] || trim($row['mm'])== ''){
                                continue;
                            }
                            if(!$row['dd'] || trim($row['dd'])== ''){
                                continue;
                            }
                            $file_name_yyyy = $row['yyyy'];
                            $file_name_mm = $row['mm'];
                            $file_name_dd = $row['dd'];

                            unset($row['yyyy']);
                            unset($row['mm']);
                            unset($row['dd']);
                            $fileupload_dir = $upload_dir."/".$file_name_yyyy."/".$file_name_mm."/".$file_name_dd;

                        foreach ($row as $img_value){
                            if($img_value == '' ){
                                continue;
                            }
                            $file_ext = pathinfo($img_value);

                            $info1 = getimagesize($fileupload_dir. "/" . $file_ext['basename']);
                            $info2 = getimagesize("http://ople.com/mall5/data/ituse/" . $file_ext['basename']);

                            $info1_1 = floor(filesize($fileupload_dir. "/" . $file_ext['basename'])/ 1024);
                            $info2_1 = floor(filesize("http://ople.com/mall5/data/ituse/" . $file_ext['basename'])/ 1024);
                            ?>
                        <tr>
                            <td>
                                <?php echo $fileupload_dir. "/" . $file_ext['basename']."<br>";?>
                                <!--<img src="<?php /*echo $fileupload_dir. "/" . $file_ext['basename'];*/?>">-->
                                <!--<img src="<?php /*echo $fileupload_dir. "/" . $file_ext['basename'];*/?>">-->
                                <?php echo $info1[0]."x".$info1[1]."<br>".$info1_1."KB";?>
                            </td>
                            <td>
                                <!--<img src="<?php /*echo "http://ople.com/mall5/data/ituse/" . $file_ext['basename'];*/?>">-->
                                <?php echo $info2[0]."x".$info2[1]."<br>".$info2_1."KB";?>
                            </td>
                        </tr>
                            <?php }} ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td class="text-center" colspan="6">
                                <?php echo paging($total_list, $now_page, $list_page, $num_page, $_GET); ?>
                            </td>
                        </tr>
                        </tfoot>
                    <? } ?>
                </table>
            </div>
        </div>
    </div>
</div>