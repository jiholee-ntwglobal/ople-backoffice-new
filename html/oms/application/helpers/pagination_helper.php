<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Short description.
 * @param   type    $varname    description
 * @return  type    description
 * @access  public or private
 * @static  makes the class property accessible without needing an instantiation of the class
 */
function set_pagination($base_url, $total_count, $perpage=null)
{
	$CI =& get_instance();

	// load pagination config variables
	$CI->config->load('pagination', TRUE);
	$pagination_config = $CI->config->item('pagination');
	$pagination_config['base_url'] = $base_url;
	$pagination_config['total_rows'] = $total_count;

	$temp_pagination = calc_pagination($pagination_config);
	$pagination_config['cur_page'] = $temp_pagination['cur_page'];
	if($perpage !== null)
	{
		$pagination_config['offset'] = $pagination_config['total_rows'] > 0 ? ($temp_pagination['cur_page'] - 1) * $perpage : 0;
	}
	else
	{
		$pagination_config['offset'] = $temp_pagination['offset'];
	}

	return $pagination_config;
} // end func

function calc_pagination($config)
{
	$CI =& get_instance();

	// get now page
	if($CI->input->post('ajax_cur_page') > 0)
	{
		$cur_page = $CI->input->post('ajax_cur_page');
	}
	else
	{
		if($CI->init_cur_page === true)
		{
			$cur_page = 1;
		}
		else
		{
			$cur_page = ($CI->uri->segment($config['uri_segment'])) ? $CI->uri->segment($config['uri_segment']) : 1;
		}
	}

	// math to get the initial record to be select in the database
	$offset = ($cur_page * $config['per_page']) - $config['per_page'];
	if ($offset < 0)
	{
		$offset = 0;
	}

	// page calculate
	$total_pages = ceil($config['total_rows'] / $config['per_page']);
	if($cur_page > $total_pages)
	{
		$cur_page = $total_pages;
	}

	return array('cur_page' => $cur_page, 'offset' => $offset);
}

function calc_pagination_ajax($config)
{
	$CI =& get_instance();

	// math to get the initial record to be select in the database
	$offset = ($config['cur_page']* $config['per_page']) - $config['per_page'];
	if ($offset < 0)
	{
		$offset = 0;
	}

	// page calculate
	$total_pages = ceil($config['total_rows'] / $config['per_page']);
	if($config['cur_page'] > $total_pages)
	{
		$config['cur_page'] = $total_pages;
	}

	return array('cur_page' => $config['cur_page'], 'offset' => $offset);
}
?>