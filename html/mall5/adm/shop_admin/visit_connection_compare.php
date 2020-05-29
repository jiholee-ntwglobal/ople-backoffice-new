<?php
$sub_menu = "800650";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");
$tr='';
if ($_GET['date1']) {
    foreach ($_GET['date1'] as $value) {
        if ($value != '') {
            $row[] = $value;
        }
    }
    foreach ($_GET['date2'] as $value) {
        if ($value != '') {
            $row2[] = $value;
        }
    }
    $rowcnt=count($row);

    if ($rowcnt > 0) {
        $inputt='';
        for($q=1; $q<$rowcnt;$q++){
            $inputt .="<div class='input-group'>
                        <input type='text' class=\"st$q\" name='date1[".$q."]' value='".$row[$q]."'> ~
                        <input type='text' class=\"en$q\" name='date2[".$q."]' value='".$row2[$q]."'>
                        <button onclick='removess(this)' class='btn btn-default' type='button'>-</button>
                    </div>";
        }
        for ($i = 0; $i < $rowcnt; $i++) {
            $sql = " SELECT date_format(vs_date, '%Y') y,date_format(vs_date, '%c') m,date_format(vs_date, '%m') mm,
                          date_format(vs_date, '%e') d,date_format(vs_date, '%d') a,
                          DAYOFWEEK(vs_date) dayweek,
                          sum(vs_count) cnt
                  FROM g4_visit_sum
                  WHERE date_format(vs_date, '%Y-%m-%d') >='$row[$i]'
                  and date_format(vs_date, '%Y-%m-%d') <='$row2[$i]'
                  GROUP BY date_format(vs_date, '%Y%m%d') ";

            $result = sql_query($sql);
            $cntt=0;
            $ma='';
            $masint= 0;
            $mtr='';$mtrs='';
            while ($tdrow = sql_fetch_array($result)) {
                if($ma==''){
                    $ma=$tdrow['m'];
                }
                if($ma==$tdrow['m']){
                     $masint++;
                    $mtrs="<td style='text-align: center; white-space: nowrap; font-weight: bold;' colspan='$masint'>".$tdrow['y']."년 ".$ma."월</td>";
                }
                if($ma!=$tdrow['m']){
                     $mtr.="<td style='text-align: center; white-space: nowrap; font-weight: bold;' colspan='$masint'>".$tdrow['y']."년 ".$ma."월</td>";
                    $ma=$tdrow['m'];
                    $masint=0;
                    $masint++;
                    if($row2[$i]==$tdrow['y']."-".$tdrow['mm']."-".$tdrow['a']){
                        $mtrs="<td style='text-align: center; white-space: nowrap; font-weight: bold;' colspan='1'>".$tdrow['y']."년 ".$ma."월</td>";
                    }
                }
                if($tdrow['cnt']!='' || $tdrow['cnt']!=null){
                    $cntt+=trim($tdrow['cnt']);
                    if($tdrow['dayweek']=='1'){
                        $td .= "<td style=\"text-align: center; font-weight: bold; color: red;\">" . trim($tdrow['d']) . "</td>";
                        $tds .= "<td style=\"text-align: right; color: red;\">" . number_format(trim($tdrow['cnt'])) . "</td>";
                    }elseif($tdrow['dayweek']=='7'){
                        $td .= "<td style=\"text-align: center; font-weight: bold; color: blue;\">" . trim($tdrow['d']) . "</td>";
                        $tds .= "<td style=\"text-align: right; color: blue;\">" . number_format(trim($tdrow['cnt'])) . "</td>";
                    }else{
                        $td .= "<td style=\"text-align: center; font-weight: bold;\">" . trim($tdrow['d']) . "</td>";
                        $tds .= "<td style=\"text-align: right;\">" . number_format(trim($tdrow['cnt'])) . "</td>";
                    }
                }
            }
            $tr .= "<tr>
                        <td></td>
                        <td></td>"
                        .$mtr.$mtrs."
                        <td></td>
                    </tr>
                    <tr>
                        <td rowspan='2'style='text-align: center; font-weight: bold; white-space: nowrap;' >".$row[$i]."<br/>~<br/>".$row2[$i]."</td>
                        <td style='text-align: center; font-weight: bold;'>일</td>
                         " . $td . "
                        <td style='text-align: center; font-weight: bold;'>합계</td>
                    </tr>
                     <tr>
                        <td style='text-align: center; font-weight: bold; white-space: nowrap;'>접속자수</td>"
                        . $tds .
                        "<td style='text-align: right; font-weight: bold;'>".number_format($cntt)."</td>
                    </tr>";
            $td = '';
            $tds = '';
        }
    }
}
$g4[title] = "접속 기간조회 통계";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
<style>
    input{
        color: #555;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        height: 34px;
        font-size: 14px;
        text-align: center;
    }
</style>

<form onsubmit="return sharchs()">
    <input type="hidden" value="<?php echo $rowcnt==''||$rowcnt==null||!$rowcnt||$rowcnt=='0'?'1':$rowcnt; ?>" id="s">
    <input type="hidden" value="<?php echo date('Y-m-d');?>" id="newdate">
    <div class="row">
        <label>기간 (YYYY-MM-DD ~ YYYY-MM-DD)</label>
    </div>
    <input class="st0" type="text" name="date1[0]" value="<?php echo $row[0]==''||$row[0]==null?'':$row[0];?>"> ~
    <input class="en0" type="text" name="date2[0]" value="<?php echo $row2[0]==''||$row2[0]==null?'':$row2[0];?>">
    <input class="btn btn-default" type="button" onclick="adds()" value="기간 +">
    <button class="btn btn-default" type="submit">검색</button>
    <div style="text-align: right;" id=add1>
        <?php echo $inputt;?>
    </div>



</form>
<br><br>
<div style="overflow: auto; width: 1000px;">
<table class="table table-bordered">
    <tbody>
    <?php echo $tr; ?>
    </tbody>
</table>
</div>

<SCRIPT>

    var index = $('#s').val();
    var objCreateNumber = 9;//갯수
    function removess(obj){
        $(obj).parents('.input-group:eq(0)').remove();
        index--;
    }

    function adds() {
        if (index > objCreateNumber) return;
        $('#add1').append("<div class=\"input-group\">"+"<input class='date' type=text name='date1["+index+"]' value=''> ~ "+"<input class='enddate' type=text name='date2["+index+"]' value=''>"+"<button onclick='removess(this)' class='btn btn-default' type='button'>-</button>"+"</div>");
        $(document).find('.date').addClass("st"+index);
        $(document).find('.enddate').addClass("en"+index);
        var dates= $(document).find('.date').removeClass('date').datepicker({
            onSelect: function( selectedDate ) {
                var option = this.className.substring(2,1);
                var st = this.className.substring(0,2);
                var option = st == "st" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" ),
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat || $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
            }
        });
        var dates=$(document).find('.enddate').removeClass('enddate').datepicker({
            onSelect: function( selectedDate ) {
                var option = this.className.substring(2,1);
                var st = this.className.substring(0,2);
                var option = st == "st" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" ),
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
            }

        });
        index++;
    }
    $(function () {
        $('input[name^=date1]').each(function(value){
            var aa= $('input[name^=date1]')[value].className;
            var bb= $('input[name^=date2]')[value].className;
            var dates=$(document).find('.'+aa).removeClass('date').datepicker({
                onSelect: function( selectedDate ) {
                    var option = this.className.substring(2,1);
                    var st = this.className.substring(0,2);
                    var option = st == "st" ? "minDate" : "maxDate",
                        instance = $( this ).data( "datepicker" ),
                        date = $.datepicker.parseDate(
                            instance.settings.dateFormat || $.datepicker._defaults.dateFormat,
                            selectedDate, instance.settings );
                    dates.not( this ).datepicker( "option", option, date );
                }
            });
            var dates=$(document).find('.'+bb).removeClass('date').datepicker({
                onSelect: function( selectedDate ) {
                    var option = this.className.substring(2,1);
                    var st = this.className.substring(0,2);
                    var option = st == "st" ? "minDate" : "maxDate",
                        instance = $( this ).data( "datepicker" ),
                        date = $.datepicker.parseDate(
                            instance.settings.dateFormat ||
                            $.datepicker._defaults.dateFormat,
                            selectedDate, instance.settings );
                    dates.not( this ).datepicker( "option", option, date );
                }
            });
        });
    });
    $.datepicker.setDefaults({
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
        maxDate: "0D",
        minDate: '2008-04-04',
        yearSuffix: '년'

    });
    function sharchs(){
        var a= true;
        var newdate= $('#newdate').val();

        $('input[name^=date1]').each(function(value){
            var date1= $.trim($('input[name^=date1]')[value].value);
            var date2= $.trim($('input[name^=date2]')[value].value);
            if(date1==''||date2==''){
                a= false;
                date1==''?$('input[name^=date1]')[value].focus():$('input[name^=date2]')[value].focus();

                return false;

            } else{

            if('2008-04-03'>=date1) {
                date1='2008-04-04';
                $('input[name^=date1]')[value].value=date1;

            }
            if(newdate<date1){
                date1=newdate;
                $('input[name^=date1]')[value].value=date1;
            }
            if(newdate<date2){
                date2=newdate;
                $('input[name^=date2]')[value].value=date2;
            }
            if('2008-04-03'>=date2) {
                date2='2008-04-04';
                $('input[name^=date2]')[value].value=date2;

            }

            if(date1>date2){
                $('input[name^=date1]')[value].value=date2;
                $('input[name^=date2]')[value].value=date1;
            }
                return true;
            }

        });
        if(a==false) {
            alert('값을 올바르게 넣지 않았습니다.');
            return false;
        }else{
            return true;
        }
    }
</SCRIPT>
<?
include_once("$g4[admin_path]/admin.tail.php");
?>

