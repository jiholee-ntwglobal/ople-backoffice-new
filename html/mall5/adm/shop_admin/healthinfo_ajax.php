<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-07-12
 * Time: 오후 5:50
 */
$sub_contents_pre= isset($_GET['sub_contents_pre']) ? trim($_GET['sub_contents_pre']) : '';
if(!$sub_contents_pre || $sub_contents_pre == '' || !is_numeric($sub_contents_pre)){
    echo false;
    exit;
}
?>
<pre class="text-right" id='sub_contents_pre<?php echo $sub_contents_pre; ?>'>
    <button class="btn" type="button" onclick="sub_contents_remove('<?php echo $sub_contents_pre; ?>')" >서브컨텐츠 삭제</button>
    <table class="table gray">
        <tr>
            <td rowspan="8" class="text-center"><h5 class="title">서브 컨텐츠</h5></td>
        </tr>
        <tr>
            <td><h5 class="title">서브 제목</h5></td>
            <td><input class="form-control" name='contents_title[<?php echo $sub_contents_pre; ?>]' required></td>
        </tr>
        <tr>
            <td><h5 class="title">서브 이미지 URL</h5></td>
            <td><input class="form-control" name='contents_image[<?php echo $sub_contents_pre; ?>]' required></td>
        </tr>
        <tr>
            <td><h5 class="title">서브 내용</h5></td>
            <td><textarea class="form-control" rows="5" name='contents_content[<?php echo $sub_contents_pre; ?>]' required></textarea></td>
        </tr>
        <tr>
            <td rowspan="3"><h5 class="title">추천상품</h5></td>
        </tr>
        <tr>
            <td>
                <div class="row">
                    <div class='col-lg-1'><label>상품코드</label></div><div class='col-lg-5'><input class="form-control" id='contents_it_id<?php echo $sub_contents_pre; ?>'></div>
                    <div class='col-lg-1'><label>우선순위</label></div><div class='col-lg-5'><input class="form-control" id='contents_sort<?php echo $sub_contents_pre; ?>' type='number'></div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="row">
                    <div class='col-lg-2'><label>상품<br>부가설명</label></div>
                    <div class='col-lg-10'><input class="form-control" width='90%' id='contents_product_desc<?php echo $sub_contents_pre; ?>'></div>
                </div>
                <div class="row">
                    <div class='col-lg-12 text-right'><button type='button' onclick='add_contents_it_id(<?php echo $sub_contents_pre; ?>)'>추가</button></div>
                </div>
            </td>
        </tr>
        <tr>
            <td class="text-center" colspan='2'>
                <table class="" width="100%" border="1">
                    <tr>
                        <td width='25' class='text-center'><b>순위</b></td>
                        <td width='80' class='text-center'>상품코드</td>
                        <td class='text-center'>제품명</td>
                        <td class='text-center'>부가설명</td>
                        <td width='31' class='text-center'></td>
                    </tr>
                    <tbody id='subcontents_item_list<?php echo $sub_contents_pre; ?>'>

                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</pre>