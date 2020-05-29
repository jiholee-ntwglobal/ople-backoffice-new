<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-03-31
 * Time: 오후 2:14
 */


$sub_menu = "600500";
include "_common.php";
auth_check($auth[$sub_menu], "w");

if($_POST['mode'] == 'ajax'){

    $json_arr = array();

    if($_POST['tp'] == 'I'){
        $data = sql_fetch("select it_name,it_amount,it_amount_usd,it_maker,it_maker_kor from ".$g4['yc4_item_table']." where it_id='$_POST[type_value]'");

        if($data['it_amount_usd']=='') $data['it_amount_usd'] = usd_convert($data['it_amount']);
        $data['it_amount'] = number_format($data['it_amount']);

        if(strpos($data['it_name'],'||') !== false){
            $it_name_arr =  explode('||',$data['it_name']);
            $data['korname'] = $it_name_arr[1];
            $data['engname'] = $it_name_arr[2];
            $data['brandname'] = $it_name_arr[0];

        } else {
            $data['korname'] = $data['engname'] = $data['brandname'] = '';
        }



        echo json_encode($data);
        exit;

    }else{
        $data = sql_fetch("select concat('[',it_maker,'] ',it_maker_kor) as brandname from ".$g4['yc4_item_table']." where it_maker = '".sql_safe_query($_POST['type_value'])."'");
        echo json_encode($data);
        exit;
    }




    exit;

} else if($_POST['mode'] == 'insert'){

    if($_POST['use_fg'] == '') $_POST['use_fg'] = 'N';

    sql_query("
      insert into yc4_item_new (s_id,type, type_value, title, title_desc, it_name_kor, it_name_eng, sort, end_dt, use_fg, img_url,img_url_mobile)
      values ('".$_POST['s_id']."','$_POST[type]', '$_POST[type_value]', '$_POST[title]', '$_POST[title_desc]', '$_POST[it_name_kor]', '$_POST[it_name_eng]', '0', '$_POST[end_dt]', '$_POST[use_fg]', '$_POST[img_url]', '$_POST[img_url_mobile]')
    ");

    alert('저장이 완료되었습니다.',$g4['shop_admin_path'].'/new_item.php');
    exit;
} else if($_POST['mode'] == 'update'){

    $update_sql = "
        update
          yc4_item_new
        set
          s_id = '".$_POST['s_id']."',
          type = '".$_POST['type']."',
          type_value = '".$_POST['type_value']."',
          title = '".$_POST['title']."',
          title_desc = '".$_POST['title_desc']."',
          it_name_kor = '".$_POST['it_name_kor']."',
          it_name_eng = '".$_POST['it_name_eng']."',
          img_url = '".$_POST['img_url']."',
          img_url_mobile = '".$_POST['img_url_mobile']."',
          end_dt = '".$_POST['end_dt']."',
          use_fg = '".$_POST['use_fg']."'
        where
            uid = '".$_POST['uid']."'

    ";
    sql_query($update_sql);
    alert('저장이 완료되었습니다.',$g4['shop_admin_path'].'/new_item.php');
    exit;
}

if($_GET['uid']){

    $data = sql_fetch("select * from yc4_item_new where uid = '".mysql_real_escape_string($_GET['uid'])."'");

}

if($data){
    $input_hidden = "
        <input type='hidden' name='mode' value='update'/>
        <input type='hidden' name='uid' value='".$data['uid']."'/>
    ";

}else{
    $input_hidden = "<input type='hidden' name='mode' value='insert'>";
}

# 제품관 로드 #
$s_sql = sql_query("select s_id,name from yc4_station where s_id<>6 order by sort asc");
$s_option = "";
while($row = sql_fetch_array($s_sql)){
    $s_option .= "<option value='".$row['s_id']."' ".($row['s_id'] == $data['s_id'] ? "selected":"").">".$row['name']."</option>";


}

include_once $g4['admin_path']."/admin.head.php";

$type_arr = array(
    'I'=>'상품코드',
    'B'=>'브랜드'
);

?>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="frm" name="save_frm" onsubmit="return chk_save_frm()">
        <?php echo $input_hidden;?>
        <table width="100%">
            <col width="150"/>
            <col/>
            <tr>
                <td>제품관</td>
                <td>
                    <select name="s_id">
                        <?php echo $s_option;?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>타입</td>
                <td>
                    <select name="type">
                        <?php
                        foreach($type_arr as $key => $val){
                            echo "<option value='".$key."' ".($key == $data['type'] ?"selected":"").">".$val."</option>".PHP_EOL;
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class='type_value_name'></td>
                <td><input type="text" name="type_value" value="<?php echo $data['type_value'];?>"/></td>
            </tr>
            <tr>
                <td>주제목</td>
                <td><textarea name="title" style="width: 100%; height:50px;"><?php echo $data['title'];?></textarea></td>
            </tr>
            <tr>
                <td>부제목</td>
                <td><textarea name="title_desc" style="width: 100%; height:50px;"><?php echo $data['title_desc']?></textarea></td>
            </tr>
            <tr class='it_info'>
                <td>한글 제품명</td>
                <td><input type="text" name="it_name_kor" style="width:100%;" value="<?php echo $data['it_name_kor'];?>"/></td>
            </tr>
            <tr class="it_info">
                <td>영문 제품명</td>
                <td><input type="text" name="it_name_eng" style="width:100%;" value="<?php echo $data['it_name_eng'];?>"/></td>
            </tr>
            <tr>
                <td>노출이미지 경로</td>
                <td><input type="text" name="img_url" style="width:100%;" value="<?php echo $data['img_url'];?>"/></td>
            </tr>
            <tr>
                <td>노출이미지 경로(모바일)</td>
                <td><input type="text" name="img_url_mobile" style="width:100%;" value="<?php echo $data['img_url_mobile'];?>"/></td>
            </tr>
            <tr>
                <td>종료일(yyyymmdd)</td>
                <td><input type="text" name="end_dt" value="<?php echo $data['end_dt'];?>"/> ex. 2015.01.01이라면 20150101로 입력해주세요.</td>
            </tr>
            <tr>
                <td>사용여부</td>
                <td><input type="checkbox" name="use_fg" value="Y" <?php echo $data['use_fg'] == 'Y' ? "checked":""?>/></td>
            </tr>
        </table>
        <input type="submit" value="저장" />
        <input type="button" value="목록" onclick="location.href='<?php echo $g4['shop_admin_path'];?>/new_item.php'"/>
    </form>

    <ul>
        <li class="new_list">
            <a href="#">
                <span class="brand"><?php echo $item_data['it_maker']; ?></span>
                <span class="list_title">
                    <span class="b_title"><?php echo nl2br($data['title']);?></span>
                    <span class="s_title"><?php echo nl2br($data['title_desc'])?></span>
                </span>
                <div class="item_con">
                <span class="title">
                    <span class="ko"><?php echo $data['it_name_kor'];?></span>
                    <span class="e"><?php echo $data['it_name_eng'];?></span>
                </span>
                    <span class="price">￦ <span class="ko"><?php echo number_format($item_data['it_amount']);?></span> ($ <span class="e"><?php echo $item_data['it_amount_usd']?></span>)</span>
                </div>
                <span class="img"><?php echo $data['img_path']?></span>
            </a>
        </li>
    </ul>
    <script>
        <?php if($data['type'] == 'B'){?>
        $(function (){
            $('.item_con').hide();
            $('.it_info').hide();
        });
        <?php }?>
        $('select[name=type]').change(function(){
            if($(this).val() == 'I'){
                $('.it_info').show().find('input').removeAttr('disabled');
                $('.type_value_name').text('상품코드');
            }else{
                $('.it_info').hide().find('input').attr('disabled',true);
                $('.type_value_name').text('브랜드명');
            }
        });
        $('.frm').change(function(){
            var tp = '';
            if($(this).find('select[name=type]').val() == 'I'){
                $('.item_con').show();
                tp = 'I';
            }else{
                $('.item_con').hide();
                tp = 'B';
            }

            $('.b_title').html($(this).find('textarea[name=title]').val().replace('\n','<br/>').trim());
            $('.s_title').html($(this).find('textarea[name=title_desc]').val().replace('\n','<br/>').trim());



            $('span.img').html("<img src='"+$(this).find('input[name=img_url]').val()+"'/>");

            $.ajax({
                url : '<?php echo $_SERVER['PHP_SELF']?>',
                data_type : 'json',
                type : 'post',
                data : {
                    'mode' : 'ajax',
                    'tp' : tp,
                    'type_value' : $(this).find('input[name=type_value]').val().trim()
                },success : function (result){
                    $(".new_list").show();
                    var json = $.parseJSON(result);
                    if(typeof (json.korname) != 'undefined') {
                        if($('input[name=it_name_kor]').val() == '') {
                            $("input[name=it_name_kor]").val(json.korname);
                        }
                    }
                    if(typeof (json.engname) != 'undefined') {
                        if($('input[name=it_name_eng]').val() == '') {
                            $("input[name=it_name_eng]").val(json.engname);
                        }
                    }

                    $('.item_con>.title>.ko').html($('input[name=it_name_kor]').val());
                    $('.item_con>.title>.e').html($('input[name=it_name_eng]').val());
                    if(typeof (json.brandname) != 'undefined') {

                        $(".brand").text(json.brandname);
                    }
                    /*
                    if(typeof (json.korname) != 'undefined') {
                        $(".title > .ko").text(json.korname);
                    }
                    if(typeof (json.engname) != 'undefined') {
                        $(".title > .e").text(json.engname);
                    }
                    */
                    if(typeof (json.it_amount) != 'undefined') {
                        $(".price > .ko").text(json.it_amount);
                    }
                    if(typeof (json.it_amount_usd) != 'undefined') {
                        $(".price > .e").text(json.it_amount_usd);
                    }




                    //$("textarea[name=title]").text(json.it_name);
                }

            });



        });

        function chk_save_frm(){
            var $frm = $("form[name=save_frm]");

            if($("textarea[name=title]").val() == ""){
                alert("주제목을 입력해주세요.");
                $("textarea[name=title]").focus();
                return false;
            }
            if($("textarea[name=title_desc]").val() == ""){
                alert("부제목을 입력해주세요.");
                $("textarea[name=title_desc]").focus();
                return false;
            }
            if($('.it_info').is(':visible') == true) {
                if ($("input[name=it_name_kor]").val() == "") {
                    alert("한글 제품명을 입력해주세요.");
                    $("input[name=it_name_kor]").focus();
                    return false;
                }
                if ($("input[name=it_name_eng]").val() == "") {
                    alert("영문 제품명을 입력해주세요.");
                    $("input[name=it_name_eng]").focus();
                    return false;
                }
            }

            if($("input[name=img_url]").val() == ""){
                alert("노출이미지 경로을 입력해주세요.");
                $("input[name=img_url]").focus();
                return false;
            }
            if($("input[name=end_dt]").val() == ""){
                alert("종료일을 입력해주세요.");
                $("input[name=end_dt]").focus();
                return false;
            }
        }
    </script>


<?php
include_once $g4['admin_path']."/admin.tail.php";
?>