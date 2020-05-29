<?
include_once("./_common.php");



if (!$member[mb_id]) {
        $msg = "비회원은 이 게시판에 접근할 권한이 없습니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오.";
        if ($cwin)
            alert_close($msg);
        else
            alert($msg, "./login.php?wr_id=$wr_id{$qstr}&url=".urlencode("./board_personnel_qa.php"));
    }


if (!$page) $page = 1;

 $g4[title] = "1:1문의 $page 페이지";



//if (!($board[bo_use_comment] && $cwin))
//    include_once("./board_head.php");

include_once("$g4[path]/_head.php");


//$g4[path] = '/mall6';


echo "<script type=\"text/javascript\" src=\"$g4[path]/js/sideview.js\"></script>\n";

switch($_GET['mode']){
	case 'view':
		include_once("./board_personnel_qa_view.php");
	break;
	case 'write': case 'edit':
		include_once("./board_personnel_qa_write.php");
	break;
	case 'delete':
		include_once("./board_personnel_qa_delete.php");
	break;	
}


if ($_GET['mode'] == 'view' || $_GET['mode'] == '')
	include_once ("./board_personnel_qa_list.php");

//include_once("./board_tail.php");;

//include_once("$g4[path]/tail.sub.php");

include_once("$g4[path]/_tail.php");
?>
<!-- <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:8px;">
<tr>
        <td style="font-size:11px; color:#666;">
        <span>전체 1</span>
                <a href="../adm/board_form.php?w=u&bo_table=qa"><img src="../skin/board/relation/img/btn_admin.gif" title="관리자" width="63" height="22" border="0" align="absmiddle"></a></td>
</tr>
</table>
 -->

