<?
$sub_menu = "100500";
include_once("./_common.php");
include_once ("$g4[path]/lib/cheditor4.lib.php");
auth_check($auth[$sub_menu], "r");


$g4[title] = "쇼핑몰설정";
include_once ("$g4[admin_path]/admin.head.php");
?>

<script src="<?=$g4[path]?>/cheditor5/cheditor.js"></script>
<?=cheditor1('de_baesong_content', '100%', '200');?>
<?=cheditor1('de_change_content', '100%', '200');?>

<form name=fconfig method=post action='./configformupdate.php' onsubmit="return fconfig_check(this)" enctype="MULTIPART/FORM-DATA" style="margin:0px;">

<a name="사업자정보"></a>
<p>
<table width=100% cellpadding=0 cellspacing=0>
<tr>
	<td width=50%><?=subtitle("사업자정보")?></td>
	<td width=50% align=right><span class=small><a href='#사업자정보'>사업자정보</a> | <a href='#초기화면'>초기화면</a> | <a href='#결제정보'>결제정보</a> | <a href='#배송정보'>배송정보</a> | <a href='#기타정보'>기타정보</a> | <a href='#SMS정보'>SMS정보</a></span></td>
</tr>
</table>

<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>
<tr class=ht>
	<td>회사명</td>
	<td>
		<input type=text name=de_admin_company_name value='<?=$default[de_admin_company_name]?>' size=30 class=ed>
		<?=help("사업자정보는 tail.php 와 content.php 에서 표시합니다.")?>
	</td>
	<td>사업자등록번호</td>
	<td>
		<input type=text name=de_admin_company_saupja_no  value='<?=$default[de_admin_company_saupja_no]?>' size=30 class=ed>
	</td>
</tr>
<tr class=ht>
	<td>대표자명</td>
	<td colspan=3>
		<input type=text name=de_admin_company_owner value='<?=$default[de_admin_company_owner]?>' size=30 class=ed>
	</td>
</tr>
<tr class=ht>
	<td>대표전화번호</td>
	<td>
		<input type=text name=de_admin_company_tel value='<?=$default[de_admin_company_tel]?>' size=30 class=ed>
	</td>
	<td>팩스번호</td>
	<td>
		<input type=text name=de_admin_company_fax value='<?=$default[de_admin_company_fax]?>' size=30 class=ed>
	</td>
</tr>
<tr class=ht>
	<td>통신판매업 신고번호</td>
	<td>
		<input type=text name=de_admin_tongsin_no value='<?=$default[de_admin_tongsin_no]?>' size=30 class=ed>
	</td>
	<td>부가통신 사업자번호</td>
	<td>
		<input type=text name=de_admin_buga_no value='<?=$default[de_admin_buga_no]?>' size=30 class=ed>
	</td>
</tr>
<tr class=ht>
	<td>사업장우편번호</td>
	<td>
		<input type=text name=de_admin_company_zip value='<?=$default[de_admin_company_zip]?>' size=10 class=ed>
	</td>
	<td>사업장주소</td>
	<td>
		<input type=text name=de_admin_company_addr value='<?=$default[de_admin_company_addr]?>' size=30 class=ed>
	</td>
</tr>
<tr class=ht>
	<td>정보관리책임자명</td>
	<td>
		<input type=text name=de_admin_info_name value='<?=$default[de_admin_info_name]?>' size=30 class=ed>
	</td>
	<td>정보책임자 e-mail</td>
	<td>
		<input type=text name=de_admin_info_email value='<?=$default[de_admin_info_email]?>' size=30 class=ed>
	 </td>
</tr>
<!-- // 김선용 201207 : -->
<tr class=ht>
	<td>메일발송주소</td>
	<td colspan=3>
		<input type=text name=de_post_mail_addr value='<?=$default['de_post_mail_addr']?>' size=30 class=ed> ※ 여기에 입력된 메일주소를 주문서 발송메일 주소로 사용합니다.
	</td>
</tr>
<tr class=ht>
	<td>메일수신주소</td>
	<td colspan=3>
		<input type=text name=de_receive_mail_addr value='<?=$default['de_receive_mail_addr']?>' size=30 class=ed> ※ 여기에 입력된 메일주소로 주문서 사본메일 발송
	</td>
</tr>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>


<a name="초기화면"></a>
<p>
<table width=100% cellpadding=0 cellspacing=0>
<tr>
	<td width=50%><?=subtitle("초기화면")?></td>
	<td width=50% align=right><span class=small><a href='#사업자정보'>사업자정보</a> | <a href='#초기화면'>초기화면</a> | <a href='#결제정보'>결제정보</a> | <a href='#배송정보'>배송정보</a> | <a href='#기타정보'>기타정보</a> | <a href='#SMS정보'>SMS정보</a></span></td>
</tr>
</table>

<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=15%></colgroup>
<colgroup width=85% bgcolor=#FFFFFF></colgroup>
<tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>
<tr class=ht>
	<td>히트상품출력</td>
	<td>
		출력 : <input type=checkbox name=de_type1_list_use value='1' <?=$default[de_type1_list_use]?"checked":"";?>>
		, 스킨 : <select id=de_type1_list_skin name=de_type1_list_skin><?=get_list_skin_options("^maintype(.*)\.php", $g4[shop_path]);?></select><script>document.getElementById('de_type1_list_skin').value='<?=$default[de_type1_list_skin]?>';</script>
		, 1라인이미지수 : <input type=text name=de_type1_list_mod value='<?=$default[de_type1_list_mod]?>' size=3 class=ed>
		, 라인 : <input type=text name=de_type1_list_row value='<?=$default[de_type1_list_row]?>' size=3 class=ed>
		, 폭 : <input type=text name=de_type1_img_width value='<?=$default[de_type1_img_width]?>' size=3 class=ed>
		, 높이 : <input type=text name=de_type1_img_height value='<?=$default[de_type1_img_height]?>' size=3 class=ed>
		<?=help("상품관리에서 히트상품으로 선택한 상품들을 설정값대로 초기화면에 출력합니다.\n히트상품으로 체크한 상품이 없다면 초기화면에 출력하지 않습니다.\n추천상품과 신상품도 같은 방법으로 사용합니다.", -150)?>
	</td>
</tr>
<tr class=ht>
	<td>추천상품출력</td>
	<td>
		출력 : <input type=checkbox name=de_type2_list_use value='1' <?=$default[de_type2_list_use]?"checked":"";?>>
		, 스킨 : <select id=de_type2_list_skin name=de_type2_list_skin><?=get_list_skin_options("^maintype(.*)\.php", $g4[shop_path]);?></select><script>document.getElementById('de_type2_list_skin').value='<?=$default[de_type2_list_skin]?>';</script>
		, 1라인이미지수 : <input type=text name=de_type2_list_mod value='<?=$default[de_type2_list_mod]?>' size=3 class=ed>
		, 라인 : <input type=text name=de_type2_list_row value='<?=$default[de_type2_list_row]?>' size=3 class=ed>
		, 폭 : <input type=text name=de_type2_img_width value='<?=$default[de_type2_img_width]?>' size=3 class=ed>
		, 높이 : <input type=text name=de_type2_img_height value='<?=$default[de_type2_img_height]?>' size=3 class=ed>
	</td>
</tr>
<tr class=ht>
	<td>최신상품출력</td>
	<td>
		출력 : <input type=checkbox name=de_type3_list_use value='1' <?=$default[de_type3_list_use]?"checked":"";?>>
		, 스킨 : <select id=de_type3_list_skin name=de_type3_list_skin><?=get_list_skin_options("^maintype(.*)\.php", $g4[shop_path]);?></select><script>document.getElementById('de_type3_list_skin').value='<?=$default[de_type3_list_skin]?>';</script>
		, 1라인이미지수 : <input type=text name=de_type3_list_mod value='<?=$default[de_type3_list_mod]?>' size=3 class=ed>
		, 라인 : <input type=text name=de_type3_list_row value='<?=$default[de_type3_list_row]?>' size=3 class=ed>
		, 폭 : <input type=text name=de_type3_img_width value='<?=$default[de_type3_img_width]?>' size=3 class=ed>
		, 높이 : <input type=text name=de_type3_img_height value='<?=$default[de_type3_img_height]?>' size=3 class=ed>
	</td>
</tr>
<tr class=ht>
	<td>인기상품출력</td>
	<td>
		출력 : <input type=checkbox name=de_type4_list_use value='1' <?=$default[de_type4_list_use]?"checked":"";?>>
		, 스킨 : <select id=de_type4_list_skin name=de_type4_list_skin><?=get_list_skin_options("^maintype(.*)\.php", $g4[shop_path]);?></select><script>document.getElementById('de_type4_list_skin').value='<?=$default[de_type4_list_skin]?>';</script>
		, 1라인이미지수 : <input type=text name=de_type4_list_mod value='<?=$default[de_type4_list_mod]?>' size=3 class=ed>
		, 라인 : <input type=text name=de_type4_list_row value='<?=$default[de_type4_list_row]?>' size=3 class=ed>
		, 폭 : <input type=text name=de_type4_img_width value='<?=$default[de_type4_img_width]?>' size=3 class=ed>
		, 높이 : <input type=text name=de_type4_img_height value='<?=$default[de_type4_img_height]?>' size=3 class=ed>
	</td>
</tr>
<tr class=ht>
	<td>할인상품출력</td>
	<td>
		출력 : <input type=checkbox name=de_type5_list_use value='1' <?=$default[de_type5_list_use]?"checked":"";?>>
		, 스킨 : <select id=de_type5_list_skin name=de_type5_list_skin><?=get_list_skin_options("^maintype(.*)\.php", $g4[shop_path]);?></select><script>document.getElementById('de_type5_list_skin').value='<?=$default[de_type5_list_skin]?>';</script>
		, 1라인이미지수 : <input type=text name=de_type5_list_mod value='<?=$default[de_type5_list_mod]?>' size=3 class=ed>
		, 라인 : <input type=text name=de_type5_list_row value='<?=$default[de_type5_list_row]?>' size=3 class=ed>
		, 폭 : <input type=text name=de_type5_img_width value='<?=$default[de_type5_img_width]?>' size=3 class=ed>
		, 높이 : <input type=text name=de_type5_img_height value='<?=$default[de_type5_img_height]?>' size=3 class=ed>
	</td>
</tr>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>


<a name="결제정보"></a>
<p>
<table width=100% cellpadding=0 cellspacing=0>
<tr>
	<td width=50%><?=subtitle("결제정보")?></td>
	<td width=50% align=right><span class=small><a href='#사업자정보'>사업자정보</a> | <a href='#초기화면'>초기화면</a> | <a href='#결제정보'>결제정보</a> | <a href='#배송정보'>배송정보</a> | <a href='#기타정보'>기타정보</a> | <a href='#SMS정보'>SMS정보</a></span></td>
</tr>
</table>

<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>

<? // 김선용 200801?>
<tr class=ht>
    <td>기준환율(원화)</td>
    <td colspan=3>
        <input type='hidden' name='bf_de_conv_pay' value='<?=$default[de_conv_pay]?>' size='10' class='ed'>
        <input type='text' name='de_conv_pay' value='<?=$default[de_conv_pay]?>' size='10' class='ed'>
        <?=help("환율 기준가(원화)를 입력합니다.  숫자만 입력해야 합니다.", 50)?>
    </td>
</tr>

<tr class=ht>
	<td>무통장입금사용</td>
	<td>
		<select id=de_bank_use name=de_bank_use>
		<option value='0'>아니오
		<option value='1'>예
		</select>
		<script>document.getElementById('de_bank_use').value="<?=$default[de_bank_use]?>";</script>
		<?=help("주문시 무통장으로 입금을 가능하게 할것인지를 설정합니다.\n사용할 경우 은행계좌번호를 반드시 입력하여 주십시오.", 50)?>
	</td>
	<td rowspan=2>은행계좌번호</td>
	<td rowspan=2>
		<textarea name=de_bank_account rows=3 class=ed style='width:99%;'><?=$default[de_bank_account]?></textarea>
	</td>
</tr>
<tr class=ht>
	<td>계좌이체 결제사용</td>
	<td>
		<select id=de_iche_use name=de_iche_use>
		<option value='0'>아니오
		<option value='1'>예
		</select>
		<script>document.getElementById('de_iche_use').value="<?=$default[de_iche_use]?>";</script>
		<?=help("주문시 실시간 계좌이체를 가능하게 할것인지를 설정합니다.", 50)?>
	</td>
</tr>
<tr>
    <td>가상계좌 결제사용</td>
    <td>
        <select name=de_vbank_use>
        <option value='0'>아니오
        <option value='1'>예
        </select>
        <script>document.fconfig.de_vbank_use.value="<?=$default[de_vbank_use]?>";</script>
        <?=help("주문자가 현금거래를 원할 경우, 해당 거래건에 대해 주문자에게 고유로 발행되는 일회용 계좌번호입니다.", 50)?>
    </td>
</tr>
<tr class=ht>
	<td>신용카드결제사용</td>
	<td>
		<select id=de_card_use name=de_card_use>
		<option value='0'>아니오
		<option value='1'>예
		</select>
		<script>document.getElementById('de_card_use').value="<?=$default[de_card_use]?>";</script>
		<?=help("주문시 신용카드 결제를 가능하게 할것인지를 설정합니다.", 50)?>
	</td>
	<td>카드결제최소금액</td>
	<td>
		<input type=text name=de_card_max_amount value='<?=$default[de_card_max_amount]?>' size=10 class=ed> 원
		<?=help("신용카드의 경우 1000원 미만은 결제가 가능하지 않습니다.\n1000원 이상으로 설정하십시오.")?>
	</td>
</tr>
<tr class=ht>
	<td>포인트 사용</td>
	<td colspan=3>
		<input type='checkbox' name='cf_use_point' value='1' <?=$config[cf_use_point]?'checked':'';?>> 사용
		<?=help("환경설정 > 기본환경설정과 동일한 설정입니다.")?>
	</td>
</tr>
<tr class=ht>
	<td>포인트 결제사용</td>
	<td>
		<input type=text name=de_point_settle value='<?=$default[de_point_settle]?>' size=10 class=ed> 점
		<?=help("회원의 포인트가 설정값 이상일 경우만 주문시 결제에 사용할 수 있습니다.\n\n포인트 사용을 하지 않는 경우에는 의미가 없습니다.")?>
	</td>
	<td>포인트결제 %</td>
	<td>
		<select id=de_point_per name=de_point_per>
		<? for ($i=100; $i>0; $i=$i-5) echo "<option value='$i'>{$i}\n"; ?>
		</select>%
		<?=help("회원의 포인트가 포인트 결제사용 포인트 보다 클 경우 주문금액의 몇% 까지 사용 가능하게 할지를 설정합니다.")?>
		<script language="JavaScript">document.getElementById('de_point_per').value='<?=$default[de_point_per]?>';</script>
	</td>
</tr>
<tr class=ht>
	<td>포인트부여</td>
	<td>
		<select id=de_card_point name=de_card_point>
		<option value='0'>아니오
		<option value='1'>예
		</select>
		<script>document.getElementById('de_card_point').value="<?=$default[de_card_point]?>";</script>
		<?=help("신용카드, 계좌이체 결제시 포인트를 부여할지를 설정합니다. (기본값은 '아니오')", 50)?>
	</td>
	<td>주문완료 포인트</td>
	<td>
		주문 완료 <input type=text name=de_point_days value='<?=$default[de_point_days]?>' size=5 class=ed> 일 이후에 포인트를 부여
		<?=help("설정값 이후에 포인트를 부여합니다.(주문자가 회원일 경우에만 해당)\n\n주문취소, 반품 등을 고려하여 적당한 기간을 입력하십시오. (기본값은 7)\n\n0 으로 설정하는 경우 주문과 동시에 포인트를 부여합니다.", -150)?>
	</td>
</tr>
<tr class=ht>
	<td>결제대행사</td>
	<td colspan=3>
		<select id=de_card_pg name=de_card_pg>
		<option value='kcp'>KCP
		<option value='dacom'>데이콤
		<option value='inicis'>이니시스
		<option value='banktown'>뱅크타운
		<option value='allthegate'>올더게이트
		<!--<option value='allat'>올앳 -->
		<option value='tgcorp'>티지코프
		<option value='kspay'>KSPAY
		<option value='authorize'>authorize
		</select>
		<script>document.getElementById('de_card_pg').value="<?=$default[de_card_pg]?>";</script>
		<?=help("흔히들 PG사라고 말하는 결제대행사를 선택합니다.\n\nKCP의 경우 카드결제 수수료를 3%(업계 최저)로 적용 받으실 수 있습니다.\nKCP와 계약하는 방법은 저희 홈페이지를 참고하시기 바랍니다.")?>
	</td>
</tr>

<tr class=ht>
	<td>신용카드 결제테스트</td>
	<td>
		<input type=checkbox name=de_card_test id=de_card_test value='1' <?=$default[de_card_test]?"checked":"";?>><label for="de_card_test">테스트사용</label>&nbsp;
		<?=help("신용카드를 테스트 하실 경우에 체크하세요. 결제단위 최소 1,000원\n\n계좌이체는 테스트가 없으며 실결제 됩니다. 결제단위 최소 300원")?>
	</td>
</tr>

<? // 김선용 200801?>
<tr class=ht>
    <td>Authorize API</td>
    <td><input type=text name='de_authorize_id' value='<?=$default[de_authorize_id]?>' size=40 class=ed></td>
    <td>Tran Key</td>
    <td><input type=text name='de_authorize_key' value='<?=$default[de_authorize_key]?>' size=40 class=ed></td>
</tr>



<!-- 김선용 2014.03 :  -->
<tr><td height=1 bgcolor=black colspan=4></td></tr>
<tr><td height=3 colspan=4></td></tr>
<tr class=ht>
	<td>KCP SITE CODE</td>
	<td><input type=text name=de_kcp_mid value='<?=$default[de_kcp_mid]?>' class=ed size=6 maxlength=5> 영대문자1+숫자4</td>
	<td>KCP SITE KEY</td>
	<td><input type=text name=de_kcp_site_key value='<?=$default[de_kcp_site_key]?>' class=ed size=32>
        <a href='http://testpay.kcp.co.kr/Pay/Test/site_key.jsp' target=_blank>SITE KEY 생성</td>
</tr>
<tr class=ht>
	<td>KCP 결제설정</TD>
	<TD COLSPAN=3>
		<label><input type=checkbox name=de_kcp_card_use id=de_kcp_card_use value=1 <?if($default['de_kcp_card_use']) echo 'checked';?> />카드결제사용</label>
		<label><input type=checkbox name=de_kcp_escrow_use id=de_kcp_escrow_use value=1 <?if($default['de_kcp_escrow_use']) echo 'checked';?> />가상계좌사용</label>
		&nbsp;&nbsp;<strong>가상계좌 결제시 </strong><label><input size=4 class=ed type=text name=de_kcp_escrow_point id=de_kcp_escrow_point value="<?=$default['de_kcp_escrow_point']?>" /> % 추가적립 (정수). 고객이 수령확인시 적립됩니다.
		<!--<p style="margin-top:5px;"><strong>※ 주) 가상계좌(에스크로)는 한국시간 07~23시까지만 출력, 사용됩니다. (구매총합이 5만원 이상일때 출력)</strong></p>-->
	</td>
</tr>
<tr><td height=3 colspan=4></td></tr>
<tr><td height=1 bgcolor=black colspan=4></td></tr>


<tr class=ht>
	<td>데이콤 몰ID</td>
	<td>
		<input type=text name=de_dacom_mid value='<?=$default[de_dacom_mid]?>' class=ed size=40>
	</td>
	<td>데이콤 mertkey</td>
	<td>
		<input type=text name=de_dacom_mertkey value='<?=$default[de_dacom_mertkey]?>' class=ed size=40>
	</td>
	<!-- <td>데이콤 테스트 모드</td>
    	<td><input type=checkbox name=de_dacom_test value='1' <?=$default[de_dacom_test]?"checked":"";?>> 테스트로 결제하실 경우에 체크하세요.</td> -->
</tr>
<tr class=ht>
	<td>이니시스 아이디</td>
	<td>
		<input type=text name=de_inicis_mid value='<?=$default[de_inicis_mid]?>' class=ed size=40>
	</td>
	<td>이니시스 패스워드</td>
	<td>
		<input type=text name=de_inicis_passwd value='<?=$default[de_inicis_passwd]?>' class=ed>
	</td>
</tr>
<tr class=ht>
	<td>뱅크타운 상점ID</td>
	<td>
		<input type=text name=de_banktown_mid value='<?=$default[de_banktown_mid]?>' class=ed size=40>
	</td>
	<td>뱅크타운 라이센스 키<!-- AuthKey --></td>
	<td>
		<input type=text name=de_banktown_auth_key value='<?=$default[de_banktown_auth_key]?>' size=40 maxlength=32 class=ed>
	</td>
</tr>
<tr class=ht>
	<td>올더게이트 몰ID</td>
	<td colspan=3>
		<input type=text name=de_allthegate_mid value='<?=$default[de_allthegate_mid]?>' class=ed size=40>
	</td>
</tr>
<tr style='display:none;' class=ht>
	<td>올앳 파트너 ID</td>
	<td>
		<input type=text name=de_allat_partner_id value='<?=$default[de_allat_partner_id]?>' class=ed size=40>
	</td>
	<td>주문번호 Prefix</td>
	<td>
		<input type=text name=de_allat_prefix value='<?=$default[de_allat_prefix]?>' class=ed> 3자리
	</td>
</tr>
<tr style='display:none;' class=ht>
	<td>올앳 FormKey 값</td>
	<td>
		<input type=text name=de_allat_formkey value='<?=$default[de_allat_formkey]?>' class=ed size=40>
	</td>
	<td>올앳 CrossKey 값</td>
	<td>
		<input type=text name=de_allat_crosskey value='<?=$default[de_allat_crosskey]?>' class=ed size=40>
	</td>
</tr>
<tr class=ht>
	<td>티지코프 ID</td>
	<td>
		<input type=text name=de_tgcorp_mxid value='<?=$default[de_tgcorp_mxid]?>' class=ed size=40>
	</td>
	<td>티지코프 접근키</td>
	<td>
		<input type=text name=de_tgcorp_mxotp value='<?=$default[de_tgcorp_mxotp]?>' class=ed size=40>
	</td>
</tr>
<tr class=ht>
	<td>KSPAY 상점아이디</td>
	<td colspan=3>
		<input type=text name=de_kspay_id value='<?=$default[de_kspay_id]?>' class=ed size=40>
	</td>
</tr>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>



<a name="배송정보"></a>
<p>
<table width=100% cellpadding=0 cellspacing=0>
<tr>
	<td width=50%><?=subtitle("배송정보")?></td>
	<td width=50% align=right><span class=small><a href='#사업자정보'>사업자정보</a> | <a href='#초기화면'>초기화면</a> | <a href='#결제정보'>결제정보</a> | <a href='#배송정보'>배송정보</a> | <a href='#기타정보'>기타정보</a> | <a href='#SMS정보'>SMS정보</a></span></td>
</tr>
</table>

<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>
<tr class=ht>
	<td>배송비유형</td>
	<td colspan=3>
		<select id=de_send_cost_case name=de_send_cost_case>
		<option value="상한">상한
		<option value="없음">없음
		</select>
		<script>document.getElementById('de_send_cost_case').value="<?=$default[de_send_cost_case]?>";</script>
		<?=help("'상한'으로 설정한 경우는 주문총액이 배송비상한가 미만일 경우 배송비를 받습니다.\n\n'없음'으로 설정한 경우에는 배송비상한가, 배송비를 무시하며 착불의 경우도 없음으로 설정하여 사용합니다.", 50);?>
	</td>
</tr>
<tr class=ht>
	<td>배송비상한가</td>
	<td colspan=3>
		<input type=text name=de_send_cost_limit value='<?=$default[de_send_cost_limit]?>' size=40 class=ed> 원
		<?=help("배송비유형이 '상한'일 경우에만 해당되며 배송비상한가를 여러개 두고자 하는 경우는 <b>;</b> 로 구분합니다.\n\n예를 들어 20000원 미만일 경우 4000원, 30000원 미만일 경우 3000원 으로 사용할 경우에는 배송비상한가를 20000;30000 으로 입력하고 배송비를 4000;3000 으로 입력합니다.", 50);?>
	</td>
</tr>
<tr class=ht>
	<td>배송비</td>
	<td colspan=3>
		<input type=text name=de_send_cost_list value='<?=$default[de_send_cost_list]?>' size=40 class=ed> 원
	</td>
</tr>
<tr class=ht>
	<td>희망배송일사용</td>
	<td>
		<select id=de_hope_date_use name=de_hope_date_use>
		<option value="0">아니오
		<option value="1">예
		</select>
		<script>document.getElementById('de_hope_date_use').value="<?=$default[de_hope_date_use]?>";</script>
		<?=help("'예'로 설정한 경우 주문서에서 희망배송일을 입력 받습니다.", 50);?>
	</td>
	<td>희망배송일날짜</td>
	<td>
		<input type=text name=de_hope_date_after value='<?=$default[de_hope_date_after]?>' size=5 class=ed> 일
		<?=help("설정한날 이후의 날짜부터 일주일까지 선택박스 형식으로 출력합니다.", 50);?>
	</td>
</tr>
<tr class=ht>
	<td>배송정보</td>
	<td colspan=3><?=cheditor2('fconfig', 'de_baesong_content', stripslashes($default[de_baesong_content]));?></td>
</tr>
<tr>
	<td>교환/반품</td>
	<td colspan=3><?=cheditor2('fconfig', 'de_change_content', stripslashes($default[de_change_content]));?></td>
</tr>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>


<a name="기타정보"></a>
<p>
<table width=100% cellpadding=0 cellspacing=0>
<tr>
	<td width=50%><?=subtitle("기타정보")?></td>
	<td width=50% align=right><span class=small><a href='#사업자정보'>사업자정보</a> | <a href='#초기화면'>초기화면</a> | <a href='#결제정보'>결제정보</a> | <a href='#배송정보'>배송정보</a> | <a href='#기타정보'>기타정보</a> | <a href='#SMS정보'>SMS정보</a></span></td>
</tr>
</table>

<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>
<tr class=ht>
	<td>관련상품출력</td>
	<td colspan=3>
		1라인이미지수 : <input type=text name=de_rel_list_mod value='<?=$default[de_rel_list_mod]?>' size=3 class=ed>
		, 이미지폭 : <input type=text name=de_rel_img_width value='<?=$default[de_rel_img_width]?>' size=3 class=ed>
		, 이미지높이 : <input type=text name=de_rel_img_height value='<?=$default[de_rel_img_height]?>' size=3 class=ed>
		<?=help("$cart_dir/item.sub.adding.php 에서 $cart_dir/maintype1.inc.php 를 include 하여 출력합니다.");?>
	</td>
</tr>
<tr class=ht>
	<td>이미지(소) 폭</td>
	<td>
		<input type=text name=de_simg_width value='<?=$default[de_simg_width]?>' size=5 class=ed> 픽셀
		<?=help("상품관리의 상품입력에서 이미지(대) 입력으로 자동생성해 줄때 이미지(소)의 폭과 높이를 설정한 값으로 생성하여 줍니다.");?>
	</td>
	<td>이미지(소) 높이</td>
	<td>
		<input type=text name=de_simg_height value='<?=$default[de_simg_height]?>' size=5 class=ed> 픽셀
	</td>
</tr>
<tr class=ht>
	<td>이미지(중) 폭</td>
	<td>
		<input type=text name=de_mimg_width value='<?=$default[de_mimg_width]?>' size=5 class=ed> 픽셀
		<?=help("상품관리의 상품입력에서 이미지(대) 입력으로 자동생성해 줄때 이미지(중)의 폭과 높이를 설정한 값으로 생성하여 줍니다.");?>
	</td>
	<td>이미지(중) 높이</td>
	<td>
		<input type=text name=de_mimg_height value='<?=$default[de_mimg_height]?>' size=5 class=ed> 픽셀
	</td>
</tr>
<tr class=ht>
	<td>로고이미지</td>
	<td colspan=3>
		<input type=file class=ed name=logo_img size=40>
		<?
		$logo_img = "$g4[path]/data/common/logo_img";
		if (file_exists($logo_img))
		{
			$size = getimagesize($logo_img);
			echo "<img src='$g4[admin_path]/img/icon_viewer.gif' border=0 align=absmiddle onclick=\"imageview('id_logo_img', $size[0], $size[1]);\"><input type=checkbox name=logo_img_del value='1'>삭제";
			echo "<div id='id_logo_img' style='left:0; top:0; z-index:+1; display:none; position:absolute;'><img src='$logo_img' border=1></div>";
		}
		?>
		<?=help("쇼핑몰에 사용하는 로고이미지 입니다.\n이미지 파일만 업로드 가능합니다.");?>
	</td>
</tr>
<tr class=ht>
	<td>메인이미지</td>
	<td colspan=3>
		<input type=file class=ed name=main_img size=40>
		<?
		$main_img = "$g4[path]/data/common/main_img";
		if (file_exists($main_img))
		{
			$size = getimagesize($main_img);
			echo "<img src='$g4[admin_path]/img/icon_viewer.gif' border=0 align=absmiddle onclick=\"imageview('id_main_img', $size[0], $size[1]);\"><input type=checkbox name=main_img_del value='1'>삭제";
			echo "<div id='id_main_img' style='left:0; top:0; z-index:+1; display:none; position:absolute;'><img src='$main_img' border=1></div>";
		}
		?>
		<?=help("쇼핑몰에 사용하는 메인이미지 입니다.\n이미지 파일만 업로드 가능합니다.");?>
	</td>
</tr>
<tr class=ht>
	<td>사용후기</td>
	<td colspan=3>
		<select id=de_item_ps_use name=de_item_ps_use>
		<option value="0">관리자 승인없이 출력
		<option value="1">관리자 승인 후 출력
		</select>
		<script>document.getElementById('de_item_ps_use').value="<?=$default[de_item_ps_use]?>";</script>
		<?=help("고객이 특정 상품에 사용후기를 작성하였을 경우 바로 출력할것인지 관리자 승인 후 출력할것인지를 설정합니다.", 50);?>
	</td>
</tr>

<?/*?>
<tr class=ht>
	<td>스크롤배너 사용</td>
	<td colspan=3>
		<select id=de_scroll_banner_use name=de_scroll_banner_use>
		<option value="0">아니오
		<option value="1">예
		</select>
		<script>document.getElementById('de_scroll_banner_use').value="<?=$default[de_scroll_banner_use]?>";</script>
		<?=help("'예'로 설정한 경우 쇼핑몰 우측에 스크롤배너가 출력됩니다.", 50);?>
	</td>
</tr>
<?*/?>

<tr class=ht>
	<td>상품구입 권한</td>
	<td>
		<?=get_member_level_select('de_level_sell', 1, 10, $default[de_level_sell]) ?>
		<?=help("설정을 1로 하게되면 모든 방문자에게 판매를 할 수 있지만 설정을 변경하여 특정회원을 대상으로 판매를 할 수 있습니다.");?>
	</td>
</tr>
<tr class=ht>
	<td>코드 중복검사</td>
	<td>
		<input type=checkbox name=de_code_dup_use value='1' <?=$default[de_code_dup_use]?'checked':'';?>> 사용
		<?=help("분류, 상품을 입력(추가) 할 때 코드 중복검사를 사용할 경우 체크하시면 됩니다.");?>
	</td>
</tr>
<tr class=ht>
	<td>장바구니 메세지</td>
	<td colspan=3>
		<input type=checkbox name=de_different_msg value='1' <?=$default[de_different_msg]?'checked':'';?>>
		비회원가격과 회원가격이 다른 상품을 장바구니에 담는 경우 "가격이 다릅니다"라는 메세지를 출력합니다.
		<?=help("상품을 장바구니에 담은 후에는 가격 수정이 불가하므로 비회원가격과 회원가격이 다른 경우에는 장바구니에 담기 전에 미리 메세지를 출력하여 로그인 한 후 구입을 하도록 유도합니다.", -150);?>
	</td>
</tr>
<tr class=ht>
	<td>MYSQL USER</td>
	<td><?=$mysql_user?></td>
	<td>MYSQL DB</td>
	<td><?=$mysql_db?></td>
</tr>
<tr class=ht>
	<td>서버 IP</td>
	<td><?=($_SERVER[SERVER_ADDR]?$_SERVER[SERVER_ADDR]:$_SERVER[LOCAL_ADDR]);?></td>
	<td>프로그램 등록번호</td>
	<td>
		<input type=text name=de_register value='<?=$default[de_register]?>' size=30 class=ed required itemname="프로그램 등록번호">
		<?=help("정식구입자께만 발급해 드리고 있습니다.\n등록번호가 틀린 경우 주문서를 확인 하실 수 없습니다.\n등록번호는 서버 IP, MYSQL USER, DB 를 알려주셔야 발급이 가능합니다.", -180, -160);?>
	</td>
</tr>
<tr class=ht>
	<td>아이해피 할인율</td>
	<td><input type=text name=de_ihappy_sale_percent value='<?=$default[de_ihappy_sale_percent]?>' size=5 class=ed> 
	<?=help("아이행복몰 상품가격 미입력시 상품 할인율을 설정합니다..");?></td>
</tr>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>


<a name="SMS정보"></a>
<p>
<table width=100% cellpadding=0 cellspacing=0>
<tr>
	<td width=50%><?=subtitle("SMS정보")?></td>
	<td width=50% align=right><span class=small><a href='#사업자정보'>사업자정보</a> | <a href='#초기화면'>초기화면</a> | <a href='#결제정보'>결제정보</a> | <a href='#배송정보'>배송정보</a> | <a href='#기타정보'>기타정보</a> | <a href='#SMS정보'>SMS정보</a></span></td>
</tr>
</table>

<script language="JavaScript">
function byte_check(el_cont, el_byte)
{
	var cont = document.getElementById(el_cont);
	var bytes = document.getElementById(el_byte);
	var i = 0;
	var cnt = 0;
	var exceed = 0;
	var ch = '';

	for (i=0; i<cont.value.length; i++) {
		ch = cont.value.charAt(i);
		if (escape(ch).length > 4) {
			cnt += 2;
		} else {
			cnt += 1;
		}
	}

	//byte.value = cnt + ' / 80 bytes';
	bytes.innerHTML = cnt + ' / 80 bytes';

	if (cnt > 80) {
		exceed = cnt - 80;
		alert('메시지 내용은 80바이트를 넘을수 없습니다.\r\n작성하신 메세지 내용은 '+ exceed +'byte가 초과되었습니다.\r\n초과된 부분은 자동으로 삭제됩니다.');
		var tcnt = 0;
		var xcnt = 0;
		var tmp = cont.value;
		for (i=0; i<tmp.length; i++) {
			ch = tmp.charAt(i);
			if (escape(ch).length > 4) {
				tcnt += 2;
			} else {
				tcnt += 1;
			}

			if (tcnt > 80) {
				tmp = tmp.substring(0,i);
				break;
			} else {
				xcnt = tcnt;
			}
		}
		cont.value = tmp;
		//byte.value = xcnt + ' / 80 bytes';
		bytes.innerHTML = xcnt + ' / 80 bytes';
		return;
	}
}
</script>

<table cellpadding=0 cellspacing=0 width=100%>
<tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>
<tr class=ht>
<?
$sms_title   = array (1=>"회원가입시", "주문서작성시", "입금확인시", "상품배송시");
$sms_daesang = array (1=>"고객님께 발송", "고객님께 발송", "고객님께 발송", "고객님께 발송");
?>
<? for ($i=1; $i<=4; $i++) { ?>
	<td width=25% align=center>
		<table>
		<tr><td align=center><b><?=$sms_title[$i]?></b></td></tr>
		<tr><td align=center><font color=#777777>(<?=$sms_daesang[$i]?>)</font></td></tr>
		</table><br>
		<table width=165 height=191 cellpadding=0 cellspacing=0 background='./img/sms_back.gif'>
		<tr>
			<td align=center><textarea cols='16' rows='6' id='de_sms_cont<?=$i?>' name='de_sms_cont<?=$i?>' wrap=virtual ONKEYUP="byte_check('de_sms_cont<?=$i?>', 'byte<?=$i?>');" style='OVERFLOW: hidden; border:solid 0; width: 100px; background-color:#C4FFFF; FONT-SIZE: 9pt; font-family:굴림체;'><?=$default["de_sms_cont".$i]?></textarea></td>
		</tr>
		</table>
		<table><tr><td><div id='byte<?=$i?>' align=center>0 / 80 바이트</div><br><input type=checkbox name='de_sms_use<?=$i?>' value='1' <?=($default["de_sms_use".$i] ? "checked" : "")?>>사용</table>
	</td>

	<script language="JavaScript">
		byte_check('de_sms_cont<?=$i?>', 'byte<?=$i?>');
	</script>
<? } ?>
</tr>
</table><br>


<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<tr><td colspan=4 height=2 bgcolor=#CCCCCC></td></tr>
<tr class=ht>
	<td>SMS 사용</td>
	<td colspan=3>
		<select id=de_sms_use name=de_sms_use>
		<option value=''>사용안함
		<option value='icode'>아이코드
		<option value='xonda'>쏜다넷
		</select>
		<?=help("서비스 회사를 선택하신 경우에만 SMS 를 사용합니다.\n위의 개별적인 기능별 사용(회원가입시, 주문서작성시 ...)보다 우선합니다.\n아이코드의 경우 무료테스트 환경을 지원합니다.");?>
		<script>document.getElementById('de_sms_use').value="<?=$default[de_sms_use]?>";</script>
	</td>
</tr>
<tr class=ht>
	<td>관리자 핸드폰번호</td>
	<td colspan=3>
		<input type=text name=de_sms_hp value='<?=$default[de_sms_hp]?>' size=20 class=ed>
		<?=help("쇼핑몰관리자 또는 보내시는분의 핸드폰번호를 입력하세요.\n\n주문서작성시 쇼핑몰관리자가 문자메세지를 받으시려면 반드시 입력하셔야 합니다.\n\n숫자만 입력하세요.\n예) 0101234567");?>
	</td>
</tr>
<tr class=ht>
	<td>아이코드 회원아이디</td>
	<td>
		<input type=text name=de_icode_id value='<?=$default[de_icode_id]?>' size=20 class=ed>
		<?=help("아이코드에서 사용하시는 회원아이디를 입력합니다.");?>
	</td>
	<td>아이코드 홈페이지</td>
	<td><a href='http://www.icodekorea.com' target=_blank>http://www.icodekorea.com</a></td>
</tr>
<tr class=ht>
	<td>아이코드 패스워드</td>
	<td colspan=3>
		<input type=password name=de_icode_pw class=ed>
		<?=help("아이코드에서 사용하시는 패스워드를 입력합니다.");?>
		<?
		echo "&nbsp; ";
		if ($default[de_icode_pw])
			echo "현재 패스워드가 입력되어 있습니다.";
		else
			echo "현재 패스워드가 입력되어 있지 않습니다.";
		?>
	</td>
</tr>
<tr class=ht>
	<td>아이코드 서버 IP</td>
	<td colspan=3>
		<input type=text name=de_icode_server_ip value='<?=$default[de_icode_server_ip]?>' size=20 class=ed>
		<?=help("아이코드에서 문자메세지를 발송하는 서버의 IP 를 입력하십시오.\n\n기본값은 211.172.232.124 입니다.");?>
	</td>
</tr>
<tr class=ht>
	<td>아이코드 서버 Port</td>
	<td colspan=3>
		<select id=de_icode_server_port name=de_icode_server_port>
		<option value=''>사용안함
		<option value='7295'>충전식
		<option value='7296'>정액제
		</select>
		<script>document.getElementById('de_icode_server_port').value="<?=$default[de_icode_server_port]?>";</script>
	</td>
</tr>
<tr class=ht>
	<td>쏜다넷 고객아이디</td>
	<td>
		<input type=text name=de_xonda_id value='<?=$default[de_xonda_id]?>' size=20 class=ed>
		<?=help("쏜다넷 회원아이디와 같은 용어입니다.");?>
	</td>
	<td>쏜다넷 홈페이지</td>
	<td><a href='http://www.smsket.com' target=_blank>http://www.smsket.com</a></td>
</tr>
<tr><td colspan=4 height=2 bgcolor=#CCCCCC></td></tr>
<tr>
	<td colspan=4>
		<br>회원가입시  : {이름} {회원아이디} {회사명}
		<br>주문서작성 : {이름} {보낸분} {받는분} {주문번호} {주문금액} {회사명}
		<br>입금확인시 : {이름} {입금액} {주문번호} {회사명}
		<br>상품배송시 : {이름} {택배회사} {운송장번호} {주문번호} {회사명}
		<p>주의) 80 bytes 까지만 전송됩니다. (영문 한글자 : 1byte , 한글 한글자 : 2bytes , 특수문자의 경우 1 또는 2 bytes 임)
		<br>
		<br>
	</td>
</tr>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>



<p align=center>
	<input type=submit class=btn1 accesskey='s' value='  확  인  '>
</form>

<script language="JavaScript">
function fconfig_check(f)
{
	<?=cheditor3('de_baesong_content');?>
	<?=cheditor3('de_change_content');?>

	return true;
}

document.fconfig.de_admin_company_name.focus();
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
