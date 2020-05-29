<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-07-30
 * Time: 오전 11:35
 */
$sub_menu = "300320";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

if($_POST['mode'] == 'update'){

    sql_query(" update {$g4['yc4_item_table']} set it_health_cnt = '".sql_safe_query($_POST['it_health_cnt'])."' where it_id = '".sql_safe_query($_POST['it_id'])."' ");
    alert('저장이 완료되었습니다.',$_SERVER['PHP_SELF'].'?it_id='.$_POST['it_id']);
}


define('bootstrap',true);
$g4[title] = "상품병수량관리";

if($_GET['it_id']){

    $it = sql_fetch("select * from {$g4['yc4_item_table']} where it_id = '".sql_safe_query($_GET['it_id'])."'");

    $mapping_sql = sql_query("select upc,qty from ople_mapping where it_id = '".sql_safe_query($it['it_id'])."'");
    $mapping_info = "";
    while($row = sql_fetch_array($mapping_sql)){
        $mapping_info .= ($mapping_info ? " / ":"").$row['upc'].' * ' . $row['qty'];
    }
    if($mapping_info){
        $mapping_info = "<dl><dt>".$mapping_info."</dt></dl>";
    }
}

include_once ("$g4[admin_path]/admin.head.php");
?>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
        <div class="input-group">
            <span class="input-group-addon">상품코드</span>
            <input type="text" class="form-control" name="it_id" value="<?php echo $_GET['it_id'];?>">
            <span class="input-group-btn">
                <button class="btn btn-primary" type="submit" >입력</button>
            </span>

        </div>
    </form>
<?php if($it['it_id']){ ?>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
        <input type="hidden" name="mode" value="update">
        <input type="hidden" name="it_id" value="<?php echo $it['it_id'];?>">
        <div class="panel">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-lg-3">
                        <a href="#" class="thumbnail">
                            <?php echo get_it_image($it['it_id'].'_s',200,200,null,null,false,false)?>
                        </a>
                    </div>
                    <div class="col-lg-9">
                        <dl>
                            <dt><?php echo get_item_name($it['it_name'],'detail')?></dt>
                        </dl>
                        <?php echo $mapping_info;?>
                        <dl>
                            <div class="input-group">
                                <span class="input-group-addon">건기식 수량</span>
                                <input type="text" class="form-control" name="it_health_cnt" value="<?php echo $it['it_health_cnt'];?>">
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="submit" >저장</button>
                                </span>
                            </div>
                        </dl>

                    </div>
                </div>

            </div>
        </div>
    </form>
<?php }?>



<?php
include_once ("$g4[admin_path]/admin.head.php");
