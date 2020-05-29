<?php
/**
 * Created by PhpStorm.
 * File name : nfo_detail.php.
 * Comment :
 * Date: 2016-01-06
 * User: Minki Hong
 */

$sub_menu = "300120";
include_once "./_common.php";

if(!$_GET['upc']){
    alert('잘못된 접근입니다.');
}

include_once $g4['full_path'] . '/lib/nfo.php';

$nfo = new nfo();

$ntics_info = $nfo->get_ntics_item_info($_GET['upc']);
if(!$ntics_info){
    alert('존재하지 않는 상품 입니다.');
}
$it = false;
$it_name = array(
    'brand_eng' => '',
    'brand_kor' => '',
    'name_kor' => '',
    'name_eng' => '',
    'name_comment' => '',
);

$ca_arr = array();
$it_id_link = '';
if ($ntics_info['it_id']) {
    if ($_GET['it_id']) {
        if (strpos($ntics_info['it_id'], $_GET['it_id']) === false) {
            alert('잘못된 접근입니다.');
            exit;
        }
        $it = $nfo->get_ople_item_info($_GET['it_id']);
        if($it['it_amount_usd'] == 0 && $it['it_amount'] > 0){
            $it['it_amount_usd'] = usd_convert($it['it_amount']);
        }
        $tmp_item_info = $nfo->get_tmp_item_info($it['it_id'],'it_id');
        $it_name['brand_eng'] = $it['it_name'];
        if (strpos($it['it_name'], '||') !== false) {
            $it_name_arr = explode('||', $it['it_name']);
            list($it_name['brand_eng'], $it_name['brand_kor']) = explode(']', $it_name_arr[0]);
        }
        $it_name['brand_eng'] = trim(preg_replace('/[\[\]]/', '', $it_name['brand_eng']));
        $it_name['brand_kor'] = trim(preg_replace('/[\[\]]/', '', $it_name['brand_kor']));
        $it_name['name_kor'] = trim($it_name_arr[1]);
        $it_name['name_eng'] = trim($it_name_arr[2]);
        $it_name['name_comment'] = trim($it_name_arr[3]);
        $ca_arr = $nfo->get_item_category_data($it['it_id'], false,true);

        $cps_it = sql_fetch("select use_yn, cps_ca_name,cps_ca_name2,cps_ca_name3,cps_ca_name4 from yc4_cps_item where it_id = '".$it['it_id']."'");


    }
    $it_id_arr = explode(',', $ntics_info['it_id']);
    foreach ($it_id_arr as $it_id_row) {
        $it_id_row = trim($it_id_row);

        if (!$it_id_row || !$nfo->chk_ople_it_id($it_id_row)) continue;

        if($_GET['it_id'] == $it_id_row){
            $it_id_link .= '<strong style=\'display:block;\'>';
        }
        $it_id_link .= "<a href=\"{$_SERVER['PHP_SELF']}?upc={$_GET['upc']}&it_id={$it_id_row}\">{$it_id_row}</a>";
        if($_GET['it_id'] == $it_id_row){
            $it_id_link .= '<a class="btn btn-info btn-sm" href="http://ople.com/mall5/shop/item.php?it_id='.$it_id_row.'" target="_blank">오플 상세페이지</a>';
            $it_id_link .= '</strong>';
        }
    }
}

$tmp_uid_arr = $nfo->get_tmp_item_uid($ntics_info['upc']);

if($_GET['uid']){
    if($nfo->chk_tmp_item_chk($_GET['uid'],$ntics_info['upc'])){
        $tmp_item_info = $nfo->get_tmp_item_info($_GET['uid'],'uid');
        $it_name['brand_eng'] = $tmp_item_info['it_maker_eng'];
        $it_name['brand_kor'] = $tmp_item_info['it_maker_kor'];
        $it_name['name_kor'] = $tmp_item_info['it_name_kor'];
        $it_name['name_eng'] = $tmp_item_info['it_name_eng'];
        $it_name['name_comment'] = $tmp_item_info['it_name_comment'];
        $it['it_amount_usd'] = $tmp_item_info['it_amount_usd'];
        $it['it_cust_amount_usd'] = $tmp_item_info['it_cust_amount_usd'];
        if($tmp_item_info['ca_id_arr']){
            $ca_arr = json_decode($tmp_item_info['ca_id_arr'],true);

        }
        if(!is_array($ca_arr)){
            $ca_arr = array();
        }

        if($tmp_item_info['img_url']){
            $img_attr = "src=\"{$g4['path']}/data/item_tmp/{$tmp_item_info['uid']}\"";
        }

        $it['it_explan'] = $tmp_item_info['it_explan'];
        $tmp_item_desc_info = array(
            'opledesc' => $tmp_item_info['desc_kor'],
            'opledirection' => $tmp_item_info['desc_direction'],
            'oplewarning' => $tmp_item_info['desc_warning'],
            'item_desc' => $tmp_item_info['desc_eng'],
            'servingpercontainer' => $tmp_item_info['desc_supp'],
            'ople_option' => $tmp_item_info['ople_option'],
        );

        $it['list_clearance'] = $tmp_item_info['list_clearance'] == 'Y' ? 'Y':'';
        $it['it_use'] = $tmp_item_info['it_use'] == '1' ? '1':'';
        $it['it_stock_qty'] = $tmp_item_info['soldout_fg'] == 'Y' ? '0':'99999';

        $it['it_health_cnt'] = $tmp_item_info['it_health_cnt'];
        $it['it_origin'] = $tmp_item_info['it_origin'];
        $it['it_order_onetime_limit_cnt'] = $tmp_item_info['it_order_onetime_limit_cnt'];
        $it['image_url'] = $tmp_item_info['img_url'];
    }
}

if(!$it_name['brand_eng']){
    $it_name['brand_eng'] = trim($ntics_info['mfgname']);
}

if(!$it_name['name_eng']){
    $it_name['name_eng'] = trim($ntics_info['item_name'])
        . (trim($ntics_info['potency']) ? ' '.trim($ntics_info['potency']):'')
        . (trim($ntics_info['unit']) ? ' '.trim($ntics_info['unit']):'')
        . (trim($ntics_info['count']) ? ' '.trim($ntics_info['count']):'')
        . (trim($ntics_info['type']) ? ' '.trim($ntics_info['type']):'')
    ;
}




$image = $nfo->get_item_image($_GET['upc'],$it['it_id']);
if(!$img_attr){
//    $img_attr = "src='".$image['ople']."' onerror=\"this.src='".$nfo->data_img($image['nfo'])."'; $('.img_caption').text('오플 이미지가 존재하지 않습니다.');\"";
    $img_attr = "src='".$image['ople']."' ";
}

// 상품설명 로드
$item_desc_info = $nfo->get_upc_item_desc($_GET['upc']);

if($tmp_item_info['desc_kor']){
    $item_desc_info['item_info']['opledesc'] = $tmp_item_info['desc_kor'];
}
if($tmp_item_info['desc_direction']){
    $item_desc_info['item_info']['opledirection'] = $tmp_item_info['desc_direction'];
}
if($tmp_item_info['desc_warning']){
    $item_desc_info['item_info']['oplewarning'] = $tmp_item_info['desc_warning'];
}
if($tmp_item_info['desc_eng']){
    $item_desc_info['item_info']['item_desc'] = $tmp_item_info['desc_eng'];
}
if($tmp_item_info['desc_supp']){
    $item_desc_info['item_info']['servingpercontainer'] = $tmp_item_info['desc_supp'];
}
if($tmp_item_info['ople_option']){
    $item_desc_info['item_info']['ople_option'] = $tmp_item_info['ople_option'];
}



$qstr = $_GET;
unset($qstr['it_id'],$qstr['uid']);
$qstr = http_build_query($qstr);

$list_page_qstr = $_GET;
unset($list_page_qstr['upc'],$list_page_qstr['uid'],$list_page_qstr['it_id']);
$list_page_qstr = http_build_query($list_page_qstr);

define('bootstrap', true);
$g4[title] = "NFO 상품관리 등록 및 수정";
include_once $g4['full_path'] . '/head.sub.php';
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo $g4['path'];?>/smart_editor/js/HuskyEZCreator.js" charset="utf-8"></script>

    <div class="page-header">
        <h1>OPLE ITEM MANAGER</h1>
    </div>

    <div class="col-lg-2">
        <div class="list-group">
            <div class="list-group-item">
                <h5 class="list-group-item-heading">UPC</h5>
                <p class="list-group-item-text"><?php echo $ntics_info['upc'];?></p>
            </div>
            <div class="list-group-item">
                <h5 class="list-group-item-heading">MFG NAME</h5>
                <p class="list-group-item-text"><?php echo $ntics_info['mfgname'];?></p>
            </div>
            <div class="list-group-item">
                <h5 class="list-group-item-heading">ITEM NAME</h5>
                <p class="list-group-item-text"><?php echo $ntics_info['item_name'];?></p>
            </div>
            <div class="list-group-item">
                <p class="list-group-item-text">POTENCY : <?php echo $ntics_info['potency'];?></p>
                <p class="list-group-item-text">UNIT : <?php echo $ntics_info['unit'];?></p>
                <p class="list-group-item-text">COUNT : <?php echo $ntics_info['count'];?></p>
                <p class="list-group-item-text">TYPE : <?php echo $ntics_info['type'];?></p>
                <p class="list-group-item-text">LOCATION : <?php echo $ntics_info['location'];?></p>
                <p class="list-group-item-text">CURRENTQTY : <?php echo $ntics_info['currentqty'] ? $ntics_info['currentqty']: '0';?></p>
            </div>

            <div class="list-group-item">
                <h5 class="list-group-item-heading"></h5>
                <p class="list-group-item-text">평균 입고가 : $ <?php echo number_format($ntics_info['wholesale_price'],2);?></p>
                <p class="list-group-item-text">MSRP : $ <?php echo number_format($ntics_info['msrp'],2);?></p>
                <p class="list-group-item-text">아이허브 판매가 : $ <?php echo number_format($ntics_info['iherb_amount'],2);?></p>
            </div>

            <div class="list-group-item">
                <h5 class="list-group-item-heading"></h5>
                <p class="list-group-item-text"><a class="btn btn-default btn-sm" href="http://kr.iherb.com/search?kw=<?php echo $ntics_info['upc'];?>" target="_blank">아이허브 상품 정보</a></p>
                <p class="list-group-item-text"><a class="btn btn-default btn-sm" href="http://www.amazon.com/s/ref=nb_sb_noss?url=search-alias%3Daps&field-keywords=<?php echo $ntics_info['upc'];?>" target="_blank">아마존 상품 정보</a></p>
                <p class="list-group-item-text"><a class="btn btn-default btn-sm" href="http://www.vitacost.com/productNoResults.aspx?Tnf=<?php echo $ntics_info['upc'];?>" target="_blank">비타코스트 상품 정보</a></p>
            </div>
        </div>
        <a href="nfo.php" class="btn btn-info">Back</a>

    </div>
    <div class="col-lg-10">
        <form action="nfo_update.php" method="post" enctype="multipart/form-data" onsubmit="return item_save_frm_chk(this);">
            <textarea name="it_explan" style="display:none;"></textarea>
            <textarea name="desc_supp" style="display:none;"></textarea>
            <input type="hidden" name="upc" value="<?php echo $ntics_info['upc'];?>">
            <input type="hidden" name="it_id" value="<?php echo $_GET['it_id'];?>">
            <input type="hidden" name="uid" value="<?php echo $_GET['uid'];?>">
            <input type="hidden" name="list_page_qstr" value="<?php echo $list_page_qstr;?>">

            <div class="list-group">
                <div class="list-group-item">
                    <h5 class="list-group-item-heading">오플 상품코드</h5>
                    <p class="list-group-item-text">
                        <?php
                        if(is_array($tmp_uid_arr)){
                            foreach ($tmp_uid_arr as $row) {
                                echo '<a href="'.$_SERVER['PHP_SELF'].'?'.$qstr.'&uid='.$row['uid'].'">임시등록상품(temp_id : '.$row['uid'].')</a><br/>';
                            }
                        }
                        echo $it_id_link ? $it_id_link : '미등록';
                        ?>
                        <a class="btn btn-primary" href="<?php echo $_SERVER['PHP_SELF']; ?>?upc=<?php echo $ntics_info['upc'];?>">신규등록</a>
                    </p>
                </div>
            </div>
            <div class="list-group">
                <div class="list-group-item">
                    <h5 class="list-group-item-heading">오플 제품명</h5>
                    <p class="list-group-item-text"><span class="brand_nm_eng"><?php echo $it_name['brand_eng'] ? '['.$it_name['brand_eng'].']':'';?></span> <span class="brand_nm_kor"><?php echo $it_name['brand_kor']?></span></p>
                    <p class="list-group-item-text item_name_kor"><?php echo $it_name['name_kor'];?></p>
                    <p class="list-group-item-text item_name_eng"><?php echo $it_name['name_eng'];?></p>
                    <p class="list-group-item-text item_name_comment"><?php echo $it_name['name_comment'];?></p>
                </div>
                <div class="list-group-item">
                    <div class="list-group-item-text">
                        <div class="form-group">
                            <label for="brand_eng">브랜드명(영문)</label>
                            <input type="text" class="form-control" name="brand_eng" value="<?php echo $it_name['brand_eng'];?>" onchange="item_name_merge();"/>
                        </div>
                    </div>
                    <div class="list-group-item-text">
                        <div class="form-group">
                            <label for="brand_eng">브랜드명(한글)</label>
                            <input type="text" class="form-control" name="brand_kor" value="<?php echo $it_name['brand_kor'];?>" onchange="item_name_merge();"/>
                        </div>
                    </div>
                    <div class="list-group-item-text">
                        <div class="form-group">
                            <label for="brand_eng">제품명(한글)</label>
                            <input type="text" class="form-control" name="name_kor" value="<?php echo $it_name['name_kor'];?>" onchange="item_name_merge();"/>
                        </div>
                    </div>
                    <div class="list-group-item-text">
                        <div class="form-group">
                            <label for="brand_eng">제품명(영문)</label>
                            <input type="text" class="form-control" name="name_eng" value="<?php echo $it_name['name_eng'];?>" onchange="item_name_merge();"/>
                        </div>
                    </div>
                    <div class="list-group-item-text">
                        <div class="form-group">
                            <label for="brand_eng">제품 코멘트(영문)</label>
                            <input type="text" class="form-control" name="name_comment" value="<?php echo $it_name['name_comment'];?>" onchange="item_name_merge();"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="list-group">
                <div class="list-group-item">
                    <h5 class="list-group-item-heading">가격 정보</h5>
                    <div class="list-group-item-text form-inline">
                        <div class="form-group">
                            <label for="it_amount_usd">판매가</label>
                            <div class="input-group">
                                <div class="input-group-addon">$</div>
                                <input type="text" class="form-control" name="it_amount_usd" value="<?php echo $it['it_amount_usd']?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="it_amount_usd">시중가</label>
                            <div class="input-group">
                                <div class="input-group-addon">$</div>
                                <input type="text" class="form-control" name="it_cust_amount_usd" value="<?php echo $it['it_cust_amount_usd']?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="list-group">
                <div class="list-group-item">
                    <h5 class="list-group-item-heading">CPS 사용여부</h5>
                    <div class="list-group-item-text form-inline">
                        <div class="form-group">
                            <label for="cps_ca_name">사용여부</label>
                            <div class="input-group">
                                <input type=checkbox name='cps_use_yn' <? echo ($cps_it[use_yn]=="y") ? "checked" : ""; ?> value='y'> 예
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cps_ca_name">카테고리1</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="cps_ca_name" value="<?php echo $cps_it['cps_ca_name']?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cps_ca_name2">카테고리2</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="cps_ca_name2" value="<?php echo $cps_it['cps_ca_name2']?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cps_ca_name3">카테고리3</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="cps_ca_name3" value="<?php echo $cps_it['cps_ca_name3']?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cps_ca_name4">카테고리4</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="cps_ca_name4" value="<?php echo $cps_it['cps_ca_name4']?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="list-group">
                <div class="list-group-item">
                    <h5 class="list-group-item-heading">카테고리 <button type="button" class="btn btn-default" onclick="ajax_category_tree_load();">추가</button></h5>
                    <div class="list-group-item-text">
                        <ul class="list-group ca_list_ul">
                        <?php
//                        print_r($ca_arr);
                        foreach ($ca_arr as $row_ca_id => $row_ca) {
                            /*if(!$row_ca_id){
                                continue;
                            }*/
                            ?>
                            <li class="list-group-item">
                                <input type="hidden" name="ca_id[]" value="<?php echo $row_ca;?>">
                                <?php echo $nfo->get_category_tree($row_ca,false);?>
                                <button type="button" class="btn btn-sm btn-danger" ca_id="<?php echo $row_ca;?>" onclick="category_del(this);">X</button>
                            </li>
                        <?php }?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="list-group">
                <div class="list-group-item">
                    <h5 class="list-group-item-heading">이미지</h5>
                    <div class="list-group-item-text">
                        <div class="row">
                            <div class="col-lg-3 thumbnail">
                                <img <?php echo $img_attr;?> class="img-thumbnail" />
                                <div class="caption img_caption">현재 이미지</div>
                            </div>
                            <div class="col-lg-9">
                                <label for="image_url">URL</label>
                                <input type="text" class="form-control" name="image_url" value="<?php echo $it['image_url']?>"/>
                                <!--<label for="image_url">FILE</label>
                                <input type="file" class="form-control" name="image_file"/>-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="list-group">
                <div class="list-group-item">
                    <h5 class="list-group-item-heading">상세설명</h5>
                    <div class="list-group-item-text">
                        <ul class="nav nav-pills desc_tab">
                            <li role="presentation" class="active"><a href="#" onclick="toggle_desc('desc_current',this); return false;">현재 설명</a></li>
                            <li role="presentation"><a href="#" onclick="toggle_desc('desc_preview',this); return false;">미리보기</a></li>
                            <li role="presentation"><a href="#" onclick="toggle_desc('desc_kor',this); return false;">한글설명</a></li>
                            <li role="presentation"><a href="#" onclick="toggle_desc('desc_direction',this); return false;">사용 OR 섭취방법</a></li>
                            <li role="presentation"><a href="#" onclick="toggle_desc('desc_warning',this); return false;">주의사항</a></li>
                            <li role="presentation"><a href="#" onclick="toggle_desc('desc_eng',this); return false;">영문설명</a></li>
                            <li role="presentation"><a href="#" onclick="toggle_desc('desc_supp',this); return false;">성분표</a></li>
                        </ul>
                        <div class="item_desc_wrap">
                            <div class="desc_current" style="display: block;">
                                <?php echo $it['it_explan']?>
                            </div>
                            <div class="desc_preview">

                            </div>
                            <div class="desc_kor">
                                <textarea name="desc_kor" id="desc_kor" style="visibility: hidden;"><?php echo $item_desc_info['item_info']['opledesc'];?></textarea>
                            </div>
                            <div class="desc_direction">
                                <div class="checkbox">
                                    <label>
                                        섭취방법
                                        <input type="radio" name="ople_option" value="1" <?php echo $item_desc_info['item_info']['ople_option'] == 1 ? 'checked':'';?>/>
                                    </label>
                                    <label>
                                        사용방법
                                        <input type="radio" name="ople_option" value="2" <?php echo $item_desc_info['item_info']['ople_option'] == 2 ? 'checked':'';?>/>
                                    </label>
                                </div>



                                <textarea name="desc_direction" id="desc_direction" style="visibility: hidden;"><?php echo $item_desc_info['item_info']['opledirection'];?></textarea>
                            </div>
                            <div class="desc_warning">
                                <textarea name="desc_warning" id="desc_warning" style="visibility: hidden;"><?php echo $item_desc_info['item_info']['oplewarning'];?></textarea>
                            </div>
                            <div class="desc_eng">
                                <textarea name="desc_eng" id="desc_eng" style="visibility: hidden;"><?php echo $item_desc_info['item_desc']['item_desc'];?> <?php echo $item_desc_info['item_desc']['item_warning'];?> <?php echo $item_desc_info['item_desc']['item_usage'];?> <?php echo $item_desc_info['item_desc']['item_other'];?></textarea>
                            </div>
                            <div class="desc_supp">
                                <?php if($item_desc_info['item_desc']['servingsize'] || $item_desc_info['item_desc']['servingpercontainer']) :?>
                                <table class="SupplementFacts" cellspacing="0" cellpadding="0">
                                    <colgroup>
                                        <col>
                                        <col width="170">
                                        <col width="130">
                                    </colgroup>
                                    <?php if($item_desc_info['item_desc']['servingsize'] || $item_desc_info['item_desc']['servingpercontainer'] || (is_array($item_desc_info['supplementfacts']) && count($item_desc_info['supplementfacts']) > 0)) :?>
                                    <thead>
                                        <tr>
                                            <th style="border: 1px dotted rgb(216, 216, 215); border-image: none" colspan="3">Serving Size :<strong> <?php echo $item_desc_info['item_desc']['servingsize']?></strong></th>
                                        </tr>
                                        <tr class="size">
                                            <th style="border: 1px dotted rgb(216, 216, 215); border-image: none" colspan="3">serving for container :<strong><?php echo $item_desc_info['item_desc']['servingpercontainer']?></strong></th>
                                        </tr>
                                        <tr>
                                            <th style="border: 1px dotted rgb(216, 216, 215); border-image: none"></th>
                                            <th style="border: 1px dotted rgb(216, 216, 215); border-image: none">Amount Per Serving</th>
                                            <th style="border: 1px dotted rgb(216, 216, 215); border-image: none">% Daily Value</th>
                                        </tr>
                                    </thead>
                                    <?php endif;?>
                                    <?php if (is_array($item_desc_info['supplementfacts']) && count($item_desc_info['supplementfacts']) > 0) : ?>
                                    <tbody>
                                        <?php foreach ($item_desc_info['supplementfacts'] as $row) : ?>
                                            <tr>
                                                <td style="border: 1px dotted rgb(216, 216, 215); border-image: none">
                                                    <?php echo $row['attname'];?>
                                                </td>
                                                <td style="border: 1px dotted rgb(216, 216, 215); border-image: none">
                                                    <?php echo $row['attvalue']?>
                                                </td>
                                                <td style="border: 1px dotted rgb(216, 216, 215); border-image: none">
                                                    <?php echo $row['attdv']?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <?php endif; ?>
                                </table>
                                <?php endif;?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="list-group">
                <div class="list-group-item">
                    <h5 class="list-group-item-heading">기타</h5>
                    <div class="list-group-item-text form-inline">
                        <div class="form-group">
                            <label for="it_amount_usd">건기식 병수</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="it_health_cnt" value="<?php echo $it['it_health_cnt'];?>">
                                <div class="input-group-addon">병</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="it_amount_usd">원산지</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="it_origin" value="<?php echo $it['it_origin']?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="it_amount_usd">1회 구매 제한 수량</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="it_order_onetime_limit_cnt" value="<?php echo $it['it_order_onetime_limit_cnt']?>">
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item-text">
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" value="Y" name="list_clearance" <?php echo $it['list_clearance'] == 'Y' ? 'checked':''; ?>> 목록통관
                                </label>
                                <label>
                                    <input type="checkbox" value="1" name="it_use" <?php echo $it['it_use'] == '1' || !$it ? 'checked':''; ?>> 판매
                                </label>
                                <label>
                                    <input type="checkbox" value="Y" name="soldout_fg" <?php echo $it['it_stock_qty'] < 1 ? 'checked':''; ?>> 품절
                                </label>
                                <?php if(!$it['it_id']){?>
                                <label>
                                    <input type="checkbox" value="Y" name="direct_fg"> 바로등록
                                </label>
                                <?php }?>
                            </div>
                        </div>
                    </div>
                    <ul class="list-group noti_wrap">

                    </ul>
                </div>
            </div>

            <div class="list-group">
                <div class="list-group-item">
                    <h5 class="list-group-item-heading text-center">
                        <button type="submit" class="btn btn-primary">저장</button>
                        <a class="btn btn-info" href="nfo.php?<?php echo $list_page_qstr;?>">Back</a>
                    </h5>
                </div>
            </div>
            <div class="well">
                이미지 등록시 반영되는 시간은 30분 입니다.<br/>
                관리자 페이지 매핑 정보는 3시간 마다 동기화 됩니다.(최대 3시간 소요)
            </div>



        </form>
    </div>

    <div class="modal fade category_layer">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Modal title</h4>
                </div>
                <div class="modal-body">

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="add_category();">선택</button>
                </div>

            </div>
        </div>
    </div>



    <style>
        .item_desc_wrap > div{
            overflow:hidden;
            /*display:none;*/
        }
        .item_desc_wrap textarea{
            width:795px;
            visibility: hidden;
            /*min-width: 100%;;*/
        }
    </style>

    <script>

        function desc_chk(){
            var desc_direction = strip_tag($('#desc_direction').val());
            var desc_warning = strip_tag($('#desc_warning').val());
            var desc_kor = strip_tag($('#desc_kor').val());

            var brand_eng = $('input[name=brand_eng]').val();
            var brand_kor = $('input[name=brand_kor]').val();
            var name_kor = $('input[name=name_kor]').val();
            var name_eng = $('input[name=name_eng]').val();
            var name_comment = $('input[name=name_comment]').val();

            $('.noti_wrap').empty();
            if(desc_direction == ''){
                $('.noti_wrap').append('<li class="list-group-item list-group-item-danger">사용 OR 섭취방법이 입력되지 않았습니다.</li>');
            }
            if(desc_warning == ''){
                $('.noti_wrap').append('<li class="list-group-item list-group-item-danger">주의사항이 입력되지 않았습니다.</li>');
            }
            if(desc_kor == ''){
                $('.noti_wrap').append('<li class="list-group-item list-group-item-danger">한글 설명이 입력되지 않았습니다.</li>');
            }

            if(brand_eng == ''){
                $('.noti_wrap').append('<li class="list-group-item list-group-item-danger">영문 브랜드명이 입력되지 않았습니다.</li>');
            }
            if(brand_kor == ''){
                $('.noti_wrap').append('<li class="list-group-item list-group-item-danger">한글 브랜드명이 입력되지 않았습니다.</li>');
            }
            if(name_kor == ''){
                $('.noti_wrap').append('<li class="list-group-item list-group-item-danger">한글 상품명이 입력되지 않았습니다.</li>');
            }
            if(name_eng == ''){
                $('.noti_wrap').append('<li class="list-group-item list-group-item-danger">영문 상품명이 입력되지 않았습니다.</li>');
            }
            if(name_comment == ''){
                $('.noti_wrap').append('<li class="list-group-item list-group-item-warning">상품명 코멘트가 입력되지 않았습니다.</li>');
            }
        }

        function item_name_merge(){
            var brand_eng = $('input[name=brand_eng]').val();
            var brand_kor = $('input[name=brand_kor]').val();
            var name_kor = $('input[name=name_kor]').val();
            var name_eng = $('input[name=name_eng]').val();
            var name_comment = $('input[name=name_comment]').val();

            $('.brand_nm_eng').text('['+brand_eng+']');
            $('.brand_nm_kor').text(brand_kor);
            $('.item_name_kor').text(name_kor);
            $('.item_name_eng').text(name_eng);
            $('.item_name_comment').text(name_comment);

            merge_desc();// 상품 설명에 반영

        }

        function toggle_desc(selector, obj){
            $('.item_desc_wrap > div').hide();
            $('.'+selector).show();
            $('.desc_tab > li').removeClass('active');
            $(obj).parent().addClass('active');

            merge_desc();
            return null;
        }
        var oEditors = [];
        var editor_load_cnt = 0;
        $(function(){
            nhn.husky.EZCreator.createInIFrame({
                oAppRef: oEditors,
                elPlaceHolder: "desc_kor",
                sSkinURI: "<?php echo $g4['path'];?>/smart_editor/SmartEditor2Skin.html",
                htParams : {
                    bUseToolbar : true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
                    bUseVerticalResizer : true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
                    bUseModeChanger : true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
                    //aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
                    fOnBeforeUnload : function(){
                        //alert("완료!");
                    }
                }, //boolean
                fOnAppLoad : function(){
                    //예제 코드
                    //oEditors.getById["ir1"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
                    editor_load_cnt++;
                    editor_load_complete();
                },
                fCreator: "createSEditor2"
            });

            nhn.husky.EZCreator.createInIFrame({
                oAppRef: oEditors,
                elPlaceHolder: "desc_direction",
                sSkinURI: "<?php echo $g4['path'];?>/smart_editor/SmartEditor2Skin.html",
                htParams : {
                    bUseToolbar : true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
                    bUseVerticalResizer : true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
                    bUseModeChanger : true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
                    //aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
                    fOnBeforeUnload : function(){
                        //alert("완료!");
                    }
                }, //boolean
                fOnAppLoad : function(){
                    //예제 코드
                    //oEditors.getById["ir1"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
                    editor_load_cnt++;
                    editor_load_complete();
                },
                fCreator: "createSEditor2"
            });
            nhn.husky.EZCreator.createInIFrame({
                oAppRef: oEditors,
                elPlaceHolder: "desc_warning",
                sSkinURI: "<?php echo $g4['path'];?>/smart_editor/SmartEditor2Skin.html",
                htParams : {
                    bUseToolbar : true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
                    bUseVerticalResizer : true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
                    bUseModeChanger : true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
                    //aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
                    fOnBeforeUnload : function(){
                        //alert("완료!");
                    }
                }, //boolean
                fOnAppLoad : function(){
                    //예제 코드
                    //oEditors.getById["ir1"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
                    editor_load_cnt++;
                    editor_load_complete();
                },
                fCreator: "createSEditor2"
            });
            nhn.husky.EZCreator.createInIFrame({
                oAppRef: oEditors,
                elPlaceHolder: "desc_eng",
                sSkinURI: "<?php echo $g4['path'];?>/smart_editor/SmartEditor2Skin.html",
                htParams : {
                    bUseToolbar : true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
                    bUseVerticalResizer : true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
                    bUseModeChanger : true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
                    //aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
                    fOnBeforeUnload : function(){
                        //alert("완료!");
                    }
                }, //boolean
                fOnAppLoad : function(){
                    //예제 코드
                    //oEditors.getById["ir1"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
                    editor_load_cnt++;
                    editor_load_complete();
                },
                fCreator: "createSEditor2"
            });

            desc_chk();

        });

        function merge_desc(){
            var result_html = '';
            var option_nm = '';
            if($('input[name=ople_option]:checked').val() == '1'){
                option_nm = '섭취방법';
            }else{
                option_nm = '사용방법';
            }
            result_html += '<div class="item_explanBOX" id="div_explan">';
            result_html += desc_row_convert('한글 제품명',$('input[name=name_kor]').val());
            result_html += desc_row_convert('영문 제품명',$('input[name=name_eng]').val());
            result_html += desc_row_convert('용량/수량','<?php echo $ntics_info['potency']?> <?php echo $ntics_info['unit']?> <?php echo $ntics_info['count']?> <?php echo $ntics_info['type']?>');
            result_html += desc_row_convert('제조사','['+$('input[name=brand_eng]').val()+'] ' + $('input[name=brand_kor]').val());
            if(strip_tag(oEditors.getById["desc_direction"].getIR()).trim() != ''){
                result_html += desc_row_convert(option_nm,oEditors.getById["desc_direction"].getIR());
            }
            if(strip_tag(oEditors.getById["desc_warning"].getIR()).trim() != ''){
                result_html += desc_row_convert('주의사항',oEditors.getById["desc_warning"].getIR());
            }
            if(strip_tag(oEditors.getById["desc_kor"].getIR()).trim() != ''){
                result_html += desc_row_convert('제품설명',oEditors.getById["desc_kor"].getIR());
            }
            if(strip_tag(oEditors.getById["desc_eng"].getIR()).trim() != ''){
                result_html += desc_row_convert('제품 영문 설명',oEditors.getById["desc_eng"].getIR());
            }
            if($('.desc_supp').html().trim() != '') {
                result_html += desc_row_convert('성분표(영문)', $('.desc_supp').html().trim());
            }
            result_html += '</div>';

            $('.desc_preview').html(result_html);
            $('textarea[name=it_explan]').val(result_html);
            $('textarea[name=desc_supp]').val($('.desc_supp').html().trim());

            for( var k in oEditors.getById ){
                oEditors.getById[k].exec('UPDATE_CONTENTS_FIELD',[]);
            }

            desc_chk();
        }

        function desc_row_convert(key,val){
            return '<div class="ex_box"><p class="box_title" style="margin:0;">'+key+'</p><div class="box_item_title"><p style="margin:0;">'+val.trim()+'</p></div></div>';
        }
        function editor_load_complete(){
            if(editor_load_cnt == 4){
                $('.desc_tab > li.active > a').click();
            }
        }

        function category_del(obj){
            $(obj).parent().remove();
        }

        function ajax_category_tree_load(){
            var selected_ca_id = [];
            $('.ca_list_ul > li > input[name=ca_id\\[\\]]').each(function(){
                selected_ca_id.push($(this).val());
            });
            $.ajax({
                'url' : 'nfo_detail_category_tree_ajax.php',
                'type' : 'post',
                'data_type' : 'html',
                'data' : {
                    it_id : '<?php echo $it['it_id']?>'
                },success : function( result ){
                    $('.category_layer .modal-content .modal-body').html(result);
                    $('.category_layer input.select_category:checkbox').each(function(){
                        var row = $(this);
                        selected_ca_id.forEach(function(ca_id){

                            if(ca_id == row.val()){
                                row.prop('checked',true);
                            }
                        })
                    });
                    $('.category_layer').modal();
                }
            });
        }

        function add_category(){
            $('.ca_list_ul').empty();
            $('.select_category:checked').each(function(){
                var ca_name = $(this).parent().text();
                var ca_id = $(this).val();
//                if($('.ca_list_ul > li > input[name=ca_id\\[\\]][value='+ca_id+']').length < 1){
                    $('.ca_list_ul').append('<li class="list-group-item"><input type="hidden" name="ca_id[]" value="'+ca_id+'"> '+ca_name.trim()+'<button type="button" class="btn btn-sm btn-danger" ca_id="'+ca_id+'" onclick="category_del(this);">X</button></li>');
//                }
            });
            $('.category_layer').modal('hide');
        }

        function item_save_frm_chk(f){
            merge_desc();
            return true;
        }

        function strip_tag (str)
        {
            return str.replace(/(<([^>]+)>)/ig,"").replace('&nbsp;','');
        }
    </script>

<?php include_once $g4['full_path'] . '/tail.sub.php';