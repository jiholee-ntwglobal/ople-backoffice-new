<?php
/**
 * Created by PhpStorm.
 * File name : nfo_detail_category_tree_ajax.php.
 * Comment :
 * Date: 2016-01-07
 * User: Minki Hong
 */

$sub_menu = "300120";
include_once "./_common.php";

include_once $g4['full_path'] . '/lib/nfo.php';

$nfo = new nfo();

$it_ca_id_arr = array();

if($_POST['it_id']){
    $it_category = $nfo->get_item_category_data($_POST['it_id']);
    if(is_array($it_category)){
        foreach ($it_category as $ca_id => $ca_name) {
            $it_ca_id_arr[] = $ca_id;
        }
    }
}

$cate_tree = $nfo->get_category_last_child();
$s_name_arr = array();
foreach ($cate_tree as $ca_id => $ca_name) : ?>
    <div class="checkbox">
        <label>
            <input type="checkbox" class="select_category" value="<?php echo $ca_id;?>"> <?php echo $ca_name;?>
        </label>
    </div>
<?php endforeach;
