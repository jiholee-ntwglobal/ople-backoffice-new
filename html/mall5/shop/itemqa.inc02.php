<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<!-- 상품문의 -->
<div id="item_qa"  class="product-info" style="display:block;">
<h2>상품문의</h2>


        <table width=100% cellpadding=0 cellspacing=0>
        <tr><td colspan=2 height=35>* 이 상품에 대한 궁금한 사항이 있으신 분은 질문해 주십시오.
            <input type=image src='<? echo "$g4[shop_img_path]/btn_qa.gif"?>' onclick="itemqa_insert();" align=absmiddle>
            </td>
        </tr>
        </table>

</div>


<script type="text/JavaScript">


function itemqa_insert()
{
    if (!g4_is_member) {
        alert("로그인 하시기 바랍니다.");
        return;
    }
    document.location.href = "<?=$g4[bbs_path]?>/write.php?bo_table=qa&wr_1=<?=$it[it_id]?>";
}

</script>
<!-- 상품문의 end -->
