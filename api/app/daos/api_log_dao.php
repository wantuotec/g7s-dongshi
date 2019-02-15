<?php
/**
 * Api_log 接口日志
 *
 * @author      yanghaibo
 * @date        2014-12-02
 * @category    Api_log_dao
 *
 * @version     $Id$
 */
class Api_log_dao extends CI_Dao
{
    protected $_db_write = array('cluster' => 1, 'mode' => 'write');
    protected $_db_read  = array('cluster' => 1, 'mode' => 'read');

    protected $_table    = 'api_log';

    protected $_fields   = '`api_log_id`, `method`, `success`, `errcode`, `message`, `execute_time`, `create_time`, `from_ip`';

    /**
     * 获取队列日志表名
     *
     * @param   string  $suffix 表后缀
     *
     * @param   string
     */
    public function get_log_table($suffix = NULL)
    {
        is_null($suffix) && $suffix = date('ym');
        return $this->_table . '_' . $suffix;
    }

    /**
     * 判断某个表是否存在
     * 
     * @access  public
     *
     * @param   
     * @return  bool
     */
    public function is_table_exists(&$db, $table)
    {
        return $db->query("show tables like '{$table}'")->row_array() ? true : false;
    }

    /**
     * 创建日志数据表
     *
     * @param   resorce $db     数据库连接
     * @param   string  $table  数据表名
     *
     * @param   string
     */
    public function create_log_table(&$db, $table)
    {
        if (!$this->is_table_exists($db, $table)) {
            $sql = <<<EOT
                CREATE TABLE IF NOT EXISTS `{$table}` (
                    `api_log_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '接口日志ID',
                    `method` VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'API接口名称',
                    `success` TINYINT(1) UNSIGNED ZEROFILL NOT NULL DEFAULT '0' COMMENT '接口成功或者失败 1成功 2失败',
                    `errcode` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '错误代码',
                    `message` TEXT NOT NULL COMMENT '错误信息',
                    `execute_time` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '执行时间 单位毫秒',
                    `create_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                    `from_ip` CHAR(15) NOT NULL DEFAULT '' COMMENT '来源IP',
                    `timestamp` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '请求时间戳',
                    `format` CHAR(10) NOT NULL DEFAULT '' COMMENT '响应格式',
                    `version` CHAR(10) NOT NULL DEFAULT '' COMMENT 'API协议版本',
                    `charset` CHAR(10) NOT NULL DEFAULT '' COMMENT '参数的字符编码',
                    `sign` CHAR(50) NOT NULL DEFAULT '' COMMENT '签名',
                    `sign_method` CHAR(10) NOT NULL DEFAULT '' COMMENT '签名方式',
                    `app_key` CHAR(32) NOT NULL DEFAULT '' COMMENT '应用密钥',
                    `app_session` CHAR(32) NOT NULL DEFAULT '' COMMENT '应用会话',
                    `app_version` CHAR(10) NOT NULL DEFAULT '' COMMENT '应用版本',
                    `app_type` TINYINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '应用类型 1 安卓商家版 2 安卓配送版 3 IOS商家版 4 IOS配送版 为app里的app_id',
                    `uuid` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '设备唯一识别符',
                    `request` TEXT NOT NULL COMMENT '原始请求信息',
                    `response` TEXT NOT NULL COMMENT '原始返回信息',
                    PRIMARY KEY (`api_log_id`)
                )
                COMMENT='接口请求日志'
                COLLATE='utf8_general_ci'
                ENGINE=MyISAM
EOT;

            $db->query($sql);
        }
    }

    /**
     * 插入数据
     *
     * @access  public
     *
     * @param   array   $params             待插入的信息
     * @param   bool    $return_insert_id   是否返回插入的 ID
     * @param   bool    $is_batch           是否插入多条
     *
     * @return  int|false
     */
    public function insert(array $params, $return_insert_id = false, $is_batch = false)
    {
        $this->load->rwdb($this->_db_write);

        // 如果表不存在则自动创建表
        $table = $this->get_log_table();
        $this->create_log_table($this->rwdb, $table);

        if (true === $is_batch) {
            $result = $this->rwdb->insert_batch($table, $params);
        } else {
            $result = $this->rwdb->insert($table, $params);
        }

        // 把 SQL 保存起来
        $this->set_sql($this->rwdb->last_query());

        if (true === $result && true === $return_insert_id && false === $is_batch) {
            return $this->rwdb->insert_id();
        } else {
            return $result;
        }
    }
}