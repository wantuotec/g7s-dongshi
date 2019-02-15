<?php
/**
 * 网站slogan管理
 *
* @author     madesheng
* @date       2017-02-18
* @category   Slogan_model
* @copyright  Copyright (c)  2017
*
* @version    $Id$
*/
class Slogan_model extends CI_Model
{
	/**
	 * 获取网站slogan列表
	 * 
	 * @param  array $params 请求参数
	 *
	 * @return  bool|array
	 */
	public function get_slogan_list($params = [])
	{
		data_filter($params);

		$service_info = [
			'service_name'   => 'operation.slogan.get_list',
			'service_params' => [
				'fields'     => 'content, item_type, item_explain',
				'is_enabled' => 1,
				'key_name'   => 'item_type',
			],
		];
		$this->load->library('requester');
		$result = $this->requester->request($service_info);

		if (true == $result['success']) {
			return $result['data'];
		} else {
			$this->set_error($result['message']);
			return false;
		}
	}

	/**
	 * 通过唯一类型标识，获取单条网站slogan
	 * 
	 * @param  array $params 请求参数
	 *
	 * @return  bool|array
	 */
	public function get_single_slogan($params = [])
	{
		data_filter($params);

		$service_info = [
			'service_name'   => 'operation.slogan.get',
			'service_params' => [
				'item_type'  => filter_empty('item_type', $params),
			],
		];
		$this->load->library('requester');
		$result = $this->requester->request($service_info);

		if (true == $result['success']) {
			return $result['data'];
		} else {
			$this->set_error($result['message']);
			return false;
		}
	}
}
