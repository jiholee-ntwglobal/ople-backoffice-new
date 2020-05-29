<?php
/**
 * Created by PhpStorm.
 * File name : promotion_category_write.php.
 * Comment :
 * Date: 2016-05-19
 * User: Minki Hong
 */
$sub_menu = "500500";
include './_common.php';
auth_check($auth[$sub_menu], "r");

if ($_POST['mode'] == 'insert') {
    $pc_view = 'N';
    $mobile_view = 'N';
    if ($_POST['pc_view']) {
        $pc_view = 'Y';
    }
    if ($_POST['mobile_view']) {
        $mobile_view = 'Y';
    }
    $sql = "
        INSERT INTO yc4_promotion_category
        (pr_id, pr_ca_name, pr_ca_banner_img_url, sort, create_dt, ip, mb_id, pc_view, mobile_view) 
        VALUES 
        ('{$_POST['pr_id']}', '{$_POST['pr_ca_name']}', '{$_POST['pr_ca_banner_img_url']}', null, now(), '{$_SERVER['REMOTE_ADDR']}', '{$member['mb_id']}', '{$pc_view}', '{$mobile_view}')
    ";
    if ($_POST['qstr']) {
        parse_str($_POST['qstr'], $qstr);
        unset($qstr['pr_id']);
    }

    if (!sql_query($sql)) {
        alert('처리중 오류 발생! 관리자에게 문의하세요');
    }
    $pr_ca_id = mysql_insert_id();

    if (!$pr_ca_id) {
        alert('처리중 오류 발생! 관리자에게 문의하세요');
    }

    $qstr['pr_ca_id'] = $pr_ca_id;
    $qstr['pr_id'] = $_POST['pr_id'];

    $qstr = http_build_query($qstr);
    $return_url = $_SERVER['PHP_SELF'] . '?' . $qstr;

    alert('프로모션 카테고리 추가가 완료되었습니다.', $return_url);
}
if ($_POST['mode'] == 'update') { // 업데이트 미작업
    if (!$_POST['pr_ca_id']) {
        alert('잘못된 경로로 접근하였습니다.');
    }

    $pc_view = 'N';
    $mobile_view = 'N';
    if ($_POST['pc_view']) {
        $pc_view = 'Y';
    }
    if ($_POST['mobile_view']) {
        $mobile_view = 'Y';
    }
    $sql = "
        update yc4_promotion_category
        set
            pr_ca_name = '{$_POST['pr_ca_name']}', 
            pr_ca_banner_img_url = '{$_POST['pr_ca_banner_img_url']}',
            pc_view = '{$pc_view}',
            mobile_view = '{$mobile_view}'
        where pr_ca_id = '{$_POST['pr_ca_id']}'
    ";
    $update_rs = sql_query($sql);
    if (!$update_rs) {
        alert('처리중 오류 발생! 관리자에게 문의하세요');
    }

    $return_url = $_SERVER['PHP_SELF'] . '?pr_id=' . $_POST['pr_id'] . '&pr_ca_id=' . $_POST['pr_ca_id'];

    if ($_POST['qstr']) {
        parse_str($_POST['qstr'], $qstr);
        unset($qstr['pr_id']);
        $qstr['pr_ca_id'] = $_POST['pr_ca_id'];
        $qstr['pr_id'] = $_POST['pr_id'];
        $qstr = http_build_query($qstr);
        $return_url = $_SERVER['PHP_SELF'] . '?' . $qstr;
    }

    alert('프로모션 카테고리 수정이 완료되었습니다.', $return_url);
}

if (!$_GET['pr_id']) {
    alert('잘못된 경로로 접근하였습니다.');
}

$pr = sql_fetch("select * from yc4_promotion where pr_id = '{$_GET['pr_id']}'");
if (!$pr) {
    alert('잘못된 경로로 접근하였습니다.');
}


if ($_GET['pr_ca_id']) {
    $data = sql_fetch("select * from yc4_promotion_category where pr_ca_id = '{$_GET['pr_ca_id']}'");
}

$input_hidden = '<input type="hidden" name="mode" value="insert">  ';
if ($data) {
    $input_hidden = '<input type="hidden" name="mode" value="update">  ';
}
$qstr = $_GET;
unset($qstr['pr_ca_id']);
$qstr = http_build_query($qstr);
$input_hidden .= '<input type="hidden" name="qstr" value="' . htmlspecialchars($qstr) . '">';

define('bootstrap', true);
$g4['title'] = "프로모션 카테고리 등록 및 수정";
include '../admin.head.php';
?>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <?php echo $input_hidden; ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <table class="table table-hover table-striped">
                    <tbody>
                    <tr>
                        <td>프로모션 코드</td>
                        <td>
                            <p class="form-control-static"><?php echo $pr['pr_id']; ?></p>
                            <input type="hidden" name="pr_id" value="<?php echo htmlspecialchars($pr['pr_id']); ?>">
                        </td>
                    </tr>
                    <?php if ($data) { ?>
                        <tr>
                            <td>프로모션 카테고리 코드</td>
                            <td>
                                <p class="form-control-static"><?php echo $data['pr_ca_id']; ?></p>
                                <input type="hidden" name="pr_ca_id" value="<?php echo htmlspecialchars($data['pr_ca_id']); ?>">
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td>프로모션 카테고리 이름</td>
                        <td><input type="text" class="form-control" name="pr_ca_name" value="<?php echo htmlspecialchars($data['pr_ca_name']) ?>"></td>
                    </tr>

                    <tr>
                        <td>카테고리 베너 이미지</td>
                        <td><input type="text" name="pr_ca_banner_img_url" class="form-control" value="<?php echo htmlspecialchars($data['pr_ca_banner_img_url']); ?>"></td>
                    </tr>
                    <tr>
                        <td>출력</td>
                        <td>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="pc_view" value="Y" <?php echo $data['pc_view'] == 'Y' ? 'checked' : ''; ?>>
                                    PC
                                </label>
                                <label>
                                    <input type="checkbox" name="mobile_view" value="Y" <?php echo $data['mobile_view'] == 'Y' ? 'checked' : ''; ?>>
                                    모바일
                                </label>
                            </div>
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>
            <div class="panel-footer text-center">
                <button type="submit" class="btn btn-primary">저장</button>
                <a href="promotion_category_list.php?<?php echo $qstr; ?>" class="btn btn-info">목록</a>
            </div>
        </div>

    </form>


<?php
include '../admin.tail.php';

