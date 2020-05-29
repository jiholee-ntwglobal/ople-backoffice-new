<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 5:02
 */
class Manager_auth
{
    protected $CI;

    public function __construct($param=array())
    {
        // Assign the CodeIgniter super-object
        $this->CI =& get_instance();

        $this->CI->session->set_userdata(array("current_url"=>uri_string()));

        if(element('chk_login', $param, true))
            $this->chkSession();
    }

    public function chkSession()
    {
        if(!$this->loginCheck())
            redirect(site_url('auth/login'));

    }

    public function loginCheck()
    {
        return $this->CI->session->has_userdata('oms_manager_name');
    }

}