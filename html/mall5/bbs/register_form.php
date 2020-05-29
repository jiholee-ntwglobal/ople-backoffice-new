<?
include_once("./_common.php");

if ($w == "") {

	// 김선용 201309 : 회원 프로모션
	// 이벤트ID 검증, 인젝션공격 방어
	if($_GET['mp_event']){
		if(preg_match("/[^\w]/", $_GET['mp_event'])) alert("회원가입 프로모션 코드가 정상적이지 않습니다.\\n\\n다시 확인해 주십시오. #1");
		$mp_event_id = mysql_real_escape_string($_GET['mp_event']);
		$chk = sql_fetch("select mp_pid from {$g4['yc4_member_promo']} where mp_event_id='$mp_event_id' ");
		if(!$chk['mp_pid']) alert("회원가입 프로모션 코드가 정상적이지 않습니다.\\n\\n다시 확인해 주십시오. #2");

	}



    // 회원 로그인을 한 경우 회원가입 할 수 없다
    // 경고창이 뜨는것을 막기위해 아래의 코드로 대체
    // alert("이미 로그인중이므로 회원 가입 하실 수 없습니다.", "./");
    if ($member[mb_id])
        goto_url($g4[path]);

    // 리퍼러 체크
    referer_check();



    // 주민등록번호를 사용한다면 중복검사를 합니다.
    if ($config[cf_use_jumin]) {
        $jumin = sql_password($mb_jumin);
        $row = sql_fetch(" select mb_name from $g4[member_table] where mb_jumin = '$jumin' ");
        if ($row[mb_name]) {
            if ($row[mb_name] == $mb_name)
                alert("이미 가입되어 있습니다.");
            else
                alert("다른 이름으로 같은 주민등록번호가 이미 가입되어 있습니다.\\n\\n관리자에게 문의해 주십시오.");
        }

        // 주민등록번호의 7번째 한자리 숫자
        $y = substr($mb_jumin, 6, 1);

        // 성별은 F, M 으로 나눈다.
        // 주민등록번호의 7번째 자리가 홀수이면 남자(Male), 짝수이면 여자(Female)
        $sex = $y % 2 == 0 ? "F" : "M";

        // 생일은 8자리로 만든다 (나중에 검색을 편하게 하기 위함)
        // 주민등록번호 앞자리를 그냥 생일로 사용함 ㅠㅠ
        // 주민등록번호 7번째 자리를 따져서...
        $birth = substr($mb_jumin, 0, 6);
        if ($y == 9 || $y == 0) // 1800년대생 (계시려나?)
            $birth = "18" . $birth;
        else if ($y == 1 || $y == 2) // 1900년대생
            $birth = "19" . $birth;
        else if ($y == 3 || $y == 4) // 2000년대생
            $birth = "20" . $birth;
        else // 오류
            $birth = "xx" . $birth;
    }

    $member[mb_birth] = $birth;
    $member[mb_sex] = $sex;
    $member[mb_name] = $mb_name;

    $g4[title] = "회원 가입";
}
else if ($w == "u")
{
    if ($is_admin)
        alert("관리자의 회원정보는 관리자 화면에서 수정해 주십시오.", $g4[path]);

    if (!$member[mb_id])
        alert("로그인 후 이용하여 주십시오.", $g4[path]);

    if ($member[mb_id] != $mb_id)
        alert("로그인된 회원과 넘어온 정보가 서로 다릅니다.");

    if (!($member[mb_password] == sql_password($_POST[mb_password]) && $_POST[mb_password]))
        alert("패스워드가 틀립니다.");

    // 수정 후 다시 이 폼으로 돌아오기 위해 임시로 저장해 놓음
    set_session("ss_tmp_password", $_POST[mb_password]);

    //if ($member[mb_id] == "xxx") alert("xxx 변경 불가");

    $g4[title] = "회원 정보 수정";
} else
    alert("w 값이 제대로 넘어오지 않았습니다.");

// 회원아이콘 경로
$mb_icon = "$g4[path]/data/member/".substr($member[mb_id],0,2)."/$member[mb_id].gif";
$member_skin_path = "$g4[path]/skin/member/$config[cf_member_skin]";

include_once("./_head.php");
include_once("./norobot.inc.php"); // 자동등록방지
include_once("$member_skin_path/register_form.skin.php");
include_once("./_tail.php");
?>