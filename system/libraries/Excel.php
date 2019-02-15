<?php
/**
 * Excel 操作类
 *
 * @author      willy
 * @date        2013-08-23
 * @copyright   Copyright(c) 2013
 * @version     $Id: Excel.php 764 2013-09-23 07:29:00Z 杨海波 $
 */
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include'. DIRECTORY_SEPARATOR . 'PHPExcel') . DIRECTORY_SEPARATOR . 'PHPExcel.php';

class CI_Excel {
    protected $_error  = null;  // 错误信息
    protected $_format = array( // 可操作格式列表
        'xlsx'     => 'Excel2007',
        'xls'      => 'Excel5',
        'xml'      => 'Excel2003XML',
        'htm'      => 'HTML',
        'csv'      => 'CSV',
        'ods'      => 'OOCalc',
        'slk'      => 'SYLK',
        'gnumeric' => 'Gnumeric',
    );
    protected $_default_format = 'xls';  // 默认导出为 xls 格式
    protected $_default_active_sheet_index = 0;  // 默认活动 sheet 的索引
    protected $_default_save_method = 'browser'; // 默认保存方式 browser file
    protected $_information = array(
        'creator'  => 'creator',
        'modified' => 'modified',
        'title'    => 'title',
    );
    protected $_excel = null;

    /**
     * 设置错误信息
     *
     * @param   string  $error  错误信息
     *
     * @return  void
     */
    public function set_error($error)
    {
        $this->_error = $error;
    }

    /**
     * 获取错误信息
     *
     * @return  string
     */
    public function get_error()
    {
        return $this->_error;
    }

    /**
     * 自动加载 CI 的属性
     *
     * @param   string  $key    属性名
     *
     * @return  mixed
     */
    public function __get($key)
    {
        $CI =& get_instance();
        return $CI->$key;
    }

    /**
     * 返回多维数组里允许导出的数据项
     *
     * @param   array   $params 要过滤的参数
     * @param   array   $allow  允许的键名
     *
     * @return  array
     */
    public function excel_data(array $params, array $allow)
    {
        $result = array();
        if (!empty($params) && is_array($params)) {
            if (!empty($allow) && is_array($allow)) {
                foreach ($params as $key => $val) {
                    foreach ($allow as $v) {
                        if(strpos($v, 's%') !== false){
                            $result[$key][substr($v, 2)] = isset($val[substr($v, 2)]) ? "'".$val[substr($v, 2)] : '';
                        }else{
                            $result[$key][$v] = isset($val[$v]) ? $val[$v] : '';
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function set_method($method = 'file') {
        if (!empty($method) && in_array($method, array('browser', 'file'))) {
            $this->_default_save_method = $method;
        }
    }

    public function get_method()
    {
        return empty($this->_default_save_method) ? 'browser' : $this->_default_save_method;
    }

    /**
     * 保存数据
     *
     * @param   string  $filename   文件名
     * @param   array   $content    内容
     * @param   array   $title      标题名
     * @return  bool
     */
    public function save($filename, array $content, array $title = array(), array $fields = array())
    {
        // IE 下文件名乱码 willy 2013-09-16
        // 判断文件名类型 GBK GB2312 为 CP936 编码
        $encoding = mb_detect_encoding($filename, array('UTF-8', 'CP936'));
        /* 如果页面是GBK 编码开启该块则行
        if ('UTF-8' == $encoding) {
            $filename = iconv('UTF-8', 'GB2312', $filename);
        } else if ('CP936' == $encoding) {
            $content = gbk_to_utf8($content);
            $title   = gbk_to_utf8($title);
        }*/


        $excel = new PHPExcel();

        $excel->getProperties()
              ->setCreator($this->_information['creator'])
              ->setLastModifiedBy($this->_information['modified'])
              ->setTitle($this->_information['title']);

        // phpexcel 写入值时如果值为数字 0 或者 NULL 时做如下转换（phpexcel默认会把0和null处理为空白字符） add by willy 2013-09-23
        foreach ($content as &$vals) {
            foreach ($vals as &$v) {
                (0 === $v)    && $v = '0';
                // (null === $v) && $v = 'NULL';
            }
        }

        if (!empty($fields) && is_array($fields)) {
            $content = $this->excel_data($content, $fields);
        }

        if (!empty($title) && is_array($title)) {
            $content = array_merge(array('__title__' => $title), $content);
            $excel->getActiveSheet()->freezePane('A2'); // 有标题的情况下，冻结首行
        }

        $excel->setActiveSheetIndex($this->_default_active_sheet_index)->fromArray($content);

        // $excel->getActiveSheet()->setTitle($filename);

        $excel->setActiveSheetIndex($this->_default_active_sheet_index);

        $write = PHPExcel_IOFactory::createWriter($excel, $this->_format[$this->_default_format]);

        $filename = $filename . '.' . $this->_default_format;

        if ('browser' == $this->get_method()) {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');

            // If you're serving to IE over SSL, then the following may be needed
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0

            $write->save('php://output');
            exit;
        } else {
            $write->save($filename);
        }
    }

    /**
     * 读取数据
     *
     * @param   string  $filename       要读取的文件路径
     * @param   bool    $has_first_line 是否包含第一行
     *
     * @return  array
     */
    public function load($filename = null, $has_first_line=false)
    {
        if ('browser' == $this->get_method()) {
            $data = $_FILES['uploadfile'];
            $fileInfo  = pathinfo($data['name']);
            $extension = $fileInfo['extension'];

            if (empty($data['tmp_name'])) {
                $this->set_error('文件上传失败');
                return false;
            }

            $filename = $data['tmp_name'];
            @chmod($filename, 0777);
        } else {
            $extension = pathinfo($filename);
        }

        if (!in_array($extension, array_keys($this->_format))) {
           $this->set_error('上传文件格式不正确，应为.xls或者.xlsx');
           return false;
        }

        if (!file_exists($filename)) {
            $this->set_error('文件不存在');
            return false;
        }

        $excel = PHPExcel_IOFactory::load($filename);
        $content = $excel->getActiveSheet()->toArray();

        if (false === $has_first_line) {
            array_shift($content);
        }

        if ('browser' == $this->get_method()) {
            @unlink($filename);
        }

        return $content;
    }

}

// 测试代码
// $excel = new CI_Excel();
// $excel->set_method('file');
// $excel->save('aaab', array(array('中国'), array('a', 'b')));
// $excel->load('./aaab.xls');
