<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 4:03
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('manager_auth', array('chk_login' => false));
    }

    public function test(){
        echo "test";
    }

    public function index()
    {
        if($this->manager_auth->loginCheck())
            redirect(site_url('dashboard'));

        $this->login_form();
    }

    public function login_form()
    {
        $this->load->view('common/header');
        $this->load->view('auth/login');
        $this->load->view('common/footer');

    }

    public function login_check()
    {
        if($this->manager_auth->loginCheck())
            redirect(site_url('dashboard'));

//        $tmp_arr	= array(
//			'komddung'	=> array(
//				'worker_id'	=>'999'
//			,	'USER_ID'	=>'komddung'
//			,	'USER_NAME'	=>'Kyungin Kang')
//		,	'fhwauin'	=> array(
//				'worker_id'	=>'998'
//			,	'USER_ID'	=>'fhwauin'
//			,	'USER_NAME'	=>'Kang Sojin')
//		,	'naya2410'	=> array(
//				'worker_id'	=>'997'
//			,	'USER_ID'	=>'naya2410'
//			,	'USER_NAME'	=>'Kim Mi Youn')
//		,	'nepnim'	=> array(
//				'worker_id'	=>'996'
//			,	'USER_ID'	=>'nepnim'
//			,	'USER_NAME'	=>'Sujin Jang')
//		,	'nmddd12'	=> array(
//				'worker_id'	=>'995'
//			,	'USER_ID'	=>'nmddd12'
//			,	'USER_NAME'	=>'Sun Juhee')
//		,	'dennis86'	=> array(
//				'worker_id'	=>'994'
//			,	'USER_ID'	=>'dennis86'
//			,	'USER_NAME'	=>'Sinhyung Lee')
//		,	'jiye5n'	=> array(
//				'worker_id'	=>'993'
//			,	'USER_ID'	=>'jiye5n'
//			,	'USER_NAME'	=>'Kim Jiyeon')
//		,	'go0422'	=> array(
//				'worker_id'	=>'992'
//			,	'USER_ID'	=>'go0422'
//			,	'USER_NAME'	=>'Seokhyeon Go')
//		,	'bmyyko'	=> array(
//				'worker_id'	=>'991'
//			,	'USER_ID'	=>'bmyyko'
//			,	'USER_NAME'	=>'Miyeon Bea')
//		,	'ssonida'	=> array(
//				'worker_id'	=>'990'
//			,	'USER_ID'	=>'ssonida'
//			,	'USER_NAME'	=>'Sunyoung Son')
//		,	'hanacooma'	=> array(
//				'worker_id'	=>'989'
//			,	'USER_ID'	=>'hanacooma'
//			,	'USER_NAME'	=>'Hyeonjin Baek')
//		);
        $this->load->model('user/ntics_user_model');

        $user_id = $this->input->post('user_id');
        $passwd = $this->input->post('passwd');

        $user_info = $this->ntics_user_model->getUser(array('USER_ID' => $user_id, "USER_PASSWORD"=>$passwd), 'worker_id, USER_ID, USER_NAME, USER_PASSWORD');
//        $user_info = element($user_id, $tmp_arr, '');

        $return_msg = 'Oops! The User ID or password did not match. Please try again.';

        if(!is_array($user_info)) $user_info = array();

        if(element('USER_ID', $user_info, '') != ''){

//            if(trim(element('USER_PASSWORD', $user_info)) == $passwd){
                $this->session->set_userdata(
                    array(
                        'qten_manager_id' => trim(element('worker_id', $user_info)),
                        'qten_manager_name' => trim(element('USER_NAME', $user_info)),
                        'qten_worker_id' => trim(element('worker_id', $user_info)),
                        'qten_manager_user_id' => trim(element('USER_ID', $user_info))
                    )
                );
                redirect(site_url('dashboard'));
//            }

        }

        alert($return_msg);

    }

    public function logout()
    {
        $this->session->unset_userdata(array('qten_manager_id', 'qten_manager_name', 'qten_worker_id'));

        redirect(site_url('auth/login'));

    }

}