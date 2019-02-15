<?php
/**
 * CI框架pagination类略加修改而成
 * @param : int $config['total_rows'] (must)
 * @param : int $config['per_page']
 * @param : string $config['get_tag'] //GET变量参数name
 * 
 * 使用：
 * $this->pageBar() 即可得到分页条;
 */
class CI_Pagenav {
    var $base_url            = '';
    var $get_tag             = 'offset'; //----默认get传递变量 ‘？p= ’
    var $total_rows          = ''; //---- 总数 ----
    var $per_page            = 10; //----默认每页显示 10 条数据 ----
    var $num_links           = 3;  //----默认当前页码前后各显示 3 个页码----
    var $cur_page            = 0; //----当前页码----
    var $first_link          = '&lt;';
    var $next_link           = '';
    var $prev_link           = '';
    var $last_link           = '&gt;';
    var $full_tag_open       = '<div class="paginator clearfix">';
    var $full_tag_close      = '</div>';
    var $first_tag_open      = '';
    var $first_tag_close     = '';
    var $last_tag_open       = '';
    var $last_tag_close      = '';
    var $cur_tag_open        = '<span class="cur">';
    var $cur_tag_close       = '</span>';
    var $next_tag_open       = '&nbsp;';
    var $next_tag_close      = '';
    var $prev_tag_open       = '';
    var $prev_tag_close      = '&nbsp;';
    var $num_tag_open        = '&nbsp;';
    var $num_tag_close       = '';    
    var $break_tag           = '<span class="break">...</span>';
    var $zero_tag            = '<div class="pagenobox clearfix"><div class="totaltitle">共<span class="totalCount">0</span>项</div></div>';
    var $character_tag_open  = '<div class="pagenobox clearfix"><div class="totaltitle">共<span class="totalCount">';
    var $character_tag_close = '</span>项 </div>';
    var $max_page_num        = '';    //最大显示页数 add by unspace
    
    public function __construct($params = array())
    {
        if(count($params) > 0){
            $this->initialize($params);
        }
    }

    public function initialize($params = array())
    {
        if(count($params) > 0){
            foreach ($params as $key => $value){
                if(isset($this->$key)){
                    $this->$key = $value;
                }
            }
        }
        $this->base_url = $this->base_url == '' ? $_SERVER['REQUEST_URI'] : $this->base_url;
        $this->num_links = $this->num_links < 0 ? 0 : $this->num_links;
    }

    public function pageBarLite()
    {
        //---- total_pages ----
        $total_pages = ceil(($this->total_rows / $this->per_page));

        //---- max show page
        if(!empty($this->max_page_num) && $this->max_page_num > 0 && $this->max_page_num < $total_pages)
        {
            $max_page_num = $this->max_page_num;
        }
        else
        {
            $max_page_num = $total_pages;
        }

        //---- chinese character before pageBar ----
        $pre_output = '';

        if($total_pages == 1)
        {
            $pre_output .= '<span class="pagelitebox"><span class="paginator">';
            $pre_output .= '<a class="prev_over" href="javascript:void(0)"></a>';
            $pre_output .= '<a class="next_over" href="javascript:void(0)"></a>';
            $pre_output .= '</span></span>';
            return $pre_output;
        }

        //---- href url ----
        if(!stripos($this->base_url, $this->get_tag . '='))
        {
            $this->base_url = $this->base_url . (stripos($this->base_url, '?') ? '&amp;' : '?') . $this->get_tag . '=';
        }
        else
        {
            $this->base_url = substr($this->base_url , 0 , (strripos($this->base_url,'=') + 1));
        }

        //---- cur_page and $_GET[$this->get_tag]----
        $offset_num = isset($_GET[$this->get_tag]) ? $_GET[$this->get_tag] : 0;
        settype($offset_num,'integer');
        $this->cur_page = floor($offset_num / $this->per_page + 1);

        //----pagebar----
        $output = '';

        // Render the "Prev" link    
        $i = ($offset_num - $this->per_page) < 0 ? 0 : ($offset_num - $this->per_page);
        if($this->cur_page == 1){
            $output .='<a class="prev_over" href="javascript:void(0)"></a>';
        }else{
            $output .='<a class="prev" href="'.$this->base_url.$i.'">'.$this->prev_link.'</a>';
        }

        $output .= '第 '. $this->cur_tag_open . $this->cur_page . $this->cur_tag_close . ' 页'; ; // Current page
        $output .= ' / ';
        $output .= '共 '. $total_pages.' 页'; 

        // Render the "next" link
        if ($this->cur_page < $max_page_num)
        {
            $output .= '<a class="next" href="'.$this->base_url.($this->cur_page * $this->per_page).'">'.$this->next_link.'</a>';
        }else{
            $output .= '<a class="next_over" href="javascript:void(0)"></a>';
        }

        // Kill double slashes.  Note: Sometimes we can end up with a double slash
        // in the penultimate link so we'll kill all double slashes.
        $output = preg_replace("#([^:])//+#", "\\1/", $output);

        // Add the wrapper HTML if exists
        $output = '<span class="pagelitebox"><span class="paginator">' . $output . '</span></span>';
        $output = $pre_output.$output;

        return $output;
    }

    public function pageBar()
    {
        //If total_rows = 0 or per_page = 0 
        if($this->total_rows == 0 || $this->per_page == 0)
        {
            return $this->zero_tag;
        }

        //---- total_pages ----
        $total_pages = ceil(($this->total_rows / $this->per_page));

        //---- max show page
        if(!empty($this->max_page_num) && $this->max_page_num > 0 && $this->max_page_num < $total_pages)
        {
            $max_page_num = $this->max_page_num;
        }
        else
        {
            $max_page_num = $total_pages;
        }

        //---- chinese character before pageBar ----
        $pre_output = '';
        $pre_output .= $this->character_tag_open.$this->total_rows.$this->character_tag_close;
        if($total_pages == 1)
        {
            $pre_output .= $this->full_tag_open;
            $pre_output .= $this->prev_tag_open.'<a class="prev_over" href="javascript:void(0)"></a>'.$this->prev_tag_close;
            $pre_output .= $this->next_tag_open.'<a class="next_over" href="javascript:void(0)"></a>'.$this->next_tag_close;
            $pre_output .= $this->full_tag_close;
            return $pre_output;
        }

        //---- href url ----
        if(!stripos($this->base_url, $this->get_tag . '='))
        {
            $this->base_url = $this->base_url . (stripos($this->base_url, '?') ? '&amp;' : '?') . $this->get_tag . '=';
        }
        else
        {
            $this->base_url = substr($this->base_url , 0 , (strripos($this->base_url,'=') + 1));
        }

        //---- cur_page and $_GET[$this->get_tag]----
        $offset_num = isset($_GET[$this->get_tag]) ? $_GET[$this->get_tag] : 0;
        settype($offset_num,'integer');
        $this->cur_page = floor($offset_num / $this->per_page + 1);

        //----pagebar----
        $output = '';

        // Render the "Prev" link    
        $i = ($offset_num - $this->per_page) < 0 ? 0 : ($offset_num - $this->per_page);
        if($this->cur_page == 1){
            $output .= $this->prev_tag_open.'<a class="prev_over" href="javascript:void(0)"></a>'.$this->prev_tag_close;
        } else {
            $output .= $this->prev_tag_open.'<a class="prev" href="'.$this->base_url.$i.'">'.$this->prev_link.'</a>'.$this->prev_tag_close;
        }

        // Write the digit links ---- 页码条 ----
        if($max_page_num <= (2 * ($this->num_links + 1)))
        {
            for ($loop = 0; $loop <= $max_page_num; $loop++)
            {
                $i = ($loop * $this->per_page) - $this->per_page;
                                        
                if ($i >= 0)
                {
                    if ($this->cur_page == $loop)
                    {
                        $output .= $this->cur_tag_open.$loop.$this->cur_tag_close; // Current page
                    }
                    else
                    {
                        $n = ($i == 0) ? 0 : $i;
                        $output .= $this->num_tag_open.'<a href="'.$this->base_url.$n.'">'.$loop.'</a>'.$this->num_tag_close;
                    }
                }
            }
        }
        else
        {
            // Calculate the start and end numbers. These determine
            // which number to start and end the digit links with
            if(($this->cur_page - $this->num_links) < 0)
            {
                $start = 1;
                $end = 2 * $this->num_links;
            }
            else
            {
                if(($this->cur_page + $this->num_links) <= $max_page_num)
                {
                    $start = $this->cur_page - ($this->num_links - 1);
                    $end = $this->cur_page + $this->num_links;
                }
                else
                {
                    $start = $max_page_num - 2 * ($this->num_links - 1);
                    $end = $max_page_num;
                }                
            }            

            //Render the "start" link
            if($this->cur_page == $this->num_links + 2)
            {
                $output .= $this->num_tag_open.'<a href="'.$this->base_url.'0'.'">1</a>'.$this->num_tag_close;
            }
            if($this->cur_page > $this->num_links + 2)
            {
                $output .= $this->num_tag_open.'<a href="'.$this->base_url.'0'.'">1</a>'.$this->num_tag_close;
                $output .= $this->num_tag_open.'<a href="'.$this->base_url.$this->per_page.'">2</a>'.$this->num_tag_close;
                $output .= $this->break_tag;
            }

            // Write the digit links
            for ($loop = $start -1; $loop <= $end; $loop++)
            {
                $i = ($loop * $this->per_page) - $this->per_page;        
                        
                if ($i >= 0)
                {
                    if ($this->cur_page == $loop)
                    {
                        $output .= $this->cur_tag_open.$loop.$this->cur_tag_close; // Current page
                    }
                    else
                    {
                        $n = ($i == 0) ? 0 : $i;
                        $output .= $this->num_tag_open.'<a href="'.$this->base_url.$n.'">'.$loop.'</a>'.$this->num_tag_close;
                    }
                }
            }

            //Render the "start" link
            if($this->cur_page == $max_page_num - $this->num_links - 1)
            {
                $i = (($max_page_num * $this->per_page) - $this->per_page);
                $output .= $this->num_tag_open.'<a href="'.$this->base_url.$i.'">'.$max_page_num.'</a>'.$this->num_tag_close;
            }
            if($this->cur_page <= $max_page_num - $this->num_links - 2)
            {
                $output .= $this->break_tag;
                $i = ((($max_page_num-1) * $this->per_page) - $this->per_page);
                $output .= $this->num_tag_open.'<a href="'.$this->base_url.$i.'">'.($max_page_num-1).'</a>'.$this->num_tag_close;
                $i = (($max_page_num* $this->per_page) - $this->per_page);
                $output .= $this->num_tag_open.'<a href="'.$this->base_url.$i.'">'.$max_page_num.'</a>'.$this->num_tag_close;        
            }
        }

        // Render the "next" link
        if ($this->cur_page < $max_page_num)
        {
            $output .= $this->next_tag_open.'<a class="next" href="'.$this->base_url.($this->cur_page * $this->per_page).'">'.$this->next_link.'</a>'.$this->next_tag_close;
        }else{
            $output .= $this->next_tag_open.'<a class="next_over" href="javascript:void(0)"></a>'.$this->next_tag_close;  
        }

        // Kill double slashes.  Note: Sometimes we can end up with a double slash
        // in the penultimate link so we'll kill all double slashes.
        $output = preg_replace("#([^:])//+#", "\\1/", $output);

        // Add the wrapper HTML if exists
        $output = $this->full_tag_open.$output.$this->full_tag_close;

        $output = $pre_output.$output;

        return $output;
    }

    public function get_links($totalCount, $countPerPage, $max_page='')
    {
        $config['total_rows'] = $totalCount;
        $config['per_page'] = $countPerPage; 
        $config['max_page_num'] = $max_page; 
        $this->initialize($config);
        return $this->pageBar();
    }

    public function getPageBarLite($totalCount, $countPerPage, $max_page='')
    {
        $config['total_rows'] = $totalCount;
        $config['per_page'] = $countPerPage; 
        $config['max_page_num'] = $max_page; 
        $this->initialize($config);
        return $this->pageBarLite();
    }

    public function getAjaxPageBar($totalCount, $countPerPage, $div_id, $max_page='')
    {
        $config['total_rows'] = $totalCount;
        $config['per_page'] = $countPerPage; 
        $config['max_page_num'] = $max_page; 
        $this->initialize($config);
        $pageBar = $this->pageBar();
        $pageBar.= "
            <script>
            $('.paginator a', $('#{$div_id}')).each(function () {
                $(this).click(function () {
                    $.get($(this).attr('href'), function (response) {
                        $('#{$div_id}').html(response);
                    });
                    return false;
                });
            });
            </script>
        ";
        return $pageBar;
    }
}//End