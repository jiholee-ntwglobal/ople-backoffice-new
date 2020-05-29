<?php
/**
 * Created by PhpStorm.
 * User: 강소진
 * Date : 2018-09-28
 * Time : 오후 4:26
 */

$sub_menu = "300667";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$g4[title] = '무게 등록 및 수정';
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<form class="form-horizontal" action="item_weight_type_action.php" onsubmit="return formSubmit();" method="post">
    <input type="hidden" name="mode" value="insert">
    <table class="table">
        <tr>
            <th colspan="2">비가공 식품 등록</th>
        </tr>
        <tr>
            <td width="30%" class="img">종류명</td>
            <td class="text-right">
                <input type="text" class="form-control" name="type_name" >
            </td>
        </tr>
        <tr>
            <td width="30%" class="img">무게 제한(g)</td>
            <td class="text-right">
                <input type="text" class="form-control" name="weight_limit" value="5000">
            </td>
        </tr>
        <tr>
            <td class="text-right" colspan="2">
                <button class="btn-success btn" type="submit">생성</button>
                <button class="btn btn-danger" type="button" onclick="history.back();">목록</button>
            </td>
        </tr>
    </table>
</form>

<script>
    function formSubmit() {
        var type_name =$('input[name=type_name]').val().trim();
        if(type_name==''){
            alert('종류명을 입력해주세요.');
            $('#type_name').focus();
            return false;
        }
        return true;
    }
</script>
<? include_once("$g4[admin_path]/admin.tail.php"); ?>

