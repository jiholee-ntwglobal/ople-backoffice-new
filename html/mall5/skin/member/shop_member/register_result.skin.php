<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>
<div class='orange_box'>
<p class='box_title' style='text-align:center;padding-left:0;'><img src="<?=$member_skin_path?>/img/member_end_title.gif" alt='오플가입을 축하드립니다.'></p>
<div style='padding:0 30px 30px 30px;line-height:20px;text-align:center;'>
	<p style='padding:15px 0;font-size:15px;'><strong><?=$mb[mb_name]?></strong>님 오플가입을 진심으로 축하드립니다.</p>
	<p style='padding-bottom:10px;color:#000;'>오플 아이디는<strong><?=$mb[mb_id]?></strong>입니다.</p>
	<p>회원님의 패스워드는 아무도 알 수 없는 암호화 코드로 저장되므로 안심하셔도 좋습니다.</p>
    <p>아이디, 패스워드 분실시에는 회원가입시 입력하신 패스워드 분실시<br/> 질문, 답변을 이용하여 찾을 수 있습니다.</p>
                        
                        <? if ($config[cf_use_email_certify]) { ?>
                        <p>E-mail(<?=$mb[mb_email]?>)로 발송된 내용을 확인한 후 인증하셔야 회원가입이 완료됩니다.</p>
                        <? } ?>

    <p>회원의 탈퇴는 언제든지 가능하며 탈퇴 후 일정기간이 지난 후,<br/>회원님의 모든 소중한 정보는 삭제하고 있습니다.</p>
	<p>감사합니다.</p>
</div>
</div>
<p style='text-align:center;padding:30px 0;'><a href="<?=$g4[url]?>/"><img src="<?=$member_skin_path?>/img/btn_go_home.gif" border=0></a></p>
