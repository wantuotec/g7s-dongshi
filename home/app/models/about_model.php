<?php
/**
 * 网站信息管理
 *
* @author     madesheng
* @date       2017-02-18
* @category   About_model
* @copyright  Copyright (c)  2017
*
* @version    $Id$
*/
class About_model extends CI_Model
{
	/**
	 * 获取网站基本信息
	 * 
	 * @param  array $params 请求参数
	 *
	 * @return  bool|array
	 */
	public function get_website_info($params = [])
	{
		data_filter($params);

		$service_info = [
			'service_name'   => 'operation.about.get',
			'service_params' => [],
		];
		$this->load->library('requester');
		$result = $this->requester->request($service_info);

		// 数据处理：生日转年龄
		if ($result['data']) {
			$result['data']['age']       = get_age($result['data']['birthday'])['age'];
			$result['data']['sex_title'] = $result['data']['sex'] == 1 ? '♂' : '♀';
		}

		if (true == $result['success']) {
			return $result['data'];
		} else {
			$this->set_error($result['message']);
			return false;
		}
	}
}
