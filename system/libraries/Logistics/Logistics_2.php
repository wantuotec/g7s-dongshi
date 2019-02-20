<?php
/**
 * ˳���ѯ��
 *
 * @author      willy
 * @date        2013-08-22
 * @copyright   Copyright(c) 2013 
 * @version     $Id: Logistics_2.php 670 2013-08-26 03:25:45Z ��� $
 */
class CI_Logistics_2 extends CI_Logistics
{
    private $__request_url = 'http://bsp-ois.sf-express.com/bsp-ois/ws/expressService?wsdl';
    private $__customer_id = '0211458763'; // �ͻ�����
    private $__check_word  = 'Ux+2KcfOSKlbwXe:yiPlY_{yCY7z=sHA'; // �ͻ�����

    protected $_max_query_number = 10; // ������ѯʱ��������

    /**
     * ����ӿ�
     *
     * @access  private
     *
     * @param   mixed   $sn     ��������
     *
     * @return  array|false
     */
    private function __request($sn)
    {
        if (empty($sn) || !is_array($sn)) {
            $this->set_error('��������ȱʧ');
            return false;
        }

        $sn = implode(',', $sn);

        //������
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

            if (0 !== strpos($result->return, '<?xml')) { // �����������ֵ��$result->return Ϊ��ֵ�� xml
                $this->set_error('���ص���������');
                return false;
            }

            //���� SimpleXML����
            $result = $this->_object2array(simplexml_load_string($result->return));

            if ('OK' != $result['Head']) { // OK ERR
                $this->set_error('�ӿڴ���' . $result['ERROR']);
                return false;
            }

            if (empty($result['Body']) || empty($result['Body']['RouteResponse'])) {
                $this->set_error('��������');
                return false;
            } else {
                $delivery = array();

                $expresses = $result['Body']['RouteResponse'];
                isset($expresses['@attributes']['mailno']) && $expresses = array($expresses); // ֻ��һ�����ؽ��

                foreach ($expresses as $express) {
                    isset($express['Route']) && isset($express['Route']['@attributes']) && $express['Route'] = array($express['Route']);
                    if (!empty($express['Route']) && is_array($express['Route'])) {
                        foreach ($express['Route'] as $val) {
                            $delivery[$express['@attributes']['mailno']][$val['@attributes']['accept_time']] = array(
                                'time'    => $val['@attributes']['accept_time'],
                                'remark'  => $val['@attributes']['remark'],
                                'address' => $val['@attributes']['accept_address'], // ·����Ϣ�����ĵ�ַ ��Ŀ�ݹ�˾����Ϊ��
                                'code'    => $val['@attributes']['opcode'],         // ������
                            );
                        }
                    }
                }
                return $delivery;
            }
        } catch (Exception $e) {
            $this->set_error('�ӿ������쳣');
            return false;
        }
    }

    /**
     * �����������Ų�ѯ
     *
     * @access  public
     * @param   string  $sn     ��������
     *
     * @return  array|false
     */
    protected function _query($sn)
    {
        if (empty($sn)) {
            $this->set_error('��������ȱʧ');
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
            $this->set_error('�����쳣');
            return false;
        }
    }

    /**
     * ����������Ų�ѯ
     *
     * @access  public
     * @param   mixed   $sn     �������� ��Ϊ������ַ��� �ַ����ö��Ÿ���
     *
     * @return  array|false
     */
    protected function _query_batch($sn)
    {
        return $this->__request($sn);
    }
}
