<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-04-15
 * Time: 오후 3:35
 */

include "_common.php";


if($_POST['mode'] == 'update'){

    if(hanacard_event_item_choice($_POST)){
        alert('사은품 선택이 완료되었습니다.',$g4['shop_path'].'/settleresult.php?on_uid='.$_POST['on_uid']);
        exit;
    }
    alert('저장중 오류 발생! 고객센터로 문의하여 주세요.');
    exit;

}



/*
$_POST['od_id'] = '';
$sql = sql_query("
    select value3 as it_id from yc4_event_data where ev_code = 'hana' and ev_data_type = 'event_item'
");
$data = array();
while($row = sql_fetch_array($sql)){
    $data[] = $row;
}
*/


$data = hanacard_event_item_chk($_GET['od_id']);



if(!$data){
    goto_url($g4['shop_path'].'/settleresult.php?on_uid='.$_GET['on_uid']);
    exit;
}

$list_li = '';
$checked = false;
if(count($data) > 0){
    $checked = true;
}
foreach($data as $val){

    $it = sql_fetch("select it_id,it_name,it_amount from ".$g4['yc4_item_table']." where it_id = '".$val['it_id']."'");

    $it_name = get_item_name($it['it_name'],'list');

    $it_img = get_it_image($val['it_id'].'_s',200,200,null,null,null,false,false,true);

    $list_li .= "
         <li class='item_box''>
            <p class='item_image'>".$it_img."</p>
            <p class='item_title'>
                ".$it_name."
            </p>
            <p class='secelt_Area'><input type='radio' name='it_id' value='".$val['it_id']."' required ".($checked ? "checked":"")."></p>
        </li>
    ";

}

include_once "_head.php";
?>
<div class="event_prouct_select">
    <p class="event_title_s"><img src="http://115.68.20.84/event/tit_hanacard_event.png" alt="하나카드이벤트 증정상품 선택"></p>
    <div class="event_box_s">
        <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" onsubmit="return hanacard_event_item_choice_chk(this);">
            <input type="hidden" name="od_id" value="<?php echo $_GET['od_id'];?>"/>
            <input type="hidden" name="on_uid" value="<?php echo $_GET['on_uid'];?>"/>
            <input type="hidden" name="mb_id" value="<?php echo $member['mb_id'];?>"/>
            <input type="hidden" name="mode" value="update"/>
            <p><img src="http://115.68.20.84/event/select_box_top.jpg"> </p>
            <fieldset class="event_prouductArea">
                <ul>
                    <?php echo $list_li;?>
                </ul>
                <p class="select_button">
                    <input type="image" src="http://115.68.20.84/event/button_select_comfirm.jpg" alt="상품선택완료"/>
                </p>
            </fieldset>
        </form>
    </div>
    <!--참고설명-->
    <p class="event_attend"><img src="http://115.68.20.84/event/txt_attend.jpg" alt="참고설명"></p>
</div>

<script>
    function hanacard_event_item_choice_chk(f){
        if($(f).find('input[name=it_id]:checked').length < 1){
            alert('상품을 선택해 주세요');
            return false;
        }


        return true;
    }
</script>
<?php
include_once "_tail.php";
?>