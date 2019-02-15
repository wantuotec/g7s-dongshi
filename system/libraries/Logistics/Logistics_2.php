<?php
/**
 * 顺丰查询类
 *
 * @author      willy
 * @date        2013-08-22
 * @copyright   Copyright(c) 2013 
 * @version     $Id: Logistics_2.php 670 2013-08-26 03:25:45Z 杨海波 $
 */
class CI_Logistics_2 extends CI_Logistics
{
    private $__request_url = 'http://bsp-ois.sf-express.com/bsp-ois/ws/expressService?wsdl';
    private $__customer_id = '0211458763'; // 客户卡号
    private $__check_word  = 'Ux+2KcfOSKlbwXe:yiPlY_{yCY7z=sHA'; // 客户卡号

    protected $_max_query_number = 10; // 批量查询时最大的数量

    /**
     * 请求接口
     *
     * @access  private
     *
     * @param   mixed   $sn     物流单号
     *
     * @return  array|false
     */
    private function __request($sn)
    {
        if (empty($sn) || !is_array($sn)) {
            $this->set_error('物流单号缺失');
            return false;
        }

        $sn = implode(',', $sn);

        //请求报文
        $xml = <<<EOT
            <Request service='RouteService' lang='zh-CN'>
                <Head>{$this->__customer_id},{$this->__check_word}</Head>
                <Body>
                    <RouteRequest tracking_type='1'  method_type='1' tracking_number='{$sn}' />
                </Body>
            </Request>
EOT;

        $params = array(
            'arg0' => $xml,
        );

         try {
            $client = new SoapClient($this->__request_url);
            $result = $client->__soapCall('sfexpressService', array('parameters' => $params));

            if (0 !== strpos($result->return, '<?xml')) { // 如果反馈错误值，$result->return 为数值非 xml
                $this->set_error('返回的数据有误');
                return false;
            }

            //创建 SimpleXML对象
            $result = $this->_object2array(simplexml_load_string($result->return));

            if ('OK' != $result['Head']) { // OK ERR
                $this->set_error('接口错误：' . $result['ERROR']);
                return false;
            }

            if (empty($result['Body']) || empty($result['Body']['RouteResponse'])) {
                $this->set_error('暂无数据');
                return false;
            } else {
                $delivery = array();

                $expresses = $result['Body']['RouteResponse'];
                isset($expresses['@attributes']['mailno']) && $expresses = array($expresses); // 只有一条返回结果

                foreach ($expresses as $express) {
                    isset($express['Route']) && isset($express['Route']['@attributes']) && $express['Route'] = array($express['Route']);
                    if (!empty($express['Route']) && is_array($express['Route'])) {
                        foreach ($express['Route'] as $val) {
                            $delivery[$express['@attributes']['mailno']][$val['@attributes']['accept_time']] = array(
                                'time'    => $val['@attributes']['accept_time'],
                                'remark'  => $val['@attributes']['remark'],
                                'address' => $val['@attributes']['accept_address'], // 路由信息发生的地址 别的快递公司可能为空
                                'code'    => $val['@attributes']['opcode'],         // 操作码
                            );
                        }
                    }
                }
                return $delivery;
            }
        } catch (Exception $e) {
            $this->set_error('接口连接异常');
            return false;
        }
    }

    /**
     * 单个物流单号查询
     *
     * @access  public
     * @param   string  $sn     物流单号
     *
     * @return  array|false
     */
    protected function _query($sn)
    {
        if (empty($sn)) {
            $this->set_error('物流单号缺失');
            return false;
        }

        $result = $this->__request(array($sn));
        if (false === $result) {
            $this->set_error($this->get_error());
            return false;
        }

        if (isset($result[$sn])) {
            return $result[$sn];
        } else {
            $this->set_error('数据异常');
            return false;
        }
    }

    /**
     * 多个物流单号查询
     *
     * @access  public
     * @param   mixed   $sn     物流单号 可为数组或字符串 字符串用逗号隔开
     *
     * @return  array|false
     */
    protected function _query_batch($sn)
    {
        return $this->__request($sn);
    }
}
