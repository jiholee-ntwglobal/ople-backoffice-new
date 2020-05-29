<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-07-29
 * Time: 오후 2:14
 */

$sub_menu = "300970";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

if($_POST['mode'] == 'get_item_name'){
    $it_name = sql_fetch("select it_name from yc4_item where it_id = '".sql_safe_query($_POST['it_id'])."'");
    if($it_name){
        echo get_item_name($it_name['it_name']);
    }



    exit;
}

if($_POST['mode']== 'insert'){
    $upload_msg = "";
    $img_put = "";
    $upload_dir = $g4['path']."/data/ituse";
    $del_arr = array();

    $conn_id = @ftp_connect($g4['front_ftp_server']);
    $login_result = @ftp_login($conn_id, $g4['front_ftp_user_name'], $g4['front_ftp_user_pass']);

    # 파일 업로드 시작 #
    for($k=0; $k<count($_FILES['is_image']['tmp_name']); $k++){
        if($_FILES['is_image']['tmp_name'][$k]){
            if($_FILES['is_image']['error'][$k] != 0) // 저장실패
            {
                $upload_msg .= "파일업로드 실패\\n({$_FILES['is_image']['name'][$k]})\\n\\n";
                continue;
            }else
            {
                // 파일명은 추측이 불가능하게. 중복 최소화를 위해 상품코드를 붙여줌.
                $file_ext = pathinfo($_FILES['is_image']['name'][$k]);
                while(1){ // 중복파일 처리
                    $file_name = "{$it_id}_{$k}_".md5(uniqid("")).".".$file_ext['extension'];
                    if(!file_exists("{$upload_dir}/$file_name"))
                        break;
                }
                upload_file($_FILES['is_image']['tmp_name'][$k], $file_name, $upload_dir);


                ftp_put($conn_id, "/ssd/ople_data/data/ituse/".$file_name, $upload_dir.'/'.$file_name, FTP_BINARY);
                $img_put .= " is_image{$k} = '$file_name', ";
                $del_arr[] = $k;
            }
            $photo = true;
        }else {
            $img_put .= " is_image{$k} = '', ";
        }
    }


    sql_query("
        insert yc4_item_ps
        set
            it_id = '".sql_safe_query($_POST['it_id'])."',
            mb_id = '',
            is_name = '".sql_safe_query($_POST['is_name'])."',
            is_password = password(123),
            is_score = '".sql_safe_query($_POST['is_score'])."',
            is_subject = '".sql_safe_query($_POST['is_subject'])."',
            is_content = '".sql_safe_query($_POST['is_content'])."',
            is_time = now(),
            is_ip = '".$_SERVER['REMOTE_ADDR']."',
            is_confirm = '1',
            ".$img_put."
            is_best = '0'
    ");

    alert('등록되었습니다.',$_SERVER['PHP_SELF']);
    exit;

}



$g4[title] = "상품후기입력";

define('bootstrap',true);
include_once ("$g4[admin_path]/admin.head.php");
?>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="frm" onsubmit="return frm_chk_fnc(this);" enctype="multipart/form-data">
        <input type="hidden" name="mode" value="insert">
        <div class="panel">
            <table class="table">
                <col width="100">
                <col>
                <tr>
                    <th class="text-right">상품코드</th>
                    <td><input type="text" name="it_id" class="form-control" onchange="get_it_name(this);"></td>
                </tr>
                <tr>
                    <th class="text-right">상품명</th>
                    <td class="it_name"></td>
                </tr>
                <tr>
                    <th class="text-right">작성자명</th>
                    <td><input type="text" name="is_name" class="form-control"></td>
                </tr>
                <tr>
                    <th class="text-right">제목</th>
                    <td><input type="text" name="is_subject" class="form-control"></td>
                </tr>
                <tr>
                    <th class="text-right">내용</th>
                    <td><textarea class="form-control" rows="15" name="is_content"></textarea></td>
                </tr>
                <tr>
                    <th class="text-right">평가</th>
                    <td>
                        <input type="radio" name="is_score" value="10" checked>
                        <img src="<?php echo $g4['shop_path']?>/img/star5.gif" align="absmiddle">
                        <input type="radio" name="is_score" value="8">
                        <img src="<?php echo $g4['shop_path']?>/img/star4.gif" align="absmiddle">
                        <input type="radio" name="is_score" value="6">
                        <img src="<?php echo $g4['shop_path']?>/img/star3.gif" align="absmiddle">
                        <input type="radio" name="is_score" value="4">
                        <img src="<?php echo $g4['shop_path']?>/img/star2.gif" align="absmiddle">
                        <input type="radio" name="is_score" value="2">
                        <img src="<?php echo $g4['shop_path']?>/img/star1.gif" align="absmiddle">
                    </td>
                </tr>
                <tr>
                    <th class="text-right">이미지</th>
                    <td>
                        <div class="input-group"> <div class="input-group-addon">이미지1</div> <input type="file" class="form-control" name="is_image[0]"> </div>
                        <div class="input-group"> <div class="input-group-addon">이미지2</div> <input type="file" class="form-control" name="is_image[1]"> </div>
                        <div class="input-group"> <div class="input-group-addon">이미지3</div> <input type="file" class="form-control" name="is_image[2]"> </div>
                        <div class="input-group"> <div class="input-group-addon">이미지4</div> <input type="file" class="form-control" name="is_image[3]"> </div>
                        <div class="input-group"> <div class="input-group-addon">이미지5</div> <input type="file" class="form-control" name="is_image[4]"> </div>
                    </td>
                </tr>
            </table>
            <div class="panel-footer text-center">
                <button type="submit" class="btn btn-primary">입력</button>
            </div>
        </div>

    </form>

    <script>
        function frm_chk_fnc(f){
            if(f.it_id0.value == ''){
                alert('상품코드를 입력해 주세요.');
                return false;
            }
            if(f.is_name.value == ''){
                alert('제목을 입력해 주세요.');
                return false;
            }
            if(f.is_content.value == ''){
                alert('내용 입력해 주세요.');
                return false;
            }
        }

        function get_it_name(obj){
            if(obj.value == ''){
                return false;
            }
            $.ajax({
                url : '<?php echo $_SERVER['PHP_SELF']?>',
                type : 'post',
                data : {
                    'mode' : 'get_item_name',
                    'it_id' : obj.value
                },
                success : function (result){
                    if(result != ''){
                        $('.it_name').text(result);
                    }
                }
            });
        }

    </script>
<?php
include_once ("$g4[admin_path]/admin.tail.php");
