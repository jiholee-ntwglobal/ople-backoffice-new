<?php
/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2016-06-22
 * Time: 오후 5:05
 */
define('bootstrap', true);

$sub_menu = "300377";
include_once("./_common.php");
include $g4['full_path'].'/lib/db.php';



$pdo		= new db();
$ople		= $pdo->ople_db_pdo;
$ntics		= $pdo->ntics_db;

if(isset($_POST['mode'])){
    print_r($_POST);

    if($_POST['move_review'] == '1'){

        $oplek = new PDO("mysql:host=115.68.114.153;dbname=opk;charset=utf8", 'neiko', 'rsmaker@ntwglobal');

        $item_data = $target_item_stmt->fetch(PDO::FETCH_ASSOC);print_r($item_data);

        /*
        $review_update_stmt = $ople->prepare("update yc4_item_ps set it_id=? where it_id=?");
        $review_update_stmt->bindParam(1, $_POST['target_it_id'], PDO::PARAM_STR);
        $review_update_stmt->bindParam(2, $_POST['origin_it_id'], PDO::PARAM_STR);
        $review_update_stmt->execute();

        $opk_review_update_stmt = $oplek->prepare("update yc4_item_ps set it_id=? where it_id=?");
        $opk_review_update_stmt->bindParam(1, $_POST['target_it_id'], PDO::PARAM_STR);
        $opk_review_update_stmt->bindParam(2, $_POST['origin_it_id'], PDO::PARAM_STR);
        $opk_review_update_stmt->execute();
        */
    }

    if($_POST['move_sales'] == '1'){

        //$this->ntics_db = new PDO ("dblib:host=ntics2.ntwsec.com:1433;dbname=NTICS","sa","Tlstkddnr80");
        //$review_update_stmt = $ople->prepare("update N_SALES_ITEM set upc=? where channel=? and it_id=? and upc=?");

    }
    exit;
}


$g4[title] = "상품 후기, 판매량 이관";
include_once ("$g4[admin_path]/admin.head.php");


if(isset($_GET['target_it_id'])){

    $target_item_stmt = $ople->prepare("
    SELECT it_id,
           it_maker,           
           it_name,
           it_amount,
           it_amount_usd
      FROM yc4_item
     WHERE it_id = ?");
    $target_item_stmt->bindParam(1, $_GET['target_it_id'], PDO::PARAM_STR);
    $target_item_stmt->execute();

    $item_data = $target_item_stmt->fetch(PDO::FETCH_ASSOC);

    if(isset($item_data['it_id'])){

        if(isset($_GET['origin_it_id'])){

            $origin_item_stmt = $ople->prepare("
            SELECT it_id,
                   it_maker,           
                   it_name,
                   it_amount,
                   it_amount_usd
              FROM yc4_item
             WHERE it_id = ? and it_id!=?");
            $origin_item_stmt->bindParam(1, $_GET['origin_it_id'], PDO::PARAM_STR);
            $origin_item_stmt->bindParam(2, $_GET['target_it_id'], PDO::PARAM_STR);
            $origin_item_stmt->execute();

            $orgin_item_data = $origin_item_stmt->fetch(PDO::FETCH_ASSOC);

        }

    }

}

?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div class='row'>
    <div class='col-lg-12'>
        <h4>상품 후기, 판매량 이관</h4>
    </div>
</div>
<div class='row'>

    <div class='col-lg-4'>
        <form onsubmit="return search_target_item()">
        <div class="panel panel-Info">
            <div class="panel-heading">
                이관 받을 상품 검색
            </div>
            <div class="panel-body">
                <p>
                    <div class="form-group col-lg-8">
                        <label>오플상품코드 검색</label>
                        <input type="text" class="form-control" name="target_it_id">
                    </div>
                    <div class="form-group col-lg-4">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> 검색</button>
                    </div>
                </p>
            </div>
        </div>
        </form>
     </div>

    <div class='col-lg-8'>
        <div class="panel panel-primary">
            <div class="panel-heading">
                이관 받을 상품 정보
            </div>
            <div class="panel-body">
                <p>
                    <?php
                    if(isset($item_data['it_id'])){ ?>
                    <div class="col-lg-4">
                    <?php echo get_it_image("{$item_data['it_id']}_s", 150, 150,$item_data['it_id'],null,false,true,true); ?>
                    </div>
                    <div class='col-md-8'>
                        <p><b><?php echo $item_data['it_maker']; ?></b></p>
                        <p><b><?php echo $item_data['it_name']; ?></b></p>
                        <p><a href="http://www.ople.com/mall5/shop/item.php?it_id=<?php echo $item_data['it_id']; ?>" target="_blank" class="btn btn-outline btn-primary btn-sm">상품페이지 이동</a></p>
                    </div>
                    <?php } else { ?>
                    오플상품코드를 검색하세요.
                    <?php } ?>


                </p>
            </div>
        </div>
    </div>

</div>

<?php if(isset($item_data['it_id'])){ ?>

    <div class='row'>

        <div class='col-lg-4'>
            <form onsubmit="return search_target_item2()">
                <input type="hidden" name="target_it_id" value="<?php echo $_GET['target_it_id']; ?>">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    이관할 상품 검색
                </div>
                <div class="panel-body">
                    <p>
                    <div class="form-group col-lg-8">
                        <label>오플상품코드 검색</label>
                        <input type="text" class="form-control" name="origin_it_id">
                    </div>
                    <div class="form-group col-lg-4">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> 검색</button>
                    </div>
                    </p>
                </div>
            </div>
                </form>
        </div>

        <div class='col-lg-8'>
            <div class="panel panel-warning">
                <div class="panel-heading">
                    이관할 상품 정보
                </div>
                <div class="panel-body">
                    <p>
                        <?php
                        if(isset($orgin_item_data['it_id'])){ ?>
                    <div class="col-lg-4">
                        <?php echo get_it_image("{$orgin_item_data['it_id']}_s", 150, 150,$orgin_item_data['it_id'],null,false,true,true); ?>
                    </div>
                    <div class='col-md-8'>
                        <p><b><?php echo $orgin_item_data['it_maker']; ?></b></p>
                        <p><b><?php echo $orgin_item_data['it_name']; ?></b></p>
                        <p><a href="http://www.ople.com/mall5/shop/item.php?it_id=<?php echo $orgin_item_data['it_id']; ?>" target="_blank" class="btn btn-outline btn-primary btn-sm">상품페이지 이동</a></p>

                        <div class="table-responsive">
                            <form method="POST" onsubmit="return chk_move()">
                                <input type="hidden" name="mode" value="submit">
                                <input type="hidden" name="target_it_id" value="<?php echo $_GET['target_it_id']; ?>">
                                <input type="hidden" name="origin_it_id" value="<?php echo $_GET['origin_it_id']; ?>">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th class="text-center">후기</th>
                                    <th class="text-center">판매량</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                <tr>
                                    <td class="text-center"><input type="checkbox" name="move_review" value="1"></td>
                                    <td class="text-center"><input type="checkbox" name="move_sales" value="1"></td>
                                    <td class="text-center"><button type="submit" class="btn btn-danger"><i class="fa fa-check"></i> 이관</button></td>
                                </tr>
                                </tbody>
                            </table>
                            </form>
                        </div>


                    </div>
                    <?php } else { ?>
                        오플상품코드를 검색하세요.
                    <?php } ?>


                    </p>
                </div>
            </div>
        </div>



        <?php } ?>

<script>
    function chk_move(){

        if($(":checkbox[name=move_review]").is(":checked") == false && $(":checkbox[name=move_sales]").is(":checked") == false){
            alert("이관할 항목을 체크해주세요.");
            return false;
        }

        var txt = "";

        if($(":checkbox[name=move_review]").is(":checked")) txt = "후기 ";
        if($(":checkbox[name=move_sales]").is(":checked")) txt += "판매량 ";

        if(confirm("이관할 상품의 " + txt + "을(를) 이관 받을 상품 쪽으로 이관하겠습니까?")){
            return true;
        }
        return false;
    }
    function search_target_item(){

        if($("input[name=target_it_id]").val().trim() == ""){
            alert("오플 상품코드를 입력해주세요.");
            $("input[name=target_it_id]").focus();
            return false;
        }
        return true;
    }
    function search_target_item2(){

        if($("input[name=origin_it_id]").val().trim() == ""){
            alert("오플 상품코드를 입력해주세요.");
            $("input[name=origin_it_id]").focus();
            return false;
        }
        return true;
    }
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
