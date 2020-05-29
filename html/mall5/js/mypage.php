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
<script type="text/JavaScript" src="<?=$g4['path']?>/js/jquery.msgbox.js"></script>


<div style="padding-top:10px; width:755px;"></div>
<div style="padding-top:10px; width:755px;">
<table width="755" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td width="319"><img src="<?=$g4['path']?>/images/menu/menu_title02.gif" width="319" height="26"></td>
	<td width="353" align="right" class="font11">HOME &gt; <span class="font11_orange">마이페이지</span></td>
</tr>
<tr><td height="1" colspan="2" bgcolor="#fa5a00"></td></tr>
</table>
</div>
<div style="padding-top:10px; width:755px;">
<table align=left width=98% style="padding-top:20px;">
<tr>
    <td><B><?=$member[mb_name]?></B> 님의 마이페이지입니다.</td>
    <td align=right>
        <? if ($is_admin == 'super') { echo "<a href='$g4[admin_path]/'><img src='$g4[shop_img_path]/btn_admin.gif' border=0 align='absmiddle'></a>"; } ?>
        <a href='<?=$g4[bbs_path]?>/member_confirm.php?url=register_form.php'><img src='<?=$g4[shop_img_path]?>/my_modify.gif' border=0 align='absmiddle'></a>
        <a href="javascript:member_leave();"><img src='<?=$g4[shop_img_path]?>/my_leave.gif' border=0 align='absmiddle'></a></td>
</tr>
</table></div>

<script type="text/JavaScript">
function member_leave()
{
    if (confirm("정말 회원에서 탈퇴 하시겠습니까?"))
            location.href = "<?=$g4[bbs_path]?>/member_confirm.php?url=member_leave.php";
}
</script>
<div style="padding-top:10px; width:755px;">
<table width=98% cellpadding=0 cellspacing=0 align=left style="padding:10px;margin-bottom:30px; border:1px solid #ccc">
<tr>
    <td height=25>&nbsp;&nbsp;&nbsp;보유포인트 </td>
    <td>: <a href="javascript:win_point();"><?=number_format($member[mb_point])?>점 (포인트 상세내역 보기)</a></td>
    <td>쪽지함</td>
    <td>: <a href="javascript:win_memo();">쪽지보기</a></td>
</tr>
<tr>
    <td height=25>&nbsp;&nbsp;&nbsp;주소</td>
    <td>: <?=sprintf("(%s-%s) %s %s", $member[mb_zip1], $member[mb_zip2], $member[mb_addr1], $member[mb_addr2]);?></td>
    <td>회원권한</td>
    <td>: <?=($mb_level_str[$member[mb_level]] != '' ? $mb_level_str[$member[mb_level]]." [{$member[mb_level]}]" : $member[mb_level]);?>
		<?if($is_admin === 'super'){?>
			<input type="button" value="혜택" onclick="$.alert('메시지');" />
		<?}?>
	</td>
</tr>
<tr>
    <td height=25>&nbsp;&nbsp;&nbsp;연락처</td>
    <td>: <?=$member[mb_tel]?></td>
    <td>최종접속일시</td>
    <td>: <?=$member[mb_today_login]?></td>
</tr>
<tr>
    <td height=25>&nbsp;&nbsp;&nbsp;E-mail</td>
    <td>: <?=$member[mb_email]?></td>
    <td>회원가입일시</td>
    <td>: <?=$member[mb_datetime]?></td>
</tr>
</table>
</div>
<div style="padding-top:10px; width:755px;">
<?
// 김선용 200804 : qna 질문/답변 최신글
// 인수 : 스킨디렉토리, 게시판테이블, 출력수, 제목글자수, 뽑을회원아이디, 검색쿼리(옵션 - 예) "and mb_id='aaa'")

echo latest_mb_write("mb_write", "qa", 10, 50, $member['mb_id']);

?>
</div>
<div style="padding-top:10px; width:755px;">
<table width=98% cellpadding=0 cellspacing=0 align=left style="padding-top:20px;">
<tr>
    <td height=35><img src='<?=$g4[shop_img_path]?>/my_title01.gif'></td>
    <td align=right><a href='./orderinquiry.php'><img src='<?=$g4[shop_img_path]?>/icon_more.gif' border=0></a></td>
</tr>
</table>
</div>
<div style="padding-top:10px; width:755px;">
<?
// 최근 주문내역
define("_ORDERINQUIRY_", true);

$limit = " limit 0, 5 ";
include "$g4[shop_path]/orderinquiry.sub.php";
?>
</div>
<div style="width:755px">
<table width=98% cellpadding=0 cellspacing=0 align=left style="padding-top:20px;">
<tr>
    <td height=35 colspan=2><img src='<?=$g4[shop_img_path]?>/my_title02.gif'></td>
    <td align=right><a href='./wishlist.php'><img src='<?=$g4[shop_img_path]?>/icon_more.gif' border=0></a></td>
</tr>
<tr><td height=2 colspan=3 class=c1></td></tr>
<tr align=center height=25 class=c2>
    <td colspan=2>상품명</td>
    <td>보관일시</td>
</tr>
<tr><td height=1 colspan=3 class=c1></td></tr>
<?
$sql = " select *
           from $g4[yc4_wish_table] a,
                $g4[yc4_item_table] b
          where a.mb_id = '$member[mb_id]'
            and a.it_id  = b.it_id
          order by a.wi_id desc
          limit 0, 3 ";
$result = sql_query($sql);
for ($i=0; $row = sql_fetch_array($result); $i++)
{
    if ($i>0)
        echo "<tr><td colspan=3 height=1 background='$g4[shop_img_path]/dot_line.gif'></td></tr>";

    $image = get_it_image($row[it_id]."_s", 50, 50, $row[it_id]);

    echo "<tr align=center height=60>";
    echo "<td width=100>$image</td>";
    echo "<td align=left><a href='./item.php?it_id=$row[it_id]'>".stripslashes($row[it_name])."</a></td>";
    echo "<td>$row[wi_time]</td>";
    echo "</tr>";
}

if ($i == 0)
    echo "<tr><td colspan=3 height=100 align=center><span class=point>보관 내역이 없습니다.</span></td></tr>";
?>
<tr><td height=1 colspan=3 class=c1></td></tr>
</table>
</div>


<!-- // 김선용 201208 : -->
<div style="width:755px; margin-top:20px;">
<p style="font-size:16px; font-weight:bold; margin:0;">품절상품 SMS 통보신청&nbsp;&nbsp;| <a href="<?=$g4['path']?>/sjsjin/item_sms_list.php">신청내역보기</a></P>
<table width=100% cellpadding=2 cellspacing=0 align=center border=0>
<tr><td colspan=10 style="height:1px; background-color:#9a9aa1;"></td></tr>
<tr><td style="height:2px; background-color:#e0e0e4;" colspan=10></td></tr>
<tr align=center>
	<td height=30 colspan=2>상품명</td>
	<td width=65>신청자</td>
	<td width=90>통보번호</td>
	<td width=50>구분</td>
	<td width=70>통보일시</td>
	<td width=70>신청일시</td>
</tr>
<tr><td style="height:2px; background-color:#e0e0e4;" colspan=10></td></tr>
<tr><td colspan=10 style="height:1px; background-color:#9a9aa1;"></td></tr>
<?
$sql  = "select * from {$g4['item_sms_table']} where mb_id='{$member['mb_id']}' order by ts_id desc limit 0, 5";
$result = sql_query($sql);
for($k=0; $row=sql_fetch_array($result); $k++)
{
	$it = sql_fetch("select it_name from {$g4['yc4_item_table']} where it_id='{$row['it_id']}'");
	$href = "{$g4[shop_path]}/item.php?it_id=$row[it_id]";
?>
<tr align=center>
	<td width=60><a href='<?=$href?>' target=_blank title='새창으로 상품보기'><?=get_it_image("{$row[it_id]}_s", 60, 50)?></a></td>
	<td><a href='<?=$href?>' target=_blank title='새창으로 상품보기'><?=stripslashes($it['it_name'])?></a></td>
	<td><?=$row['ts_name']?></td>
	<td><?=$row['ts_hp']?></td>
	<td ><font color="#ff3300"><?=($row['ts_send'] ? '통보' : '미통보')?></font></td>
	<td><?=str_replace(' ', '<br/>', $row['ts_send_time'])?></td>
	<td><?=str_replace(' ', '<br/>', $row['ts_time'])?></td>
</tr>
<?}?>
<?if(!$k) echo "<tr><td colspan=10 height=100 align=center>자료가 없습니다.</td></tr>"; ?>
<tr><td style="height:2px; background-color:#e0e0e4;" colspan=10></td></tr>
<tr><td colspan=10 style="height:1px; background-color:#9a9aa1;"></td></tr>
</table>
</div>

<?
include_once("./_tail.php");
?>