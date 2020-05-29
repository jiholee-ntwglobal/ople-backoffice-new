<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-06-07
 * Time: 오후 2:10
 */
class Test extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('manager_auth');
        $this->load->model('channel/channel_info_model');
        $this->load->model('order/order_model');
        $this->load->model('customer_number/customer_number_history_model');
        $this->load->model('user/ntics_user_model');

    }

    public function index()
    {
        $update_data =
            array(
                'customer_number' =>'sadasdasd',
                'status' => '3',
                'validate_error' => 'cast(validate_error as int) - 1'
            );

        $where = array(
            'package_no' => '123123123123',
            'status' => '1',
            'validate_errors' => '1',
            'channel_id'=>'123123123123'
        );

        $this->order_model->updateOrder($update_data,$where);

    }

}