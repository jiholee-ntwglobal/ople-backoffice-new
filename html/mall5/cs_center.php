<?php
/*
----------------------------------------------------------------------
file name	 : cs_center.php
comment		 : 고객센터 페이지
date		 : 2015-01-21
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/
include "./_common.php";

# 고객센터 공지사항 로드 #
$notice_arr = sql_fetch("
	select bo_notice from ".$g4['board_table']." where bo_table = 'qa'
");
$notice_arr = explode("\n",$notice_arr['bo_notice']);
$notice_in = '';
if(is_array($notice_arr)){
	foreach($notice_arr as $val){
		if($val){
			$notice_in .= ($notice_in ? ",":"") . "'".trim($val)."'";
		}
	}
}
$notice_tr = '';
if($notice_in){
	$sql = sql_query("
		select
			*
		from
			g4_write_qa
		where
			wr_id in (".$notice_in.")
		order by wr_num
	");

	while($row = sql_fetch_array($sql)){
		$notice_tr .= "
			<tr>
        <td class='ico notice_subject'><img src='http://115.68.20.84/notice_ico.gif' /></td>
				<td class='notice_subject' wr_id='".$row['wr_id']."' onclick='notice_view(this); return false;'>".$row['wr_subject']."</td>
			</tr>
			<tr class='notice_content' wr_id='".$row['wr_id']."'>
				<td colspan='2'>".nl2br($row['wr_content'])."</td>
			</tr>
		";
	}

}


if($customer_question) {
  if($member['mb_id']){
    # 1:1문의 로드 #
    $qa_sql = sql_query("
		select
			distinct (wr_num) as wr_num , wr_1
		from
			g4_write_qa
		where
			mb_id = '".$member['mb_id']."'
			and
			wr_reply = ''

	");
    $qa_wr_num_in = '';
    while($row = sql_fetch_array($qa_sql)){
      $qa_wr_num_in .= ($qa_wr_num_in ? "," : "") . "'" . $row['wr_num'] . "'";
    }


    if($qa_wr_num_in){ //곽범석 ca_name, 상품문의  추가
      $qa_sql = sql_query($a="
			select
				wr_id,wr_num,wr_subject,wr_content,wr_datetime,ca_name,wr_1
			from
				g4_write_qa
			where
				wr_num in (".$qa_wr_num_in.")
				and
				wr_reply = ''
			order by wr_num,wr_id
			limit 5
		");
      while($row = sql_fetch_array($qa_sql)){
        $qa_anwer = sql_fetch("
				select
					wr_content,wr_datetime
				from
					g4_write_qa
				where
					wr_num = '" . $row['wr_num'] . "'
					and
					wr_reply = 'A'
			");


        $qa_subject = "";

        $tr_class = 'qa_q';
        $list_class = 'ico question';

        if ($qa_anwer) {
          $row['wr_content'] .= "\n\n\n---------- 답변 ----------\n\n" . $qa_anwer['wr_content'];
        }
        if($row['wr_1']){
          $wr_1_row=sql_fetch("
            select it_name
            from yc4_item
            where it_id = '".$row['wr_1']."'");
          $qa_tr .= "
				<tr class='qa_q' wr_id='" . $row['wr_id'] . "' onclick=\"qa_view('" . $row['wr_id'] . "');\" style='cursor:pointer;'>
					<td class='ico question'><img src='http://115.68.20.84/P_icon.jpg'></td>
					<td class='qa_subject'>" . $row['wr_subject'] . "</td>
					<td class='qa_afg'>" . ($qa_anwer ? "<b>답변완료</b>" : "답변대기중") . "</td>
				</tr>
				<tr class='qa_q_content' wr_id='" . $row['wr_id'] . "'>
					<td style='padding:10px;'>
					  <a href='http://ople.com/mall5/shop/item.php?it_id=".$row['wr_1']."'>
					    <img src='".$g4['path']."/data/item/".$row['wr_1']."_s' width='100'/>
					  </a>
					</td>
					<td class='qa_content' colspan='2'>
					  <a href='http://ople.com/mall5/shop/item.php?it_id=".$row['wr_1']."'>
					    ".get_item_name($wr_1_row['it_name'],"detail").
              "    </a>
                 </td>
				</tr>
				<tr class='qa_q_content' wr_id='" . $row['wr_id'] . "'>
				  <td></td>
				  <td class='qa_content' colspan='2'>
					".conv_content($row['wr_content'], 0) .
              "</td>
				</tr>
			";
        }else{
          $qa_tr .= "
				<tr class='qa_q' wr_id='" . $row['wr_id'] . "' onclick=\"qa_view('" . $row['wr_id'] . "');\" style='cursor:pointer;'>
					<td class='ico question'><img src='http://115.68.20.84/Q_icon.jpg'></td>
					<td class='qa_subject'>" . $row['wr_subject'] . "</td>
					<td class='qa_afg'>" . ($qa_anwer ? "<b>답변완료</b>" : "답변대기중") . "</td>
				</tr>
				<tr class='qa_q_content' wr_id='" . $row['wr_id'] . "'>
					<td></td>
					<td class='qa_content' colspan='2'>
					".conv_content($row['wr_content'], 0) . "</td>
				</tr>
			";

        }
      }

    }
  }
}else{
  if($member['mb_id']){
    # 1:1문의 로드 #
    $qa_sql = sql_query("
		select
			distinct (wr_num) as wr_num
		from
			g4_write_qa
		where
			mb_id = '".$member['mb_id']."'
			and
			wr_reply = ''

	");
    $qa_wr_num_in = '';
    while($row = sql_fetch_array($qa_sql)){
      $qa_wr_num_in .= ($qa_wr_num_in ? ",":"") . "'".$row['wr_num']."'";
    }
    if($qa_wr_num_in){
      $qa_sql = sql_query($a="
			select
				wr_id,wr_num,wr_subject,wr_content,wr_datetime
			from
				g4_write_qa
			where
				wr_num in (".$qa_wr_num_in.")
				and
				wr_reply = ''
			order by wr_num,wr_id
			limit 5
		");
      while($row = sql_fetch_array($qa_sql)){

        $qa_anwer = sql_fetch("
				select
					wr_content,wr_datetime
				from
					g4_write_qa
				where
					wr_num = '".$row['wr_num']."'
					and
					wr_reply = 'A'
			");




        $qa_subject = "";

        $tr_class = 'qa_q';
        $list_class = 'ico question';

        if($qa_anwer){
          $row['wr_content'] .= "\n\n\n---------- 답변 ----------\n\n".$qa_anwer['wr_content'];
        }

        $qa_tr .= "
				<tr class='qa_q' wr_id='".$row['wr_id']."' onclick=\"qa_view('".$row['wr_id']."');\" style='cursor:pointer;'>
					<td class='ico question'><img src='http://115.68.20.84/Q_icon.jpg'></td>
					<td class='qa_subject'>".$row['wr_subject']."</td>
					<td class='qa_afg'>".($qa_anwer ? "<b>답변완료</b>":"답변대기중")."</td>
				</tr>
				<tr class='qa_q_content' wr_id='".$row['wr_id']."'>
					<td></td>
					<td class='qa_content' colspan='2'>".conv_content($row['wr_content'],0)."</td>
				</tr>
			";
      }

    }

    $item_qa_sql = sql_query("
		select
			a.iq_id,a.it_id,a.iq_subject,a.iq_question,a.iq_answer,
			b.it_name
		from
			yc4_item_qa a,
			yc4_item b
		where
			a.it_id = b.it_id
			and
			a.mb_id = '".$member['mb_id']."'
		order by iq_time desc
		limit 5
	");
    $item_qa_tr = '';
    while($row = sql_fetch_array($item_qa_sql)){
      if($row['iq_answer']){
        $row['iq_question'] .= "\n\n\n---------- 답변 ----------\n\n".$row['iq_answer'];
      }

      $item_qa_tr .= "
			<tr class='item_qa_subject_tr' iq_id='".$row['iq_id']."' onclick=\"item_qa_view('".$row['iq_id']."');\">
				<td style='padding:10px;'>
					<img src='".$g4['path']."/data/item/".$row['it_id']."_s' width='100'/>
				</td>
				<td class='item_qa_subject'>
					상품명 : ".get_item_name($row['it_name'])."
					<br />
					제목 : ".$row['iq_subject']."
				</td>
				<td align='center'>".(!$row['iq_answer'] ? "답변대기중":"<b>답변완료</b>")."</td>
			</tr>
			<tr class='item_qa_content' iq_id='".$row['iq_id']."'>
				<td></td>
				<td colspan='2' style='padding:20px;'>
					".conv_content($row['iq_question'],0)."
				</td>
			</tr>
			<tr>
				<td colspan='3' style='border-bottom: 1px solid #eeeeee;'></td>
			</tr>
		";
    }
  }
}


if(!$qa_tr){
	$qa_tr = "
		<tr>
			<td colspan='3' align='center' style='padding:20px;'>1:1문의 글이 존재하지 않습니다.</td>
		</tr>
	";
}

if(!$customer_question){ //곽범석 추가
  if(!$item_qa_tr){
    $item_qa_tr = "
		<tr>
			<td colspan='3' align='center' style='padding:20px;'>상품문의가 존재하지 않습니다</td>
		</tr>
	";
  }
}

if($member['mb_id']){
	$qa_btn1 = "<p class='button_QuestWrite'><a href='". $g4['bbs_path']."/write.php?bo_table=qa'><img src='http://115.68.20.84/mall6/btn_onebyoneCustomer.gif' alt='1:1문의하기' /></a></p>";
	$qa_btn2 = "<p class='button_QuestWrite'><a href='". $g4['bbs_path']."/board2.php?bo_table=qa'><img src='http://115.68.20.84/mall6/btn_onebyoneCustomer2.gif' alt='1:1문의하기' /></a></p>";
}else{
	$qa_btn1 = "<p class='button_QuestWrite'><a href='#' onclick=\"location.href='".$g4['bbs_path']."/email_confirm.php'; return false;\"><img src='http://115.68.20.84/mall6/btn_onebyoneCustomer.gif' alt='1:1문의하기' /></a></p>";
	$qa_btn2 = "<p class='button_QuestWrite'><a href='#' onclick=\"location.href='".$g4['bbs_path']."/email_confirm.php'; return false;\"><img src='http://115.68.20.84/mall6/btn_onebyoneCustomer2.gif' alt='1:1문의하기' /></a></p>";
}

$g4['title'] = "고객센터";
include_once $g4['full_path']."/_head.php";
?>
<style type="text/css">
.qa_table{
	display:table;
}

.qa_content{
	padding: 20px;
	width: 968px;
	padding-left: 20px;
}
.qa_subject{
	padding: 10px;
	border-bottom: 1px solid #eeeeee;
	border-top: 1px solid #eeeeee;
	width: 820px;
	padding-left: 20px;
}
.qa_subject.active{
	font-weight:bold;
}
.qa_afg{
	padding: 10px;
	border-bottom: 1px solid #eeeeee;
	border-top: 1px solid #eeeeee;
	text-align:center;
}
.qa_q_content{
	display:none;
}
.item_qa_table{
	display:none;
}
.item_qa_subject{
	padding: 10px;


	width: 810px;
	padding-left: 20px;
}
.item_qa_subject.active{
	font-weight:bold;
}
.item_qa_content{

	padding: 20px;
}
.button_QuestWrite {
	text-align: right;
	height: 40px;
	margin-top: -50px;
}

.iqa_btn_wrap{
	height: 40px;
	margin-top: -44px;
	text-align:right;
}
.iqa_btn_wrap > .button_QuestWrite{
	display:inline-block;
	height: auto;
	margin-top: 0;
}
</style>
<div class="FAQTitle">
  <img alt="고객센터" src="http://ople.com/mall5/images/menu/menu_title07.gif" />
</div>


<?php echo $qa_btn1;?>


<table class="notice_wrap">
  <?php echo $notice_tr;?>
</table>


<div class="faq_tab">
  <ul class="TabStyle_A">
    <li class="active">
      <a href="#" onclick='faq_tab_change(this); return false;' tab_id='1'>주문/결제</a>
    </li>
    <li>
      <a href="#" onclick='faq_tab_change(this); return false;' tab_id='2'>배송</a>
    </li>
    <li>
      <a href="#" onclick='faq_tab_change(this); return false;' tab_id='3'>상품</a>
    </li>
    <li>
      <a href="#" onclick='faq_tab_change(this); return false;' tab_id='4'>반품/교환/취소</a>
    </li>
    <li>
      <a href="#" onclick='faq_tab_change(this); return false;' tab_id='5'>기타</a>
    </li>
  </ul>
</div>

<div class="faq_board">
  <!-- 주문/결제 시작 -->
  <table cellpadding="0" cellspacing="0" border="0" class='faq_table faq_table_on' tab_id='1'>
    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(1,1); return false;'>카드결제가 가능한가요?</a>
      </td>
    </tr>

    <tr class='faq_content' list_id='1'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        해외결제를 지원하는 신용/체크카드만 가능합니다. (Master/Visa/Amex) <br />
        당 사이트 내에서는 일시불 결제만 가능합니다. (할부 전환을 원할 경우 결제 후 해당 카드사에 문의바랍니다)<br />
        <?/*
		<span class="emphasis">※ 2015년 12월 31일까지 삼성 마스타카드 무이자 할부 가능</span> <br/>
		*/?>
        카드 결제정보는 미국 최대 PG사인 Authorize의 최고 보안모듈 AIM을 통해 보호됩니다. <br />
        저희 사이트는 ActiveX 를 사용하지 않고, 고객님께서 입력하신 카드 정보를 저희 서버를 거치지 않고 바로 카드사로 전송하는 고급 보안 기술을 사용하여 해킹 또는 크래킹이 불가능하기 때문에 매우 안전한 카드 결제 시스템입니다

      </td>
    </tr>

    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(1,2); return false;'>카드결제를 취소를 하고 싶습니다.</a>
      </td>
    </tr>
    <tr class='faq_content' list_id='2'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        카드취소를 원하실 경우에는 <span class="emphasis">한국시각 기준 주문당일 밤 12시이전까지 전화(고객상담시간내) 또는 1:1 문의로 요청</span> 해주시기바랍니다.<br />
        밤 12시 이후에는 배송작업을 완료하여 주문취소가 안되니 이용에 참고 부탁드립니다.<br />
        또한 시간과 관계없이 물품이 발송중으로 상태 변경이 될 경우 제품수량변경, 취소가 안되니 참고해주세요.<br />
        <span class="emphasis">※ 주문 전 신중한 선택을 부탁드립니다.</span>
      </td>
    </tr>

    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(1,3); return false;'>카드 승인이 안되었는데 카드사로부터 승인되었다는 메세지를 받았어요.</a>
      </td>
    </tr>
    <tr class='faq_content' list_id='3'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        간혹 저희 사이트에서 카드 결제를 하셨는데, 분명 카드결제에는 실패했지만, 카드사에서 카드결제가 되었다는 문자가 올 수 있습니다. <br/>
        이와 같은 현상은 국내카드로 해외결제 시 해외에서 사용된 정확한 승인내역을 확인하려면 카드 사용 후 (결제 후), 1~2일이 지나야 정확한 승인/거부 조회가 가능하기 때문입니다.
        구체적인 내역은 다음과 같습니다.<br/><br/>
        <span class="emphasis_box">
          해외결제 시, 카드대행사와 은행사간의 연동을 통해 결제를 대행하고 있으므로, 미국 외의 국가에서 발급된 카드(국내 카드)등은 전산처리 시스템상 2차 승인거부(카드 번호는 일치하나, 유효기간 또는 cvv/cvc 코드가 틀린 경우) 발생 시 실시간 조회가 되지 않으며 , 1~2일 후에 정확한 조회가 가능합니다.<br/>
          이와 같은 이유로 인해서 저희 사이트에서는 결제가 실패되어도 카드사에는 1차승인이 완료된 것으로 간주되어 고객님께서 승인문자 메시지를 받게 되는 경우가 발생합니다.
          그러나 1차승인(카드 번호 입력)부터 승인거부가 발생한 경우에는 실시간 결제 실패 상황이 카드사에서 조회가 가능합니다.
        </span>
      </td>

      <tr>
        <td class="ico question">
          <img src="http://115.68.20.84/Q_icon.jpg" />
        </td>
        <td class="question">
          <a href="#" onclick='faq_view(1,4); return false;'>개인통관부호를 꼭 사용해야하나요?</a>
        </td>
      </tr>
      <tr class='faq_content' list_id='4'>
        <td class="ico answer">
          <img src="http://115.68.20.84/A_icon.jpg" />
        </td>
        <td class="answer">
          오플닷컴은 입력하신 개인통관고유부호를 오직 물품 통관에 관련된 목적으로 계약된 관세법인에게만 제공하며
          다른 목적으로 이용 또는 제3자에게 판매, 양도하지 않습니다.
          또한 입력하신 개인통관고유부호는 배송완료 후 자동 파기됩니다.<br/>
          <span class="emphasis">
            물품을 받으시는 분의 개인통관고유부호 오류, 또는 미입력시 통관이 지연될 수 있으며,
            이러한 경우에 관한 배송지연은 오플닷컴에서 책임지지 않습니다.
          </span>
        </td>
      </tr>

      <tr>
        <td class="ico question">
          <img src="http://115.68.20.84/Q_icon.jpg" />
        </td>
        <td class="question">
          <a href="#" onclick='faq_view(1,5); return false;'>선결제 포인트 구매가 무엇인가요?</a>
        </td>
      </tr>
      <tr class='faq_content' list_id='5'>
        <td class="ico answer">
          <img src="http://115.68.20.84/A_icon.jpg" />
        </td>
        <td class="answer">
          선결제 포인트는 해당금액의 포인트를 구매하시면 추가로 포인트를 더 적립해드리는 시스템으로 오플닷컴을 꾸준히 이용하시는 고객님에들게는 훨씬 알뜰하고 유리한 구매 방법입니다.<br/>
          <span class="emphasis">※ 사용법을 정확히 확인하신 후 이용해 주시길 바랍니다 </span>
        </td>
      </tr>

      <tr>
        <td class="ico question">
          <img src="http://115.68.20.84/Q_icon.jpg" />
        </td>
        <td class="question">
          <a href="#" onclick='faq_view(1,6); return false;'>스마트폰에서 구매가 가능한가요?</a>
        </td>
      </tr>
      <tr class='faq_content' list_id='6'>
        <td class="ico answer">
          <img src="http://115.68.20.84/A_icon.jpg" />
        </td>
        <td class="answer">
          오플닷컴은 ACTIVEX를 사용하지 않기 때문에 접속 기기에 관계없이 주문 및 결제가 가능합니다.
        </td>
      </tr>

      <tr>
        <td class="ico question">
          <img src="http://115.68.20.84/Q_icon.jpg" />
        </td>
        <td class="question">
          <a href="#" onclick='faq_view(1,7); return false;'>비회원도 주문 가능한가요?</a>
        </td>
      </tr>
      <tr class='faq_content' list_id='7'>
        <td class="ico answer">
          <img src="http://115.68.20.84/A_icon.jpg" />
        </td>
        <td class="answer">
          회원가입 없이 비회원으로도 상품구입이 가능합니다. <br/>
          비회원으로 주문하신 경우에는 해당 주문내역 및 배송상황을 오플닷컴 홈페이지 또는 구매 당시 입력하신 이메일을 통해 확인하실 수 있습니다.<br/>
          <span class="emphasis">※ 비회원으로 구매시 포인트 적립, 사용이 불가능 합니다.</span>
        </td>
      </tr>

	  <tr>
        <td class="ico question">
          <img src="http://115.68.20.84/Q_icon.jpg" />
        </td>
        <td class="question">
          <a href="#" onclick='faq_view(1,8); return false;'>카드승인 후 결제 카드 변경이 가능한가요?</a>
        </td>
      </tr>
      <tr class='faq_content' list_id='8'>
        <td class="ico answer">
          <img src="http://115.68.20.84/A_icon.jpg" />
        </td>
        <td class="answer">
          고객센터 1:1문의 또는 고객상담 전화로 문의해 주시면 변경 가능합니다.<br/>
          <span class="emphasis">※ 현재 주문 상태가 배송중일 경우에는 결제 카드 변경이 불가능합니다.</span>
        </td>
      </tr>
    </table>
  <!-- 주문/결제 끝 -->

  <!-- 배송 시작 -->
  <table cellpadding="0" cellspacing="0" border="0" class='faq_table' tab_id='2'>
    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(2,1); return false;'>배송비가 어떻게 되나요?</a>
      </td>
    </tr>

    <tr class='faq_content' list_id='1'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        오플닷컴은 주말을 제외하고 한국으로의 특송 항공편으로 매일 배송이 나가고 있습니다.<br/>
        세관통관 후에는 한국 우체국 택배를 통하여 고객님의 수령처까지 안전하게 상품을 전달해 드립니다.<br/>
        미국에서 한국으로의 상품 발송시 실제로는 무게에 따른 배송비가 상당 금액 발생되나, 오플닷컴은 파트너사와의 대량 물류 계약을 통해 한국내 쇼핑몰 배송비에 준하는 업계 최저가 수준을 유지하고 있으며, 무게에 따른 추가 배송비가 발생하지 않습니다. (일부 상품 제외)<br/><br/>
        <span class="emphasis_box">
          <b>※ 배송비 안내(2015년 01월 기준)</b>
          <br/>
          <span class="box_harf">
            - 총 구입액 5만원 미만 : 5,000 원<br/>
            - 총 구입액 5만원 이상 10만원 미만 : 4,000 원<br/>
          </span>
          <span>
            - 총 구입액 10만원 이상 20만원 미만 : 2,000 원<br/>
            - 총 구입액 20만원 이상 : 무료배송<br/>
          </span>
        </span>
      </td>
    </tr>

    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(2,2); return false;'>배송기간은 얼마나 걸리나요?</a>
      </td>
    </tr>
    <tr class='faq_content' list_id='2'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        결제완료 후 3~5 일 내로 요청하신 배송지로 상품이 도착합니다. (공휴일 제외) <br/>
        검역이 필요한 일부 상품의 세관 통관 지연 및 천재지변 등 특정 사유로 배송기간이 일부 더 길어질 수도 있습니다. <br/><br/>

        <span class="emphasis_box">
          <b>※ 배송기간 안내</b>
          <br/>
          <span class="box_harf">
            - 월요일 주문 : 목 ~ 금 도착 예상<br/>
            - 화요일 주문 : 금 ~ 차주 월 도착 예상<br/>
            - 수요일 주문 : 차주 월 ~ 화 도착 예상<br/>
          </span>
          <span>
            - 목요일 주문 : 차주 월 ~ 화 도착 예상<br/>
            - 금요일 주문 : 차주 화 ~ 수 도착 예상<br/>
            - 주말(토/일요일) 주문 : 차주 목 ~ 금 도착 예상<br/>
          </span>
        </span>
      </td>
    </tr>

    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(2,3); return false;'>배송조회는 어떻게 할 수 있나요?</a>
      </td>
    </tr>
    <tr class='faq_content' list_id='3'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        배송조회를 하실 경우 반드시 마이페이지에 있는 송장번호로 조회를 하셔야 합니다.  <br/>
        해외배송물품은 주문사 성함과 전화번호를 입력하면 배송조회가 되지 않습니다. <br/>
        반드시 마이페이지에 있는 송장번호로 조회하시기 바랍니다.
        배송조회는 통관과정을 마치는 이후부터 실시간 배송조회가 가능합니다. <br/> <br/>
        <span class="emphasis_box">
          <b>※ 배송조회 안내</b>
          <br/>
          <span class="box_harf">
            - 월요일 주문 : 수요일 오후5시이후 또는 목요일 아침부터 조회가능 <br/>
            - 화요일 주문 : 목요일 오후5시이후 또는 금요일 아침부터 조회가능 <br/>
            - 수요일 주문 : 금요일 오후5시이후 또는 다음주 월요일 아침부터 조회가능 <br/>
          </span>
          <span>
            - 목요일 주문 : 토요일 오후5시이후 또는 다음주 월요일 아침부터 조회가능 <br/>
            - 금요일 주문 : 다음주 월요일 오후5시 이후 실시간 조회가능<br/>
          </span>
        </span>
      </td>
    </tr>

    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(2,4); return false;'>배송이 너무 늦어요.</a>
      </td>
    </tr>
    <tr class='faq_content' list_id='4'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        미국 제조사 품절, 백오더로 인한 입고지연, 제조사 이전으로 인한 입고지연 등 한국에선 생각지도 않는 일들이 미국에선 종종 일어나고 있어 부득이하게 발송이 지연되는 경우가 있습니다.
        이러한 경우 고객님들께 빠른 안내를 하고자 하지만 미국 제조사에서 지연안내를 받는 시간이 평균 4~5일 소요가 되어 빠른 안내에 어려움이 있습니다.
        이 점 양해 부탁 드리며 보다 빠른 배송을 하도록 노력하겠습니다.

      </td>
    </tr>

    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(2,5); return false;'>추가 주문 및 묶음배송</a>
      </td>
    </tr>
    <tr class='faq_content' list_id='5'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        추가 주문을 원하실 경우 주문하신 제품이 한국으로 발송 전에는 주문을 취소하신 후 다시 주문을 해주셔야 합니다. <br/>
        묶음배송은 원칙적으로 불가능합니다.
      </td>
    </tr>
  </table>
  <!-- 배송 끝 -->

  <!-- 상품 시작 -->
  <table cellpadding="0" cellspacing="0" border="0" class='faq_table' tab_id='3'>
    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(3,1); return false;'>가격이 매우 저렴한데 정품이 맞나요?</a>
      </td>
    </tr>

    <tr class='faq_content' list_id='1'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        오플닷컴의 모든 제품은 100% 정품입니다. <br/>
        오랜 기간 미국현지에서 제조사와의 직접적인 거래로 고객님들께 안전하고 우수한 품질의 제품만을 공급해왔습니다. <br/>
        만일 오플닷컴에서 구매하신 제품이 정품이 아닐 경우 구입가격의 100배를 보상해드리오니, 안심하시고 구매하셔도 됩니다.
      </td>
    </tr>

    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(3,2); return false;'>상품을 추천해주세요.</a>
      </td>
    </tr>
    <tr class='faq_content' list_id='2'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        우측상단 베스트100 또는 직구 인기아이템을 참조해주세요. <br/>
        보다 상세한 제품 추천을 원하실 경우 1:1문의 게시판을 이용해주세요.
      </td>
    </tr>

    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(3,3); return false;'>상품입고문의</a>
      </td>
    </tr>
    <tr class='faq_content' list_id='3'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        오플닷컴에서 찾을 수 없는 제품은 1:1문의 게시판을 이용해주세요.<br />
        문의하신 제품은 담당자가 검토 후 적극 반영하도록 하겠습니다.
      </td>
    </tr>

    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(3,4); return false;'>품절된 상품은 언제 입고 되나요?</a>
      </td>
    </tr>
    <tr class='faq_content' list_id='4'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        제조사 사정 및 물류 상황에 따라 기간이 상이할 수 있으며, 최대한 빠른 시일내로 입고하도록 노력하고 있습니다.
      </td>
    </tr>

    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(3,5); return false;'>상품가격이 바뀌었어요.</a>
      </td>
    </tr>
    <tr class='faq_content' list_id='5'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        상품가격이 변동되는 이유는 아래와 같습니다.<br/> <br/>
        <span class="emphasis_box">
          1. 환율에 의한 가격변동 <br/>
          2. 제조사 및 유통사의 가격 정책 변경에 따른 가격변동 <br/>
        </span>
      </td>
    </tr>
  </table>
  <!-- 상품 끝 -->

  <!-- 반품/교환/취소 시작 -->
  <table cellpadding="0" cellspacing="0" border="0" class='faq_table' tab_id='4'>
    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(4,1); return false;'>반품이나 교환을 하고 싶어요.</a>
      </td>
    </tr>

    <tr class='faq_content' list_id='1'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        <b>① 고객 변심의 경우 (제품에 하자가 없는 경우)</b> <br/>
        제품을 받으신 날로부터 60일 이내로 고객센터에 전화통화 또는 게시글 작성 후에 제품 미개봉 상태로 우편을 통해 미국 본사로 보내주셔야 합니다. <br/>
        이때 발생되는 비용은 고객님 부담이며, 반품제품이 저희에게 도착하게 되면, 고객님께서 결제하신 금액 중에서 미국에서 한국으로 최초 발송시 소요된 실제 배송비를 제외한
        금액을 환불해 드립니다. 이러한 경우 고객님의 부담이 매우 커지게 되므로 신중한 구매 결정을 부탁 드립니다. <br/><br/>
        <b>② 오배송 / 제품이상의 경우 </b> <br/>
        100% 저희 부담으로 신속한 교환처리를 해드립니다. <br/><br/>
        <b>③ 배송 중 사고의 경우 </b> <br/>
        배송 중의 사고로 인한 제품 분실 및 파손 등의 문제는 택배보험을 통해 택배 회사에서 처리를 해드립니다.  <br/>
        이러한 경우에 고객님께서 받으시는 불이익은 전혀 발생하지 않습니다.<br/><br/>

        <span class="emphasis_box">
          <b>반품/교환 비용 (미국에서 보낼때 소요되는 실제 배송비)</b>
          <br/>
          일반적인 교환은, 미국으로 교환을 하실 제품을 보내주시고, 추가적인 교환비용을 지불해주셔야 합니다. <br/>
          물품 반품/교환시 미국에서 실제 소요되는 배송비를 부담해주셔야 하고 물품을 고객님부담으로 미국까지 보내주셔야 합니다.
          <br/>
          <img style="
    margin-top: 20px;" src="http://115.68.20.84/FAQ-price.jpg" />
        </span>

      </td>
    </tr>

    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(4,2); return false;'>주문취소나 변경을 하고 싶어요.</a>
      </td>
    </tr>
    <tr class='faq_content' list_id='2'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        주문취소를 하실 경우 주문당일 밤 12시이전까지 고객센터 또는 1:1문의게시판, 카톡으로 연락해 주시기 바랍니다. <br/>
        고객님의 주문건이 미국 현지 물류센터에서 한국으로 이미 발송된 후에는 취소가 불가능하오니 이점 착오 없으시기 바랍니다.<br/>
      </td>
    </tr>

    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(4,3); return false;'>교환하고 싶어요.</a>
      </td>
    </tr>
    <tr class='faq_content' list_id='3'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        교환시 각 브랜드 제조사측에서 거부를 할 수도 있으며 비용 부담, 시간소요 등으로 불편함이 생길 수 있으니 주문 전에 신중한 선택을 하시길 바랍니다. <br/>
        <p class="emphasis"> ※ 한국으로 발송 전 교환을 요청하시는 경우에는 반품료와 마찬가지로 미국 내 shipping charge + handling fee 를 고객께서 부담하셔야 합니다. </p>
        <p class="emphasis"> ※ 한국에 도착한 상품은 교환이 불가능 합니다. </p>
      </td>
    </tr>

    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(4,4); return false;'>환불은 언제되나요?</a>
      </td>
    </tr>
    <tr class='faq_content' list_id='4'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        요청일로 부터 1~3일정도가 소요됩니다.
      </td>
    </tr>
  </table>
  <!-- 반품/교환/취소 끝 -->

  <!-- 기타 시작 -->
  <table cellpadding="0" cellspacing="0" border="0" class='faq_table' tab_id='5'>
    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(5,1); return false;'>문의는 어디에서 하면 되나요?</a>
      </td>
    </tr>

    <tr class='faq_content' list_id='1'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        사이트 이용 중 궁금한 사항이 있으시면 고객센터로 전화, 카카오톡, 1:1 문의 게시판에 남겨주시면 빠른 시간 내에 답변해 드리겠습니다.<br/>
        <span class="emphasis">오전 10:00 ~ 오후 04:30 (월요일~금요일 / 한국 공휴일제외) </span><br/><br/>
        <span class="emphasis_box">
          <span class="box_harf">
            <b>※ 고객상담안내</b><br/>
            070-7678-7004 / 070-7678-7809<br/>
            (070 통화료 3분기준 49원)
          </span>
          <span><b>※ 카카오톡 문의</b><br/>
          카카오톡 아이디 : opcs</span>
        </span>
      </td>
    </tr>

    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(5,2); return false;'>적립금을 얻을려면 어떻게 해야하나요?</a>
      </td>
    </tr>
    <tr class='faq_content' list_id='2'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        구매후기를 남기시면, 적립금이 적립됩니다. <br/>
        <span class="emphasis">※ 포인트가 부여되는 제품을 구매하실 경우에는 [마이페이지 ▶ 해당 주문번호 ▶ 상품수령확인]을 하셔야 적립됩니다.</span><br/>
        <span class="emphasis">
          ※ 적립금은 중복으로 적립받으 실 수 없습니다.<br/>
          (예) 포토후기 500점 적립 후 베스트 후기가 되실 경우 1,500점만 적립이 됩니다.
        </span> <br/><br/>

        <span class="emphasis_box">
          <b>※ 적립금 지급 안내</b>
          <br/>
          <span class="box_harf">
            - 일반후기 : 200 점<br/>
            - 포토후기 : 500 점<br/>
          </span>
          <span>
            - 제품의 첫번째 후기 : 1,000 점<br/>
            - 베스트 후기 : 2,000 점<br/>
          </span>
        </td>
    </tr>

    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(5,3); return false;'>굿데이 세일이 무엇인가요?</a>
      </td>
    </tr>
    <tr class='faq_content' list_id='3'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        굿데이 세일은 인기상품을 한정 수량으로 최저가로 판매하는 이벤트 입니다. <br/>
        일반적으로 매주 목요일마다 진행되며 굿데이 세일 정보는 사이트 하단 굿데이세일 알람신청을 통하여 SMS로 받아보실 수 있습니다. <br/>
        식물 및 동물 검역이 필요한 일부 상품의 경우 다른 상품보다 통관 소요 시간이 최대 1주일까지 지연될 수 있습니다. 자세한 사항은 해당 상품 상세페이지에서 확인하실 수 있습니다.

      </td>
    </tr>

    <tr>
      <td class="ico question">
        <img src="http://115.68.20.84/Q_icon.jpg" />
      </td>
      <td class="question">
        <a href="#" onclick='faq_view(5,4); return false;'>어디에서 발송을 하나요? 미국? 한국?</a>
      </td>
    </tr>
    <tr class='faq_content' list_id='4'>
      <td class="ico answer">
        <img src="http://115.68.20.84/A_icon.jpg" />
      </td>
      <td class="answer">
        오플닷컴의 주문하신 모든 상품은 미국에서 발송이 됩니다.
      </td>
    </tr>

  </table>
  <!-- 기타 끝 -->
</div>



<div class="qa_tab">
  <ul class="TabStyle_A">
	<li class="active" table_name='qa_table'>
	  <a href="#" onclick="qa_tab_change('qa_table');return false;" >1:1문의</a>
	</li>
    <?php if(!$customer_question){ //곽범석 추가?>
	<li table_name='item_qa_table'>
	  <a href="#" onclick="qa_tab_change('item_qa_table');return false;" >상품문의</a>
	</li>
    <?php } ?>
  </ul>
</div>
<div class='iqa_btn_wrap'>
	<?php echo $qa_btn1;?>
	<?php echo $qa_btn2;?>
</div>
<div class='qa_board'>
	<table class='qa_table' width='100%'>
		<col width='62'/>
		<col width=''/>
		<col width='100'/>
		<tbody>
		<?php echo $qa_tr;?>
		</tbody>
	</table>
  <?php if(!$customer_question)	{//곽범석 추가 ?>
	<table class='item_qa_table' width='100%'>
		<col width='62'/>
		<col width=''/>
		<col width='100'/>
		<tbody>
		<?php echo $item_qa_tr;?>
		</tbody>
	</table>
  <?php } ?>
</div>

<script type="text/javascript">
  function notice_view(obj){
  var wr_id = $(obj).attr('wr_id');

  if($('.notice_content[wr_id='+wr_id+']').attr('class').indexOf('notice_content_active') < 0){
		$('.notice_subject').removeClass('notice_subject_active');
		$('.notice_content').removeClass('notice_content_active');
		$('.notice_content[wr_id='+wr_id+']').addClass('notice_content_active');

		$(obj).addClass('notice_subject_active');
	}else{
		$('.notice_subject').removeClass('notice_subject_active');
		$('.notice_content').removeClass('notice_content_active');
	}
}


function faq_view(tab_id,list_id){

	var result_tr = $('.faq_table[tab_id='+tab_id+'] .faq_content[list_id='+list_id+']');
	var faq_title = $('.faq_table[tab_id='+tab_id+'] .faq_content[list_id='+list_id+']').prev().find('.question');


	var fg = false;
	if(result_tr.is(':visible') == true){
		fg = true;
	}else{
		$('.faq_content').hide();
		result_tr.show();
		$('.question').removeClass('question_active');
		faq_title.addClass('question_active');
	}
	if(fg == true){
		$('.faq_content').hide();
		$('.question').removeClass('question_active');
	}


}
function faq_tab_change(obj){
	var tab_id = $(obj).attr('tab_id');
	$('.faq_tab > .TabStyle_A > li').removeClass('active');
	$(obj).parent().addClass('active');
	$('.faq_table').removeClass('faq_table_on');
	$('.faq_table[tab_id='+tab_id+']').addClass('faq_table_on');

}

function qa_tab_change(table_name){
	$('.qa_tab > .TabStyle_A > li.active').removeClass('active');
	$('.qa_tab > .TabStyle_A > li[table_name='+table_name+']').addClass('active');
	$('.qa_board > table').hide();
	$('.qa_board > table.'+table_name).show();
	if(table_name != 'qa_table'){
		$('.iqa_btn_wrap').hide();
	}else{
		$('.iqa_btn_wrap').show();
	}
}

function qa_view(wr_id){

	if($('.qa_q_content[wr_id='+wr_id+']:visible').length>0){
		$('.qa_subject.active').removeClass('active');
		$('.qa_q_content').hide();
	}else{
		$('.qa_q_content').hide();
		$('.qa_subject.active').removeClass('active');
		$('.qa_q[wr_id='+wr_id+'] > .qa_subject').addClass('active');
		$('.qa_q_content[wr_id='+wr_id+']').show();
	}
}

function item_qa_view(iq_id){

	if($('.item_qa_content[iq_id='+iq_id+']:visible').length>0){
		$('.item_qa_subject').removeClass('active');
		$('.item_qa_content').hide();
	}else{
		$('.item_qa_content').hide();
		$('.item_qa_subject.active').removeClass('active');
		$('.item_qa_subject_tr[iq_id='+iq_id+'] > .item_qa_subject').addClass('active');
		$('.item_qa_content[iq_id='+iq_id+']').show();
	}
}


</script>
<?php
include_once $g4['full_path']."/_tail.php";