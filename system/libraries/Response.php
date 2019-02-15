<?php
/**
 * 服务输出类
 */
class CI_Response
{
    // 错误信息
    protected $_error = null;

    /**
     * 自动加载 CI 的属性
     *
     * @param   string  $key    属性名
     *
     * @return  mixed
     */
    public function __get($key)
    {
        $CI = &get_instance();
        return $CI->$key;
    }

    /**
     * 输出错误信息
     * 
     * @return array
     */
    public function response_error_info()
    {
        $ret = [
            'success' => false,
            'errcode' => 99998,
            'message' => '',
            'data'    => []
        ];

        return $ret;
    }

    /**
     * 输出正确信息
     * 
     * @return array
     */
    public function response_success_info()
    {
        $ret = [
            'success' => true,
            'errcode' => 10000,
            'message' => 'ok',
            'data'    => []
        ];

        return $ret;
    }
}