<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-04-17
 * Time: 오전 10:52
 */
$sub_menu = "600950";
include_once("./_common.php");

$_GET['tab'] = $_GET['tab']? trim($_GET['tab']) : 'ing';
$_GET['connect_page'] = $_GET['connect_page']? trim($_GET['connect_page']) : 'pc';
auth_check($auth[$sub_menu], "r");
$where = '';
if($_GET['tab']=='ing'){
    $where .= " where date_format(st_dt,'%Y%m%d') <= date_format(now(),'%Y%m%d')
              and
              if(en_dt is null or en_dt ='' ,date_format(now(),'%Y%m%d') ,en_dt) >= date_format(now(),'%Y%m%d')";
    $order ='order by sort';
}elseif($_GET['tab']=='end'){
    $where .= " where (date_format(st_dt,'%Y%m%d') > date_format(now(),'%Y%m%d')
             or date_format(en_dt,'%Y%m%d') < date_format(now(),'%Y%m%d'))";
    $order ='order by sort';
}
if($_GET['connect_page']=='pc'){
    $select = ' uid, title, pc_img_url as img, pc_link_url as link,st_dt,en_dt,sort ';
}elseif($_GET['connect_page']=='mobile'){
    $select = ' uid, title, mobile_img_url as img, mobile_link_url as link, st_dt, en_dt, sort ';
}
$md_item_sql = sql_query("select $select from md_choice_data $where $order");

$md_choice_list = array();
while ($row = sql_fetch_array($md_item_sql)) {
    $md_choice_list[]=$row;
}
$tab_get =$_GET;
unset($tab_get['tab']);
$tab_get= http_build_query($tab_get);
$connect_page_get =$_GET;
unset($connect_page_get['connect_page']);
$connect_page_get =http_build_query($connect_page_get );
$g4[title] = "롤링배너 관리";
include_once ("$g4[admin_path]/admin.head.php");

?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div class="row">
    <div class="col-lg-12">
        <h4>MD CHOICE 관리</h4>
    </div
</div>
<script>
    function see(fg,fgs) {
        if(fgs=='pc') {
            $("#seeople").attr("action", "http://ople.com/mall5/index_mdchoice_test.php");
            $('input[name=fg]').val(fg)
            $('#seeople').submit();
        }else if(fgs=='mo'){
            $("#seeople").attr("action", "http://www.ople.com/m/index_mdchoice_test.php");
            $('input[name=fg]').val(fg)
            $('#seeople').submit();
        }
    }
</script>
<form action="" id="seeople" method="post" target="_blank">
    <input type="hidden" name="fg">
</form>
<div class="row">
    <div class="col-lg-5 text-right">
        <span style="color: red">미리보기</span>
        <a class="btn btn-link" onclick="see('Y','pc')" target="_blank">PC</a>
        <a class="btn btn-link" onclick="see('Y','mo')" target="_blank" >MOBILE</a>
    </div>
    <div class="col-lg-5 text-right">
        <span style="color: red">적용후</span>
        <a class="btn btn-link" href="http://ople.com/mall5/index.php" target="_blank">PC</a>
        <a class="btn btn-link" href="http://ople.com/m/index.php" target="_blank" >MOBILE</a>
    </div>
    <div class="col-lg-2 text-right">
        <button class="btn btn-success" type="button" onclick="location.href='./md_choice_porc.php?real=Y'">적용</button>
        <button class="btn btn-info" type="button" onclick="location.href='./md_choice_write.php'">생성</button>
    </div>
</div>
<div class='row'>
    <div class="col-lg-12 text-right">
        <ul class="nav nav-tabs">
            <li role="presentation" id="tab_upc" class='<?php echo $_GET['tab'] == 'ing' ?'active': '';?>'><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $tab_get;?>&tab=ing">진행중</a></li>
            <li role="presentation" id="tab_it_id" class='<?php echo $_GET['tab'] == 'end' ?'active': '';?>'><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $tab_get;?>&tab=end">종료 및 대기</a></li>
        </ul>
    </div>
</div>
<div class='row'>
    <div class="col-lg-12 text-right">
        <ul class="nav nav-pills pull-left">
            <li role="presentation" class="<?php echo $_GET['connect_page'] == 'pc' ?'active': '';?>"><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $connect_page_get;?>&connect_page=pc">PC</a></li>
            <li role="presentation" class="<?php echo $_GET['connect_page'] == 'mobile' ?'active': '';?>"><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $connect_page_get;?>&connect_page=mobile">MOBILE</a></li>
        </ul>
    </div>
</div>


<div class="row">
    <div class="col-lg-12">
        <table CLASS="table">
            <thead>
            <tr>
                <th >정렬</th>
                <th colspan="4" CLASS="text-center" align="<?php echo $_GET['connect_page'] == 'mobile' ?'center':'';?>">MD&nbsp;CHOICE&nbsp;<?php echo strtoupper($_GET['connect_page']);?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($md_choice_list as $item){?>
            <tr>
                <td><?php echo $item['sort'];?></td>
                <td colspan="4" align="<?php echo $_GET['connect_page'] == 'mobile' ?'center':'';?>">
                    <table>
                        <thead>
                        <tr>
                            <th><?php echo $item['title'];?></th>
                            <th><?php echo $item['st_dt'];?>~<?php echo $item['en_dt'];?></th>
                            <th><button class="btn btn-info" type="button" onclick="update_data('<?php echo $item['uid'];?>','update');">수정</button><button class="btn btn-danger" type="button" onclick="update_data('<?php echo $item['uid'];?>','delete','<?php echo $item['title'];?>');">삭제</button></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="3"><a href="<?php echo $item['pc_link_url'];?>"><img src="<?php echo $item['img'];?>" alt="<?php echo $item['link'];?>" /></a></td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <?php }?>
            </tbody>
        </table>
    </div>
</div>
<form id="f" action="./md_choice_porc.php" method="post">
    <input type="hidden" name="uid">
    <input type="hidden" name="mode" value="delete">
</form>
<script>
    function update_data(id,fg,title) {
        if(fg =='update'){
            location.href='./md_choice_write.php?uid='+id;
        }else if(fg=='delete'){
            if(confirm(title+'를 삭제 하시겠습니까?')){
                $('input[name=uid').val(id);
                $('#f').submit();
            }
        }
    }
</script>