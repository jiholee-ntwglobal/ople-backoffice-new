<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-04-13
 * Time: 오전 9:36
 */
$sub_menu = "300888";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$g4[title] = "관별 TOP100 리스트";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <div class="row">
        <div class="col-lg-12">
            <h4>MSRP 변경</h4>
        </div>
    </div>

    <div class="col-lg-12">
        <form class="row" method="post" action="./item_msrp_change.php" onsubmit="return frm_chk(this);" enctype="multipart/form-data">
            <div class="alert alert-info">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="pull-left">MSRP 대량변경 샘플</p>
                        <a href="sample_item_msrp_change.xlsx"
                           class="pull-right btn btn-default" role="button">샘플 엑셀 파일 다운로드</a>
                    </div>
                </div>
            </div>
            <div class="alert alert-info">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="pull-left">대량변경</p>
                        <input type="hidden" name="mode" value="msrp_excel_change">
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="input-group">
                            <input type="file" class="form-control" name="excel_file">
                            <span class="input-group-btn">
                            <button class="btn btn-default" type="submit">파일 업로드</button>
                        </span>
                        </div><!-- /input-group -->
                    </div>
                </div>
            </div>
        </form>
        <form class="row" method="post" action="./item_msrp_change.php"
              onsubmit="return frm_chk2(this);" enctype="multipart/form-data">
            <input type="hidden" name="mode" value="msrp_change">
            <div class="alert alert-success">
                <div class="row">
                    <div class="col-lg-6">
                        <p class="pull-left"><input type="checkbox" id="chk" name="chk">개별 등록</p>
                    </div>
                    <div class="col-lg-6 text-right">
                        <span style="color: red;">※ 오플상품코드,MSRP(KRW),MSRP(USD) 반드시 입력</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3">
                        <label>오플상품코드</label>
                        <input type="text" class="form-control" name="it_id" disabled>
                    </div>
                    <div class="col-lg-3">
                        <label>MSRP(KRW)</label>
                        <input type="text" class="form-control" name="msrp_k" disabled>
                    </div>
                    <div class="col-lg-3">
                        <label>MSRP(USD)</label>
                        <input type="text" class="form-control" name="msrp_u" disabled>
                    </div>
                    <div class="col-lg-3 text-right">
                        <br>
                        <button type="submit" class="btn btn-success" disabled id="chk_btn">등록</button>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <script>
        function frm_chk(f) {
            return true;
        }
        function frm_chk2(f){
            var upc_str = f.it_id.value;
            var msrp_k = f.msrp_k.value;
            var msrp_u = f.msrp_u.value;
            if(!upc_str.trim()|| !msrp_k.trim()|| !msrp_u.trim()){
                alert('상품코드,MSRP 모두 입력해주세요');
                return false;
            }
            return true;
        }
        $('#chk').click(function(){
            var chk  = $(this).is(':checked');
            var upc_str = $('input[name=it_id]');
            var msrp_k = $('input[name=msrp_k]');
            var msrp_u = $('input[name=msrp_u]');
            var chk_btn = $('#chk_btn');
            if(!chk){
                upc_str.attr("disabled", "disabled");
                msrp_k.attr("disabled", "disabled");
                msrp_u.attr("disabled", "disabled");
                chk_btn.attr("disabled", "disabled");
            }else{
                chk_btn.removeAttr('disabled');
                upc_str.removeAttr('disabled');
                msrp_k.removeAttr('disabled');
                msrp_u.removeAttr('disabled');

            }
        });
    </script>







<?
include_once("$g4[admin_path]/admin.tail.php");
?>