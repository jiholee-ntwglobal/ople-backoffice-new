<?php
$sub_menu = "500512";
include "_common.php";
auth_check($auth[$sub_menu], "r");
//버튼
$button_url="event=".$_GET['event']."&page=".$_GET['page']."&tab=".$_GET['tab'];
// 검색 조건
$where = '';
//날짜 위한 배열
$week_array = array("일", "월", "화", "수", "목", "금", "토");
$pr_id=$_GET['pr_id']?$_GET['pr_id']:'';
$dts = $_GET['dts']?$_GET['dts']:'';
$url= "pr_name=".$_GET['pr_name']."&st_dt=".$_GET['st_dt']."&en_dt=".$_GET['en_dt'];
// 처음출력
$start_sql="select pr_name,st_dt,en_dt,if(date_format(now(),'%Y-%m%-%d')>= st_dt
       and date_format(now(),'%Y-%m%-%d') <= en_dt or en_dt is null,'진행중','종료') ing from yc4_promotion where pr_id ='$pr_id'";
$start_result=sql_query($start_sql);
$start_row=sql_fetch_array($start_result);
if($pr_id!=''){
    $where="where a.pr_id = '$pr_id'";
    $select_group="DATE_FORMAT(b.dt, '%Y-%m')";
    $sqls="SELECT $select_group dts,
       a.pr_id,
       a.pr_name,
       a.st_dt,
       a.en_dt,
       sum(if(b.fg = 'P', 1, 0)) pc,
       sum(if(b.fg = 'M', 1, 0)) mo,
       sum(if(c.mb_sex = 'M', 1, 0)) f,
       sum(if(c.mb_sex = 'F', 1, 0)) m,
       sum(if(c.mb_sex = '' or c.mb_sex is null, 1, 0)) rain
  FROM yc4_promotion a
       LEFT JOIN yc4_promotion_visit_log b ON a.pr_id = b.pr_id
       left JOIN g4_member c ON b.mb_id = c.mb_id
       $where
GROUP BY  $select_group,a.pr_name
order by $select_group";
    $results = sql_query($sqls);
    $tosql_sqls="SELECT DATE_FORMAT(b.dt, '%Y') dts,
       a.pr_id,
       a.pr_name,
       a.st_dt,
       a.en_dt,
       sum(if(b.fg = 'P', 1, 0)) pc,
       sum(if(b.fg = 'M', 1, 0)) mo,
       sum(if(c.mb_sex = 'M', 1, 0)) f,
       sum(if(c.mb_sex = 'F', 1, 0)) m,
       sum(if(c.mb_sex = '' OR c.mb_sex IS NULL, 1, 0)) rain
  FROM yc4_promotion a
       LEFT JOIN yc4_promotion_visit_log b ON a.pr_id = b.pr_id
       LEFT JOIN g4_member c ON b.mb_id = c.mb_id
      $where
GROUP BY DATE_FORMAT(b.dt, '%Y'), a.pr_name";
    $total_results = sql_query($tosql_sqls);
    $total_rows=sql_fetch_array($total_results);
    $total_rows['pc']=$total_rows['pc']?$total_rows['pc']:'0';
    $total_rows['mo']=$total_rows['mo']?$total_rows['mo']:'0';
    $total_pcmos= $total_rows['pc']+$total_rows['mo'];
    $total_fmrains= $total_rows['f']+$total_rows['m']+$total_rows['rain'];

}
if($dts!=''){
    $where.=" AND DATE_FORMAT(b.dt, '%Y-%m')='$dts' ";
    $select_group="DATE_FORMAT(b.dt, '%Y-%m-%d')";
    $sql="SELECT $select_group dts,
       a.pr_id,
       a.pr_name,
       a.st_dt,
       a.en_dt,
       sum(if(b.fg = 'P', 1, 0)) pc,
       sum(if(b.fg = 'M', 1, 0)) mo,
       sum(if(c.mb_sex = 'M', 1, 0)) f,
       sum(if(c.mb_sex = 'F', 1, 0)) m,
       sum(if(c.mb_sex = '' or c.mb_sex is null, 1, 0)) rain
  FROM yc4_promotion a
       LEFT JOIN yc4_promotion_visit_log b ON a.pr_id = b.pr_id
       left JOIN g4_member c ON b.mb_id = c.mb_id
       $where
GROUP BY  $select_group,a.pr_name
order by $select_group";
    $result = sql_query($sql);

    $total_sql="SELECT DATE_FORMAT(b.dt, '%Y-%m') dts,
       a.pr_id,
       a.pr_name,
       a.st_dt,
       a.en_dt,
       sum(if(b.fg = 'P', 1, 0)) pc,
       sum(if(b.fg = 'M', 1, 0)) mo,
       sum(if(c.mb_sex = 'M', 1, 0)) f,
       sum(if(c.mb_sex = 'F', 1, 0)) m,
       sum(if(c.mb_sex = '' OR c.mb_sex IS NULL, 1, 0)) rain
  FROM yc4_promotion a
       LEFT JOIN yc4_promotion_visit_log b ON a.pr_id = b.pr_id
       LEFT JOIN g4_member c ON b.mb_id = c.mb_id
       $where
GROUP BY DATE_FORMAT(b.dt, '%Y-%m'), a.pr_name";
    $total_result = sql_query($total_sql);
    $total_row=sql_fetch_array($total_result);
    $total_pcmo= $total_row['pc']+$total_row['mo'];
    $total_fmrain= $total_row['f']+$total_row['m']+$total_row['rain'];
}

$g4[title] = "프로모션 통계";
define('bootstrap', true);
include $g4['full_path'] . "/adm/admin.head.php";
?>
<style>
    thead{
        font-size: 12px;

    }
</style>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<label><h2><?php echo $start_row['pr_name'];?></h2></label>&nbsp;&nbsp;&nbsp;
<label><h3><?php echo $start_row['st_dt'];?>~</h3></label>
<label><h3><?php echo $start_row['en_dt'];?></h3></label>&nbsp;
<label><h3><?php echo $start_row['ing'];?></h3></label>
<button class="btn btn-default" onclick="location='http://209.216.56.107/mall5/adm/shop_admin/event_stats.php?<?php echo $button_url;?>'">처음으로</button>
<?php if($pr_id){ ?>
<table class="table table-bordered table-hover">
    <thead>
    <tr>
        <th style="text-align: center;">날짜</th>
        <th style="text-align: center; " colspan="2">PC</th>
        <th style="text-align: center;" colspan="2">MOBILE</th>
        <th style="text-align: center;" colspan="2">남자</th>
        <th style="text-align: center;" colspan="2">여자</th>
        <th style="text-align: center;" colspan="2">비회원</th>
        <th style="text-align: center;" colspan="2">합계</th>
        <th style="text-align: center;">자세히보기</th>
    </tr>
    </thead>
    <tbody>
    <?php
    while ($row = sql_fetch_array($results)) {
        $row['pc']=$row['pc']?$row['pc']:'0';
        $row['mo']=$row['mo']?$row['mo']:'0';
        $pcmo= $row['mo']+$row['pc'];
        ?>
        <tr>
            <td style="text-align: center;"><?php echo $row['dts']; ?></td>
            <td style="text-align: right; border-right: none;"><?php echo number_format($row['pc']); ?></td>
            <td style="text-align: right; border-left: none;">(<?php echo $pcmo!='0'?round($row['pc']/($pcmo)*100,2):'0'; ?>%) </td>
            <td style="text-align: right; border-right: none;"><?php echo number_format($row['mo']); ?></td>
            <td style="text-align: right; border-left: none;">(<?php echo $pcmo!='0'?round($row['mo']/($pcmo)*100,2):'0'; ?>%) </td>
            <td style="text-align: right; border-right: none;"><?php echo number_format($row['f']); ?></td>
            <td style="text-align: right; border-left: none;">(<?php echo round($row['f']/($row['f'] + $row['m'] + $row['rain'])*100,2); ?>%) </td>
            <td style="text-align: right; border-right: none;"><?php echo number_format($row['m']); ?></td>
            <td style="text-align: right; border-left: none;">(<?php echo round($row['m']/($row['f'] + $row['m'] + $row['rain'])*100,2); ?>%) </td>
            <td style="text-align: right; border-right: none;"><?php echo number_format($row['rain']); ?></td>
            <td style="text-align: right; border-left: none;">(<?php echo round($row['rain']/($row['f'] + $row['m'] + $row['rain'])*100,2); ?>%) </td>
            <td style="text-align: right; border-right: none;"><?php echo number_format($row['f'] + $row['m'] + $row['rain']); ?></td>
            <td style="text-align: right; border-left: none;">(<?php echo round(($row['f'] + $row['m'] + $row['rain'])/$total_fmrains*100,2); ?>%)</td>
            <td><button class="btn btn-default btn-xs" onclick="location='http://209.216.56.107/mall5/adm/shop_admin/event_stats_deteil.php?pr_id=<?php echo $pr_id;?>&dts=<?php echo $row['dts'];?>&<?php echo $button_url?>'">자세히보기</button></td>
        </tr>
    <?php }?>
    <tr>
        <td colspan="1" style="text-align: center; font-weight: bold;">총합계</td>
        <td style="text-align: right; font-weight: bold; border-right: none"><?php echo number_format($total_rows['pc']);?></td>
        <td style="text-align: right; border-left: none;">(<?php echo $total_pcmos!='0'?round($total_rows['pc']/($total_pcmos)*100,2):'0'; ?>%)</td>
        <td style="text-align: right; border-right: none"><?php echo number_format($total_rows['mo']);?></td>
        <td style="text-align: right; border-left: none;">(<?php echo $total_pcmos!='0'?round($total_rows['mo']/($total_pcmos)*100,2):'0'; ?>%)</td>

        <td style="text-align: right; font-weight: bold; border-right: none"><?php echo number_format($total_rows['f']);?></td>
        <td style="text-align: right; border-left: none;">(<?php echo round($total_rows['f']/($total_fmrains)*100,2); ?>%)</td>
        <td style="text-align: right; font-weight: bold; border-right: none"><?php echo number_format($total_rows['m']);?></td>
        <td style="text-align: right; border-left: none;">(<?php echo round($total_rows['m']/($total_fmrains)*100,2); ?>%)</td>
        <td style="text-align: right; font-weight: bold; border-right: none"><?php echo number_format($total_rows['rain']);?></td>
        <td style="text-align: right; border-left: none;">(<?php echo round($total_rows['rain']/($total_fmrains)*100,2); ?>%)</td>
        <td style="text-align: right; font-weight: bold; border-right: none"><?php echo number_format($total_fmrains);?></td>
        <td style="text-align: right; border-left: none;">(100%)</td>
    </tr>
    </tbody>
</table>
<?php } ?>
<?php if($dts){ ?>

<table class="table table-bordered">
    <thead>

    <tr>
        <th style="text-align: center; font-size: 12px;">날짜</th>
        <th style="text-align: center;" colspan="2">PC</th>
        <th style="text-align: center;" colspan="2">MOBILE</th>
        <th style="text-align: center;" colspan="2">남자</th>
        <th style="text-align: center;" colspan="2">여자</th>
        <th style="text-align: center;" colspan="2">비회원</th>
        <th style="text-align: center;" colspan="2">합계</th>

    </tr>

    </thead>
    <tbody>
    <?php
    while ($row = sql_fetch_array($result)) {

        ?>

        <tr>
            <td style="text-align: center;"><?php echo $row['dts']."&nbsp;&nbsp;".$week_array[date('w', strtotime($row['dts']))]; ?></td>
            <td style="text-align: right; border-right: none;"><?php echo number_format($row['pc']); ?></td>
            <td style="text-align: right; border-left: none;">(<?php echo round($row['pc']/($row['pc'] + $row['mo'])*100,2); ?>%)</td>
            <td style="text-align: right; border-right: none;"><?php echo number_format($row['mo']); ?></td>
            <td style="text-align: right; border-left: none;">(<?php echo round($row['mo']/($row['pc'] + $row['mo'])*100,2); ?>%)</td>
            <td style="text-align: right; border-right: none;"><?php echo number_format($row['f']); ?></td>
            <td style="text-align: right; border-left: none;">(<?php echo round($row['f']/($row['f'] + $row['m'] + $row['rain'])*100,2); ?>%)</td>
            <td style="text-align: right; border-right: none;"><?php echo number_format($row['m']); ?></td>
            <td style="text-align: right; border-left: none;">(<?php echo round($row['m']/($row['f'] + $row['m'] + $row['rain'])*100,2); ?>%)</td>
            <td style="text-align: right; border-right: none;"><?php echo number_format($row['rain']); ?></td>
            <td style="text-align: right; border-left: none;">(<?php echo round($row['rain']/($row['f'] + $row['m'] + $row['rain'])*100,2); ?>%)</td>
            <td style="text-align: right; border-right: none;"><?php echo number_format($row['f'] + $row['m'] + $row['rain']); ?></td>
            <td style="text-align: right; border-left: none;">(<?php echo round(($row['f'] + $row['m'] + $row['rain'])/$total_fmrain*100,2); ?>%)</td>
        </tr>

    <?php }?>

        <tr>
            <td colspan="1" style="text-align: center; font-weight: bold;">총합계</td>
            <td style="text-align: right; font-weight: bold; border-right: none;"><?php echo number_format($total_row['pc']);?></td>
            <td style="text-align: right; border-left: none;">(<?php echo round($total_row['pc']/($total_pcmo)*100,2); ?>%)</td>
            <td style="text-align: right; font-weight: bold; border-right: none;"><?php echo number_format($total_row['mo']);?></td>
            <td style="text-align: right; border-left: none;">(<?php echo round($total_row['mo']/($total_pcmo)*100,2); ?>%)</td>
            <td style="text-align: right; font-weight: bold; border-right: none;"><?php echo number_format($total_row['f']);?></td>
            <td style="text-align: right; border-left: none;">(<?php echo round($total_row['f']/($total_fmrain)*100,2); ?>%)</td>
            <td style="text-align: right; font-weight: bold; border-right: none;"><?php echo number_format($total_row['m']);?></td>
            <td style="text-align: right; border-left: none;">(<?php echo round($total_row['m']/($total_fmrain)*100,2); ?>%)</td>
            <td style="text-align: right; font-weight: bold; border-right: none;"><?php echo number_format($total_row['rain']);?></td>
            <td style="text-align: right; border-left: none;">(<?php echo round($total_row['rain']/($total_fmrain)*100,2); ?>%)</td>
            <td style="text-align: right; font-weight: bold; border-right: none;"><?php echo number_format($total_fmrain);?></td>
            <td style="text-align: right; border-left: none;">(100%)</td>

        </tr>

    </tbody>
</table>
<?php } ?>
<?
include_once("$g4[admin_path]/admin.tail.php");
?>
