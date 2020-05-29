<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-09-18
 * Time: 오후 2:30
 */
$sub_menu = "500550";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");

$_GET['mode'] = trim($_GET['mode'])=='update' ? trim($_GET['mode']) : 'insert';
$coupon_result = array();
$member_price_it_ids = array();

if( $_GET['mode'] == 'update' ){

    if(!trim($_GET['uid'])){
        alert('잘못된 접근방식입니다');
    }

    if ($_GET['uid']) {
        $sql =
            "
            SELECT a.it_id,
                   b.it_maker,
                   b.it_name,
                   b.it_amount_usd,
                   a.member_price,
                   a.start_date,
                   a.end_date
            FROM item_member_price a INNER JOIN yc4_item b ON a.it_id = b.it_id
            where a.uid = '" . sql_safe_query(trim($_GET['uid'])) . "'
            AND ifnull(date_format(a.end_date,'%Y-%m-%d'),'2077-12-31') >=  date_format(now(),'%Y-%m-%d')
               ";

        $member_price_result = sql_fetch($sql);

    }

    if(count($member_price_result)<1){
        alert('수정 할수없습니다');
    }

    $_GET['it_id'] = $member_price_result['it_id'];


    $sql =
        "
            SELECT a.uid ,
                   a.it_id,
                   b.it_maker,
                   b.it_name,
                   b.it_amount_usd,
                   a.member_price,
                   a.start_date,
                   a.end_date
            FROM item_member_price a INNER JOIN yc4_item b ON a.it_id = b.it_id
            where a.it_id = '" . sql_safe_query(trim($member_price_result['it_id'])) . "'           
            AND ifnull(date_format(a.end_date,'%Y-%m-%d'),'2077-12-31') >=  date_format(now(),'%Y-%m-%d')
               ";

    $member_price_results = sql_query($sql);

    while ($member_priceit_id = sql_fetch_array($member_price_results)){

        array_push($member_price_it_ids,$member_priceit_id);

    }

}elseif ( $_GET['mode'] == 'insert' ){

    if (trim($_GET['it_id'])) {

        $sql =
            "
            SELECT a.uid ,
                   a.it_id,
                   b.it_maker,
                   b.it_name,
                   b.it_amount_usd,
                   a.member_price,
                   a.start_date,
                   a.end_date
            FROM item_member_price a INNER JOIN yc4_item b ON a.it_id = b.it_id
            where a.it_id = '" . sql_safe_query(trim($_GET['it_id'])) . "'     
            AND ifnull(date_format(a.end_date,'%Y-%m-%d'),'2077-12-31') >=  date_format(now(),'%Y-%m-%d')
               ";

        $member_price_results = sql_query($sql);

        $int = 1;

        while ($member_priceit_id = sql_fetch_array($member_price_results)){

            array_push($member_price_it_ids,$member_priceit_id);

        }

        $sql ="
        select it_id,
              it_maker,
              it_name,
              it_amount_usd 
         from yc4_item 
         where it_id = '" . sql_safe_query(trim($_GET['it_id'])) . "'
        ";

        $member_price_result = sql_fetch($sql);

        if(count($member_price_result)<1){
            alert('존재하지 않는 상품입니다');
        }

    }

}

//var_dump($_GET['it_id']);
//var_dump($member_price_it_ids);
$g4[title] = "회원할인가 등록 및 수정";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <form class="form-horizontal" action="item_member_price_save.php" method="post" onsubmit="return member_price_data_chk()">
        <input type="hidden" name="mode" value="<?php echo $_GET['mode'];?>">
        <?php if($_GET['mode']=='update'){?>
            <input type="hidden" name="uid" value="<?php echo $_GET['uid'];?>">
        <?php } ?>
            <table class="table">
                <tr>
                    <th>상품 회원 할인가 등록</th>
                    <td class="text-right">
                        <div class="form-inline">
                            ※상품을 검색하세요
                            <input class="form-control" type="text" id="it_id" value="<?php echo htmlspecialchars($_GET['it_id']);?>">
                            <button class="btn btn-primary" type="button" onclick="it_id_search();" >검색</button>
                            <button class="btn btn-primary" type="button"  onclick="location.href='./item_member_price_list.php'">목록</button>
                        </div>
                    </td>
                </tr>
            </table>
        <?php if(count($member_price_result)>0){?>
            <table class="table table-hover">
                <thead>
                <tr>
                    <td><strong>상품코드</strong></td>
                    <td><strong>이미지</strong></td>
                    <td><strong>브랜드</strong></td>
                    <td><strong>상품명</strong></td>
                    <td><strong>판매가</strong></td>
                    <td><strong>회원가</strong></td>
                    <td width="90px;"><strong>기간</strong></td>
                    <td>
                        <?php if($_GET['mode']=='update'){?>
                            <button class="btn btn-success"  type="button" onclick="location.href='./item_member_price_form.php?mode=insert&it_id=<?php echo $member_price_result['it_id'];?>'">추가</button>
                        <?php }?>
                    </td>
                </tr>
                </thead>
                <tbody>
                <?php
                if(count($member_price_it_ids)>0){
                foreach ($member_price_it_ids as $value){ ?>
                    <tr <?php echo trim($_GET['uid'])== $value['uid'] ? 'style="background-color: rgba(255,0,0,0.08);"' :'';?>>
                        <td><a href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $value['it_id'];?>"><?php echo $value['it_id'];?></a></td>
                        <td><img src="http://115.68.20.84/item/<?php echo $value['it_id'];?>_l1" width="80" height="80"></td>
                        <td><?php echo $value['it_maker'];?></td>
                        <td><?php echo get_item_name($value['it_name'],'list');?></td>
                        <td class="text-right"><?php echo $value['it_amount_usd'];?></td>
                        <td class="text-right"><?php echo $value['member_price'];?></td>
                        <td><?php echo $value['start_date'].'<br>~<br>';?><?php echo (trim($value['end_date']) ? $value['end_date'] :"무기한") ;?></td>
                        <td><button class="btn btn-info" type="button" onclick="location.href='./item_member_price_form.php?mode=update&uid=<?php echo $value['uid'];?>'">수정</button></td>
                        </tr>
                <?php }
                }else{
                    echo "<td colspan='7' class='text-center'>멤버 프라이스로 등록되지않았습니다</td>";
                }
                ?>
                </tbody>
            </table>
            <div class="alert <?php echo $_GET['mode']=='update' ? 'alert-danger' : 'alert-success'; ?>">
            <table class="table">
                <tr>
                    <td class="text-center" rowspan="4">
                        <strong style="float: left;"><?php echo $_GET['mode']=='update'?" 수정" : '등록';?></strong>
                        <img src="http://115.68.20.84/item/<?php echo $value['it_id'];?>_l1" width="200" height="200">
                    </td>
                    <td>
                        <div class="row">
                            <label class="col-sm-2 control-label">상품코드</label>
                            <div class="col-sm-5">
                                <?php echo $member_price_result['it_id']; ?>
                                <input type="hidden" value="<?php echo $member_price_result['it_id']; ?>" name="it_id">
                            </div>
                            <div class="col-sm-5">

                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="row">
                            <label class="col-sm-2 control-label">상품명</label>
                            <div class="col-sm-10">
                                <?php echo get_item_name($member_price_result['it_name'], 'list'); ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="row">
                            <label class="col-sm-2 control-label">판매가</label>
                            <div class="col-sm-3 it_amount_usd">
                                <?php echo $member_price_result['it_amount_usd']; ?>
                            </div>
                            <label class="col-sm-2 control-label">회원 할인가</label>
                            <div class="col-sm-5">
                                <input class="form-control" type="text" name="member_price" placeholder="회원할인가" value="<?php echo $member_price_result['member_price'];?>">
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="row">
                            <label class="col-sm-2 control-label">기간</label>
                            <div class="col-sm-5">
                                <input class="form-control from" type="text" id="from"  name="start_date" placeholder="시작날짜" value="<?php echo trim($member_price_result['start_date']);?>" <?php echo $_GET['mode']=='insert'?'':'disabled'?>>
                            </div>
                            <div class="col-sm-5">
                                <input class="form-control to" type="text" id="to"  name="end_date" placeholder="종료날짜" value="<?php echo trim($member_price_result['end_date']);?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-10" style="color: red;">
                                ※종료날짜를 입력하지 않을 경 우 무기한 노출이 됩니다
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="row">
                            <div class="col-sm-12 text-right">
                                <button class="btn btn-success" type="submit">적용</button>
                                <?php if( $_GET['mode'] == 'update' ){ ?>
                                <button class="btn btn-danger" type="button" onclick="delete_member_price()">삭제</button>
                                <?php } ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            </div>
        <? }?>
    </form>
<form id="delete" action="item_member_price_delete.php" method="post">
    <input type="hidden" name="delete_uid">
    <input type="hidden" name="mode" value="delete">
</form>
<script>
    function it_id_search() {
        var it_id = $('#it_id').val().trim();
        if(it_id == ''){
            alert('상품코드를 검색해주세요');
            $('#it_id').focus();
        }else{
            location.href="./item_member_price_form.php?mode=insert&it_id="+it_id;
        }
    }
    function delete_member_price() {
        if(confirm('삭제 하시겠습니까?')){
            var uid = $('input[name=uid]').val().trim();
            $('input[name=delete_uid]').val(uid);
            $('#delete').submit();
        }

    }
    
    function member_price_data_chk() {

        var start_date = $('input[name=start_date]').val().trim();
        var end_date = $('input[name=end_date]').val().trim();
        var yyyymmdd = /^(19|20)\d{2}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[0-1])$/;

        var member_price = $('input[name=member_price]').val().trim();


        if(member_price == ''){
            alert('달러 가격을 입력해주세요');
            $('input[name=member_price]').focus();
            return false;
        }

        if(Number($('.it_amount_usd').text())<Number(member_price)){
            alert('판매가 보다 회원할인가 금액이 더 높습니다');
            $('input[name=member_price]').focus();
            return false;
        }

        if(start_date ==''){
            alert('시작날짜를 선택해주세요');
            $('input[name=start_date]').focus();
            return false;
        }
        if(end_date != '' && !yyyymmdd.test(start_date) ){
            alert('YYYY-MM-DD 형식으로만 가능합니다');
            $('input[name=end_date]').focus();
            return false;
        }

        if(!yyyymmdd.test(start_date)){
            alert('YYYY-MM-DD 형식으로만 가능합니다');
            $('input[name=start_date]').focus();
            return false;
        }

        $('input[name=start_date]').removeAttr('disabled');
        return true;
    }
</script>

    <link rel="stylesheet" href="http://code.jquery.com/ui/1.8.18/themes/base/jquery-ui.css" type="text/css" media="all">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
    <script src="http://code.jquery.com/ui/1.8.18/jquery-ui.min.js" type="text/javascript"></script>
    <script>

        $.datepicker.setDefaults({
            prevText: '이전 달',
            nextText: '다음 달',
            monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
            monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
            dayNames: ['일', '월', '화', '수', '목', '금', '토'],
            dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
            dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
            dateFormat: 'yy-mm-dd',
            showMonthAfterYear: true,
            yearSuffix: '년'
        });

        $(function () {
            var dates = $("#from, #to ").datepicker({
                onSelect: function (selectedDate) {
                    var option = this.id == "from" ? "minDate" : "maxDate",
                        instance = $(this).data("datepicker"),
                        date = $.datepicker.parseDate(
                            instance.settings.dateFormat ||
                            $.datepicker._defaults.dateFormat,
                            selectedDate, instance.settings);
                    dates.not(this).datepicker("option", option, date);
                }
            });
        });

    </script>

<?
include_once("$g4[admin_path]/admin.tail.php");
?>