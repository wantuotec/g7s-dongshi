<?php
/**
 * 系统级别校验助手
 * 
 * @author      willy
 * @date        2012-06-06
 * @category    Validate
 * @copyright   Copyright(c) 2012 
 * @version     $Id: validate_helper.php 253 2013-05-27 10:11:45Z 熊飞龙 $
 */

/**
 * 检查一个变量是否是邮箱
 *
 * @param   mixed   $param  待检测的字符串
 *
 * @return  bool
 */
function is_email($param)
{
    return (bool) preg_match('/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/i', $param);
}

/**
 * 检查一个变量是否是 IP 地址
 *
 * @param   mixed   $param  待检测的字符串
 *
 * @return  bool
 */
function is_ip($param)
{
    return (bool) preg_match('/^\d+\.\d+\.\d+\.\d+$/', $param);
}

/**
 * 检查一个变量是否是身份证号码
 *
 * @param   mixed   $param  待检测的字符串
 *
 * @return  bool
 */
function is_id($param)
{
    return (bool) preg_match('/^\d{15}|\d{18}$/', $param);
}

/**
 * 检查一个变量是否是邮编
 *
 * @param   mixed   $param  待检测的字符串
 *
 * @return  bool
 */
function is_zip($param)
{
    return (bool) preg_match('/^\d{6}$/', $param);
}

/**
 * 检查一个变量是否是 QQ
 *
 * @param   mixed   $param  待检测的字符串
 *
 * @return  bool
 */
function is_qq($param)
{
    return (bool) preg_match('/^[1-9][0-9]{4,}$/', $param);
}

/**
 * 检查一个变量是否是手机号码
 *
 * @param   mixed   $param  待检测的字符串
 *
 * @return  bool
 */
function is_mobile($param)
{
    return (bool) preg_match('/^1(3|4|5|6|7|8|9)[0-9]{1}\d{8}$/', $param);
}

/**
 * 检查是否是座机电话号码
 *
 * @param $param
 *
 * @return bool
 */
function is_telphone($param)
{
    return (bool) preg_match('/^\d{7,8}$/', $param) || preg_match('/^0\d{2,3}-?\d{7,8}(-\d+)?$/', $param);
}

/**
 * 检查一个变量是否是手机号码或者座机号
 *
 * @param   mixed   $param  待检测的字符串
 *
 * @return  bool
 */
function is_phone($param)
{
    return (bool) (is_mobile($param) || is_telphone($param));
}

/**
 * 检查是否是有效的验证码
 *
 * @param $param
 *
 * @return bool
 */
function is_code($param)
{
    return (bool) preg_match('/^\d{4}$/', $param);
}

/**
 * 检查一个变量是否是数字，且长度是否超过最大长度
 * 
 * @param    string    $val    待检测的字符串
 * @param    int       $length 最大长度
 * 
 * @return  bool
 */
function is_int_strlen($val, $length)
{
    // 如果最大长度为1 则变量不允许为0
    if ($length == 1) {
        return (bool) preg_match('/^[1-9]$/', $val);
    } else {
        return (bool) preg_match('/^[0-9]\\d{0,'.($length-1).'}$/', $val);
    }
}

/**
 * 检查是否是有效的日期格式 0000-00-00 00:00:00
 *
 * @param $param
 *
 * @return bool
 */
function is_date($param)
{
    if ($param == date('Y-m-d H:i:s', strtotime($param))) {
        return true;
    } else {
        return false;
    }
}