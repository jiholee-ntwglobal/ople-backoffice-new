<?
$menu["menu100"] = array (
    array("100000", "시스템 관리", ""),
    array("", "기본환경설정", "$g4[admin_path]/config_form.php"),
	array("100100", "BUI접근IP설정", "{$g4['shop_admin_path']}/bui_ip_access.php"),
    array("100200", "관리권한설정", "$g4[admin_path]/auth_list.php"),	
    array("100300", "복구/최적화", "$g4[admin_path]/repair.php"),
    array("100400", "세션 삭제", "$g4[admin_path]/session_delete.php"),
    array("100500", "쇼핑몰설정", "$g4[shop_admin_path]/configform.php"),
	array("100600", "확장설정", "$g4[shop_admin_path]/configform_extension.php"),
	array("100700", "분류관리", "$g4[shop_admin_path]/categorylist.php"),
    array("100710", "제품관 관리", "$g4[shop_admin_path]/station.php"),
	array("100800", "게시판관리", "$g4[admin_path]/board_list.php"),
    array("100900", "게시판그룹관리", "$g4[admin_path]/boardgroup_list.php"),
	array("100999", "IP 관리", "$g4[admin_path]/ip_manager.php"),
);
?>