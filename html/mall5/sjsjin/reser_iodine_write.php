<?php
include_once("./_common.php");
include_once "{$g4['path']}/lib/mailer.lib.php";

/*
sql_query("CREATE TABLE {$g4['yc4_rs_table']} (
  `rs_pid` int NOT null AUTO_INCREMENT,
  `rs_name` varchar(20) not null default '',
  `rs_hp` varchar(20) NOT NULL default '',
  `rs_email` varchar(60) NOT NULL default '',
  PRIMARY KEY  (`rs_pid`),
  KEY `search_index` (`rs_name`, `rs_hp`)
)", false);
sql_query("alter table {$g4['yc4_rs_table']}
	add rs_datetime datetime not null,
	add rs_ip varchar(15) not null,
	add rs_agent varchar(255) not null", false);
$a = sql_fetch("select * from {$g4['yc4_rs_table']} order by rs_pid desc");
print_r2($a);
*/


/*
	mail.ople.com
	포트 : 587
	주소(도메인등) : 209.216.56.108
	info@ople.com
	qwe123password
*/

//$subject = "{$default[de_admin_company_name]}에서 다음과 같이 주문하셨습니다.";
//ob_start();
//include "{$g4['path']}/shop/mail/orderupdate2.mail.php";
//$content = ob_get_contents();
//ob_end_clean();
//$content = email_content("테스트메일, 테스트메일");
//mailer_test($default['de_admin_company_name'], $default['de_post_mail_addr'], "sjsjin@gmail.com", $subject, $content, 1);


$g4['title'] = '요오드(켈프) 예약 접수신청';
include_once("./_head.php");
?>
<br/>
<div>예약상품은 <a href="<?=$g4['shop_path']?>/item.php?it_id=1300274302" title="상품정보보기"><b><u>[source naturals] 요오드화 칼륨 정제 32.5 mg 120정 피폭시 상비용품</u></b></a> 입니다.</div>
<form name="" target="_self" method="post" action="reser_iodine_writeupdate.php" style="margin:0;">
<table cellpadding=2 cellspacing=2 align="center" width="100%" summary="" border=0 style="border:4px solid #eee;">
<tr>
	<td width=100>성명</td>
	<td><input type="text" name="rs_name" id="rs_name" size=20 class="ed" value="<?=$member['mb_name']?>" required itemname="성명"></td>
</tr>
<tr>
	<td>휴대전화</td>
	<td><input type="text" name="rs_hp" id="rs_hp" size=20 class="ed" value="<?=$member['mb_hp']?>" telnumber required itemname="휴대전화"> 예) 010-123(4)-1234</td>
</tr>
<tr>
	<td>이메일</td>
	<td><input type="text" name="rs_email" id="rs_email" size=40 class="ed" value="<?=$member['mb_email']?>" required itemname="이메일"></td>
</tr>
<tr>
	<td colspan=2 align=center><input type=submit value=" 확 인 "></td>
</tr>
</table>
</form>


<? include_once("./_tail.php"); ?>