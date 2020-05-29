<?php

$sub_menu = "100999";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.");

//페이징 function
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
        $pages .= '검색한 결과는 없습니다.';
    } else {
        if ($now_page > 1) {
            $pages .= '<a href=' . $server . '?page=1' . '&' . $search . '> 처음 </a>';
        }
        if ($now_block > 1) {
            $pages .= '<a href=' . $server . '?page=' . $prev_page . '&' . $search . '> 이전 </a>';
        }
        if ($end_page >= $total_page) {
            $end_page = $total_page;
        }
        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $now_page) {
                $pages .= '<b> ' . $i . ' </b>';
            } else {
                $pages .= '<a href=' . $server . '?page=' . $i . '&' . $search . '> ' . $i . ' </a>';
            }
        }
        if ($now_page >= 1) {
            if ($total_block != $now_block) {
                $pages .= '<a  href=' . $server . '?page=' . $next_page . '&' . $search . '> 다음 </a>';
            }
            if ($now_page != $total_page) {
                $pages .= '<a href=' . $server . '?page=' . $total_page . '&' . $search . '> 마지막 </a>';
            }
        }
    }
    return $pages;
}

//초기화 및 값
$total_ip = isset($_GET['total_ip']) ? $_GET['total_ip'] : ""; //ip 검색
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : ""; // 아이디검색
$user_explanation = isset($_GET['user_explanation']) ? $_GET['user_explanation'] : "";// 설명 검색

$ip_values = ""; //ip 삭제
$where = ""; //조건
$str = ""; // 리셋
$arr = array(); //출력 query
$total_ips = ""; // 아이피 검색
$user_ids = ""; // 아이디 검색
$user_explanations = ""; // 설명검색
//페이징
$now_page = isset($_GET['page']) ? $_GET['page'] : '1';
$now_page = $now_page == 0 ? 1 : $now_page;
$now_page = is_numeric($now_page) ? $now_page : '1';

//검색
if ($total_ip != "") {
    $total_ips = trim($total_ip);
    $where .= (($where == "") ? " where " : " and ") . " INET_ATON('" . $total_ips . "') between start_ip_num and end_ip_num ";
}
if ($user_id != "") {
    $user_ids = trim($user_id);
    $where .= (($where == "") ? " where " : " and ") . " user_id ='" . $user_ids . "'";
}
if ($user_explanation != "") {
    $user_explanations = trim($user_explanation);
    $where .= (($where == "") ? " where " : " and ") . " user_explanation like '%" . $user_explanations . "%'";
}


//페이징
$pagequery = "select COUNT(uid) cnt from ip_manager "
    . $where;
$pageresult = sql_query( $pagequery);
$page_rows = sql_fetch_array($pageresult);
$row = $page_rows['cnt'];
$total_list = $row;//페이징=전체컬럼수
$list_page = 20;//보여줄 컬럼
$num_page = 10;//표시할 페이지


// 보여줄 쿼리
$sql = "select uid,start_ip,end_ip,user_explanation,user_id,create_dt
        from ip_manager " . $where . " order by create_dt desc " . " limit " . ($now_page - 1) * $list_page . "," . $list_page;
$result = sql_query($sql);

$g4['title'] = "IP관리";
include_once ("./admin.head.php");
//리셋 버튼
if ($total_ip != "" || $user_id != "" || $user_explanation != "") {
    $str = "<input type='button' value='리셋' onclick=\"location.href='./ip_manager.php'\" >";
}
?>
    <table bgcolor="#f5f5dc">
        <form action="ip_manager.php" method="get" id="search" name="searchs" onsubmit="return searchs01()">

            <tr>
                <td><input type="text" id="total_ips" name="total_ip" class="decimal" placeholder="IP 예)xxx.xxx.xxx.xxx"
                           value="<?php echo $total_ips; ?>"></td>
                <td><input type="text" id="user_ids" name="user_id" placeholder="ID" value="<?php echo $user_ids; ?>">
                </td>
                <td><input type="text" id="user_explanations" name="user_explanation" placeholder="설명"
                           value="<?php echo $user_explanations; ?>"></td>
            </tr>
            <tr>
                <td align="center" colspan="3"><input value="검색" type="submit"><?php echo $str; ?></td>
            </tr>
        </form>
    </table>

    <table bgcolor="#ffebcd" width="1000px">
        <tr>
            <td align="center" colspan="6"><span style="font-weight: bold">IP 리스트</span></td>
        </tr>
        <tr>
            <td align="center">IP</td>
            <td align="center">아이디</td>
            <td align="center">설명</td>
            <td align="center">날짜</td>
            <td align="center" colspan="2"><a href="ip_manager_write.php">추가</a></td>
        </tr>
        <?php while ($results = sql_fetch_array($result)) { ?>
            <tr onmouseover=this.style.backgroundColor="#FFCC33" onmouseout=this.style.backgroundColor=''>
                <?php if ($results['start_ip'] == $results['end_ip']) { ?>
                    <td width="280px" align="center"><span
                            style="color: red;font-weight: bold"><?php echo $ip_values = $results['start_ip']; ?></span>
                    </td>
                <?php } else { ?>
                    <td width="280px" align="center"><span
                            style="color: blue;font-weight: bold"><?php echo $ip_values = $results['start_ip'] . "~" . $results['end_ip']; ?></span>
                    </td>
                <?php } ?>
                <td width="90px" align="center"><span style="font-weight: bold"><?php echo $results['user_id']; ?></span>
                </td>
                <td><?php echo $results['user_explanation']; ?></td>
                <td width="180px" align="center"><?php echo $results['create_dt']; ?></td>
                <td align="center" width="50px"><input type="button" value="수정"
                                                       onclick="location.href='ip_manager_write.php?<?php echo http_build_query($results); ?>'">
                </td>
                <td align="center" width="50px"><input type="button"
                                                       onclick="next(<?= $results['uid'] ?>,'<?= $ip_values ?>')"
                                                       value="삭제"></td>
            </tr>
        <?php } ?>
        <tr>
            <td colspan="6" align="center"><?php
                echo paging($total_list, $now_page, $list_page, $num_page, $_GET);
                ?></td>
        </tr>
    </table>
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script>
        //    삭제
        function next(idx, ips) {
            if (confirm(ips + ' 를삭제하시겠습니까?')) {
                location.href = "./ip_manager_delete.php?uid=" + idx;
            } else {

            }
        }
        //    검색
        function searchs01() {
            var total_ips = $.trim($("#total_ips").val());
            var user_ids = $.trim($("#user_ids").val());
            var user_explanations = $.trim($("#user_explanations").val());
            var ipformat = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
            if (total_ips == "" && user_ids == "" && user_explanations == "") {
                alert("검색어를 입력해주세요.");
                return false;
            } else {
                if (total_ips != "") {
                    if (total_ips.match(ipformat)) {
                        return true;
                    } else {
                        alert("잘못된 IP 주소를 입력하였습니다.");
                        $("#total_ips").val("");
                        return false;
                    }
                }
            }

            return true;
        }
        //    숫자 . 입력
        $('.decimal').keyup(function () {
            var val = $(this).val();
            if (isNaN(val)) {
                val = val.replace(/[^0-9\.]/g, '');
                if (val.split('.').length > 4)
                    val = val.replace(/\.+$/, "");
            }
            $(this).val(val);
        });
    </script>
<?
include_once("./admin.tail.php");
?>