<?php 
/*
----------------------------------------------------------------------
file name	 : ok_category_covert_save.php
comment		 : 오플코리아 카테고리 연동 저장
date		 : 2014-09-11
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/

include_once("./_common.php");

$rs = sql_query("select it_id from oplekorea.yc4_category_item where ca_id='$_POST[oplekorea_last_code]'");

while($data = sql_fetch_array($rs)){

	sql_query("insert into yc4_category_item (it_id,ca_id) values ('$data[it_id]','$_POST[ople_last_code]')");
	
}

alert('복사가 완료되었습니다.',"./ok_category_convert.php?menu_1depth=$_POST[menu_1depth]&menu_2depth=$_POST[menu_2depth]&menu_3depth=$_POST[menu_3depth]&menu_4depth=$_POST[menu_4depth]&menu_5depth=$_POST[menu_5depth]");

?>