<?php
/**
 * Created by PhpStorm.
 * File name : promotion_write.php.
 * Comment :
 * Date: 2016-05-19
 * User: Minki Hong
 */
$sub_menu = "500500";
include './_common.php';
auth_check($auth[$sub_menu], "w");

if ($_POST['mode'] == 'get_promotion_list') {
    $where = '';
    if ($_POST['pr_id']) {
        $where .= ($where ? ' and ' : ' where ') . " pr_id != '{$_POST['pr_id']}'";
    }
    if ($_POST['fg'] == 'Y') {
        $where .= ($where ? ' and ' : ' where ') . " date_format(now(),'%Y-%m-%d') between ifnull(st_dt,date_format(now(),'%Y-%m-%d')) and ifnull(en_dt,date_format(now(),'%Y-%m-%d'))";
    } elseif ($_POST['fg'] == 'N') {
        $where .= ($where ? ' and ' : ' where ') . " date_format(now(),'%Y-%m-%d') not between ifnull(st_dt,date_format(now(),'%Y-%m-%d')) and ifnull(en_dt,date_format(now(),'%Y-%m-%d'))";
    }
    $sql = sql_query("
        select
        pr_id,pr_name
        from
        yc4_promotion
        {$where}
        order by pr_id desc
    ");
    $result = array();
    while ($row = sql_fetch_array($sql)) {
        $result[] = $row;
    }
    echo json_encode($result);
    exit;
}

if ($_POST['mode'] == 'insert') {
    $sql = "
        insert into yc4_promotion
        (pr_name, comment, list_skin, banner_url,banner_url_bg_color,banner_mobile_url, create_dt, ip, mb_id,promotion_type, keyword_mobile_main_url)
        VALUES 
        ('{$_POST['pr_name']}', '{$_POST['comment']}', '{$_POST['list_skin']}', '{$_POST['banner_url']}','{$_POST['banner_url_bg_color']}','{$_POST['banner_mobile_url']}', now(), '{$_SERVER['REMOTE_ADDR']}', '{$member['mb_id']}','{$_POST['promotion_type']}', '{$_POST['keyword_mobile_main_url']}')
    ";

    $insert_rs = sql_query($sql);
    if ($insert_rs === false) {
        alert('처리중 오류 발생! 관리자에게 문의하세요');
    }
    $pr_id = mysql_insert_id();
    if (!$pr_id) {
        alert('처리중 오류 발생! 관리자에게 문의하세요');
    }

    if (trim($_POST['st_dt']) || trim($_POST['en_dt'])) {
        $update_set = "";
        if ($_POST['st_dt']) {
            $update_set .= ($update_set ? ', ' : '') . "st_dt = '{$_POST['st_dt']}'";
        }
        if ($_POST['en_dt']) {
            $update_set .= ($update_set ? ', ' : '') . "en_dt = '{$_POST['en_dt']}'";
        }
        $update_sql = "
            update yc4_promotion
            set
            {$update_set}
            where pr_id = '{$pr_id}'
        ";
        $update_rs = sql_query($update_sql);
        if ($update_rs === false) {
            alert("프로모션 등록은 성공하였으나, 기간 지정에 실패하였습니다. 관리자에게 문의하세요", $_SERVER['PHP_SELF'] . '?pr_id=' . $pr_id);
        }
    }

    if (isset($_POST['link_pr_id']) && is_array($_POST['link_pr_id'])) {
        foreach ($_POST['link_pr_id'] as $link_pr_id) {
            sql_query("insert into yc4_promotion_link (pr_id,link_pr_id) VALUES ('{$pr_id}','{$link_pr_id}')");
        }
    }

//        link_pr_id
    alert('프로모션 등록이 완료되었습니다.', $_SERVER['PHP_SELF'] . '?pr_id=' . $pr_id);
}
if ($_POST['mode'] == 'update') { // 업데이트 미작업
    if (!$_POST['pr_id']) {
        alert('잘못된 경로로 접근하였습니다.');
    }
    $pr_id = $_POST['pr_id'];

    $update_set = "";
    if ($_POST['st_dt']) {
        $update_set .= ",st_dt = '{$_POST['st_dt']}'";
    } else {
        $update_set .= ",st_dt = null";
    }
    if ($_POST['en_dt']) {
        $update_set .= ",en_dt = '{$_POST['en_dt']}'";
    } else {
        $update_set .= ",en_dt = null";
    }
    $update_sql = "
    update yc4_promotion
    set
        pr_name = '{$_POST['pr_name']}', 
        promotion_type = '{$_POST['promotion_type']}',
        comment = '{$_POST['comment']}', 
        list_skin = '{$_POST['list_skin']}', 
        banner_url = '{$_POST['banner_url']}',
        banner_url_bg_color = '{$_POST['banner_url_bg_color']}',
        banner_mobile_url = '{$_POST['banner_mobile_url']}',
        keyword_mobile_main_url= '{$_POST['keyword_mobile_main_url']}'
        {$update_set}
    where
        pr_id = '{$pr_id}'
    ";
    $update_rs = sql_query($update_sql);
    if (!$update_rs) {
        alert('처리중 오류 발생! 관리자에게 문의하세요');
    }
    $link_del_sql = sql_query("delete from yc4_promotion_link where pr_id = '$pr_id'");
    if (!$link_del_sql) {
        alert('상품 상세페이지 출력 프로모션 처리중 오류 발생! 관리자에게 문의하세요');
    }
    if (isset($_POST['link_pr_id']) && is_array($_POST['link_pr_id'])) {
        foreach ($_POST['link_pr_id'] as $link_pr_id) {
            sql_query("insert into yc4_promotion_link (pr_id,link_pr_id) VALUES ('{$pr_id}','{$link_pr_id}')");
        }
    }
    alert('프로모션 수정이 완료되었습니다.', $_SERVER['PHP_SELF'] . '?pr_id=' . $pr_id);
}


if ($_GET['pr_id']) {
    $data = sql_fetch("select * from yc4_promotion where pr_id = '{$_GET['pr_id']}'");
}

$link_data = array();
$input_hidden = ' <input type="hidden" name="mode" value="insert"> ';
if ($data) {
    $input_hidden = '<input type="hidden" name="mode" value="update">';
    $link_data_stmt = sql_query("
        select
        p.pr_id,p.pr_name
        from
        yc4_promotion_link pl
        left join
        yc4_promotion p on pl.link_pr_id = p.pr_id
        where pl.pr_id = '{$data['pr_id']}' and p.pr_id is not null
    ");
    while ($row = sql_fetch_array($link_data_stmt)) {
        $link_data[] = $row;
    }
}

// 프로모션 타입 데이터 로드
$p_type_stmt = sql_query("select code_value, code_name from yc4_code where code_type = 'p_type'  ");
$p_type_arr = array();
while ($row = sql_fetch_array($p_type_stmt)) {
    $p_type_arr[$row['code_value']] = $row['code_name'];
}

$banner_skin_arr = array(
    1 => '4열 일반',
    2 => '4열 할인율',
    3 => '3열 일반',
    4 => '3열 할인율',
);

define('bootstrap', true);
$g4['title'] = "프로모션 등록 및 수정";
include '../admin.head.php';
?>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>


    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return frm_chk(this);">
        <?php echo $input_hidden; ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <table class="table table-hover table-striped">
                    <tbody>
                    <?php if ($data) { ?>
                        <tr>
                            <td>프로모션 코드</td>
                            <td>
                                <p class="form-control-static"><?php echo $data['pr_id']; ?></p>
                                <input type="hidden" name="pr_id" value="<?php echo htmlspecialchars($data['pr_id']); ?>">
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td>타입</td>
                        <td>
                            <select name="promotion_type" class="form-control">
                                <?php foreach ($p_type_arr as $p_type_key => $p_type_name) { ?>
                                    <option value="<?php echo $p_type_key; ?>"<?php echo $data['promotion_type'] == $p_type_key ? ' selected' : ''; ?>><?php echo $p_type_name; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>프로모션 이름</td>
                        <td><input type="text" class="form-control" name="pr_name" value="<?php echo htmlspecialchars($data['pr_name']) ?>"></td>
                    </tr>
                    <tr>
                        <td>기간</td>
                        <td>
                            <div class="form-inline">
                                <input type="text" name="st_dt" class="form-control date_input" value="<?php echo htmlspecialchars($data['st_dt']); ?>">
                                ~
                                <input type="text" name="en_dt" class="form-control date_input" value="<?php echo htmlspecialchars($data['en_dt']); ?>">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>베너 이미지</td>
                        <td><input type="text" name="banner_url" class="form-control" value="<?php echo htmlspecialchars($data['banner_url']); ?>"></td>
                    </tr>
                    <tr>
                        <td>베너 이미지 백그라운드</td>
                        <td><input type="text" name="banner_url_bg_color" class="form-control" value="<?php echo htmlspecialchars($data['banner_url_bg_color']); ?>"></td>
                    </tr>
                    <!--<tr>
                        <td>베너 이미지(모바일)</td>
                        <td><input type="text" name="banner_mobile_url" class="form-control" value="<?php /*echo htmlspecialchars($data['banner_mobile_url']);*/ ?>"></td>
                    </tr>-->
                    <tr>
                        <td>리스트 스킨</td>
                        <td>
                            <select name="list_skin" class="form-control">
                                <?php foreach ($banner_skin_arr as $key => $key_name) { ?>
                                    <option value="<?php echo $key; ?>" <?php echo $data['list_skin'] == $key ? 'selected' : ''; ?>><?php echo $key_name; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>코멘트</td>
                        <td><textarea name="comment" class="form-control" rows="10"><?php echo htmlspecialchars($data['comment']); ?></textarea></td>
                    </tr>
                    <tr>
                        <td>키워드 모바일 메인 이미지 URL</td>
                        <td><input type="text" name="keyword_mobile_main_url" class="form-control" value="<?php echo htmlspecialchars($data['keyword_mobile_main_url']); ?>"></td>
                    </tr>
                    <?php if($_SERVER['REMOTE_ADDR'] == "211.214.213.101") { ?>
                        <tr>
                            <td>이벤트 정렬순서</td>
                            <td><input type="text" name="event_sort" class="form-control" value="<?php echo htmlspecialchars($data['event_sort']); ?>"></td>
                        </tr>
                        <tr>
                            <td>이벤트 연결 URL</td>
                            <td>
                                <input type="text" name="event_link_url" class="form-control" value="<?php echo htmlspecialchars($data['event_link_url']); ?>">
                                ( * 따로 페이지를 만들어 작업해주신 경우 등록해주세요. 빈값으로 저장할 경우 기본 프로모션 페이지로 연결됩니다. )
                            </td>
                        </tr>
                        <tr>
                            <td>이벤트 배너 URL</td>
                            <td><input type="text" name="event_banner_img_url" class="form-control" value="<?php echo htmlspecialchars($data['event_banner_img_url']); ?>"></td>
                        </tr>
                        <tr>
                            <td>이벤트 배너 URL(Mobile)</td>
                            <td><input type="text" name="event_banner_img_url_mobile" class="form-control" value="<?php echo htmlspecialchars($data['event_banner_img_url_mobile']); ?>"></td>
                        </tr>

                        <tr>
                            <td>이벤트 노출여부</td>
                            <td>
                                <label>Y</label> <input type="radio" name="use_yn" value="y" <?php if($data['use_yn']=="y") echo "checked"; ?>>
                                <label>N</label> <input type="radio" name="use_yn" value="n" <?php if($data['use_yn']=="n") echo "checked"; ?>>
                            </td>
                        </tr>
                        <tr>
                            <td>이벤트(카드/오플) 타입</td>
                            <td>
                                <label>카드 이벤트</label> <input type="radio" name="event_type" value="card" <?php if($data['event_type']=="card") echo "checked"; ?>>
                                <label>오플 이벤트</label> <input type="radio" name="event_type" value="ople" <?php if($data['event_type']=="ople") echo "checked"; ?>>
                            </td>
                        </tr>

                    <?php } ?>
                    <tr>
                        <td>상품 상세페이지 출력 프로모션</td>
                        <td>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            지정된 프로모션
                                        </div>
                                        <ul class="list-group selected_pr_list">
                                            <?php foreach ($link_data as $row) { ?>
                                                <li class="list-group-item">
                                                    <input type="hidden" name="link_pr_id[]" value="<?php echo $row['pr_id']; ?>">
                                                    <span class="badge" style="cursor: pointer;" onclick="del_link_pr_id(this);"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></span>
                                                    <?php echo $row['pr_name'] ?>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>

                                </div>
                                <div class="col-lg-6">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            프로모션 선택
                                        </div>
                                        <ul class="nav nav-tabs pr_link_tab">
                                            <li role="presentation" class="active"><a href="#" onclick="get_promotion_list('<?php echo $data['pr_id'] ?>','',this); return false;">ALL</a></li>
                                            <li role="presentation"><a href="#" onclick="get_promotion_list('<?php echo $data['pr_id'] ?>','Y',this); return false;">진행중</a></li>
                                            <li role="presentation"><a href="#" onclick="get_promotion_list('<?php echo $data['pr_id'] ?>','N',this); return false;">미진행</a></li>
                                        </ul>
                                        <table class="table table-hover table-bordered">
                                            <thead>
                                            <tr>
                                                <td>코드</td>
                                                <td>이름</td>
                                                <td></td>
                                            </tr>
                                            </thead>

                                            <tbody class="promotion_link_list">

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>


                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="panel-footer text-center">
                <button type="submit" class="btn btn-primary">저장</button>
                <a href="promotion_list.php" class="btn btn-info">목록</a>
            </div>
        </div>

    </form>

    <script>
        $(function () {
            $(".date_input").datepicker({
                dateFormat: 'yy-mm-dd',
                prevText: '이전 달',
                nextText: '다음 달',
                monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
                monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
                dayNames: ['일', '월', '화', '수', '목', '금', '토'],
                dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
                dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
                showMonthAfterYear: true,
                changeMonth: true,
                changeYear: true,
                yearSuffix: '년'
            });

            get_promotion_list('<?php echo $data['pr_id']?>', '', $('.pr_link_tab .active a'));
        });

        function frm_chk(f) {
            if (f.pr_name.value.trim() == '') {
                alert('프로모션 이름을 입력 해 주세요.');
                f.pr_name.focus();
                return false;
            }

            return true;
        }

        function get_promotion_list(pr_id, fg, obj) {

            $.ajax({
                url: '<?php echo $_SERVER['PHP_SELF'];?>',
                type: 'post',
                beforeSend: function () {

                    $('.pr_link_tab > .active').removeClass('active');
                    var html = '<td colspan="3"> <div class="progress"> <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"> Loading...</div> </div> </td>';
                    $('.promotion_link_list').html(html);
                },
                dataType: 'json',
                data: {
                    mode: 'get_promotion_list',
                    pr_id: pr_id,
                    fg: fg
                }, success: function (json) {
                    var html = '';
                    var selected_pr_id = get_selected_link_pr_id();
                    for (var i in json) {
                        if ($.inArray(json[i]['pr_id'], selected_pr_id) >= 0) {
                            continue;
                        }
                        html +=
                            '<tr class="pr_list_row">' +
                            '<td class="pr_list_row_pr_id">' + json[i]['pr_id'] + '</td>' +
                            '<td class="pr_list_row_pr_name">' + json[i]['pr_name'] + '</td>' +
                            '<td><button type="button" class="btn btn-default" onclick="add_link_pr_id(this);"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button></td>' +
                            '</tr>';
                    }
                    if (html == '') {
                        html = '<tr><td class="text-center" colspan="3">데이터가 존재하지 않습니다.</td></tr>';
                    }
                    $('.promotion_link_list').html(html);
                    $('.pr_link_tab > .active').removeClass('active');
                    $(obj).parent().addClass('active');
                }
            });
        }

        function get_selected_link_pr_id() {
            var pr_id = [];
            $('.selected_pr_list input').each(function () {
                pr_id.push($(this).val());
            });
            return pr_id;
        }

        function add_link_pr_id(btn_obj) {
            var obj = $(btn_obj).parents('tr.pr_list_row:eq(0)');
            var pr_id = $(obj).find('.pr_list_row_pr_id').text();
            var pr_name = $(obj).find('.pr_list_row_pr_name').text();

            var html =
                '<li class="list-group-item">' +
                '<input type="hidden" name="link_pr_id[]" value="' + pr_id + '">' +
                '<span class="badge" style="cursor: pointer;" onclick="del_link_pr_id(this);"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></span>' +
                pr_name +
                '</li>';
            $('.selected_pr_list').append(html);
            $(obj).remove()
            if ($('.promotion_link_list').html().trim() == '') {
                var html = '<tr><td class="text-center" colspan="3">데이터가 존재하지 않습니다.</td></tr>';
                $('.promotion_link_list').html(html);
            }
        }

        function del_link_pr_id(obj) {
            $(obj).parent().remove();
            $('.pr_link_tab .active a').trigger('click');
        }


    </script>
<?php
include '../admin.tail.php';

