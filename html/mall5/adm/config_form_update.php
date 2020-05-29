<?
$sub_menu = "100100";
include_once("./_common.php");

check_demo();

auth_check($auth[$sub_menu], "w");

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.");

$mb = get_member($cf_admin);
if (!$mb[mb_id])
    alert("최고관리자 회원아이디가 존재하지 않습니다.");

$sql = " update $g4[config_table]
            set cf_title                = '$cf_title',
                cf_admin                = '$cf_admin',
                cf_use_point            = '$cf_use_point',
                cf_use_norobot          = '$cf_use_norobot',
                cf_use_copy_log         = '$cf_use_copy_log',
                cf_use_email_certify    = '$cf_use_email_certify',
                cf_login_point          = '$cf_login_point',
                cf_cut_name             = '$cf_cut_name',
                cf_nick_modify          = '$cf_nick_modify',
                cf_new_skin             = '$cf_new_skin',
                cf_new_rows             = '$cf_new_rows',
                cf_search_skin          = '$cf_search_skin',
                cf_connect_skin         = '$cf_connect_skin',
                cf_read_point           = '$cf_read_point',
                cf_write_point          = '$cf_write_point',
                cf_comment_point        = '$cf_comment_point',
                cf_download_point       = '$cf_download_point',
                cf_search_bgcolor       = '$cf_search_bgcolor',
                cf_search_color         = '$cf_search_color',
                cf_write_pages          = '$cf_write_pages',
                cf_link_target          = '$cf_link_target',
                cf_delay_sec            = '$cf_delay_sec',
                cf_filter               = '$cf_filter',
                cf_possible_ip          = '".trim($cf_possible_ip)."',
                cf_intercept_ip         = '".trim($cf_intercept_ip)."',
                cf_member_skin          = '$cf_member_skin',
                cf_use_homepage         = '$cf_use_homepage',
                cf_req_homepage         = '$cf_req_homepage',
                cf_use_tel              = '$cf_use_tel',
                cf_req_tel              = '$cf_req_tel',
                cf_use_hp               = '$cf_use_hp',
                cf_req_hp               = '$cf_req_hp',
                cf_use_addr             = '$cf_use_addr',
                cf_req_addr             = '$cf_req_addr',
                cf_use_signature        = '$cf_use_signature',
                cf_req_signature        = '$cf_req_signature',
                cf_use_profile          = '$cf_use_profile',
                cf_req_profile          = '$cf_req_profile',
                cf_register_level       = '$cf_register_level',
                cf_register_point       = '$cf_register_point',
                cf_icon_level           = '$cf_icon_level',
                cf_use_recommend        = '$cf_use_recommend',
                cf_recommend_point      = '$cf_recommend_point',
                cf_leave_day            = '$cf_leave_day',
                cf_search_part          = '$cf_search_part',
                cf_email_use            = '$cf_email_use',
                cf_email_wr_super_admin = '$cf_email_wr_super_admin',
                cf_email_wr_group_admin = '$cf_email_wr_group_admin',
                cf_email_wr_board_admin = '$cf_email_wr_board_admin',
                cf_email_wr_write       = '$cf_email_wr_write',
                cf_email_wr_comment_all = '$cf_email_wr_comment_all',
                cf_email_mb_super_admin = '$cf_email_mb_super_admin',
                cf_email_mb_member      = '$cf_email_mb_member',
                cf_email_po_super_admin = '$cf_email_po_super_admin',
                cf_prohibit_id          = '$cf_prohibit_id',
                cf_prohibit_email       = '$cf_prohibit_email',
                cf_new_del              = '$cf_new_del',
                cf_memo_del             = '$cf_memo_del',
                cf_visit_del            = '$cf_visit_del',
                cf_popular_del          = '$cf_popular_del',
                cf_use_jumin            = '$cf_use_jumin',
                cf_use_member_icon      = '$cf_use_member_icon',
                cf_member_icon_size     = '$cf_member_icon_size',
                cf_member_icon_width    = '$cf_member_icon_width',
                cf_member_icon_height   = '$cf_member_icon_height',
                cf_login_minutes        = '$cf_login_minutes',
                cf_image_extension      = '$cf_image_extension',
                cf_flash_extension      = '$cf_flash_extension',
                cf_movie_extension      = '$cf_movie_extension',
                cf_formmail_is_member   = '$cf_formmail_is_member',
                cf_page_rows            = '$cf_page_rows',
                cf_stipulation          = '$cf_stipulation',
                cf_privacy              = '$cf_privacy',
                cf_open_modify          = '$cf_open_modify',
                cf_memo_send_point      = '$cf_memo_send_point',
                cf_1_subj               = '$cf_1_subj',
                cf_2_subj               = '$cf_2_subj',
                cf_3_subj               = '$cf_3_subj',
                cf_4_subj               = '$cf_4_subj',
                cf_5_subj               = '$cf_5_subj',
                cf_6_subj               = '$cf_6_subj',
                cf_7_subj               = '$cf_7_subj',
                cf_8_subj               = '$cf_8_subj',
                cf_9_subj               = '$cf_9_subj',
                cf_10_subj              = '$cf_10_subj',
                cf_1                    = '$cf_1',
                cf_2                    = '$cf_2',
                cf_3                    = '$cf_3',
                cf_4                    = '$cf_4',
                cf_5                    = '$cf_5',
                cf_6                    = '$cf_6',
                cf_7                    = '$cf_7',
                cf_8                    = '$cf_8',
                cf_9                    = '$cf_9',
                cf_10                   = '$cf_10',

				/* 김선용 200801 */
				cf_return_ip = trim('$cf_return_ip'),
				cf_return_url = trim('$cf_return_url'),
				cf_point_sum = '$cf_point_sum', /* 200804 */
				/* 김선용 201005 : */
				cf_ssl_use = '$cf_ssl_use'
                ";
sql_query($sql);

//sql_query(" OPTIMIZE TABLE `$g4[config_table]` ");

goto_url("./config_form.php");
?>