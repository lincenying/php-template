<?php

/**

 * example:

 * 模式四种分页模式：

require_once('../libs/classes/page.class.php');

$page=new page(array('total'=>1000,'per_page'=>20));

echo 'mode:1<br>'.$page->show();

echo '<hr>mode:2<br>'.$page->show(2);

echo '<hr>mode:3<br>'.$page->show(3);

echo '<hr>mode:4<br>'.$page->show(4);

开启AJAX：

$ajaxpage=new page(array('total'=>1000,'per_page'=>20,'ajax'=>'ajax_page','page_name'=>'test'));

echo 'mode:1<br>'.$ajaxpage->show();


 */

class page
{
    /**

     * config ,public

     */

    public $rewrite = true;
    public $page_name = 'page'; //page标签，用来控制url页。比如说xxx.php?PB_page=2中的PB_page
    public $next_page = '>'; //下一页
    public $pre_page = '<'; //上一页
    public $first_page = 'First'; //首页
    public $last_page = 'Last'; //尾页
    public $pre_bar = '<<'; //上一分页条
    public $next_bar = '>>'; //下一分页条
    public $format_left = '<li>';
    public $format_right = '</li>';
    public $is_ajax = false; //是否支持AJAX分页模式
    /**
     * private
     *
     */
    public $page_bar_num = 10; //控制记录条的个数。
    public $total_page = 0; //总页数
    public $ajax_action_name = ''; //AJAX动作名
    public $now_index = 1; //当前页
    public $url = ''; //url地址头
    public $offset = 0;
    public $pattern = [
        '/index\.php\?page=([\d]+)(\'|\")/is',
    ];
    public $replace = [
        'page/\\1\\2',
    ];
    /**
     * constructor构造函数
     *
     * @param array $array['total'],$array['per_page'],$array['now_index'],$array['url'],$array['ajax']...
     */

    public function __construct($array)
    {
        if (is_array($array)) {
            if (!array_key_exists('total', $array)) {
                $this->error(__FUNCTION__, 'need a param of total');
            }
            $total = intval($array['total']);
            $per_page = array_key_exists('per_page', $array) ? intval($array['per_page']) : 10;
            $now_index = array_key_exists('now_index', $array) ? intval($array['now_index']) : '';
            $url = array_key_exists('url', $array) ? $array['url'] : '';
            $rewrite = array_key_exists('rewrite', $array) ? $array['rewrite'] : true;
        } else {
            $total = $array;
            $per_page = 10;
            $now_index = '';
            $url = '';
            $rewrite = true;
        }
        if (!is_int($total) || $total < 0) {
            $this->error(__FUNCTION__, $total . ' is not a positive integer!');
        }
        if (!is_int($per_page) || $per_page <= 0) {
            $this->error(__FUNCTION__, $per_page . ' is not a positive integer!');
        }
        if (!empty($array['page_name'])) {
            $this->set('page_name', $array['page_name']);
        }
        //设置pageName
        if ($now_index > 999) {
            $this->page_bar_num = 7;
        }
        $this->total = $total;
        $this->total_page = ceil($total / $per_page);
        $this->set_now_index($now_index); //设置当前页
        $this->set_url($url); //设置链接地址
        $this->offset = ($this->now_index - 1) * $per_page;
        $this->rewrite = $rewrite;
        if (!empty($array['ajax'])) {
            $this->open_ajax($array['ajax']);
        }
        //打开AJAX模式
    }

    /**
     * 设定类中指定变量名的值，如果改变量不属于这个类，将throw一个exception
     *
     * @param string $var
     * @param string $value
     */

    public function set($var, $value)
    {
        if (in_array($var, get_object_vars($this))) {
            $this->$var = $value;
        } else {
            $this->error(__FUNCTION__, $var . ' does not belong to Page!');
        }
    }

    /**
     * 打开倒AJAX模式
     *
     * @param string $action 默认ajax触发的动作。
     */
    public function open_ajax($action)
    {
        $this->is_ajax = true;
        $this->ajax_action_name = $action;
    }

    public function page_bar($style = 'page')
    {
        return '<li class="text">共' . $this->total . '条记录,' . $this->now_index . '/' . $this->total_page . '页 </li>';
    }

    /**
     * 获取显示"下一页"的代码
     *
     * @param string $style
     * @return string
     */

    public function next_page($style = '')
    {
        if ($this->now_index < $this->total_page) {
            return '<li class="next">' . $this->_get_link($this->_get_url($this->now_index + 1), $this->next_page, $style) . '</li>';
        }
        return '<li class="next"><a href="javascript:;">' . $this->next_page . '</a></li>';
    }

    /**
     * 获取显示“上一页”的代码
     *
     * @param string $style
     * @return string
     */

    public function pre_page($style = '')
    {
        if ($this->now_index > 1) {
            return '<li class="prev">' . $this->_get_link($this->_get_url($this->now_index - 1), $this->pre_page, $style) . '</li>';
        }
        return '<li class="prev"><a href="javascript:;">' . $this->pre_page . '</a></li>';
    }

    /**
     * 获取显示“首页”的代码
     *
     * @return string
     */

    public function first_page($style = '')
    {
        if ($this->now_index == 1) {
            return '<li class="prev"><a href="javascript:;">' . $this->first_page . '</a></li>';
        }
        return '<li class="prev">' . $this->_get_link($this->_get_url(1), $this->first_page, $style) . '</li>';
    }

    /**
     * 获取显示“尾页”的代码
     *
     * @return string
     */

    public function last_page($style = '')
    {
        if ($this->now_index == $this->total_page) {
            return '<li class="next"><a href="javascript:;">' . $this->last_page . '</a></li>';
        }
        return '<li class="next">' . $this->_get_link($this->_get_url($this->total_page), $this->last_page, $style) . '</li>';
    }

    public function nowbar($style = '', $now_index_style = 'active')
    {
        $plus = ceil($this->page_bar_num / 2);
        if ($this->page_bar_num - $plus + $this->now_index > $this->total_page) {
            $plus = $this->page_bar_num - $this->total_page + $this->now_index;
        }
        $begin = $this->now_index - $plus + 1;
        $begin = $begin >= 1 ? $begin : 1;
        $return = '';
        for ($i = $begin; $i < $begin + $this->page_bar_num; $i++) {
            if ($i <= $this->total_page) {
                if ($i != $this->now_index) {
                    $return .= $this->_get_text($this->_get_link($this->_get_url($i), $i, $style));
                } else {
                    $return .= $this->_get_text($this->_get_link('javascript:;', $i, $now_index_style));
                }
            } else {
                break;
            }
            $return .= '';
        }
        unset($begin);
        return $return;
    }

    /**
     * 获取显示跳转按钮的代码
     *
     * @return string
     */

    public function select()
    {
        $return = '<select name="PB_Page_Select" onchange=window.location="' . $this->url . '"+this.value;>';
        for ($i = 1; $i <= $this->total_page; $i++) {
            if ($i == $this->now_index) {
                $return .= '<option value="' . $i . '" selected>' . $i . '</option>';
            } else {
                $return .= '<option value="' . $i . '">' . $i . '</option>';
            }
        }
        unset($i);
        $return .= '</select>';
        return $return;
    }

    /**
     * 获取mysql 语句中limit需要的值
     *
     * @return string
     */

    public function offset()
    {
        return $this->offset;
    }

    /**
     * 控制分页显示风格（你可以增加相应的风格）
     *
     * @param int $mode
     * @return string
     */

    public function show($mode = 1)
    {
        switch ($mode) {
            case '1':
                $this->first_page = '<span class="icon-fast-backward"></span>';
                $this->pre_page = '<span class="icon-triangle"></span>';
                $this->next_page = '<span class="icon-triangle-4"></span>';
                $this->last_page = '<span class="icon-fast-forward"></span>';
                $return =
                $this->page_bar('page') .
                $this->first_page('') .
                $this->pre_page('') .
                $this->nowbar('', 'active') .
                $this->next_page('') .
                $this->last_page('');
                break;
            case '2':
                $this->first_page = '<span class="icon-fast-backward"></span>';
                $this->pre_page = '<span class="icon-triangle"></span>';
                $this->next_page = '<span class="icon-triangle-4"></span>';
                $this->last_page = '<span class="icon-fast-forward"></span>';
                $return = $this->first_page('') . $this->pre_page('') . $this->nowbar('', 'active') . $this->next_page('') . $this->last_page('');
                break;
            case '3':
                $this->next_page = '下一页';
                $this->pre_page = '上一页';
                $this->first_page = '首页';
                $this->last_page = '尾页';
                $return = $this->first_page() . $this->pre_page() . $this->next_page() . $this->last_page();
                break;
            case '4':
                $this->next_page = '下一页';
                $this->pre_page = '上一页';
                $return = $this->pre_page() . $this->nowbar() . $this->next_page();
                break;
        }
        if ($this->rewrite) {
            $return = preg_replace($this->pattern, $this->replace, $return);
            $return = str_replace('"/page/1"', '"/"', $return);
            $return = str_replace('/page/1"', '"', $return);
        }
        return $return;
    }

    /*----------------private function (私有方法)-----------------------------------------------------------*/

    /**
     * 设置url头地址
     * @param: String $url
     * @return boolean
     */
    public function set_url($url = '')
    {
        if (!empty($url)) {
            //手动设置
            $last = $url[strlen($url) - 1];
            if (stristr($url, '?') == '?') {
                $this->url = $url . ($last == '?' ? '' : '&') . $this->page_name . '=';
            } else {
                $this->url = $url . ($last == '&' ? '' : '&') . $this->page_name . '=';
            }
        } else {
            //自动获取
            $qs = $_SERVER['QUERY_STRING'];
            $qs = $this->replacePjax($qs);
            if (empty($qs)) {
                //不存在QUERY_STRING时
                $this->url = $_SERVER['PHP_SELF'] . '?' . $this->page_name . '=';
            } else {
                if (stristr($qs, $this->page_name . '=')) {
                    //地址存在页面参数
                    $this->url = $_SERVER['PHP_SELF'] . '?' . str_replace($this->page_name . '=' . $this->now_index, '', $this->parseurl($qs));
                    $last = $this->url[strlen($this->url) - 1];
                    if ($last == '?' || $last == '&') {
                        $this->url .= $this->page_name . '=';
                    } else {
                        $this->url .= '&' . $this->page_name . '=';
                    }
                } else {
                    $this->url = $_SERVER['PHP_SELF'] . '?' . $this->parseurl($qs) . '&' . $this->page_name . '=';
                } //end if
            } //end if
        } //end if
    }

    /**
     * 设置当前页面
     *
     */

    public function set_now_index($now_index)
    {
        if (empty($now_index)) {
            //系统获取
            if (isset($_GET[$this->page_name])) {
                if ($_GET[$this->page_name] > $this->total_page) {
                    $nowpage = $this->total_page;
                } else {
                    $nowpage = $_GET[$this->page_name];
                }
                if (empty($nowpage)) {
                    $nowpage = 1;
                }
                $this->now_index = intval($nowpage);
            }
        } else {
            //手动设置
            $this->now_index = intval($now_index);
        }
    }

    /**
     * 为指定的页面返回地址值
     *
     * @param int $pageno
     * @return string $url
     */

    public function _get_url($pageno = 1)
    {
        return $this->url . $pageno;
    }

    /**
     * 获取分页显示文字，比如说默认情况下_get_text('<a href="">1</a>')将返回[<a href="">1</a>]
     *
     * @param String $str
     * @return string $url
     */

    public function _get_text($str)
    {
        return $this->format_left . $str . $this->format_right;
    }

    /**
     * 获取链接地址
     */
    public function _get_link($url, $text, $style = '')
    {
        $style = empty($style) ? '' : 'class="' . $style . '"';
        switch ($text) {
            case '&lt;':
                $alt = '上一页';
                break;
            case '&gt;':
                $alt = '下一页';
                break;
            case '&lt;&lt;':
                $alt = '首页';
                break;
            case '&gt;&gt;':
                $alt = '末页';
                break;
            default:
                $alt = '第' . $text . '页';
                break;
        }
        if ($this->is_ajax) {
            //如果是使用AJAX模式
            return '<a ' . $style . ' href="' . $url . '" onclick="javascript:' . $this->ajax_action_name . '(\'' . $url . '\');return false;" title="">' . $text . '</a>';
        } else {
            return '<a ' . $style . ' href="' . $url . '" title="">' . $text . '</a>';
        }
    }

    /**
     * 出错处理方式
     */

    public function error($function, $errormsg)
    {
        die('Error in file <b>' . __FILE__ . '</b> ,Function <b>' . $function . '()</b> :' . $errormsg);
    }

    public function replacePjax($url)
    {
        $return = [];
        $arr_url = explode('&', $url);
        if (count($arr_url) > 0) {
            foreach ($arr_url as $key => $value) {
                $arr_val = explode('=', $value);
                if ($arr_val[0] != '_pjax') {
                    $return[] = $value;
                }
            }
            return implode('&', $return);
        } else {
            return '';
        }
    }

    public function parseurl($url = '')
    {
        // $tmpurl =  urlencode($url);
        // $pattern = array("%3F", "%3D", "%26", "%25","%3A", "%2F", "%40");
        // $replace = array("?", "=", "&","%",":", "/", "@");
        // $tmpurl = str_replace($pattern, $replace, $tmpurl);
        return $url;
    }
}
