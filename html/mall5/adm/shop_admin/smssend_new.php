<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-04-29
 * Time: 오전 10:35
 */

$sub_menu = "200600";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

if($_POST['mode'] == 'send'){
    $send_hp = stripslashes($_POST['send_hp']);
    $contents = $_POST['contents'];
    $callback_no = preg_replace("/[^0-9]/",'',$_POST['callback_no']);
    if(count($send_hp) < 1 && !$_POST['send_code']){
        alert('발송 대상이 존재하지 않습니다.');
        exit;
    }
    if(trim($contents) == ''){
        alert('SMS 내용을 입력해 주세요.');
        exit;
    }

    if($_POST['now_fg'] == 'Y'){
        $send_date = "now()";
    }else{
        $send_date_unix = strtotime($_POST['send_dt_y']. $_POST['send_dt_m']. $_POST['send_dt_d']. $_POST['send_dt_h']. $_POST['send_dt_i']);

        if($send_date_unix < time()){
            $send_date = "now()";
        }else{
            $send_date = date('Y-m-d H:i:s',$send_date_unix);
        }

    }


    if($send_date != 'now()'){
        $send_date = "'".$send_date."'";
    }

    $insert_sql = "
      INSERT INTO yc4_sms
      (TR_SENDDATE ,TR_CALLBACK ,TR_MSG ,send_code ,send_addtion_no ,mb_id ,create_dt) VALUES
      (".$send_date.",'".$callback_no."','".sql_safe_query($contents)."','".sql_safe_query($_POST['send_code'])."','".sql_safe_query($send_hp)."','".$member['mb_id']."',now())
    ";

    sql_query($insert_sql);


    alert('SMS 발송 등록이 완료되었습니다.',$_SERVER['PHP_SELF']);
    exit;


}

if($_POST['mode'] == 'ajax'){
    $data = array();
    switch($_POST['type']){
        case 'goodday' :
            $sql = sql_fetch("select count(distinct hp_no) as cnt from yc4_oneday_sms");
            break;
        case 'all_member' :
            $sql = sql_fetch("select count(distinct mb_hp) as cnt from g4_member where mb_sms = 1 and mb_leave_date = ''");
            break;
    }

        $cnt = $sql['cnt'];
        $result = array('cnt' => $cnt);
        echo json_encode($result);
    exit;

}

# 발송 시간 selectbox 처리 #
$send_dt_y = " <option value='".date('Y')."'>".date('Y')."</option> <option value='".(date('Y')+1)."'>".(date('Y')+1)."</option> ";
$send_dt_m = $send_dt_d ='';
for($m=1; $m<=12; $m++){
    $mm = str_pad($m,2,0,STR_PAD_LEFT);
    $send_dt_m .= "<option value='".$mm."' ".($mm == date('m') ? "selected":"").">".$mm."</option>";
}
for($d=1; $d<=date('t'); $d++){
    $dd = str_pad($d,2,0,STR_PAD_LEFT);
    $send_dt_d .= "<option value='".$dd."' ".($dd == date('d') ? "selected":"").">".$dd."</option>";
}
for($h=1; $h<24; $h++){
    $hh = str_pad($h,2,0,STR_PAD_LEFT);
    $send_dt_h .= "<option value='".$hh."' ".($hh == date('H') ? "selected":"").">".$hh."</option>";
}
for($i=1; $i<60; $i++){
    $ii = str_pad($i,2,0,STR_PAD_LEFT);
    $send_dt_i .= "<option value='".$ii."' ".($ii == date('i') ? "selected":"").">".$ii."</option>";
}

$g4['title'] = "SMS 문자전송";
include_once $g4['admin_path']."/admin.head.php";

echo subtitle($g4['title']);
?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return sms_send();" method="post">
    <input type="hidden" name='send_hp'/>
    <input type="hidden" name='mode' value='send'/>

    <div>내용</div>
    <textarea name="contents" id="" cols="30" rows="10" ONKEYUP="byte_check(this,bytes)"></textarea>
    <div>
        <span id="bytes">0 / 90 bytes</span>
    </div>
    <div>발송시간</div>
    <select class="send_time" name="send_dt_y" onchange="set_en_daty();"><?php echo $send_dt_y;?></select>년
    <select class="send_time" name="send_dt_m" onchange="set_en_daty();"><?php echo $send_dt_m;?></select>월
    <select class="send_time" name="send_dt_d"><?php echo $send_dt_d;?></select>일
    <select class="send_time" name="send_dt_h"><?php echo $send_dt_h;?></select>시
    <select class="send_time" name="send_dt_i"><?php echo $send_dt_i;?></select>분
    <input type="checkbox" name="now_fg" value="Y" checked onchange="now_chk();"/> 즉시발송
    <p>과거의 시간으로 설정할 경우 즉시 발송됩니다.</p>
    <div>발송대상</div>
    <input type="radio" name="send_code" value="goodday" onchange="target_add('goodday');"/>
    <label for="send_code"> 오플 굿데이 </label>
    <input type="radio" name="send_code" value="all_member" onchange="target_add('all_member');"/>
    <label for="send_code"> 전체회원(수신동의) </label>
    <input type="radio" name="send_code" value=''/>
    <label for="send_code"> 기타 </label>


    <table>
        <tr>
            <td valign="top">발신번호</td>
            <td>
                <ul class="">
                    <li><input type="text" name='callback_no' value="<?php echo preg_replace("/[^0-9]/",'',$default['de_admin_company_tel']);?>"/></li>
                </ul>
            </td>
            <td></td>
        </tr>
        <tr>
            <td valign="top">

                발송대상 <a href="#" onclick="hp_no_add(); return false;">+(추가)</a>
                <p class="hp_cnt"></p>
            </td>
            <td valign="top">
                <input type="text" name='hp_no_add_input'/>
            </td>
            <td>
                <select class="hp_no" multiple style='width:200px;'></select>
            </td>
        </tr>
    </table>

    <input type="submit" value="전송"/>


</form>




<script language="JavaScript">
    $(function(){
        now_chk();
    });
    function now_chk(){
        if($('input:checkbox[name=now_fg]').prop('checked')){
            $('.send_time').attr('disabled',true);
        }else{
            $('.send_time').attr('disabled',false);
        }
        return ;

    }
    function set_en_daty(){
        var y = $('select[name=send_dt_y]').val();
        var m = $('select[name=send_dt_m]').val();
        var last_daty = getMonthEndDate(y,m);

        var result_option = "";
        for(var d=1; d<=last_daty; d++){
            var dd = d;
            if(String(d).length == 1){
                dd = '0' + d;
            }
            result_option += "<option value='"+dd+"'>"+dd+"</option>";
        }
        $('select[name=send_dt_d]').html(result_option);
        return;
    }
    function getMonthEndDate(year, month) { // 말일 구하는 함수
        var dt = new Date(year, month, 0);
        return dt.getDate();
    }
    function sms_send(){
        if(!confirm('SMS를 발신하시겠습니까?')){
            return false;
        }
        if(hp_no_cnt_chk() < 1 && $('input[name=send_code]').val() == ''){
            alert('발송 대상이 없습니다.');
            return false;
        }

        hp_no_implode();
        if($('textarea[name=contents]').val() == ''){
            alert('메세지 내용을 작성해 주세요.');
            $('textarea[name=contents]').focus();
            return false;
        }
    }
    function hp_no_implode(){
        var hp_no = new Array();
        $('.hp_no>option').each(function(){
            hp_no.push($(this).val());
        });
        var result = JSON.stringify(hp_no);
        $('input[name=send_hp]').val(result);
        return;

    }
    function hp_no_cnt_chk(){
        var cnt = $('select.hp_no>option').length;
        $('.hp_cnt').text(cnt+'명');
        return cnt;

    }
    function hp_no_del(t){
        $(t).remove();
        hp_no_cnt_chk();
        return;
    }
    function hp_no_add(){
        var hp_no = $('input[name=hp_no_add_input]').val().replace(/[^0-9]/,'');
        if(hp_no != '') {
            $('select.hp_no').prepend("<option value=\"" + hp_no + "\" ondblclick=\"hp_no_del(this);\"'>" + hp_no + "</option>");
            $('input[name=hp_no_add_input]').val('');
        }else{
            alert('휴대폰 번호가 올바르지 않습니다.');
        }
        hp_no_cnt_chk();
        return;
    }
    function target_add(type){
        $.ajax({
            url : '<?php echo $_SERVER['PHP_SELF']?>',
            type : 'POST',
            data_type : 'json',
            data : {
                'mode' : 'ajax',
                'type' : type
            },success : function ( json ){
                var data = $.parseJSON(json) ;

                if(!confirm('발송대상 '+data.cnt+'명 입니다. 발송 대상에 추가하시겠습니까?')){
                    $('input:radio[name=send_code][value=]').prop('checked',true);
                    return false;

                }


                /*
                if($('select.hp_no>option').length>0){
                    if(confirm('발송 대상에 입력된 데이터가 존재합니다 삭제 후 추가하시겠습니까?')){
                        $('select.hp_no').empty();
                    }
                }
                for(var i=0; i<data.cnt; i++){
                    data.data[i] = data.data[i].replace(/[^0-9]/,'');
                    $('select.hp_no').prepend("<option value=\""+data.data[i]+"\" ondblclick=\"hp_no_del(this);\">"+data.data[i]+"</option>");
                }
                */

                return;
            }
        });

    }
    function byte_check(cont, bytes)
    {
        var i = 0;
        var cnt = 0;
        var exceed = 0;
        var ch = '';

        for (i=0; i<cont.value.length; i++) {
            ch = cont.value.charAt(i);
            if (escape(ch).length > 4) {
                cnt += 2;
            } else {
                cnt += 1;
            }
        }

        //byte.value = cnt + ' / 80 bytes';
        bytes.innerHTML = cnt + ' / 90 bytes';

        if (cnt > 90) {
            exceed = cnt - 90;
            alert('메시지 내용은 80바이트를 넘을수 없습니다.\r\n작성하신 메세지 내용은 '+ exceed +'byte가 초과되었습니다.\r\n초과된 부분은 자동으로 삭제됩니다.');
            var tcnt = 0;
            var xcnt = 0;
            var tmp = cont.value;
            for (i=0; i<tmp.length; i++) {
                ch = tmp.charAt(i);
                if (escape(ch).length > 4) {
                    tcnt += 2;
                } else {
                    tcnt += 1;
                }

                if (tcnt > 90) {
                    tmp = tmp.substring(0,i);
                    break;
                } else {
                    xcnt = tcnt;
                }
            }
            cont.value = tmp;
            //byte.value = xcnt + ' / 80 bytes';
            bytes.innerHTML = xcnt + ' / 90 bytes';
            return;
        }
    }
</script>

<?php
include_once $g4['admin_path']."/admin.tail.php";
?>
