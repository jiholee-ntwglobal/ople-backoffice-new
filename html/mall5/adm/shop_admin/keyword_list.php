<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-06-11
* Time : 오후 5:43
*/

$sub_menu = "500600";
include './_common.php';
auth_check($auth[$sub_menu], "r");


if($_GET['search_type']=="keyword_name") $where = " AND k.keyword_name = '".$_GET['search_value']."'";
if($_GET['category']!="") $where = " AND k.category = '".$_GET['category']."'";
if($_GET['search_type']=="keyword_description") $where = " AND k.keyword_description like '%".$_GET['search_value']."%'";

//전체리스트
$count_row = sql_fetch("SELECT count(k.uid) as cnt 
                                FROM yc4_keyword k 
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
$keword_query = sql_query($a= "SELECT k.*, count(distinct i.uid) as item_cnt
                                FROM yc4_keyword k 
                                LEFT JOIN yc4_keyword_item i ON k.uid = i.keyword_uid
                                WHERE 1=1 $where
                                GROUP BY k.uid
                                ORDER BY k.category, k.sort
                                LIMIT $from_record, $rows");

while($keyword_row = sql_fetch_array($keword_query)){
    $list_arr[] = $keyword_row;
}


define('bootstrap', true);
$g4['title'] = "키워드 관리 리스트";
include '../admin.head.php';
?>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<div align="right">
<form action="<?php echo $_SERVER['PHP_SELF']?>" method="get">
<table>
    <tr>
        <td>
            <select name="category" onchange="form.submit();return false;">
                <option value="">카테고리</option>
                <option value="MAN" <?php if($_GET['category']=="MAN") echo "selected";?> >MAN</option>
                <option value="WOMAN" <?php if($_GET['category']=="WOMAN") echo "selected";?> >WOMAN</option>
                <option value="SILVER" <?php if($_GET['category']=="SILVER") echo "selected";?> >SILVER</option>
                <option value="CHILD" <?php if($_GET['category']=="CHILD") echo "selected";?> >CHILD</option>
            </select>
        </td>
        <td>
            <select name="search_type">
                <option value="">검색타입</option>
                <option value="keyword_name" <?php if($_GET['search_type']=="keyword_name") echo "selected";?> >검색키워드</option>
                <option value="keyword_description" <?php if($_GET['search_type']=="keyword_description") echo "selected";?>>상세설명</option>
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
            <td class="text-center">카테고리</td>
            <td class="text-center">키워드</td>
            <td class="text-center">상세설명</td>
            <td class="text-center">정렬순서</td>
            <td class="text-center">노출여부</td>
            <td class="text-center">등록상품수</td>
            <td class="text-center">관리</td>
        </tr>
        </thead>
        <tbody>
        <?php
        $i =1 ;
        foreach ($list_arr as $val){ ?>
            <tr>
                <td><?php echo $i ?></td>
                <td><?php echo $val['category'] ?></td>
                <td><?php echo htmlspecialchars($val['keyword_name']) ?></td>
                <td><?php echo mb_strimwidth(htmlspecialchars($val['keyword_description']), 0, 80, "...", "utf-8"); ?></td>
                <td><?php echo htmlspecialchars($val['sort']) ?></td>
                <td><?php echo $val['use_yn']?></td>
                <td class="text-right">
                    <strong><?php echo number_format($val['item_cnt']); ?></strong>
                </td>
                <td>
                    <a href="./keyword_insert.php?<?php echo $qstr; ?>&keyword_uid=<?php echo $val['uid'] ?>"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>
                    <a href="#" onclick="keyword_delete('<?php echo $val['uid'] ?>'); return false;"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a>
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
    <a href="./keyword_insert.php" class="btn btn-primary">키워드 등록</a>
</div>
    <br/>

    <style>
        a.btn.btn-primary:link {
            color: #ffffff;
        }
    </style>

    <script>
        function keyword_delete(keyword_uid) {
            if (!confirm('해당 키워드를 삭제하시겠습니까?\n해당 키워드로 등록한 상품도 함께 삭제됩니다.')) {
                return false;
            }
            var get_str = location.search;
            var qstr = get_str.substr(2, get_str.length - 2);
            qstr = encodeURIComponent(qstr);

            var submit_url = './keyword_action.php?mode=delete&keyword_uid=' + keyword_uid + '&qstr=' + qstr + '&return_url=' + encodeURIComponent(location.pathname);

            location.href = submit_url;
        }
    </script>
<?php
include '../admin.tail.php';
