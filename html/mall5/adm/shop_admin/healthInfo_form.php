<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-06-20
 * Time: 오후 6:00
 */
include_once("./_common.php");
$sub_menu = "600970";
auth_check($auth[$sub_menu], "w");

$g4[title] = "건강정보 관리자";
define('bootstrap', true);

$uid = trim($_GET['uid']) ? trim($_GET['uid']) : '';
$health_info =array();
//수정시 데이터 select
if($uid) {

    //health_info 본문
    $health_info = sql_fetch("
        SELECT * 
        FROM health_info 
        WHERE health_info_uid ='{$uid}';
    ");

    //health_info_product 추천상품
    $health_info_porduct_result = sql_query("
        SELECT a.it_id, a.sort, b.it_name
        FROM health_info_product    a
        LEFT OUTER JOIN yc4_item b ON a.it_id = b.it_id
        WHERE a.health_info_uid = '{$uid}'
        ORDER BY a.sort ASC
     ");
    $health_info_porduct= array();
    while ($row = sql_fetch_array($health_info_porduct_result)){
        $health_info_porduct[] =$row;
    }

    //health_info_contents 서브 컨텐츠
    $health_info_contents_result = sql_query("
        SELECT *
        FROM health_info_contents
        WHERE health_info_uid = '{$uid}'
        order by health_info_contents_uid asc
     ");
    $health_info_contents= array();
    $health_info_contents_product =array();
    while ($row = sql_fetch_array($health_info_contents_result)){
        $health_info_contents[] =$row;

        //health_info_contents_product 서브 컨텐츠 상품
        $health_info_contents_product_result = sql_query("
            SELECT a.*,b.it_name
            FROM health_info_contents_product a
            left outer join yc4_item b on a.it_id = b.it_id
            WHERE health_info_contents_uid = '{$row['health_info_contents_uid']}'
          ORDER BY sort ASC
        ");
        while ($health_info_contents_product_row = sql_fetch_array($health_info_contents_product_result)){
            $health_info_contents_product[$row['health_info_contents_uid']][$health_info_contents_product_row['sort']] = $health_info_contents_product_row;
        }
    }

    //서브 컨텐츠 번호
    $health_info_contents_max_cnt = sql_fetch("
        SELECT max(health_info_contents_uid) cnt
        FROM health_info_contents
        WHERE health_info_uid = '{$uid}'
     ");
    $sub_contents_cnt = 1;
}

include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div class="row">
    <div class="col-lg-12">
        <h4>건강정보</h4>
    </div>
</div>

<form class="form" action="healthInfo_action.php" method="post">
    <input name="mode" value="<? echo $uid == '' ? 'insert' : 'update'; ?>" type="hidden">
    <?php if($uid){?><input name="uid" value="<?php echo $uid;?>" type="hidden"> <?php } ?>
    <div class="table">
        <div>
            <div class="row col-lg-12"><h4 class="title">카테고리</h4></div>
            <div class="row col-lg-12">
                <select class="form-control" name="category_code">
                    <option value="1" <?php echo $health_info['category_code']=='1'? 'selected': '';?><>증상별</option>
                    <option value="2" <?php echo $health_info['category_code']=='2'? 'selected': '';?>>성분별</option>
                    <option value="3" <?php echo $health_info['category_code']=='3'? 'selected': '';?>>가이드</option>
                </select>
            </div>
        </div>
        <div>
            <div class="row col-lg-12"><h4 class="title">리스트 제목</h4></div>
            <div class="row col-lg-12"><input class="form-control" type="text" name="list_title" required value="<?php echo isset($health_info['list_title'])? $health_info['list_title']: '';?>"></div>
        </div>
        <div>
            <div class="row col-lg-12"><h4 class="title">리스트 부제목</h4></div>
            <div class="row col-lg-12"><input class="form-control" type="text" name="list_subtitle" required value="<?php echo isset($health_info['list_subtitle'])? $health_info['list_subtitle']: '';?>"></div>
        </div>
        <div>
            <div class="row col-lg-12"><h4 class="title">리스트 이미지 URL</h4></div>
            <div class="row col-lg-12"><input class="form-control" type="text" name="list_imamge" required value="<?php echo isset($health_info['list_image'])? $health_info['list_image']: '';?>"></div>
        </div>
        <div>
            <div class="row col-lg-12"><h4 class="title">본문 부제목1</h4></div>
            <div class="row col-lg-12"><input class="form-control" type="text" name="content_title1" required value="<?php echo isset($health_info['content_title1'])? $health_info['content_title1']: '';?>"></div>
        </div>
        <div>
            <div class="row col-lg-12"><h4 class="title">본문 부제목2</h4></div>
            <div class="row col-lg-12"><input class="form-control" type="text" name="content_title2" value="<?php echo isset($health_info['content_title2'])? $health_info['content_title2']: '';?>"></div>
        </div>
        <div>
            <div class="row col-lg-12"><h4 class="title">본문 이미지 URL</h4></div>
            <div class="row col-lg-12"><input class="form-control" type="text" name="content_image" required value="<?php echo isset($health_info['content_image'])? $health_info['content_image']: '';?>"></div>
        </div>
        <div>
            <div class="row col-lg-12"><h4 class="title">본문 내용</h4></div>
            <div class="row col-lg-12"><textarea class="form-control" rows="5" name="content"><?php echo isset($health_info['content'])? $health_info['content']: '';?></textarea></div>
        </div>
        <div>
            <div class="row col-lg-12"><h4 class="title">본문 키워드</h4></div>
            <div class="row col-lg-12"><input class="form-control" type="text" name="keyword" value="<?php echo isset($health_info['keyword'])? $health_info['keyword']: '';?>"></div>
        </div>
        <div class="">
            <div class="row col-lg-12"><h4 class="title" style="display: block">서브컨텐츠 정보</h4></div>
            <div class="row col-lg-12 text-right">
                <button class="btn btn-danger" type="button" onclick="sub_contents_add()">서브컨텐츠 추가</button>
            </div>
        </div>
        <div class="row col-lg-12" id="sub_contents">
            <?php if(!empty($health_info_contents)){ ?>
                <?php foreach ($health_info_contents as $row){ ?>
                    <pre class="text-right" id='sub_contents_pre<?php echo $row['health_info_contents_uid'];?>'>
                        <button class="btn" type="button" onclick="sub_contents_remove('<?php echo $row['health_info_contents_uid'];?>')" >서브컨텐츠 삭제</button>
                        <table class="table gray">
                            <tr>
                                <td rowspan="8" class="text-center"><h5 class="title">서브 컨텐츠</h5></td>
                            </tr>
                            <tr>
                                <td><h5 class="title">서브 제목</h5></td>
                                <td><input class="form-control" name='contents_title[<?php echo $row['health_info_contents_uid'];?>]' value="<?php echo $row['contents_title'];?>"></td>
                            </tr>
                            <tr>
                                <td><h5 class="title">서브 이미지 URL</h5></td>
                                <td><input class="form-control" name='contents_image[<?php echo $row['health_info_contents_uid'];?>]' required value="<?php echo $row['contents_image'];?>"></td>
                            </tr>
                            <tr>
                                <td><h5 class="title">서브 내용</h5></td>
                                <td><textarea class="form-control" rows="5" name='contents_content[<?php echo $row['health_info_contents_uid'];?>]' required> <?php echo $row['contents_content'];?></textarea></td>
                            </tr>
                            <tr>
                                <td rowspan="3"><h5 class="title">추천상품</h5></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="row">
                                        <div class='col-lg-1'><label>상품코드</label></div>
                                        <div class='col-lg-5'><input class="form-control" id='contents_it_id<?php echo $row['health_info_contents_uid'];?>'></div>
                                        <div class='col-lg-1'><label>우선순위</label></div>
                                        <div class='col-lg-5'><input class="form-control" id='contents_sort<?php echo $row['health_info_contents_uid'];?>' type='number'></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="row">
                                        <div class='col-lg-2'><label>상품<br>부가설명</label></div>
                                        <div class='col-lg-10'><input class="form-control" width='90%' id='contents_product_desc<?php echo $row['health_info_contents_uid'];?>'></div>
                                    </div>
                                    <div class="row">
                                        <div class='col-lg-12 text-right'><button type='button' onclick="add_contents_it_id('<?php echo $row['health_info_contents_uid'];?>')">추가</button></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center" colspan='2'>
                                    <table class="" width="100%" border="1">
                                        <tr>
                                            <td width='25' class='text-center'>
                                                <b>순위</b>
                                            </td>
                                            <td width='80' class='text-center'>
                                                상품코드
                                            </td>
                                            <td class='text-center'>
                                                제품명
                                            </td>
                                            <td class='text-center'>
                                                부가설명
                                            </td>
                                            <td width='31' class='text-center'></td>
                                        </tr>
                                        <tbody id='subcontents_item_list<?php echo $row['health_info_contents_uid'];?>'>
                                        <?php if(isset($health_info_contents_product[$row['health_info_contents_uid']])) {?>
                                            <?php foreach ($health_info_contents_product[$row['health_info_contents_uid']] as $item) {?>
                                                <tr id='subcontents_tr<?php echo $item['health_info_contents_uid'].$sub_contents_cnt ;?>'>
                                                    <td width='25' class='text-center sort_change'>
                                                        <span><?php echo $item['sort'];?></span>
                                                    </td>
                                                    <td width='80' class='text-center'>
                                                        <?php echo $item['it_id'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo $item['it_name'];?>
                                                    </td>
                                                    <td class="product_desc">
                                                         <input type='hidden' value='<?php echo $item['product_desc'];?>' name='contents_product_desct_id<?php echo $item['health_info_contents_uid'];?>[<?php echo $sub_contents_cnt ;?>]'>
                                                        <?php echo $item['product_desc'];?>
                                                    </td>
                                                    <td width='31' class="product_item">
                                                        <input type='hidden' value='<?php echo $item['it_id'];?>' name='subcontents_item<?php echo $item['health_info_contents_uid'];?>[<?php echo $sub_contents_cnt ;?>]'>
                                                        <button type='button' onclick="sub_contents_item_remove('<?php echo $item['health_info_contents_uid'].$sub_contents_cnt++;?>','<?php echo $item['health_info_contents_uid'];?>')">삭제</button>
                                                    </td>
                                                </tr>
                                            <?php } $sub_contents_cnt =1;?>

                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </pre>
                <?php } ?>
            <?php } ?>
        </div>
        <div class="row col-lg-12 text-right sub_contents_add1">
            <?php if(count($health_info_contents)>0){?>
                <button class="btn btn-danger" type="button" onclick="sub_contents_add()">서브컨텐츠 추가</button>
            <?php } ?>
        </div>
        <div>
            <div class="row col-lg-12"><h4 class="title">추천상품 제목</h4></div>
            <div class="row col-lg-12"><input class="form-control" type="text" name="product_title" value="<?php echo isset($health_info['product_title'])? $health_info['product_title']: '';?>"></div>
        </div>
        <div>
            <div class="row col-lg-12"><h4 class="title">추천상품 리스트</h4></div>
            <div class="row ">
                <div class="col-lg-5 text-center"><textarea class="form-control" rows="5" id="product_it_id"
                                                            placeholder="※ 상품코드를 엔터구분으로 입력해주세요."></textarea>
                    <button type="button" onclick="product_item_insert()">상품등록</button>
                    <b style="col"></b>
                </div>
                <div class="col-lg-7">
                    <b style="color: red;">▼ 여기에 노출된 상품만 등록이 됩니다</b>
                    <table class="" width="100%" border="1">
                        <thead>
                        <tr>
                            <td width="25" class="text-center"><b>순위</b></td>
                            <td width="80" class='text-center'><b>상품코드</b></td>
                            <td class='text-center'><b>제품명</b></td>
                            <td width="20"></td>
                        </tr>
                        </thead>
                        <tbody id="product_item_list">
                        <?php if(!empty($health_info_porduct)){ ?>
                            <?php foreach ($health_info_porduct as $row){ ?>
                                <tr id='product_tr<?php echo $row['sort'];?>'>
                                    <td width='25' class='text-center sort_change'>
                                        <span><?php echo $row['sort'];?></span>
                                    </td>
                                    <td width='80' class='text-center product_item'>
                                        <input type='hidden' value='<?php echo $row['it_id'];?>' name='product_item[<?php echo $row['sort'];?>]'>
                                        <?php echo $row['it_id'];?>
                                    </td>
                                    <td><?php echo $row['it_name'];?></td>
                                    <td><button type='button' onclick="product_remove('<?php echo $row['sort'];?>')">삭제</button></td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div>
            <div class="row col-lg-12"><h4 class="title">상태</h4></div>
            <div class="row col-lg-12"><select class="form-control" name="status" required>
                    <option value="1" <?php echo isset($health_info['status']) && $health_info['status'] == '1'? 'selected' : '';?>>노출</option>
                    <option value="0" <?php echo !$health_info['status'] ? 'selected': '';?>>비노출</option>
                </select>
            </div>
        </div>
        <div>
            <div class="row col-lg-12"><h4 class="title">등록일자</h4></div>
            <div class="row col-lg-12"><span id="create_date"><?php echo isset($health_info['create_date'])? $health_info['create_date']: '';?></span></div>
        </div>
    </div>
    <div class="row col-lg-12 text-right">
        <button class="btn btn-success" type="submit">적용</button>
        <button class="btn btn-danger" type="button" onclick="history.back()">취소</button>
    </div>
</form>
<script>
    function subcontentadd() {
        if($('#sub_contents > pre').length>0){

        }

    }
    product_item_list_tr_length = $('#product_item_list > tr').length;
    if(product_item_list_tr_length >0){
        product_cnt_name = product_item_list_tr_length+1;
        product_cnt =  product_item_list_tr_length+1;
    }else{
        product_cnt = 1;
        product_cnt_name = 1;
    }

    sub_contents_loengrh = <?php echo $health_info_contents_max_cnt['cnt']? $health_info_contents_max_cnt['cnt'] :'1';?>;
    //추천상품 등록
    function product_item_insert() {
        var productitem = $("#product_it_id").val().trim();
        var result = productitem.split("\n");
        if(result==''){
            return false;
        }
        var uniqueNames = [];
        $.each(result, function(i, el){
            el= el.trim();
            if(el!='') {
                if ($.inArray(el, uniqueNames) === -1) {
                    uniqueNames.push(el);
                }
            }
        });
        $.ajax({
            url:"healthInfo_itemchk.php",
            type:'POST',
            data:{
                "item" : uniqueNames ,
                "mode" : 'it_id_chk'
            },
            dataType : 'json',
            success:function(data){
                if(data['fg'] == false){
                    alert(data['msg']+' 오플에 존재하지 않는 상품코드 입니다.');
                }else{
                    if($('input[name^=product_item]').length >0){
                        $('input[name^=product_item]').each(function () {
                            if(uniqueNames.indexOf($(this).val())!== -1){
                                uniqueNames.splice($.inArray($(this).val(),uniqueNames),1);
                            }
                        })
                    }
                    for (var i = 0; i < uniqueNames.length; i++) {
                        $('#product_item_list').append("" +
                            "<tr id='product_tr"+product_cnt_name+"'>" +
                            "<td width='25' class='text-center sort_change'>" +
                            "<span>"+product_cnt+"</span>" +
                            "</td>" +
                            "<td width='80' class='text-center product_item' >" +
                            uniqueNames[i] +
                            "<input type='hidden' value='"+uniqueNames[i]+"' name='product_item["+product_cnt+"]'>"+
                            "</td>" +
                            "<td>" +
                            data[uniqueNames[i]]+
                            "</td>" +
                            "<td width='31'>" +
                            "<button type='button' onclick=\"product_remove('"+product_cnt_name++ +"')\">삭제</button>"+
                            "</td>" +
                            "</tr>" +
                            "");
                        product_cnt++;
                    }
                }
            }
        });

    }
    //추천상품리스트 상품삭제
    function product_remove(id) {
        $('#product_tr'+id).remove();
        for (var i = 0; i < $('#product_item_list > tr').length; i++) {
            product_cnt = i + 1;
            $('#product_item_list > tr:eq(' + i + ') > .sort_change > span').text(product_cnt);
            $('#product_item_list > tr:eq(' + i + ') > .product_item > input').removeAttr('name').attr('name','product_item['+product_cnt+']');
        }
        product_cnt++;

    }
    //서브 컨텐츠 등록
    function sub_contents_add() {
        sub_contents_loengrh++;
        $.ajax({
            url: "healthinfo_ajax.php",
            type: 'GET',
            data: 'sub_contents_pre='+sub_contents_loengrh,
            dataType: 'Html',
            success: function (data) {
                if(data!=false){
                    $('#sub_contents').append(data);
                    if($('#sub_contents > pre').length==1){
                        $('.sub_contents_add1').html(''+
                            '<button class="btn btn-danger" type="button" onclick="sub_contents_add()">서브컨텐츠 추가</button>'+
                        '');
                    }
                }
            }
        });
    }
    //서브컨텐츠 삭제

    function sub_contents_remove(id) {
        $('#sub_contents_pre' + id).remove();
        if($('#sub_contents > pre').length==0){
            $('.sub_contents_add1').html('');
        }
    }
    function sub_contents_item_remove(id,tbid) {
        $('#subcontents_tr' + id).remove();
        var product_cnt = 0;
        for (var i = 0; i < $('#subcontents_item_list'+tbid+' > tr').length; i++) {
            product_cnt = i + 1;
            $('#subcontents_item_list'+tbid+' > tr:eq(' + i + ') > .sort_change > span').text(product_cnt);
            $('#subcontents_item_list'+tbid+' > tr:eq(' + i + ') > .product_desc > input').removeAttr('name').attr('name','contents_product_desct_id'+tbid+'['+product_cnt+']');
            $('#subcontents_item_list'+tbid+' > tr:eq(' + i + ') > .product_item > input').removeAttr('name').attr('name','subcontents_item'+tbid+'['+product_cnt+']');
        }
    }
    function add_contents_it_id(id) {
        var sub_contents_cnt =  $('#subcontents_item_list'+id+' > tr').length;
        if(sub_contents_cnt < 1){
            sub_contents_cnt = 1;
        }else{
            sub_contents_cnt = sub_contents_cnt+1;
        }
        var contents_it_id = $('#contents_it_id'+id).val().trim();
        if(contents_it_id==''){
            $('#contents_it_id'+id).focus();
            alert('상품코드를 입력해주세요.');
            return;
        }
        var contents_sort = $('#contents_sort'+id).val();
        if(contents_sort==''){
            $('#contents_sort'+id).focus();
            alert('우선순위 입력해주세요.');
            return;
        }
        var contents_product_desct_id = $('#contents_product_desc'+id).val().trim();
        if(contents_product_desct_id==''){
            $('#contents_product_desc'+id).focus();
            alert('부가설명 입력해주세요.');
            return;
        }
        if($('input[name^=subcontents_item'+id+']').length >0){
            $('input[name^=subcontents_item'+id+']').each(function () {
                if($(this).val()==contents_it_id){
                    return contents_it_id='';
                }
            })
        }
        if(contents_it_id==''){
            $('#contents_it_id'+id).focus();
            alert('중복되는 상품코드입니다.');
            return;
        }
        $.ajax({
            url:"healthInfo_itemchk.php",
            type:'POST',
            data:{
                "item" : contents_it_id ,
                "mode" : 'it_id_chk'
            },
            dataType : 'json',
            success:function(data){
                if(data['fg'] == false){
                    alert(data['msg']+' 오플에 존재하지 않는 상품코드 입니다.');
                }else{
                    $('#subcontents_item_list'+id).append("" +
                        "<tr id='subcontents_tr"+id+sub_contents_cnt+"'>" +
                        "<td width='25' class='text-center sort_change'>" +
                        "<span>"+contents_sort+"</span>" +
                        "</td>" +
                        "<td width='80' class='text-center'>" +
                        contents_it_id +
                        "</td>" +
                        "<td>" +
                        data[contents_it_id]+
                        "</td>" +
                        "<td class='product_desc'>" +
                        contents_product_desct_id+
                        "<input type='hidden' value='"+contents_product_desct_id+"' name='contents_product_desct_id"+id+"["+sub_contents_cnt+"]'>"+
                        "</td>" +
                        "<td width='31' class='product_item'>" +
                        "<input type='hidden' value='"+contents_it_id+"' name='subcontents_item"+id+"["+sub_contents_cnt+"]'>"+
                        "<button type='button' onclick=\"sub_contents_item_remove('"+id+sub_contents_cnt++ +"','"+id+"')\">삭제</button>"+
                        "</td>" +
                        "</tr>" +
                        "");
                    var table, rows, switching, i, x, y, shouldSwitch;
                    table = document.getElementById('subcontents_item_list'+id);
                    switching = true;
                    while (switching) {
                        switching = false;
                        rows = table.getElementsByTagName("TR");
                        for (i = 0; i < (rows.length-1); i++) {
                            shouldSwitch = false;
                            x = rows[i].getElementsByTagName("TD")[0].getElementsByTagName('SPAN');
                            y = rows[i + 1].getElementsByTagName("TD")[0].getElementsByTagName('SPAN') ;
                            if (parseInt(x[0].innerText) > parseInt(y[0].innerText)) {
                                shouldSwitch= true;
                                break;
                            }
                        }
                        if (shouldSwitch) {
                            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                            switching = true;
                        }
                    }
                    var product_cnt = 0;
                    for (var i = 0; i < $('#subcontents_item_list'+id+' > tr').length; i++) {
                        product_cnt = i + 1;
                        $('#subcontents_item_list'+id+' > tr:eq(' + i + ') > .sort_change > span').text(product_cnt);
                        $('#subcontents_item_list'+id+' > tr:eq(' + i + ') > .product_desc > input').removeAttr('name').attr('name','contents_product_desct_id'+id+'['+product_cnt+']');
                        $('#subcontents_item_list'+id+' > tr:eq(' + i + ') > .product_item > input').removeAttr('name').attr('name','subcontents_item'+id+'['+product_cnt+']');
                    }
                }
            }
        });
    }

</script>
<?
include_once("$g4[admin_path]/admin.tail.php");
?>

