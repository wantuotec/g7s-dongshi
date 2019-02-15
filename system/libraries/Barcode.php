<?php
/**
 * 条码类，目前支持39码
 * 
 * @author      willy
 * @date        2013-05-07
 * @category    Barcode
 * @copyright   Copyright(c) 2013
 * @version     $Id: Barcode.php 97 2013-05-07 03:10:50Z 杨海波 $
 */
class CI_Barcode
{
    protected $_n2w;     // 条码粗细比例
    protected $_scale;   // 图像水平比例
    protected $_height;  // 图像高度
    protected $_color;   // 图像前景色
    protected $_bgcolor; // 图像背景色
    protected $_format;  // 图像格式
    protected $_encode;  // 编码类型 目前仅有39
    protected $_font;    // 编码字体

    function __construct()
    {

        if (!function_exists("imagecreate")) {
            die("This class needs GD library support.");
            return false;
        }
        $this->_n2w     = 2;
        $this->_scale   = 2;
        $this->_height  = 60;
        $this->_color   = '000000';
        $this->_bgcolor = 'FFFFFF';

        $this->set_format('gif');
        $this->set_hex_color($this->_color, $this->_bgcolor);
        $this->set_encode('CODE39');
    }

    /**
     * barCode::get_barcode()
     * 生成条形码
     *
     * @access  public
     * 
     * @param   string  $barnumber  需要转换的数字
     * @param   string  $format     输出格式png|jpeg|jpg
     * @param   string  $file       保存图片的路径，默认或空返回header形式
     *
     * @return void
     */
    public function get_barcode($barnumber, $file = "")
    {
        switch ($this->_encode) {
            case 'CODE39':
            default:
                $this->_c39_barcode($barnumber, $file, false);
                break;
        }
    }

    /**
     * barCode::_c39_barcode()
     * 生成39条形码
     *
     * @access  protected
     *
     * @param   string  $barnumber      需要转换的数字
     * @param   string  $file           保存图片的路径，默认或空返回header形式
     * @param   bool    $checkdigit     转换符号为数字   
     * @return void
     */
    protected function _c39_barcode($barnumber, $file = "", $checkdigit = false)
    {

        $bars = $this->_c39_encode($barnumber, $checkdigit);

        if (empty($file))
            header("Content-type: image/" . $this->_format);

        if ($this->_scale < 1)
            $this->_scale = 2;
        $total_y = (double)$this->_scale * $this->_height + 10 * $this->_scale;

        $space = array(
            'top'    => 2 * $this->_scale,
            'bottom' => 2 * $this->_scale,
            'left'   => 2 * $this->_scale,
            'right'  => 2 * $this->_scale,
        );

        /* count total width */
        $xpos = 0;

        $xpos = $this->_scale * strlen($bars) + 2 * $this->_scale * 10;

        /* allocate the image */
        $total_x = $xpos + $space['left'] + $space['right'];
        $xpos    = $space['left'] + $this->_scale * 10;

        $height  = floor($total_y - ($this->_scale * 20));
        $height2 = floor($total_y - $space['bottom']);

        $im        = @imagecreatetruecolor($total_x, $total_y);
        $bg_color  = @imagecolorallocate($im, $this->_bgcolor[0], $this->_bgcolor[1], $this->_bgcolor[2]);
        @imagefilledrectangle($im, 0, 0, $total_x, $total_y, $bg_color);
        $bar_color = @imagecolorallocate($im, $this->_color[0], $this->_color[1], $this->_color[2]);

        for ($i = 0; $i < strlen($bars); $i++) {
            $h   = $height;
            $val = $bars[$i];

            if ($val == 1)
                @imagefilledrectangle($im, $xpos, $space['top'], $xpos + $this->_scale - 1, $h, $bar_color);
            $xpos += $this->_scale;
        }

        $font_arr = @imagettfbbox($this->_scale * 10, 0, $this->_font, $barnumber);

        $x = floor($total_x - (int)$font_arr[0] - (int)$font_arr[2] + $this->_scale * 10) / 2;

        @imagettftext($im, $this->_scale * 8, 0, $x, $height2, $bar_color, $this->_font, $barnumber);


        if ($this->_format == "png") {
            if (!empty($file))
                @imagepng($im, $file . "." . $this->_format);
            else
                @imagepng($im);
        }

        if ($this->_format == "gif") {
            if (!empty($file))
                @imagegif($im, $file . "." . $this->_format);
            else
                @imagegif($im);
        }

        if ($this->_format == "jpg" || $this->_format == "jpeg") {
            if (!empty($file))
                @imagejpeg($im, $file . "." . $this->_format);
            else
                @imagejpeg($im);
        }

        @imagedestroy($im);
    }

    /**
     * barCode::_c39_encode()
     * 加密待生成的39条形码
     *
     * @access  protected
     *
     * @param   string  $barnumber      需要转换的数字
     * @param   bool    $checkdigit     转换符号为数字   
     * @return void
     */
    protected function _c39_encode($barnumber, $checkdigit = false)
    {
        $encTable = array(
            "0" => "NNNWWNWNN",
            "1" => "WNNWNNNNW",
            "2" => "NNWWNNNNW",
            "3" => "WNWWNNNNN",
            "4" => "NNNWWNNNW",
            "5" => "WNNWWNNNN",
            "6" => "NNWWWNNNN",
            "7" => "NNNWNNWNW",
            "8" => "WNNWNNWNN",
            "9" => "NNWWNNWNN",
            "A" => "WNNNNWNNW",
            "B" => "NNWNNWNNW",
            "C" => "WNWNNWNNN",
            "D" => "NNNNWWNNW",
            "E" => "WNNNWWNNN",
            "F" => "NNWNWWNNN",
            "G" => "NNNNNWWNW",
            "H" => "WNNNNWWNN",
            "I" => "NNWNNWWNN",
            "J" => "NNNNWWWNN",
            "K" => "WNNNNNNWW",
            "L" => "NNWNNNNWW",
            "M" => "WNWNNNNWN",
            "N" => "NNNNWNNWW",
            "O" => "WNNNWNNWN",
            "P" => "NNWNWNNWN",
            "Q" => "NNNNNNWWW",
            "R" => "WNNNNNWWN",
            "S" => "NNWNNNWWN",
            "T" => "NNNNWNWWN",
            "U" => "WWNNNNNNW",
            "V" => "NWWNNNNNW",
            "W" => "WWWNNNNNN",
            "X" => "NWNNWNNNW",
            "Y" => "WWNNWNNNN",
            "Z" => "NWWNWNNNN",
            "-" => "NWNNNNWNW",
            "." => "WWNNNNWNN",
            " " => "NWWNNNWNN",
            "$" => "NWNWNWNNN",
            "/" => "NWNWNNNWN",
            "+" => "NWNNNWNWN",
            "%" => "NNNWNWNWN",
            "*" => "NWNNWNWNN");

        $mfcStr = "";
        $widebar = str_pad("", $this->_n2w, "1", STR_PAD_LEFT);
        $widespc = str_pad("", $this->_n2w, "0", STR_PAD_LEFT);

        if ($checkdigit == true) {
            $arr_key = array_keys($encTable);
            for ($i = 0; $i < strlen($barnumber); $i++) {
                $num = $barnumber[$i];
                if (preg_match("/[A-Z]+/", $num))
                    $num = ord($num) - 55;
                elseif ($num == '-')
                    $num = 36;
                elseif ($num == '.')
                    $num = 37;
                elseif ($num == ' ')
                    $num = 38;
                elseif ($num == '$')
                    $num = 39;
                elseif ($num == '/')
                    $num = 40;
                elseif ($num == '+')
                    $num = 41;
                elseif ($num == '%')
                    $num = 42;
                elseif ($num == '*')
                    $num = 43;
                $sum += $num;
            }
            $barnumber .= trim($arr_key[(int)($sum % 43)]);
        }

        $barnumber = "*" . $barnumber . "*";

        for ($i = 0; $i < strlen($barnumber); $i++) {
            $tmp = $encTable[$barnumber[$i]];

            $bar = true;

            for ($j = 0; $j < strlen($tmp); $j++) {
                if ($tmp[$j] == 'N' && $bar)
                    $mfcStr .= '1';
                else
                    if ($tmp[$j] == 'N' && !$bar)
                        $mfcStr .= '0';
                    else
                        if ($tmp[$j] == 'W' && $bar)
                            $mfcStr .= $widebar;
                        else
                            if ($tmp[$j] == 'W' && !$bar)
                                $mfcStr .= $widespc;
                $bar = !$bar;
            }
            $mfcStr .= '0';
        }
        return $mfcStr;
    }

    /**
     * 设置图像颜色
     *
     * @access  public
     *
     * @param   string  $color      条码颜色
     * @param   string  $bgcolor    背景颜色
     *
     * @return void
     */
    public function set_hex_color($color, $bgcolor)
    {
        $this->_color = array(
            hexdec(substr($color, 0, 2)),
            hexdec(substr($color, 2, 2)),
            hexdec(substr($color, 4, 2)));
        $this->_bgcolor = array(
            hexdec(substr($bgcolor, 0, 2)),
            hexdec(substr($bgcolor, 2, 2)),
            hexdec(substr($bgcolor, 4, 2)));
    }

    /**
     * 设置图像高度
     *
     * @access  public
     *
     * @param   int     $height     图像高度
     *
     * @return void
     */
    public function set_height($height)
    {
        $this->_height = (int)$height;
    }

    /**
     * 设置图像比例
     *
     * @access  public
     *
     * @param   int     $scale  图像比例
     *
     * @return void
     */
    public function set_scale($scale)
    {
        $this->_scale = (float)$scale;
    }

    /**
     * 设置生成的图片格式
     *
     * @access  public
     *
     * @param   string  $format     图像格式
     *
     * @return void
     */
    public function set_format($format)
    {
        $this->_format = strtolower($format);
    }

    /**
     * 设置使用的编码
     *
     * @access  public
     *
     * @param   string  $encoding   编码
     *
     * @return void
     */
    public function set_encode($encoding)
    {
        $this->_encode = strtoupper($encoding);
    }

    /**
     * 设置字体
     *
     * @access  public
     *
     * @param   string  $font_patch 字体路径
     *
     * @return void
     */
    public function set_font($font_patch)
    {
        if(file_exists($font_patch))
            $this->_font = $font_patch;

    }
}