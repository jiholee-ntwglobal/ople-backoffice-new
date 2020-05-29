<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-06-11
* Time : 오후 5:42
*/
$sub_menu = "500600";
include './_common.php';
auth_check($auth[$sub_menu], "w");

define('bootstrap', true);
$g4['title'] = "키워드 등록 및 수정";
include '../admin.head.php';

if($_GET['keyword_uid']!=""){

    $keyword_info = sql_fetch("SELECT * FROM yc4_keyword WHERE uid = '".$_GET['keyword_uid']."'");

    # 이벤트 상품 로드 #
    $ev_item_sql = sql_query("
		select 
			a.*,
			b.it_name,b.it_amount,b.it_maker
		from 
			yc4_keyword_item a
			left join
			".$g4['yc4_item_table']." b on a.it_id = b.it_id
		where 
			a.keyword_uid = '".$_GET['keyword_uid']."'
			order by a.uid
	");
    $ev_item_cnt = mysql_num_rows($ev_item_sql);
    $i = 0;
    while($ev_row= sql_fetch_array($ev_item_sql)){
        $it_item_new .= $ev_row['it_id']."<br>";

        $ev_item .= "
			<li class='ui-state-highlight'>
				<input type='hidden' name='real_it_id[".$i."]' value='".$ev_row['it_id']."'/>
				<input type='hidden' name='real_sort[".$i."]' value='".$i."'>
				<p>".$ev_row['it_maker']."</p>
				".$ev_row['it_name']."
			</li>
		";
        $i++;
    }

    $mode = "update";
}else{

    $mode = "insert";
}

# 오플 상품 로드 #
$it_sql = sql_query("select it_id,it_name,it_maker from ".$g4['yc4_item_table']." order by it_time desc limit 10");
while($it = sql_fetch_array($it_sql)){
    $it_item .= "
			<li class='ui-state-default'>
				<input type='hidden' name='it_id[".$i."]' value='".$it['it_id']."'/>
				<input type='hidden' name='sort[".$i."]' value='".$i."'>
				<p>".$it['it_maker']."</p>
				".$it['it_name']."
			</li>
		";
}

?>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>


    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <form action="./keyword_action.php" method="post" onsubmit="return frm_chk(this, event);">
        <input type="hidden" name="mode" value="<?php echo $mode?>">
        <input type="hidden" name="keyword_uid" value="<?php echo $_GET['keyword_uid']?>">
        <div class="panel panel-default">
            <div class="panel-body">
                <table class="table table-hover table-striped">
                    <tbody>
                    <tr>
                        <td>카테고리</td>
                        <td>
                            <?php if($mode=="update") {
                                echo $keyword_info['category']; ?>
                            <input type="hidden" value="<?php echo $keyword_info['category']?>" name="category"
                           <? }else {  ?>
                                <select name="category" class="form-control">
                                    <option value="MAN" <?php if($keyword_info['category']=="MAN") echo "selected"; ?>>MAN</option>
                                    <option value="WOMAN" <?php if($keyword_info['category']=="WOMAN") echo "selected"; ?>>WOMAN</option>
                                    <option value="SILVER" <?php if($keyword_info['category']=="SILVER") echo "selected"; ?>>SILVER</option>
                                    <option value="CHILD" <?php if($keyword_info['category']=="CHILD") echo "selected"; ?>>CHILD</option>
                                </select>
                            <?php } ?>

                        </td>
                    </tr>
                    <tr>
                        <td>키워드명</td>
                        <td><input type="text" class="form-control" name="keyword_name" value="<?php echo htmlspecialchars($keyword_info['keyword_name']) ?>"></td>
                    </tr>
                    <tr>
                        <td>키워드 설명</td>
                        <td><textarea name="keyword_description" class="form-control" rows="10"><?php echo str_replace("<br>","\r\n",htmlspecialchars_decode($keyword_info['keyword_description'])); ?></textarea></td>
                    </tr>
                    <tr>
                        <td>PC 이미지</td>
                        <td><input type="text" name="pc_image_url" class="form-control" value="<?php echo htmlspecialchars($keyword_info['pc_image_url']); ?>"></td>
                    </tr>

                    <tr>
                        <td>PC 오버 이미지</td>
                        <td><input type="text" name="pc_image_url_over" class="form-control" value="<?php echo htmlspecialchars($keyword_info['pc_image_url_over']); ?>"></td>
                    </tr>
                    <tr>
                        <td>모바일 이미지</td>
                        <td><input type="text" name="mobile_image_url" class="form-control" value="<?php echo htmlspecialchars($keyword_info['mobile_image_url']); ?>"></td>
                    </tr>
                    <tr>
                        <td>모바일 오버 이미지</td>
                        <td><input type="text" name="mobile_image_url_over" class="form-control" value="<?php echo htmlspecialchars($keyword_info['mobile_image_url_over']); ?>"></td>
                    </tr>
                    <tr>
                        <td>모바일 배너 이미지</td>
                        <td><input type="text" name="mobile_banner_url" class="form-control" value="<?php echo htmlspecialchars($keyword_info['mobile_banner_url']); ?>"></td>
                    </tr>
                    <tr>
                        <td>정렬 순서</td>
                        <td><input type="text" name="keyword_sort" class="form-control" value="<?php echo htmlspecialchars($keyword_info['sort']); ?>"></td>
                    </tr>
                    <tr>
                        <td>노출 여부</td>
                        <td>
                            <label>Y</label> <input type="radio" name="use_yn" value="y" <?php if($keyword_info['use_yn']=="y") { echo "checked"; } else if ($_GET['keyword_uid']=="") { echo "checked"; } ?>>
                            <label>N</label> <input type="radio" name="use_yn" value="n" <?php if($keyword_info['use_yn']=="n") echo "checked"; ?>>
                        </td>
                    </tr>
                    <tr>
                        <td>관련상품</td>
                        <td>
                            <textarea name="it_item_new" class="form-control" rows="10"><?php echo str_replace("<br>","\r\n",htmlspecialchars_decode($it_item_new)); ?></textarea>

                            <!--<div class='inline-form1'>
                                <input type="text" name='it_id' placeholder='상품코드' value='<?/*=$_GET['it_id']*/?>'/>
                                <input type="text" name='SKU' placeholder='SKU' value='<?/*=$_GET['SKU']*/?>'/>
                                <input type="text" name='it_name' placeholder='상품명' value='<?/*=$_GET['it_name']*/?>'/>
                                <input type="text" name='it_maker' placeholder='브랜드명' value='<?/*=$_GET['it_maker']*/?>'/>
                                <input type="button" value='검색' onclick='item_search();' />
                                <p>오플상품 <span class='item_cnt'></span></p>
                                <ul id="sortable1" class="droptrue">
                                    <?/*=$it_item;*/?>
                                </ul>
                            </div>
                            <div class='inline-form2'>
                                <p>이벤트상품 <span class='event_item_cnt'><?/*=number_format($ev_item_cnt);*/?>개</span></p>
                                <ul id="sortable2" class="dropfalse">
                                    <?/*=$ev_item;*/?>
                                </ul>
                            </div>
                            <ul id="sortable3" class="droptrue">
                            </ul>

                            <br style="clear:both">
                            --><?/*=$contents;*/?>


                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="panel-footer text-center">
                <button type="submit" class="btn btn-primary">저장</button>
                <a href="./keyword_list.php" class="btn btn-info">목록</a>
            </div>
        </div>

    </form>


    <style type="text/css">
        .droptrue {

        }
        .dropfalse{

        }
        .inline-form1{
            float:left;
            width:45%;
        }
        .inline-form2{
            float:right;
            width:45%;
        }
        #sortable1, #sortable2{
            min-height: 400px;
        }
        #sortable2{
            background-color:#ccffff;
        }
    </style>
    <script type="text/javascript">
        $(function() {
            $( "ul.droptrue" ).sortable({
                connectWith: "ul",
                update : function (event,ui) {
                    var tmp_arr = Array();
                    var it_id = $(this).find('input[name^=real_it_id]').val();
                    for(var i=0; i<$('.droptrue li').length; i++){
                        var it_id = $('.droptrue li:eq('+i+')').find('input[name^=real_it_id]').val();

                        tmp_arr[it_id] = true;
                        $('.droptrue li:eq('+i+')').find('input[name^=real_it_id]').attr('name','it_id\['+i+'\]');
                        $('.droptrue li:eq('+i+')').find('input[name^=real_sort]').attr('name','sort\['+i+'\]');
                        $('.droptrue li:eq('+i+')').find('input[name^=real_sort]').val(i);
                    }
                }
            });

            $( "ul.dropfalse" ).sortable({
                connectWith: "ul",
                dropOnEmpty: false,
                update : function (event,ui) {
                    var tmp_arr = Array();
                    var it_id = $(this).find('input[name^=it_id]').val();
                    for(var i=0; i<$('.dropfalse li').length; i++){
                        var it_id = $('.dropfalse li:eq('+i+')').find('input[name^=it_id]').val();

                        tmp_arr[it_id] = true;
                        $('.dropfalse li:eq('+i+')').find('input[name^=it_id]').attr('name','real_it_id\['+i+'\]');
                        $('.dropfalse li:eq('+i+')').find('input[name^=real_it_id]').attr('name','real_it_id\['+i+'\]');
                        $('.dropfalse li:eq('+i+')').find('input[name^=sort]').attr('name','real_sort\['+i+'\]');
                        $('.dropfalse li:eq('+i+')').find('input[name^=sort]').val(i);
                    }
                }
            });

            $( "#sortable1, #sortable2, #sortable3" ).disableSelection();
        });
        function item_search(){
            $.ajax({
                url : './ajax_keyword_item_search.php',
                type : 'post',
                data : {
                    'mode' : 'item_search',
                    'it_id' : $('input[name=it_id]').val(),
                    'SKU' : $('input[name=SKU]').val(),
                    'it_name' : $('input[name=it_name]').val(),
                    'it_maker' : $('input[name=it_maker]').val()
                },success : function ( result ) {
                    $('#sortable1').html(result);
                    var item_cnt = $('#sortable1 li').length;
                    item_cnt = number_format(String(item_cnt));
                    $('.item_cnt').text(item_cnt+'개');

                }

            });
        }

        function frm_chk(frm,e) {

    /*        if(e.keyCode==13 && e.rcElement.type != 'textarea')
            {
                item_search();
                return false;
            }*/

            if (frm.keyword_name.value.trim() == '') {
                alert('키워드명을 입력해 주세요.');
                frm.keyword_name.focus();
                return false;
            }
            if (frm.keyword_name.value.length >20) {
                alert('키워드명의 경우 20자 입력이 불가능합니다.');
                frm.keyword_name.focus();
                return false;
            }

            if (frm.keyword_description.value.trim() == '') {
                alert('키워드 설명 입력해 주세요.');
                frm.keyword_description.focus();
                return false;
            }

            return true;
        }

    </script>
<?php
include '../admin.tail.php';

?>