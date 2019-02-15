<?php
/**
 * 验证码图片类
 *
 * @author      willy
 * @date        2012-05-24
 * @copyright   Copyright(c) 2012
 * @version     $Id: Captcha.php 1 2013-04-12 11:19:06Z 杨海波 $
 */
class CI_Captcha {
    private $__words_pool = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
    private $__expire     = 300; // 默认5分钟内有效
    private $__error      = null;

    /**
     * 设置错误信息
     *
     * @param   string  $error  错误信息
     *
     * @return  void
     */
    public function set_error($error)
    {
        $this->__error = $error;
    }

    /**
     * 获取错误信息
     *
     * @return  string
     */
    public function get_error()
    {
        return $this->__error;
    }

    /**
     * 校验验证码是否有效
     *
     * @access  public
     *
     * @param   string  $captcha        验证码内容
     * @param   string  $useage         验证码用途
     * @param   string  $session_name   存验证码的 session 名称
     *
     * @return  bool
     */
    public function is_valid($captcha = null, $useage = null, $expire = null, $session_name = 'captcha')
    {
        if (!isset($captcha) || $captcha === '') {
            $this->set_error('验证码不能为空');
            return false;
        }

        if (!isset($_SESSION[$session_name]) || (isset($useage) && !isset($_SESSION[$session_name][$useage]))) {
            $this->set_error('验证码已过期');
            return false;
        }

        $expire       = isset($expire) && intval($expire) > 0 ? intval($expire) : $this->__expire;
        $captcha_data = isset($useage) ? $_SESSION[$session_name][$useage] : $_SESSION[$session_name];

        if (empty($captcha_data) || !is_array($captcha_data) || !isset($captcha_data['word']) || !isset($captcha_data['time'])) {
            $this->set_error('验证码已过期');
            return false;
        }

        if (time() > $captcha_data['time'] + $expire) {
            $this->set_error('验证码已过期');
            return false;
        }

        if (strtolower($captcha_data['word']) != strtolower($captcha)) {
            $this->set_error('验证码不正确');
            return false;
        }

        return true;
    }

    /**
     * 验证码生成 (本方法改造自 captcha_helper.php 代码格式、用法保持一致)
     *
     * @access  public
     * @param   array   $data       验证码配置
     * @param   string  $font_path  字体路径
     * @return  void
     */
    public function create($data = '', $font_path = '')
    {
        $defaults = array(
            'session_name' => 'captcha',
            'useage'       => '',
            'word'         => '',
            'width'        => '150',
            'height'       => '30',
            'font_path'    => '',
            'expiration'   => 7200
        );

        foreach ($defaults as $key => $val)
        {
            if ( ! is_array($data))
            {
                if ( ! isset($$key) OR $$key == '')
                {
                    $$key = $val;
                }
            }
            else
            {
                $$key = ( ! isset($data[$key])) ? $val : $data[$key];
            }
        }

        $img_height = $height > 20 ? $height : 20;
        $img_width  = $width  > 42 ? $width  : 42;
        unset($height, $width);

        if ( ! extension_loaded('gd'))
        {
            return FALSE;
        }

        // -----------------------------------
        // Remove old images
        // -----------------------------------

        list($usec, $sec) = explode(" ", microtime());
        $now = ((float)$usec + (float)$sec);

        // -----------------------------------
        // Do we have a "word" yet?
        // -----------------------------------

       if ($word == '')
       {
            $pool = $this->__words_pool;

            $str = '';
            for ($i = 0; $i < 4; $i++)
            {
                $str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
            }

            $word = $str;
       }

        // -----------------------------------
        // Determine angle and position
        // -----------------------------------

        $length    = strlen($word);
        $angle    = ($length >= 6) ? rand(-($length-6), ($length-6)) : 0;
        $x_axis    = rand(6, (360/$length)-16);
        $y_axis = ($angle >= 0 ) ? rand($img_height, $img_width) : rand(6, $img_height);

        // -----------------------------------
        // Create image
        // -----------------------------------

        // PHP.net recommends imagecreatetruecolor(), but it isn't always available
        if (function_exists('imagecreatetruecolor'))
        {
            $im = imagecreatetruecolor($img_width, $img_height);
        }
        else
        {
            $im = imagecreate($img_width, $img_height);
        }

        // -----------------------------------
        //  Assign colors
        // -----------------------------------

        $bg_color        = imagecolorallocate ($im, 255, 255, 255);
        $border_color    = imagecolorallocate ($im, 153, 102, 102);
        $text_color      = imagecolorallocate ($im, 14, 78, 173);
        $grid_color      = imagecolorallocate($im, 255, 182, 182);
        $shadow_color    = imagecolorallocate($im, 255, 240, 240);

        // -----------------------------------
        //  Create the rectangle
        // -----------------------------------

        ImageFilledRectangle($im, 0, 0, $img_width, $img_height, $bg_color);

        // -----------------------------------
        //  Create the spiral pattern
        // -----------------------------------

        $theta        = 1;
        $thetac        = 7;
        $radius        = 16;
        $circles    = 20;
        $points        = 20;

        for ($i = 0; $i < ($circles * $points) - 1; $i++)
        {
            $theta = $theta + $thetac;
            $rad = $radius * ($i / $points );
            $x = ($rad * cos($theta)) + $x_axis;
            $y = ($rad * sin($theta)) + $y_axis;
            $theta = $theta + $thetac;
            $rad1 = $radius * (($i + 1) / $points);
            $x1 = ($rad1 * cos($theta)) + $x_axis;
            $y1 = ($rad1 * sin($theta )) + $y_axis;
            imageline($im, $x, $y, $x1, $y1, $grid_color);
            $theta = $theta - $thetac;
        }

        // -----------------------------------
        //  Write the text
        // -----------------------------------

        $use_font = ($font_path != '' AND file_exists($font_path) AND function_exists('imagettftext')) ? TRUE : FALSE;

        if ($use_font == FALSE)
        {
            $font_size = 5;
            $padding   = 2;
            $r = ($img_width - $font_size * $length * $padding)-1;
            $x = mt_rand(1, $r);
            $y = 0;
        }
        else
        {
            $font_size    = 16;
            $x = rand(0, $img_width/($length/1.2));
            $y = $font_size+2;
        }

        for ($i = 0; $i < strlen($word); $i++)
        {
            if ($use_font == FALSE)
            {
                $y = mt_rand(0 , $img_height/3);
                imagestring($im, $font_size, $x, $y, substr($word, $i, 1), $text_color);
                $x += ($font_size*$padding);
            }
            else
            {
                $y = rand($img_height/2, $img_height-3);
                imagettftext($im, $font_size, $angle, $x, $y, $text_color, $font_path, substr($word, $i, 1));
                $x += $font_size;
            }
        }


        // -----------------------------------
        //  Create the border
        // -----------------------------------

        imagerectangle($im, 0, 0, $img_width-1, $img_height-1, $border_color);

        // -----------------------------------
        //  Generate the image
        // -----------------------------------

        // 把结果存入 session
        $captcha = array('word' => $word, 'time' => $now);
        if (isset($useage) && $useage !== '') {
            $_SESSION[$session_name][$useage] = $captcha;
        } else {
            $_SESSION[$session_name] = $captcha;
        }

        header('Content-Type:image/png');
        Imagepng($im);
        ImageDestroy($im);
    }

    /**
     * 验证码生成 (本方法改造自 captcha_helper.php 代码格式、用法保持一致) base64 格式字符串
     *
     * @access  public
     * @param   array   $data       验证码配置
     * @param   string  $font_path  字体路径
     * @return  void
     */
    public function create_base64($data = '', $font_path = '')
    {
        $defaults = array(
            'session_name' => 'captcha',
            'useage'       => '',
            'word'         => '',
            'width'        => '60',
            'height'       => '30',
            'font_path'    => '',
            'expiration'   => 7200
        );

        foreach ($defaults as $key => $val)
        {
            if ( ! is_array($data))
            {
                if ( ! isset($$key) OR $$key == '')
                {
                    $$key = $val;
                }
            }
            else
            {
                $$key = ( ! isset($data[$key])) ? $val : $data[$key];
            }
        }

        $img_height = $height > 20 ? $height : 20;
        $img_width  = $width  > 42 ? $width  : 42;
        unset($height, $width);

        if ( ! extension_loaded('gd'))
        {
            return FALSE;
        }

        // -----------------------------------
        // Remove old images
        // -----------------------------------

        list($usec, $sec) = explode(" ", microtime());
        $now = ((float)$usec + (float)$sec);

        // -----------------------------------
        // Do we have a "word" yet?
        // -----------------------------------

       if ($word == '')
       {
            $pool = $this->__words_pool;

            $str = '';
            for ($i = 0; $i < 4; $i++)
            {
                $str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
            }

            $word = $str;
       }

        // -----------------------------------
        // Determine angle and position
        // -----------------------------------

        $length    = strlen($word);
        $angle    = ($length >= 6) ? rand(-($length-6), ($length-6)) : 0;
        $x_axis    = rand(6, (360/$length)-16);
        $y_axis = ($angle >= 0 ) ? rand($img_height, $img_width) : rand(6, $img_height);

        // -----------------------------------
        // Create image
        // -----------------------------------

        // PHP.net recommends imagecreatetruecolor(), but it isn't always available
        if (function_exists('imagecreatetruecolor'))
        {
            $im = imagecreatetruecolor($img_width, $img_height);
        }
        else
        {
            $im = imagecreate($img_width, $img_height);
        }

        // -----------------------------------
        //  Assign colors
        // -----------------------------------

        $bg_color        = imagecolorallocate ($im, 255, 255, 255);
        $border_color    = imagecolorallocate ($im, 153, 102, 102);
        $text_color      = imagecolorallocate ($im, 14, 78, 173);
        $grid_color      = imagecolorallocate($im, 255, 182, 182);
        $shadow_color    = imagecolorallocate($im, 255, 240, 240);

        // -----------------------------------
        //  Create the rectangle
        // -----------------------------------

        ImageFilledRectangle($im, 0, 0, $img_width, $img_height, $bg_color);

        // -----------------------------------
        //  Create the spiral pattern
        // -----------------------------------

        $theta        = 1;
        $thetac        = 7;
        $radius        = 16;
        $circles    = 20;
        $points        = 20;

        for ($i = 0; $i < ($circles * $points) - 1; $i++)
        {
            $theta = $theta + $thetac;
            $rad = $radius * ($i / $points );
            $x = ($rad * cos($theta)) + $x_axis;
            $y = ($rad * sin($theta)) + $y_axis;
            $theta = $theta + $thetac;
            $rad1 = $radius * (($i + 1) / $points);
            $x1 = ($rad1 * cos($theta)) + $x_axis;
            $y1 = ($rad1 * sin($theta )) + $y_axis;
            imageline($im, $x, $y, $x1, $y1, $grid_color);
            $theta = $theta - $thetac;
        }

        // -----------------------------------
        //  Write the text
        // -----------------------------------

        $use_font = ($font_path != '' AND file_exists($font_path) AND function_exists('imagettftext')) ? TRUE : FALSE;

        if ($use_font == FALSE)
        {
            $font_size = 5;
            $padding   = 2;
            $r = ($img_width - $font_size * $length * $padding)-1;
            $x = mt_rand(1, $r);
            $y = 0;
        }
        else
        {
            $font_size    = 16;
            $x = rand(0, $img_width/($length/1.2));
            $y = $font_size+2;
        }

        for ($i = 0; $i < strlen($word); $i++)
        {
            if ($use_font == FALSE)
            {
                $y = mt_rand(0 , $img_height/3);
                imagestring($im, $font_size, $x, $y, substr($word, $i, 1), $text_color);
                $x += ($font_size*$padding);
            }
            else
            {
                $y = rand($img_height/2, $img_height-3);
                imagettftext($im, $font_size, $angle, $x, $y, $text_color, $font_path, substr($word, $i, 1));
                $x += $font_size;
            }
        }


        // -----------------------------------
        //  Create the border
        // -----------------------------------

        imagerectangle($im, 0, 0, $img_width-1, $img_height-1, $border_color);

        // -----------------------------------
        //  Generate the image
        // -----------------------------------

        header('Content-Type:image/png');
        ob_start();
        Imagepng($im);
        $content = ob_get_contents();
        ob_end_clean();
        ImageDestroy($im);

        return array(
            'word'   => $word,
            'base64' => base64_encode($content),
        );
    }
}