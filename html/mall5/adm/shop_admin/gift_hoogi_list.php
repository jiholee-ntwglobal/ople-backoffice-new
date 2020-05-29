<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-07-16
* Time : 오후 4:44
*/

$sub_menu = "500700";
include './_common.php';
auth_check($auth[$sub_menu], "r");

if($_GET['search_type']=="card_event_id") $where = " AND card_event_id like '%".$_GET['search_value']."%'";

//전체리스트
$count_row = sql_fetch("SELECT count(uid) as cnt 
                                FROM yc4_gift_hoogi
                                WHERE 1=1 $where
                                LIMIT 1");
$total_count = $count_row['cnt'];

//페이징
$rows = 20;// $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$qstr  = "page=$page";
//                                ORDER BY k.uid DESC
$keword_query = sql_query($a= "SELECT *
                                FROM yc4_gift_hoogi
                                WHERE 1=1 $where
                                ORDER BY uid DESC
                                LIMIT $from_record, $rows");

while($keyword_row = sql_fetch_array($keword_query)){
    $list_arr[] = $keyword_row;
}


define('bootstrap', true);
$g4['title'] = "경품후기 관리";
include '../admin.head.php';
?>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <div align="right">
        <form action="<?php echo $_SERVER['PHP_SELF']?>" method="get">
            <table>
                <tr>
                    <td>
                        <select name="search_type">
                            <option value="">검색타입</option>
                            <option value="card_event_id" <?php if($_GET['search_type']=="card_event_id") echo "selected";?> >카드사</option>
<!--                            <option value="od_id" <?php /*if($_GET['search_type']=="od_id") echo "selected";*/?>>주문번호</option>
                            <option value="mb_id" <?php /*if($_GET['mb_id']=="od_id") echo "selected";*/?>>대상자ID</option>-->
                        </select>
                    </td>
                    <td><input name="search_value" value="<?php echo $search_value?>"></td>
                    <td>
                        <input type="submit" value="검색"/>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <br/>
    <table class="table table-hover table-bordered table-condensed table-striped">
        <thead>
        <tr>
            <td class="text-center">No</td>
            <td class="text-center">카드사</td>
            <td class="text-center">대상자ID</td>
            <td class="text-center">경품명</td>
            <td class="text-center">등록일</td>
            <td class="text-center">등록자</td>
            <td class="text-center">후기작성유무</td>
            <td class="text-center">삭제</td>
        </tr>
        </thead>
        <tbody>
        <?php
        $i =1 ;
        foreach ($list_arr as $val){ ?>
            <tr>
                <td><?php echo $i ?></td>
                <td><?php echo $val['card_event_id'] ?></td>
                <td><?php echo $val['mb_id'];?></td>
                <td><?php echo $val['it_name'];?></td>
                <td><?php echo $val['create_dt'];?></td>
                <td><?php echo $val['write_id'];?></td>
                <td><?php echo $val['is_id']=="" ? "" : "O";?></td>

                <td>
                    <a href="#" onclick="gift_hogi_delete('<?php echo $val['uid'] ?>'); return false;"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a>
                </td>
            </tr>
            <?php
            $i++;
        } ?>
        </tbody>
    </table>
    <div align="center">
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                <?php echo get_paging_boot($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?>
            </ul>
        </nav>
    </div>
    <div align="right">
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#excel_modal">엑셀 업로드</button>
    </div>
    <br/>
    <div class="modal fade" id="excel_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="post" enctype="multipart/form-data" action="./gift_hoogi_action.php">
                <input type="hidden" name="mode" value="excel_upload">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">엑셀 업로드</h4>
                </div>
                <div class="modal-body form-horizontal">
                    <div class="row">
                        <label class="control-label col-lg-4">엑셀 파일 업로드</label>
                        <div class="col-lg-8"><input type="file" name="excel_file" class="form-control" required/></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">저장</button>
                    <a href="gift_hoogi_sample.xlsx" class="btn btn-info">샘플 엑셀파일 다운로드</a>
                    <button type="button" class="btn btn-default" data-dismiss="modal">닫기</button>
                </div>
            </form>
        </div>
    </div>
    <style>
        a.btn.btn-primary:link {
            color: #ffffff;
        }
    </style>

    <script>
        function gift_hogi_delete(uid) {
            if (!confirm('해당 내용을 삭제하시겠습니까?\n삭제하실 경우 복구하실 수 없습니다.')) {
                return false;
            }
            var get_str = location.search;
            var qstr = get_str.substr(2, get_str.length - 2);
            qstr = encodeURIComponent(qstr);

            var submit_url = './gift_hoogi_action.php?mode=delete&uid=' + uid + '&qstr=' + qstr + '&return_url=' + encodeURIComponent(location.pathname);

            location.href = submit_url;
        }
    </script>
<?php
include '../admin.tail.php';


