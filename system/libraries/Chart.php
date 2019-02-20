<?php
/**
 * 图表
 *
 * @author      杨海波
 * @date        2015-09-18
 * @copyright   Copyright(c) 2015
 * @version     $Id$
 */
class CI_Chart {
    // Chart 根目录
    public $uri       = 'http://echarts.baidu.com/build/dist';

    // 图表容器名称
    public $container = 'chartContainer';

    // 图表容器高
    public $height    = '600px';

    // 图表容器宽
    public $width     = '';

    // 错误信息
    protected $_error = null;

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
     * 配置
     *
     * @param   string  $params     请求参数
     *
     * @return  void
     */
    public function config($params = array())
    {
        if (!empty($params) && is_array($params)) {
            foreach ($params as $key => $val) {
                $this->$key = $val;
            }
        }
    }

    /**
     * 图表头部
     *
     * @param   string  $url        请求地址
     * @param   string  $params     请求参数
     * @param   string  $method     请求方式
     * @param   string  $timeout    超时时间
     *
     * @return  string/false
     */
    private function __header()
    {
        $height = empty($this->height) ? '' : 'height:' . $this->height . ';';
        $width  = empty($this->width)  ? '' : 'width:'  . $this->width  . ';';

        $output = <<<EOT
            <!-- ECharts 容器 -->
            <div id="{$this->container}" style="{$height}{$width}"></div>

            <!-- ECharts 单文件引入 -->
            <script src="{$this->uri}/echarts.js"></script>

            <script>
                // 路径配置
                require.config({
                    paths: {
                        echarts: '{$this->uri}'
                    }
                });
            </script>
EOT;
        return $output;
    }

    /**
     * 折线图
     *
     * @param   string  $url        请求地址
     * @param   string  $params     请求参数
     * @param   string  $method     请求方式
     * @param   string  $timeout    超时时间
     *
     * @return  string/false
     */
    public function line($params = array())
    {
        // $height = empty($this->height) ? '' : 'height:' . $this->height . ';';
        // $width  = empty($this->width)  ? '' : 'width:'  . $this->width  . ';';
        $output = '';

        if (empty($params) || !is_array($params)) {
            return $output;
        }

        if (empty($params['datas']) || !is_array($params['datas'])) {
            return $output;
        }

        $legend = [];
        $xaxis  = json_encode($params['xaxis']['data']);
        $series = [];
        foreach ($params['datas'] as $v) {
            $legend[] = $v['legend'];
            $data     = json_encode($v['data']);
            $series[] = <<<EOT
                {
                    name:'{$v['legend']}',
                    type:'line',
                    data:{$data},
                    markPoint : {
                        data : [
                            {type : 'max', name: '最大值'},
                            {type : 'min', name: '最小值'}
                        ]
                    },
                    markLine : {
                        data : [
                            {type : 'average', name: '平均值'}
                        ]
                    }
                }
EOT;
        }
        $legend = json_encode($legend);
        $series = implode(', ', $series);

        $output  = $this->__header();

        $output .= <<<EOT
            <script>
                // 使用
                require(['echarts', 'echarts/chart/line', 'echarts/chart/bar'], function (ec) {
                    var myChart = ec.init(document.getElementById('{$this->container}'));

                    var option = {
                        title : {
                            text: '{$params['title']}',
                            subtext: ''
                        },
                        tooltip : {
                            trigger: 'axis'
                        },
                        legend: {
                            data:$legend
                        },
                        toolbox: {
                            show : true,
                            feature : {
                                mark : {show: true},
                                dataView : {show: true, readOnly: false},
                                magicType : {show: true, type: ['line', 'bar']},
                                restore : {show: true},
                                saveAsImage : {show: true}
                            }
                        },
                        calculable : true,
                        xAxis : [
                            {
                                type : 'category',
                                boundaryGap : false,
                                data : {$xaxis}
                            }
                        ],
                        yAxis : [
                            {
                                type : 'value',
                                axisLabel : {
                                    formatter: '{value} {$params['yaxis']['formatter']}'
                                }
                            }
                        ],
                        series : [
                            {$series}
                        ]
                    };

                    myChart.setOption(option);
                });
            </script>
EOT;

        return $output;
    }
}