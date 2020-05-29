<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-08-03
 * Time: 오전 10:25
 */

$sub_menu = "200710";
include "_common.php";
auth_check($auth[$sub_menu], "r");

if($_POST['mode'] == 'reset'){
    sql_query("update yc4_member_promor set use_fg = 'N' where mb_id2 = '".sql_safe_query($_POST['mb_id'])."'");
    alert('블로그 혜택 초기화 완료!',$_SERVER['PHP_SELF'].'?mb_id='.$_POST['mb_id']);
    exit;
}

if($_GET['mb_id']){
    $mb = sql_fetch("
        select
        a.mb_id,a.mb_name,b.use_fg,b.ms_pid
        from
        g4_member a
        left JOIN
        yc4_member_promor b on a.mb_id = b.mb_id2
        WHERE
        a.mb_id = '".sql_safe_query($_GET['mb_id'])."'
    ");
}

define('bootstrap',true);
include_once $g4['admin_path']."/admin.head.php";
?>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
        아이디 : <input type="text" name="mb_id" value="<?php echo $_GET['mb_id']?>" />
        <input type="submit" value="검색" />
    </form>

    <?php if($mb){?>
    <p>이름 : <?php echo $mb['mb_name']?></p>

    <?php if($mb['ms_pid']){?>
        <p>블로그 유입 회원</p>
        <?php if($mb['use_fg'] == 'Y'){?>
            <p>블로그 혜택 사용 완료</p>
            <form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
                <input type="hidden" name="mode" value="reset">
                <input type="hidden" name="mb_id" value="<?php echo $mb['mb_id'];?>">
                <p>
                    <button class="btn btn-primary" type="submit">혜택 초기화</button>
                </p>
            </form>
        <?php }else{?>
            <p>블로그 혜택 미사용</p>
        <?php }?>
    <?php }else{?>
        <p>블로그 유입 회원이 아닙니다</p>
    <?php }?>

    <?php }?>


<?php
include_once $g4['admin_path']."/admin.tail.php";
