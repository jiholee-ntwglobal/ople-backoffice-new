<?php
$sub_menu = "100999";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.");
// 수정으로 받은값
$uid = isset($_GET['uid']) ? $_GET['uid'] : "";
$start_ip = isset($_GET['start_ip']) ? $_GET['start_ip'] : "";
$end_ip = isset($_GET['end_ip']) ? $_GET['end_ip'] : "";
$user_explanation = isset($_GET['user_explanation']) ? $_GET['user_explanation'] : "";
$g4['title'] = "ip등록";
include_once ("./admin.head.php");
?>

<?php if ($uid == "") { /*등록일때*/ ?>
    <table border="1" bgcolor="#ffe4c4">
            <form action="ip_manager_write_i.php" onsubmit="return submits()">
            <tr>
                <td align="center" colspan="2">IP 등록 <input type="hidden" name="user_id" value="<?=$member['mb_id']?>"></td>
            </tr>
            <tr>
                <td width="20px">
                    <select id="select_ip" onchange="d()">
                        <option value="1">IP(단일)</option>
                        <option value="2">IP(대역)</option>
                    </select>
                </td>
                <td><input type="text" id="ip_input" class="decimal" name="start_ip" placeholder="예)xxx.xxx.xxx.xxx">
                    <span id="hides01" style="display: none">~</span>
                    <input type="text" id="hides02" class="decimal" style="display: none" name="end_ip" placeholder="예)xxx.xxx.xxx.xxx">
                </td>
            </tr>
            <tr>
                <td align="center" colspan="2">설명</td>
            </tr>
            <tr>
                <td align="center" colspan="2"><input type="text" colspan="2" size="60px" name="user_explanation"></td>
            </tr>
            <tr>
                <td align="center" colspan="2"><input type="submit" value="등록"><input type="button"
                                                                                      onclick=history.back();
                                                                                      value="취소"></td>
            </tr>
            </form>
        </table>
<?php } else {  /*수정일때*/ ?>
    <table border="1" bgcolor="#ffe4c4">
        <form action="ip_manager_write_i.php" onsubmit="return submits()">
            <tr>
                <td align="center" colspan="2">IP 수정 <input type="hidden" name="user_id" value="<?=$member['mb_id']?>">
                    <input type="hidden" name="uid" value="<?php echo $uid; ?>"></td>
            </tr>
            <tr>
                <td width="20px">
                    <?php if ($start_ip == $end_ip) { ?>
                        <select id="select_ip" onchange="d()">
                            <option value="1" selected="selected">IP(단일)</option>
                            <option value="2">IP(대역)</option>
                        </select>
                    <?php } else { ?>
                        <select id="select_ip" onchange="d()">
                            <option value="1">IP(단일)</option>
                            <option value="2" selected="selected">IP(대역)</option>
                        </select>
                    <?php } ?>
                </td>
                <td><input type="text" id="ip_input" class="decimal" name="start_ip" placeholder="예)xxx.xxx.xxx.xxx"
                           value="<?php echo $start_ip; ?>">
                    <span id="hides01" style="display: none">~</span>
                    <input type="text" id="hides02" class="decimal" style="display: none" name="end_ip"
                           placeholder="예)xxx.xxx.xxx.xxx" value="<?php echo $end_ip; ?>">
                </td>
            </tr>
            <tr>
                <td align="center" colspan="2">설명</td>
            </tr>
            <tr>
                <td align="center" colspan="2"><input type="text" colspan="2" size="60px" name="user_explanation"
                                                      value="<?php echo $user_explanation; ?>"></td>
            </tr>
            <tr>
                <td align="center" colspan="2"><input type="submit" value="수정"><input type="button"
                                                                                      onclick=history.back();
                                                                                      value="취소"></td>
            </tr>
        </form>
    </table>
<?php } ?>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script>
    //  selectbox
    $(document).ready(function () {
        var select_ip = $("#select_ip").val();
        if (select_ip == "1") {
            $("#hides01").hide();
            $("#hides02").hide();
        } else {
            $("#hides01").show();
            $("#hides02").show();
        }
    });
    function d() {
        var select_ip = $("#select_ip").val();
        if (select_ip == "1") {
            $("#hides01").hide();
            $("#hides02").hide();
        } else {
            $("#hides01").show();
            $("#hides02").show();
        }
    }
    //  조건
    function submits() {
        var select_ip = $.trim($("#select_ip").val());
        var ip_input = $.trim($("#ip_input").val());
        var hides02 = $.trim($("#hides02").val());
        var ipformat = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/ ;
        if (select_ip == "1") {
            if (ip_input != "") {
                $("#hides02").val("");
                if(ip_input.match(ipformat)) {
                    return true;
                }else{
                    alert('잘못된 아이피를 입력하였습니다.');
                    return false;
                }
            } else {
                alert('아이피를 입력해주세요');
                return false;
            }
        } else if (select_ip == "2") {
            if (ip_input != "" && hides02 != "") {
                if(ip_input.match(ipformat) && hides02.match(ipformat)){
                    return true;
                }else{
                    alert('잘못된 아이피를 입력하였습니다.');
                    return false;
                }
            } else {
                alert('아이피를 입력해주세요');
                return false;
            }
        } else {
            alert('입력해주세요');
            return false;
        }
    }
    // 숫자 . 입력
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