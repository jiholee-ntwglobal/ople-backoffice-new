<?php
include_once "_common.php";
if(!$is_member) alert("회원이 아닙니다.");

$g4['title'] = "품절상품입고 SMS통보 신청내역";
include_once "{$g4['path']}/head.php";


$sql_common = " from {$g4['item_sms_table']} where mb_id='{$member['mb_id']}' ";
if($ts_send)
	$sql_common .= " and ts_send=1 ";
else
	$sql_common .= " and ts_send=0 ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt {$sql_common} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = "select * {$sql_common} order by ts_id desc limit $from_record, $rows";
$result = sql_query($sql);

$qstr = "ts_send=".(int)$ts_send;
?>
 <div class='sub_title'>
	  <span><strong>품절상품 SMS 통보 신청내역</strong></span>
  </div>
<div style="line-height:18px;padding:15px 0;">
  <p style="color:#959595;">※ SMS통보는 <strong style="color:#ff3300;">09~21시</strong> 사이에만 발송됩니다.사용자들의 불편함을 고려하여 이외시간에는 발송되지 않습니다.</p>
</div>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td width=30% style="padding-bottom:5px;">
		<a href="item_sms_list.php"><?=(!$ts_send ? '<b>미통보내역</b>' : '미통보내역');?></a> |
		<a href="item_sms_list.php?ts_send=1"><?=($ts_send ? '<b>통보내역</b>' : '통보내역');?></a> |  전체 : <? echo $total_count ?>
	</td>
</tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" class="list_styleA">
  <thead>
    <tr>
      <th colspan='2'>상품명</th>
      <th width='100'>신청자</th>
      <th width='100'>통보번호</th>
      <th width='90'>구분</th>
      <th width='75'>통보일시</th>
      <th width='75'>신청일시</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
<?
for($k=0; $row=sql_fetch_array($result); $k++)
{
	$it = sql_fetch("select it_name from {$g4['yc4_item_table']} where it_id='{$row['it_id']}'");
	$href = "{$g4[shop_path]}/item.php?it_id=$row[it_id]";
	$s_del = "<a href=\"javascript:del('item_sms_listdelete.php?ts_id={$row['ts_id']}&{$qstr}&page=$page')\">삭제</a>";
	if($row['it_name']){
		$row['it_name'] = get_item_name($row['it_name']);
	}

?>
<tr>
	<td><a href='<?=$href?>' target=_blank title='새창으로 상품보기'><?=get_it_image("{$row[it_id]}_s", 50, 50)?></a></td>
  <td style='text-align:left;'><a href='<?=$href?>' target=_blank title='새창으로 상품보기'><?=stripslashes($it['it_name'])?></a></td>
	<td><?=$row['ts_name']?></td>
	<td><?=$row['ts_hp']?></td>
	<td><strong style='color:#ff3300;'><?=($row['ts_send'] ? '통보' : '미통보')?></strong></td>
	<td><?=str_replace(' ', '', $row['ts_send_time'])?></td>
  <td style='color:#73a8ce;'><?=str_replace(' ', '', $row['ts_time'])?></td>
  <td><?=$s_del?></td>
</tr>
<?}?>
<?if(!$k) echo "<tr><td height=100 colspan=10 align=center>자료가 없습니다.</td></tr>"; ?>
  </tbody>
</table>

<div class='paging'>
  <?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?>
</div>


<? include_once "{$g4['path']}/tail.php"; ?>