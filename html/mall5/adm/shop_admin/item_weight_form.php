<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-07-11
 * Time: 오전 10:41
 */
$sub_menu = "300123";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");

$g4[title] = '무게 등록 및 수정';
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<form class="form-horizontal" action="item_weight_action.php" onsubmit="return datachk();" method="post">
<table class="table">
    <tr>
        <th >비가공 식품 등록</th>
        <td class="text-right">
            <div class="form-inline">
                <input type="hidden" name="mode">
                ※먼저 상품을 검색 해주세요
                <input type="text" id="it_id" class="form-control" placeholder="오플상품코드">
                <input type="hidden" name="it_id">
                <button class="btn btn-pirmary" onclick="ajax_item_data()" type="button">검색</button>
            </div>
        </td>
    </tr>
    <tr>
        <td rowspan="3" width="30%" class="img">이미지</td>
        <td>
            <label class="col-sm-2 control-label">상품명</label>
            <div class="col-sm-10 control-label itname" >

            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="form-inline">
                <label class="col-sm-2 control-label">종류</label>
                <div class="col-sm-10 control-label">
                    <select class="form-control" name="type">
                        <?php
                        $sql = "SELECT *  FROM yc4_weight_type_info ORDER BY type_name ASC";
                        $result = sql_query($sql);
                        $weight_item_list_excel = array();
                        while ($row = sql_fetch_array($result)){
                            ?>
                            <option value="<?php echo $row['weight_type_id'] ?>"><?php echo $row['type_name']?></option>
                        <?php } ?>
                </select>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="form-inline">
                <div class="col-sm-6">
                    <label class="control-label">무게</label>
                    <p>   1g = 1g, 1oz = 28.349523g, 1lb = 453.59237g</p>
                    <p>무게 (무게 단위를 oz, lb를 할 경우 g으로 바꿔서 소수점자리 반올림)</p>
                </div>
                <div class="col-sm-6 control-label text-right">
                    <input type="text" class="form-control" required name="weight" onkeyup="weight_chk();">
                    <select  class="form-control" name="weight_unit" onchange="weight_chk()">
                        <option value="g">g</option>
                        <option value="oz">oz</option>
                        <option value="lb">lb</option>
                    </select>
                    <span id="weight_text" style="color: red"></span>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td class="text-right" colspan="2">
            <button class="btn-success btn" type="submit">생성</button><button class="btn btn-danger" type="button" onclick="history.back();">목록</button>
        </td>
    </tr>
</table>
</form>

<script>
    $("#it_id").keydown(function (key) {

        if(key.keyCode == 13){//키가 13이면 실행 (엔터는 13)
            ajax_item_data();
        }

    });
    function weight_chk() {

        var weight = $('input[name=weight]').val().trim();
        if(weight != '') {
            if (!$.isNumeric(weight)) {
                alert('숫자만 입력해주세요 ');
            }
        }
        var weight_unit = $('select[name=weight_unit]').val();
        if(weight_unit=='g'){
            weight_unit = 1;
        }else if(weight_unit=='oz'){
            weight_unit = 28.349523;
        }else if(weight_unit=='lb'){
            weight_unit =  453.59237;
        }else{
            alert('개발팀의 문의해주시기 바랍니다5');
            return false;
        }
        weight= Math.round(weight_unit*weight);
        $('#weight_text').text(weight+'g');
    }
    function ajax_item_data() {
        var it_id = $('#it_id').val().trim();
        if(it_id != ''){
            $.ajax({
                type: 'post'
                , url: './item_weight_action.php'
                , data: {
                    mode : 'it_id_chk',
                    it_id : it_id
                }
                , datatype: 'json'
                , success: function (data) {
                    var data =  JSON.parse(data);
                    if(data.fg == false){
                        $("#it_id").focus();
                        alert('없는 상품이거나 등록된 상품입니다.');
                    }else{
                        $('.img').html(data.img);
                        $('.itname').html(data.name);
                        $('input[name=it_id]').val($.trim(it_id));
                    }
                }
            });
        }else {
            alert('상품 코드를 입력해주세요 ');
            $('#it_id').focus();
        }
    }
    function datachk() {
        var it_id =$('input[name=it_id]').val().trim();
        if(it_id==''){
            alert('오플 상품코드를 검색해주세요');
            $('#it_id').focus();
            return false;
        }
        var weight =$('input[name=weight]').val().trim();
        if(weight==''){
            alert('오플 상품코드를 검색해주세요');
            $('input[name=weight]').focus();
            return false;
        }
        $('input[name=mode]').val('insert');
        return true;
    }
</script>
<?
include_once("$g4[admin_path]/admin.tail.php");
?>

