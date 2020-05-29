<?php
/**
 * Created by PhpStorm.
 * File name : ople_item_add_lms_contents.php.
 * Comment :
 * Date: 2016-04-15
 * User: Minki Hong
 */

$sub_menu = "500900";
include './_common.php';

auth_check($auth[$sub_menu], "w");


if($_POST['mode'] == 'insert'){
    $sql = "
        insert into yc4_add_item_lms_contents 
        ( title, content, create_dt, ip, mb_id, st_dt, en_dt )    
        VALUES 
        ('{$_POST['title']}','{$_POST['content']}',now(),'{$_SERVER['REMOTE_ADDR']}','{$member['mb_id']}','{$_POST['st_dt']}','{$_POST['en_dt']}')
    ";
    sql_query($sql);
    $uid = mysql_insert_id();
    if(!$uid){
        alert('처리중 오류 발생! 관리자에게 문의하여 주세요');
    }
    alert('저장이 완료되었습니다.',$_SERVER['PHP_SELF'].'?uid='.$uid);

}elseif($_POST['mode'] =='update'){
    if(!$_POST['uid']){
        alert('잘못된 경로로 접근하였습니다.');
    }
    $sql = sql_query("
        update 
            yc4_add_item_lms_contents
        set
            title = '{$_POST['title']}',
            content = '{$_POST['content']}',
            st_dt = '{$_POST['st_dt']}',
            en_dt = '{$_POST['en_dt']}'
        WHERE uid = '{$_POST['uid']}'
    ");
    if(!$sql){
        alert('처리중 오류 발생! 관리자에게 문의하세요!');
    }
    alert('수정이 완료되었습니다.',$_SERVER['PHP_SELF'].'?uid='.$_POST['uid']);

}elseif($_POST['mode'] == 'delete' || $_GET['mode'] == 'delete'){
    $uid = '';
    if($_GET['uid']){
        $uid = $_GET['uid'];
    }
    if($_POST['uid']){
        $uid = $_POST['uid'];
    }
    if(!$uid){
        alert('잘못된 경로로 접근하였습니다.');
    }

    sql_query("delete from yc4_add_item_lms_contents where uid = '{$uid}'");
    alert('삭제가 완료되었습니다.','ople_item_add_lms_contents.php');
}


include '../admin.head.php';
if($_GET['uid']){
    $data = sql_fetch("
        select 
        *
        from 
        yc4_add_item_lms_contents
        where uid = '{$_GET['uid']}'   
    ");
}

$input_hidden = "";
if($data){
    $input_hidden = "
        <input type='hidden' name='uid' value='{$data['uid']}'>       
        <input type='hidden' name='mode' value='update'>       
    ";
}else{
    $input_hidden = " 
            <input type='hidden' name='mode' value='insert'>       
    ";
}

?>
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/themes/smoothness/jquery-ui.css">
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/jquery-ui.min.js"></script>



    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
        <?php echo $input_hidden;?>
        <table cellpadding='0' cellspacing='0' width='100%'>
            <tr class="ht">
                <td>제목</td>
                <td>
                    <input type="text" class="ed" name="title" value="<?php echo htmlspecialchars($data['title']);?>">
                </td>
            </tr>
            <tr class="ht">
                <td>기간</td>
                <td>
                    <input type="text" name="st_dt" class="datepicker_input ed"  value="<?php echo htmlspecialchars($data['st_dt']);?>" readonly>
                    ~
                    <input type="text" name="en_dt" class="datepicker_input ed"  value="<?php echo htmlspecialchars($data['en_dt']);?>" readonly>
                </td>
            </tr>
            <tr class="ht">
                <td>내용</td>
                <td>
                    <textarea name="content" class="ed"  rows="10" style="width: 100%;"><?php echo $data['content']?></textarea>
                </td>
            </tr>
        </table>
        <div style="text-align: center;">
            <input type="submit" value="저장">
            <button type="button" onclick="location.href='ople_item_add_lms_contents.php'">목록</button>
        </div>
    </form>

    <script>
        $(function() {

            $( ".datepicker_input" ).datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat : 'yy-mm-dd',
                prevText: '이전 달',
                nextText: '다음 달',
                monthNames: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
                monthNamesShort: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
                dayNames: ['일','월','화','수','목','금','토'],
                dayNamesShort: ['일','월','화','수','목','금','토'],
                dayNamesMin: ['일','월','화','수','목','금','토'],
//                showMonthAfterYear: true,
                yearSuffix: '년'
            });
        });
    </script>

<?php
include '../admin.tail.php';
