<?php
/**
 * Created by PhpStorm.
 * File name : nfo.php.
 * Comment :
 * Date: 2016-01-05
 * User: Minki Hong
 */

$sub_menu = "300120";

error_reporting(E_ALL);

include_once "./_common.php";
auth_check($auth[$sub_menu], "w");
include_once $g4['full_path'] . '/lib/nfo.php';

$nfo = new nfo();

$mfg_list = $nfo->get_mfg_list();

$search = array();
if ($_GET['sh_upc']) {
    $search['upc'] = $_GET['sh_upc'];
}
if ($_GET['sh_it_id']) {
    $search['it_id'] = $_GET['sh_it_id'];
}
if ($_GET['mfgcd']) {
    $search['mfgcd'] = $_GET['mfgcd'];
}

if ($_GET['item_name']) {
    $search['item_name'] = $_GET['item_name'];
}

if ($_GET['insert_fg'] && is_array($_GET['insert_fg'])) {
    $search['insert_fg'] = $_GET['insert_fg'];
}

if($_GET['discontinued_fg']){
    $search['discontinued_fg'] = 1;
}
if($_GET['temp_fg']){
    $search['temp_fg'] = 1;
}

$search['rows'] = 20;
if ((int)$_GET['rows']) {
    $search['rows'] = (int)$_GET['rows'];
}

$search['page'] = 1;
if ((int)$_GET['page']) {
    $search['page'] = (int)$_GET['page'];
}

$item_list_data = $nfo->get_item_list($search);
$item_list = $item_list_data['data'];
$total_count = $item_list_data['count'];

$total_page = ceil($total_count / $search['rows']);

$page_qstr = $_GET;
unset($page_qstr['page']);
$page_qstr = http_build_query($page_qstr);

$st_page = 1;
$page_prev_block = 1;
if ($search['page'] > 10) {
    $st_page = $search['page'] - 5;
    $page_prev_block = $st_page - 5;
}
$en_page = $st_page + 10;

if ($en_page >= $total_page) {
    $en_page = $total_page;
}
$page_next_block = $en_page + 5;
if ($page_next_block >= $total_page) {
    $page_next_block = $total_page;
}


$edit_qstr = $_GET;
unset($edit_qstr['it_id'],$edit_qstr['upc']);
$edit_qstr = http_build_query($edit_qstr);


define('bootstrap', true);
$g4['title'] = "NFO 상품관리 리스트";
include_once $g4['full_path'] . '/head.sub.php';
?>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <form class="container-fluid" method="get" action="<?php echo $_SERVER['PHP_SELF']?>">
        <div class="col-lg-2 row">
            <div data-spy="affix" data-offset-top="60">
                <div class="panel mfg_list_wrap">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-lg-8">
                                <input type="text" class="mfg_name_search form-control" name="sh_mfg" value="<?php echo $_GET['sh_mfg']?>" >
                            </div>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-primary" onclick="mfg_search();">Search</button>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <table class="table">
                            <thead>
                            <tr>
                                <th><input type="checkbox" class="mfg_chk_all" checked onclick="mfg_chk_all();"/></th>
                                <th>CODE</th>
                                <th>MFG NAME</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($mfg_list as $row) { ?>
                                <tr class="mfg_list_row">
                                    <td>
                                        <input type="checkbox" name="mfgcd[]"
                                               value="<?php echo $row['mfgcd']; ?>" <?php echo is_array($_GET['mfgcd']) && in_array($row['mfgcd'], $_GET['mfgcd']) ? 'checked' : '' ?>
                                               onclick="mfg_checked();">
                                    </td>
                                    <td><?php echo $row['mfgcd']; ?></td>
                                    <td class="mfg_list_row_name"><?php echo ucfirst(strtolower($row['mfgname'])); ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-10">
            <div class="panel">
                <div class="panel-heading">
                    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="get">
                        <div class="row col-lg-12">
                            <p>
                                Selected Brand
                            </p>
                            <div class="selected_brand hero-unit"></div>
                        </div>
                        <div class="row col-lg-12">
                            <div class="col-lg-4">
                                <label>UPC(엔터로 구분)</label>
                                <textarea name="sh_upc" cols="30" rows="5" class="form-control"><?php echo $_GET['sh_upc'] ?></textarea>
                            </div>
                            <div class="col-lg-4">
                                <label>IT_ID(엔터로 구분)</label>
                                <textarea name="sh_it_id" cols="30" rows="5" class="form-control"><?php echo $_GET['sh_it_id'] ?></textarea>
                            </div>
                            <div class="col-lg-4">
                                <div>
                                    <label>ITEM NAME</label>
                                    <input type="text" class="form-control" name="item_name" value="<?php echo $_GET['item_name'] ?>">
                                </div>
                                <div class="form-inline">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="insert_fg[]" value="N" <?php echo is_array($_GET['insert_fg']) && in_array('N',$_GET['insert_fg']) ? 'checked':'';?>>
                                            미등록 상품
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="insert_fg[]" value="Y" <?php echo is_array($_GET['insert_fg']) && in_array('Y',$_GET['insert_fg']) ? 'checked':'';?>>
                                            등록 상품
                                        </label>
                                    </div>

                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="discontinued_fg" value="1" <?php echo $_GET['discontinued_fg'] == 1 ? 'checked':'';?>>
                                            단종상품 제외
                                        </label>
                                    </div>

                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="temp_fg" value="1" <?php echo $_GET['temp_fg'] == 1 ? 'checked':'';?>>
                                            임시 등록 상품만 보기
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Search</button>
                                <div class="clearfix"></div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </form>

                </div>
                <div class="panel-body">
                    <div class="row">총 <?php echo number_format($total_count);?>건</div>
                    <table class="table table-bordered table-hover table-condensed">
                        <thead>
                        <tr>
                            <th>UPC/<br/>ITEM NAME/<br/>Location/<br/>QTY</th>
                            <th></th>
                            <th>IT_ID</th>
                            <th>오플 판매상태</th>
                            <th>평균 입고가</th>
                            <th>오플 판매가</th>
                            <th>오플 상품명</th>
                            <th>카테고리</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($item_list as $no => $row) {
                            $no++;
                            $tr_class = '';
                            if($no%2 == 0){
                                $tr_class = 'active';
                            }

                            $add_tr = '';
                            $img = '';
                            $image = $nfo->get_item_image($row['upc'], $row['it_id']);

                            if ($image['ople']) {
                                $img = "<img src='" . $image['ople'] . "' onerror=\"this.src='http://ople.com/mall5/shop/img/no_image.gif'\" width='70'>";
                            }

                            $it = $nfo->get_ople_item_info($row['it_id']);


                            $it_id_cnt = 0;
                            $ople_status = '';

                            if($it) {
                                if ($it['it_stock_qty'] < 1) {
                                    $ople_status .= ($ople_status ? '<br/>' : '') . '<strong>품절</strong>';
                                }
                                if ($it['it_discontinued']) {
                                    $ople_status .= ($ople_status ? '<br/>' : '') . '<strong>단종</strong>';
                                }
                                if (!$it['it_use']) {
                                    $ople_status .= ($ople_status ? '<br/>' : '') . '<strong>판매중단</strong>';
                                }
                                if ($ople_status == '') {
                                    $ople_status = '판매중';
                                }
                            }

                            if($row['it_id'] && strpos($row['it_id'],',') !== false){
                                $it_id_arr = explode(',',$row['it_id']);
                                if($it_id_arr>1){
                                    $row['it_id'] = '';
                                    foreach ($it_id_arr as $it_id_key => $it_id_row) {
                                        $it_id_row = trim($it_id_row);
                                        if(!$nfo->chk_ople_it_id($it_id_row)){
                                            continue;
                                        }


                                        if($it_id_cnt > 0){
                                            $row_img_data = $nfo->get_item_image(null, $it_id_row);

                                            $row_it = $nfo->get_ople_item_info($it_id_row);
                                            $row_img = '';

                                            if ($row_img_data['ople']) {
                                                $row_img = "<img src='" . trim($row_img_data['ople']) . "' onerror=\"this.src='http://ople.com/mall5/shop/img/no_image.gif'\" width='70'>";
                                            }
                                            $row_category = $nfo->get_item_category_data($it['it_id']);
                                            $row_ca_data = '';
                                            if ($row_category) {
                                                $row_ca_data = implode('<br/>', $row_category);
                                            }

                                            $row_ople_status = '';

                                            if($row_it['it_stock_qty'] < 1){
                                                $row_ople_status .= $row_ople_status ? '<br/>':''. '<strong>품절</strong>';
                                            }
                                            if($row_it['it_discontinued']){
                                                $row_ople_status .= $row_ople_status ? '<br/>':''. '<strong>단종</strong>';
                                            }
                                            if(!$row_it['it_use']){
                                                $row_ople_status .= $row_ople_status ? '<br/>':''. '<strong>판매중단</strong>';
                                            }
                                            if($row_ople_status == ''){
                                                $row_ople_status = '판매중';
                                            }

                                            $add_tr .= "
                                        <tr class='{$tr_class}'>
                                            <td style=\"vertical-align: middle;\">{$row_img}</td>
                                            <td style=\"vertical-align: middle;\"><a href='http://ople.com/mall5/shop/item.php?it_id=" . $it_id_row . "' target='_blank'>" . $it_id_row . "</a></td>
                                            <td style=\"vertical-align: middle;\">{$row_ople_status}</td>
                                            <td style=\"vertical-align: middle;\">$ ".number_format($row_it['it_amount_usd'],2)."</td>
                                            <td style=\"vertical-align: middle;\">".get_item_name($row_it['it_name'],'list')."</td>
                                            <td style=\"vertical-align: middle;\">{$row_ca_data}</td>
                                            <td style=\"vertical-align: middle;\" style=\"vertical-align: middle;\"><a href=\"nfo_detail.php?".$edit_qstr."&upc=".trim($row['upc'])."&it_id={$ori_it_id}\" class=\"btn btn-info\">수정</a></td>
                                        </tr>
                                        ";
                                        }else{
                                            $it = $nfo->get_ople_item_info($it_id_row);

                                            $row_img_data = $nfo->get_item_image(null, $it_id_row);

                                            if ($row_img_data['ople']) {
                                                $img = "<img src='" . trim($row_img_data['ople']) . "' onerror=\"this.src='http://ople.com/mall5/shop/img/no_image.gif'\" width='70'>";
                                            }

                                            $ori_it_id = $it_id_row;
                                            $row['it_id'] = ($row['it_id'] ? '<br/>':'')."<a href='http://ople.com/mall5/shop/item.php?it_id=" . $it_id_row . "' target='_blank'>" . $it_id_row . '</a>';
                                            $category = $nfo->get_item_category_data($it['it_id']);
                                            $ca_data = '';
                                            if ($category) {
                                                $ca_data = implode('<br/>', $category);
                                            }

                                            /**
                                             * 최초 상품은 노출이 안되서 추가
                                             */
                                            if($it) {
                                                if ($it['it_stock_qty'] < 1) {
                                                    $ople_status .= ($ople_status ? '<br/>' : '') . '<strong>품절</strong>';
                                                }
                                                if ($it['it_discontinued']) {
                                                    $ople_status .= ($ople_status ? '<br/>' : '') . '<strong>단종</strong>';
                                                }
                                                if (!$it['it_use']) {
                                                    $ople_status .= ($ople_status ? '<br/>' : '') . '<strong>판매중단</strong>';
                                                }
                                                if ($ople_status == '') {
                                                    $ople_status = '판매중';
                                                }
                                            }
                                        }

                                        $it_id_cnt++;
                                    }
                                }
                            }elseif($row['it_id']){
                                $category = $nfo->get_item_category_data($it['it_id']);
                                $ca_data = '';
                                if ($category) {
                                    $ca_data = implode('<br/>', $category);
                                }


                                $ori_it_id = $row['it_id'];
                                $row['it_id'] = "<a href='http://ople.com/mall5/shop/item.php?it_id=" . $row['it_id'] . "' target='_blank'>" . $row['it_id'] . '</a>';
                                $it_id_cnt++;
                            }else{
                                $ca_data = '';
                                $row['it_id'] = 'X';
                            }

                            if($row['temp_cnt'] > 0){
                                $tr_class .= ' warning';
                            }

                            ?>
                            <tr class="<?php echo $tr_class;?>">
                                <td rowspan="<?php echo $it_id_cnt > 0 ? $it_id_cnt : 1;?>" style="vertical-align: middle;">
                                    UPC<br/><strong><?php echo $row['upc'] ?></strong>
                                    <br/><br/>
                                    ITEM NAME<br/><?php echo $row['item_name'] ?>
                                    <br/><br/>
                                    LOCATION<br/><?php echo $row['location'] ?>
                                    <br/><br/>
                                    QTY<br/><strong><?php echo number_format($row['currentqty']) ?></strong>
                                </td>
                                <td style="vertical-align: middle;"><?php echo $img; ?> </td>
                                <td style="vertical-align: middle;">
                                    <?php
                                    echo $row['it_id'];
                                    echo $row['temp_cnt'] > 0 ? '<br/><strong>가등록 데이터가 존재합니다.</strong>':'';
                                    ?>
                                </td>
                                <td style="vertical-align: middle;"><?php echo $ople_status;?></td>
                                <td rowspan="<?php echo $it_id_cnt > 0 ? $it_id_cnt : 1;?>" style="vertical-align: middle;">$ <?php echo number_format($row['wholesale_price'], 2); ?></td>
                                <td style="vertical-align: middle;">$ <?php echo number_format($it['it_amount_usd'], 2) ?></td>
                                <td style="vertical-align: middle;"><?php echo get_item_name($it['it_name'], 'list') ?></td>
                                <td style="vertical-align: middle;"><?php echo $ca_data; ?></td>
                                <td style="vertical-align: middle;"><a href="nfo_detail.php?<?php echo $edit_qstr;?>&upc=<?php echo urlencode(trim($row['upc'])); ?><?php echo $ori_it_id ? '&it_id='.urlencode($ori_it_id):'';?>" class="btn btn-info">수정</a> </td>
                            </tr>
                        <?php
                            echo $add_tr;
                        } ?>
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer text-center">
                    <nav>
                        <ul class="pagination">
                            <li>
                                <a href="<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $page_qstr; ?>&page=1"
                                   aria-label="Previous">
                                    <span aria-hidden="true">&lt;&lt;</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $page_qstr; ?>&page=<?php echo $page_prev_block; ?>"
                                   aria-label="Previous">
                                    <span aria-hidden="true">&lt;</span>
                                </a>
                            </li>
                            <?php for ($p = $st_page; $p <= $en_page; $p++) : ?>
                                <li<?php echo $p == $search['page'] ? " class='active'" : '' ?>><a
                                        href="<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $page_qstr; ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li>
                                <a href="<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $page_qstr; ?>&page=<?php echo $page_next_block; ?>"
                                   aria-label="Previous">
                                    <span aria-hidden="true">&gt;</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $page_qstr; ?>&page=<?php echo $total_page; ?>"
                                   aria-label="Previous">
                                    <span aria-hidden="true">&gt;&gt;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>


    </form>

    <style>
        .mfg_list_wrap {
            overflow: scroll;
            /*display: none;*/
        }

        table.table tr > th {
            font-size: 11px;
        }
        .selected_brand > .label{
            margin:3px;
            display: inline-block;
        }
    </style>

    <script>
        $(function () {
            resize();
            mfg_search();
            mfg_checked();
            $(window).resize(function () {
                resize();
            });

            $('.mfg_name_search').keypress(function(key){
                if(key.keyCode == 13){
                    mfg_search();
                    return false;
                }

            });
        });

        function resize() {
            $('.mfg_list_wrap').height($(window).height() - 20);
        }
        function mfg_chk_all() {
            if ($('.mfg_chk_all').prop('checked') == true) {
                $('input[name=mfgcd\\[\\]]').prop('checked', false);
                $('input[name=mfgcd\\[\\]]:visible').prop('checked', true);
            } else {
                $('input[name=mfgcd\\[\\]]').prop('checked', false);
            }
            mfg_checked();

        }

        function mfg_search() {
            var key = initCap($('.mfg_name_search').val().trim());
            if (key == '') {
                $('.mfg_list_row').show();
            } else {
                $('.mfg_list_row').hide();
                $('.mfg_list_row_name:contains(' + key + ')').parent().show();
            }
        }



        function initCap(str) {
            return str.substring(0, 1).toUpperCase() + str.substring(1, str.length).toLowerCase();
        }

        function mfg_checked() {
            var selected_mfg_html = '';
            if ($('.mfg_list_wrap .mfg_list_row input:checkbox[name=mfgcd\\[\\]]:visible:not(:checked)').length > 0) {
                $('.mfg_chk_all').prop('checked', false);
            }

            if ($('.mfg_list_wrap .mfg_list_row input:checkbox[name=mfgcd\\[\\]]:visible:checked').length == $('.mfg_list_wrap .mfg_list_row input:checkbox[name=mfgcd\\[\\]]').length) {
                selected_mfg_html += '<span class="label label-info">ALL <span class="glyphicon glyphicon-remove mfg_remove_btn"></span></span>';
                $('.mfg_chk_all').prop('checked', true);
            }else{
                $('.mfg_list_wrap .mfg_list_row input:checkbox[name=mfgcd\\[\\]]:visible:checked').each(function () {
                    selected_mfg_html += (selected_mfg_html != '' ? '&nbsp;' : '') + '<span class="label label-info">' +initCap($(this).parent().parent().find('.mfg_list_row_name').text().trim()) + '<span class="glyphicon glyphicon-remove mfg_remove_btn" onclick="uncheck_mfg(\''+$(this).val()+'\')"></span></span>';
                });
            }

            $('.selected_brand').empty().append(selected_mfg_html);
        }

        function uncheck_mfg(mfgcd){
            if(!mfgcd){
                return null;
            }
            $('.mfg_list_wrap .mfg_list_row input:checkbox[name=mfgcd\\[\\]][value='+mfgcd+']').prop('checked',false);
            mfg_checked();

        }
    </script>
<?php include_once $g4['full_path'] . '/tail.sub.php';