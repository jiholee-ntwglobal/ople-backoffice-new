<?php
include_once("./_common.php");

if (!$is_member)
    goto_url("$g4[path]/bbs/login.php?url=".urlencode("$g4[shop_path]/mypage.php"));

include_once "{$g4['path']}/lib/latest.lib.php";

$g4[title] = "마이페이지";
include_once("./_head.php");

//$str = $g4[title];
//include("./navigation2.inc.php");
?>

<div class='PageTitle'>
  <img src="<?=$g4['path']?>/images/menu/menu_title02.gif" alt="마이페이지" />
</div>

<div style="padding:10px 0;">
<table width="100%">
<tr>
    <td class="member_name"><strong><?=$member['mb_name']?></strong>님</td>
    <td align="right">
        <? if ($is_admin == 'super') { echo "<a href='$g4[admin_path]/' ><img src='$g4[shop_img_path]/btn_admin.gif' border=0 align='absmiddle'></a>"; } ?>
        <a href='<?=$g4[bbs_path]?>/member_confirm.php?url=register_form.php'><img src='<?=$g4[shop_img_path]?>/my_modify.gif' border=0 align='absmiddle'></a>
        <a href="javascript:member_leave();"><img src='<?=$g4[shop_img_path]?>/my_leave.gif' border=0 align='absmiddle'></a></td>
</tr>
</table>
</div>

<script type="text/JavaScript">
function member_leave()
{
    if (confirm("정말 회원에서 탈퇴 하시겠습니까?"))
            location.replace("<?=$g4[bbs_path]?>/member_confirm.php?url=member_leave.php");
}
</script>

<div class="my_infoBox">
  <div class="box_01">
    <table width="100%" cellpadding="0" cellspacing="0">
      <tr><th>보유포인트</th></tr>
      <tr><td class="point_num"><?=number_format($member[mb_point])?></td></tr>
      <tr><td style="padding:0;font-size:11px;"><a href="javascript:win_point();"> (포인트 상세내역 보기)</a></td></tr>
    </table>
  </div>
  <div class="box_02">
    <table width="100%" cellpadding="0" cellspacing="0">
      <tr><th>회원권한</th></tr>
      <tr>
        <td><span class="point_num"><?=($mb_level_str[$member[mb_level]] != '' ? $mb_level_str[$member[mb_level]]." [{$member[mb_level]}]" : $member[mb_level]);?></span>

			<div id="dis_mb_lv_str" style="display:none;  position:absolute;"><div style="background-Color:#e7e7e7; border:2px solid darkblue; left:-100px; top:20px; position:absolute;">
			<table cellpadding="2" cellspacing="0" align="center" width="245">
			<tr><td colspan="3" height="25" style="vertical-align:top;">&nbsp;※ 회원등급별 혜택안내</td></tr>
			<?
			$off_arr = explode("|", $default['de_mb_level_off']);
			$post_arr = explode("|", $default['de_mb_level_free_post']);
			$dis_str = false;
			for($k=3; $k<5; $k++){
				if($mb_level_str[$k] != ''){
					$dc_arr = explode('=>', $off_arr[($k-3)]);
					$dc_per = array_pop($dc_arr);
					$dc_arr2 = explode('=>', $post_arr[($k-3)]);
			?>
			<tr>
				<td width="70"><?=$mb_level_str[$k]?> [<?=$k?>]</td>
				<td width="120"><?if($dc_per){ $dis_str = true; ?>상품가격 할인 <?=$dc_per;?>%<?}?></td>
				<td width="70" align="right"><?if(array_pop($dc_arr2)){?><u>무료배송</u><?}?>&nbsp;</td>
			</tr>
			<?}}?>
			</table>
			</div></div>

<!--
			<?if($dis_str){ // 혜택 설정이 되어있는 경우만 출력?>
			<span onmouseover="document.getElementById('dis_mb_lv_str').style.display='block';" onmouseout="document.getElementById('dis_mb_lv_str').style.display='none';" style="cursor:pointer; color:blue; text-decoration:underline;">혜택보기</span>
			<?}?>
-->

        </td>
      </tr>
    </table>
  </div>
  <div class="box_03">
    <table width="100%" cellpadding="0" cellspacing="0">
      <tr>
        <th>주&nbsp;&nbsp;소</th>
        <td><?=sprintf("(%s-%s) %s %s", $member[mb_zip1], $member[mb_zip2], $member[mb_addr1], $member[mb_addr2]);?></td>
      </tr>
      <tr>
        <th>연락처</th>
        <td><?=$member[mb_tel]?></td>
      </tr>
      <tr>
        <th>이메일</th>
        <td><?=$member[mb_email]?></td>
      </tr>
    </table>
  </div>
  <div class="box_04">
    <table width="100%" cellpadding="0" cellspacing="0">
      <?/*
	  <tr>
        <th>쪽지함</th>
        <td><a href="javascript:win_memo();">쪽지보기</a></td>
      </tr>
	  */?>
      <tr>
        <th>최종접속일시</th>
        <td><?=$member[mb_today_login]?></td>
      </tr>
      <tr>
        <th>회원가입일시</th>
        <td><?=$member[mb_datetime]?></td>
      </tr>
    </table>
  </div>

</div>
<div style="margin-bottom:20px;">
<?
// 김선용 200804 : qna 질문/답변 최신글
// 인수 : 스킨디렉토리, 게시판테이블, 출력수, 제목글자수, 뽑을회원아이디, 검색쿼리(옵션 - 예) "and mb_id='aaa'")

echo latest_mb_write("mb_write", "qa", 10, 50, $member['mb_id']);

echo latest_personel_write("personnal_qa", "qa", 10, 50, $member['mb_id']);

?>
</div>
<div  style="padding-bottom:20px;">
  <div class='sub_title'>
	  <span><strong>최근주문내역</strong></span>
	  <span class='btn_more'><a href='./orderinquiry.php'><img src='<?=$g4[shop_img_path]?>/icon_more.gif'></a></span>
  </div>

<?
// 최근 주문내역
define("_ORDERINQUIRY_", true);

$limit = " limit 0, 5 ";
include "$g4[shop_path]/orderinquiry.sub.php";
?>
</div>
<div style='margin-bottom:20px;clear:both;'>
  <div class='sub_title'>
	  <span><strong>찜한상품목록</strong></span>
	  <span class='btn_more'><a href='./wishlist.php'><img src='<?=$g4[shop_img_path]?>/icon_more.gif' border=0></a></span>
  </div>
<table width="100%" cellpadding="0" cellspacing="0" class="list_styleA">
  <colgroup>
    <col width="120"/>
    <col />
    <col width='250'/>
  </colgroup>
  <thead>
    <tr>
        <th colspan='2'>상품명</th>
        <th>보관일시</th>
    </tr>
  </thead>
  <tbody>

<?
$sql = " select *
           from $g4[yc4_wish_table] a,
                $g4[yc4_item_table] b
          where a.mb_id = '$member[mb_id]'
            and a.it_id  = b.it_id and isnull(a.ihappy_fg)
          order by a.wi_id desc
          limit 0, 3 ";
$result = sql_query($sql);
for ($i=0; $row = sql_fetch_array($result); $i++)
{
    if($row['it_name']){
		$row['it_name'] = get_item_name($row['it_name']);
	}
	//if ($i>0)
        //echo "<tr><td colspan=3 height=1 background='$g4[shop_img_path]/dot_line.gif'></td></tr>";

    $image = get_it_image($row[it_id]."_s", 50, 50, $row[it_id]);

    echo "<tr>";
    echo "<td>$image</td>";
    echo "<td style='text-align:left;'><a href='./item.php?it_id=$row[it_id]'>".stripslashes($row[it_name])."</a></td>";
    echo "<td>$row[wi_time]</td>";
    echo "</tr>";
}

if ($i == 0)
    echo "<tr><td colspan=3 height=100 align=center><span class=point>보관 내역이 없습니다.</span></td></tr>";
?>
  </tbody>
</table>
</div>


<!-- // 김선용 201208 : -->
<div style="padding-bottom:20px;">
  <div class='sub_title'>
	  <span><strong>품절상품 SMS 통보신청</strong></span>
	  <span class='btn_more'><a href="<?=$g4['path']?>/sjsjin/item_sms_list.php">신청내역보기</a></span>
  </div>
<table width="100%" cellpadding="0" cellspacing="0" class="list_styleA">
  <colgroup>
    <col width="100"/>
    <col />
    <col width='75'/>
    <col width='100'/>
    <col width='90'/>
    <col width='160'/>
    <col width='160'/>
  </colgroup>
  <thead>
  <tr>
	  <th colspan=2>상품명</th>
    <th>신청자</th>
    <th>통보번호</th>
    <th>구분</th>
	  <th>통보일시</th>
	  <th>신청일시</th>
  </tr>
  </thead>
  <tbody>
<?
$sql  = "select * from {$g4['item_sms_table']} where mb_id='{$member['mb_id']}' order by ts_id desc limit 0, 5";
$result = sql_query($sql);
for($k=0; $row=sql_fetch_array($result); $k++)
{
	$it = sql_fetch("select it_name from {$g4['yc4_item_table']} where it_id='{$row['it_id']}'");
	if($it['it_name']){
		$it['it_name'] = get_item_name($it['it_name']);
	}
	$href = "{$g4[shop_path]}/item.php?it_id=$row[it_id]";
?>
<tr>
	<td><a href='<?=$href?>' target=_blank title='새창으로 상품보기'><?=get_it_image("{$row[it_id]}_s", 60, 50)?></a></td>
	<td style='text-align:left;'><a href='<?=$href?>' target=_blank title='새창으로 상품보기'><?=stripslashes($it['it_name'])?></a></td>
	<td><?=$row['ts_name']?></td>
	<td><?=$row['ts_hp']?></td>
	<td><strong style='color:#ff3300;'><?=($row['ts_send'] ? '통보' : '미통보')?></strong></td>
	<td><?=str_replace(' ', '', $row['ts_send_time'])?></td>
	<td style='color:#73a8ce;'><?=str_replace(' ', '', $row['ts_time'])?></td>
</tr>
<?}?>
<?if(!$k) echo "<tr><td colspan=10 height=100 align=center>자료가 없습니다.</td></tr>"; ?>
  </tbody>
</table>
</div>

<?
include_once("./_tail.php");
?>