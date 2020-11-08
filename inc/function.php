<?php
if (PHP_VERSION < '4.1.0') {
    $_GET = &$HTTP_GET_VARS;
    $_POST = &$HTTP_POST_VARS;
    $_COOKIE = &$HTTP_COOKIE_VARS;
    $_SERVER = &$HTTP_SERVER_VARS;
    $_ENV = &$HTTP_ENV_VARS;
    $_FILES = &$HTTP_POST_FILES;
}
if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
    exit('Access Error');
}
foreach (['_COOKIE', '_GET', '_POST'] as $_request) {
    foreach ($$_request as $_key => $_value) {
        $_key[0] != '_' && ($$_key = $_value);
    }
}

// php7
if (!function_exists('ereg')) {
    function ereg($pattern, $subject, &$matches = [])
    {
        return preg_match('/' . $pattern . '/', $subject, $matches);
    }
}

function isEmpty($str)
{
    return !isset($str) ? true : ($str === 0 || $str === '0' || !empty($str) ? false : true);
}

function formatStr($str)
{
    if (MAGIC_QUOTES_GPC == 1) {
        return stripslashes($str);
    } else {
        return $str;
    }
}
function isPjax()
{
    return array_key_exists('HTTP_X_PJAX', $_SERVER) && $_SERVER['HTTP_X_PJAX'];
}

//strlen($str) ==> 字符串长度
/*
 **随机产生字符串**
 */

function random($length, $type)
{
    switch ($type) {
        case 'A':
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case 'a':
            $chars = 'abcdefghijklmnopqrstuvwxyz';
            break;
        case '1':
            $chars = '0123456789';
            break;
        case 'Aa':
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            break;
        case 'Aa1':
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
            break;
    }
    $max = strlen($chars) - 1;
    mt_srand((float) microtime() * 1000000);
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

/*
 **截取字符串**
 **$string ==> 需截取的字符串
 **$length ==> 截取的长度
 **$sss ==> 是否加省略号
 */
function wordsCut($string, $length, $sss = 0)
{
    if (strlen($string) > $length) {
        if ($sss) {
            $length = $length - 3;
            $addstr = '...';
        }
        for ($i = 0; $i < $length; $i++) {
            if (ord($string[$i]) > 127) {
                $wordscut .= $string[$i] . $string[$i + 1];
                $i++;
            } else {
                $wordscut .= $string[$i];
            }
        }
        return $wordscut . $addstr;
    }
    return $string;
}
function strCut($str, $start, $len)
{
    if ($start < 0) {
        $start = strlen($str) + $start;
    }

    $retstart = $start + getOfFirstIndex($str, $start);
    $retend = $start + $len - 1 + getOfFirstIndex($str, $start + $len);
    return substr($str, $retstart, $retend - $retstart + 1);
}
//判断字符开始的位置
function getOfFirstIndex($str, $start)
{
    $char_aci = ord(substr($str, $start - 1, 1));
    if (223 < $char_aci && $char_aci < 240) {
        return -1;
    }

    $char_aci = ord(substr($str, $start - 2, 1));
    if (223 < $char_aci && $char_aci < 240) {
        return -2;
    }

    return 0;
}

/*
 **判断邮箱地址**
 */
function checkEmail($inAddress)
{
    return ereg('^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+', $inAddress);
}

/*
 **获取客户端IP地址**
 */
function getIP()
{
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = '0.0.0.0';
    }

    if (!preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $ip)) {
        $ip = '0.0.0.0';
    }
    return $ip;
}

/*
 **获取系统相关信息**
 */

function agent()
{
    $visitor = [];
    $visitor['agent'] = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($visitor['agent'], 'Netscape') !== false) {
        $visitor['browser'] = 'Netscape';
    } elseif (strpos($visitor['agent'], 'Lynx') !== false) {
        $visitor['browser'] = 'Lynx';
    } elseif (strpos($visitor['agent'], 'Opera') !== false) {
        $visitor['browser'] = 'Opera';
    } elseif (strpos($visitor['agent'], 'Konqueror') !== false) {
        $visitor['browser'] = 'Konqueror';
    } elseif (strpos($visitor['agent'], 'MSIE') !== false) {
        $visitor['browser'] = 'MSIE';
    } elseif (strpos($visitor['agent'], 'Firefox') !== false) {
        $visitor['browser'] = 'Firefox';
    } elseif (strpos($visitor['agent'], 'Safari') !== false) {
        $visitor['browser'] = 'Safari';
    } elseif (empty($visitor['agent'])) {
        $visitor['browser'] = 'Null';
    } else {
        $visitor['browser'] = 'Other';
    }

    if (strpos($visitor['agent'], 'Win') !== false) {
        $visitor['os'] = 'Windows';
    } elseif (strpos($visitor['agent'], 'Mac') !== false) {
        $visitor['os'] = 'Mac';
    } elseif (strpos($visitor['agent'], 'Linux') !== false) {
        $visitor['os'] = 'Linux';
    } elseif (strpos($visitor['agent'], 'FreeBSD') !== false) {
        $visitor['os'] = 'FreeBSD';
    } elseif (strpos($visitor['agent'], 'SunOS') !== false) {
        $visitor['os'] = 'SunOS';
    } elseif (strpos($visitor['agent'], 'OS/2') !== false) {
        $visitor['os'] = 'OS/2';
    } elseif (strpos($visitor['agent'], 'AIX') !== false) {
        $visitor['os'] = 'AIX';
    } elseif (preg_match('/(Bot|Crawl|Spider)/i', $visitor['agent'])) {
        $visitor['os'] = 'Spiders';
    } elseif (empty($visitor['agent'])) {
        $visitor['os'] = 'Null';
    } else {
        $visitor['os'] = 'Other';
    }
    return $visitor;
}

function checkSpider()
{
    $useragent = agent();
    if ($useragent['browser'] == 'Null' && $useragent['os'] == 'Null') {
        $return = 'null';
    } elseif ($useragent['os'] == 'Spiders') {
        $return = 'Spiders';
    } else {
        // $return = $useragent['os']." ".$useragent['browser'];
        $return = 'ok';
    }
    return $return;
}

/*
 **日期格式化函数**
 */

function getFixDate($str, $date = '')
{
    $fixtime = 86400;
    $tmpdate = $date == '' ? date($str, time() + $fixtime) : date($str, strtotime($date));
    return $tmpdate;
}

//判断是否为图片
function checkFileType($str)
{
    $tmpfiletype = false;
    $filetypes = 'jpg,gif,png,jpeg';
    $filetype_ = explode(',', $filetypes);
    for ($i = 0; $i < count($filetype_); $i++) {
        if (strtolower($str) == $filetype_[$i]) {
            $tmpfiletype = true;
            break;
        }
    }
    return $tmpfiletype;
}

//字符串编码
function htmlEncode($fString)
{
    $val = preg_replace(['/\</i', '/\>/i'], ['&lt;', '&gt;'], $fString);
    //$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {
        $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
        $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
    }

    $ra1 = [
        'javascript',
        'vbscript',
        'expression',
        'applet',
        'meta',
        'xml',
        'blink',
        'style',
        'script',
        'embed',
        'object',
        'iframe',
        'frame',
        'frameset',
        'ilayer',
        'bgsound',
        'title',
        'base',
    ];
    $ra2 = [
        'onabort',
        'onactivate',
        'onafterprint',
        'onafterupdate',
        'onbeforeactivate',
        'onbeforecopy',
        'onbeforecut',
        'onbeforedeactivate',
        'onbeforeeditfocus',
        'onbeforepaste',
        'onbeforeprint',
        'onbeforeunload',
        'onbeforeupdate',
        'onblur',
        'onbounce',
        'oncellchange',
        'onchange',
        'onclick',
        'oncontextmenu',
        'oncontrolselect',
        'oncopy',
        'oncut',
        'ondataavailable',
        'ondatasetchanged',
        'ondatasetcomplete',
        'ondblclick',
        'ondeactivate',
        'ondrag',
        'ondragend',
        'ondragenter',
        'ondragleave',
        'ondragover',
        'ondragstart',
        'ondrop',
        'onerror',
        'onerrorupdate',
        'onfilterchange',
        'onfinish',
        'onfocus',
        'onfocusin',
        'onfocusout',
        'onhelp',
        'onkeydown',
        'onkeypress',
        'onkeyup',
        'onlayoutcomplete',
        'onload',
        'onlosecapture',
        'onmousedown',
        'onmouseenter',
        'onmouseleave',
        'onmousemove',
        'onmouseout',
        'onmouseover',
        'onmouseup',
        'onmousewheel',
        'onmove',
        'onmoveend',
        'onmovestart',
        'onpaste',
        'onpropertychange',
        'onreadystatechange',
        'onreset',
        'onresize',
        'onresizeend',
        'onresizestart',
        'onrowenter',
        'onrowexit',
        'onrowsdelete',
        'onrowsinserted',
        'onscroll',
        'onselect',
        'onselectionchange',
        'onselectstart',
        'onstart',
        'onstop',
        'onsubmit',
        'onunload',
    ];
    $ra = array_merge($ra1, $ra2);

    $found = true;

    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(&#0{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2) . '_' . substr($ra[$i], 2);

            $val = preg_replace($pattern, $replacement, $val);

            if ($val_before == $val) {
                $found = false;
            }
        }
    }
    return $val;
}

//屏蔽部分非法字符
function delBlock($str)
{
    $str = str_replace("'", '', $str);
    $str = str_replace('"', '', $str);
    return $str;
}

//字符串反编码
function encodeHtml($fString)
{
    if ($fString != '') {
        $fString = str_replace('&lt;br /&gt;', chr(10) & chr(10), $fString);
        $fString = str_replace('&gt;', '>', $fString);
        $fString = str_replace('&lt;', '<', $fString);
        $fString = str_replace('&nbsp;', chr(32), $fString);
        $fString = str_replace('', chr(13), $fString);
        $fString = str_replace('<br />', chr(10) & chr(10), $fString);
        $fString = str_replace('<BR />', chr(10), $fString);
    }
    return $fString;
}

//模拟ASP中dateadd函数
function dateAdd($unit = 'd', $int, $date)
{
    $date_time_array = getdate(strtotime($date));
    $hours = $date_time_array['hours'];
    $minutes = $date_time_array['minutes'];
    $seconds = $date_time_array['seconds'];
    $month = $date_time_array['mon'];
    $day = $date_time_array['mday'];
    $year = $date_time_array['year'];
    switch ($unit) {
        case 'yyyy':
            $year += $int;
            break;
        case 'q':
            $month += $int * 3;
            break;
        case 'm':
            $month += $int;
            break;
        case 'y':
            $day += $int;
            break;
        case 'd':
            $day += $int;
            break;
        case 'w':
            $day += $int;
            break;
        case 'ww':
            $day += $int * 7;
            break;
        case 'h':
            $hours += $int;
            break;
        case 'n':
            $minutes += $int;
            break;
        case 's':
            $seconds += $int;
            break;
    }
    $timestamp = mktime($hours, $minutes, $seconds, $month, $day, $year);
    return date('Y-m-d H:i:s', $timestamp);
}

//模拟ASP中datediff函数
function dateDiff($unit = '', $date1, $date2)
{
    switch ($unit) {
        case 's':
            $div = 1;
            break;
        case 'i':
            $div = 60;
            break;
        case 'h':
            $div = 3600;
            break;
        case 'd':
            $div = 86400;
            break;
        case 'm':
            $div = 2592000;
            break;
        case 'y':
            $div = 946080000;
            break;
        default:
            $div = 86400;
    }
    $time1 = strtotime($date1);
    $time2 = strtotime($date2);
    if ($time1 && $time2) {
        return bcdiv($time2 - $time1, $div, 1);
    }

    return false;
}

//清除HTML标签
function killHtml($str)
{
    $str = preg_replace('/<\/?([^>]*)>/is', '', $str);
    return $str;
}

//提取内容中的第一张图片地址
function getImgFromContent($str)
{
    if (preg_match_all('/<img.*?src=(.+?)(\s|>)/is', $str, $match)) {
        $match[1] = str_replace(["'", '"'], '', $match[1]);
        //for ($i=0;$i<count($match[1]);$i++) {
        return $match[1][0];
        //}
    }
}

//获取文件后缀名
function getExtName($name)
{
    if (strrpos($name, '.') == false) {
        return '';
    } else {
        return strtolower(substr($name, strrpos($name, '.') + 1, strlen($name) - strrpos($name, '.')));
    }
}

function isInt($str)
{
    return ereg('^[0-9]{0,}$', $str);
}

//获取月份的天数
function getMonthday($month, $year)
{
    switch ($month) {
        case 2:
            if (($year % 4 === 0 and $year % 100 != 0) || $year % 400 == 0) {
                $days = 29;
            } else {
                $days = 28;
            }
            break;
        case 4:
        case 6:
        case 9:
        case 11:
            $days = 30;
            break;
        default:
            $days = 31;
    }
    return $days;
}

//隐藏Ip最后一段
function hiddenIp($ip)
{
    global $userisadmin;
    if (!empty($userisadmin) && $userisadmin > 0) {
        return $ip;
    } else {
        if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip)) {
            $arr_ip = explode('.', $ip);
            $newip = implode('.', [$arr_ip[0], $arr_ip[1], $arr_ip[2], '*']);
            return $newip;
        }
        return '';
    }
}

//字符串最后一位存在某字符时,则去除
function strEnd($str, $sign)
{
    $str = trim($str);
    $len = strlen($str);
    $signend = substr($str, -1, 1);
    if ($signend == $sign) {
        return substr($str, 0, $len - 1);
    } else {
        return $str;
    }
}

function _microtime()
{
    $time = explode(' ', microtime());
    $time = $time[1] . $time[0] * 1000;
    $time2 = explode('.', $time);
    $time = $time2[0];
    return $time;
}

//MD5加密
function newMd5($str, $type = 'encode')
{
    $val = 'Q,W,E,R,T,Y,U,I,O,P,A,S,D,F,G,H,J,K,L,Z,X,C,V,B,N,M,1,2,3,4,5,6,7,8,9,0';
    $key = '0,1,2,3,4,5,6,7,8,9,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z';
    $arr_val = explode(',', $val);
    $arr_key = explode(',', $key);
    $arr_hash = [];
    if ($type == 'encode') {
        foreach ($arr_val as $k => $v) {
            $arr_hash[$arr_key[$k]] = $v;
        }
        $str = md5($str);
        $firstchar = $str[0];
        $hashfirstchar = strtolower($arr_hash[$firstchar]);
    } else {
        foreach ($arr_key as $k => $v) {
            $arr_hash[$arr_val[$k]] = $v;
        }
        $firstchar = strtoupper($str[0]);
        $hashfirstchar = $arr_hash[$firstchar];
    }
    $hashchar = substr($str, 1);
    $return = $hashfirstchar . $hashchar;
    return $return;
}

function getMicrotime()
{
    [$usec, $sec] = explode(' ', microtime());
    return (float) $usec + (float) $sec;
}

function timeTran($the_time)
{
    $now_time = date('Y-m-d H:i:s');
    $now_time = strtotime($now_time);
    $show_time = strtotime($the_time);
    $dur = $now_time - $show_time;
    if ($dur < 0) {
        return $the_time;
    } elseif ($dur < 60) {
        return $dur . '秒前';
    } elseif ($dur < 3600) {
        return floor($dur / 60) . '分钟前';
    } elseif ($dur < 86400) {
        return floor($dur / 3600) . '小时前';
    } elseif ($dur < 259200) {
        return floor($dur / 86400) . '天前';
    } else {
        return $the_time;
    }
}

function randImg()
{
    $num = rand(1, 50);
    $num = empty($num) ? 1 : $num;
    $img = [
        '',
        'http://ww4.sinaimg.cn/bmiddle/005uQRNCgw1eh959gpia5j304g04gglq.jpg',
        'http://ww3.sinaimg.cn/bmiddle/005uQRNCgw1eh959gto4pj304g04gglr.jpg',
        'http://ww1.sinaimg.cn/bmiddle/005uQRNCgw1eh959gxof4j304g04g74g.jpg',
        'http://ww1.sinaimg.cn/bmiddle/005uQRNCgw1eh959h6urvj304g04gt8r.jpg',
        'http://ww4.sinaimg.cn/bmiddle/005uQRNCgw1eh959hide5j304g04gt8r.jpg',
        'http://ww3.sinaimg.cn/bmiddle/005uQRNCgw1eh959hcu4bj304g04gwej.jpg',
        'http://ww2.sinaimg.cn/bmiddle/005uQRNCgw1eh959hibp5j304g04gaa7.jpg',
        'http://ww4.sinaimg.cn/bmiddle/005uQRNCgw1eh959hs6e2j304g04gmxb.jpg',
        'http://ww2.sinaimg.cn/bmiddle/005uQRNCgw1eh959hrd9mj304g04gaa4.jpg',
        'http://ww1.sinaimg.cn/bmiddle/005uQRNCgw1eh959u6gw5j304g04gdg1.jpg',
        'http://ww1.sinaimg.cn/bmiddle/005uQRNCgw1eh969i05pvj304g04g0sv.jpg',
        'http://ww3.sinaimg.cn/bmiddle/005uQRNCgw1eh969hxjgzj304g04gaa9.jpg',
        'http://ww3.sinaimg.cn/bmiddle/005uQRNCgw1eh969i23lvj304g04gwen.jpg',
        'http://ww2.sinaimg.cn/bmiddle/005uQRNCgw1eh969i85uqj304g04gglr.jpg',
        'http://ww4.sinaimg.cn/bmiddle/005uQRNCgw1eh969icy39j304g04gt8u.jpg',
        'http://ww3.sinaimg.cn/bmiddle/005uQRNCgw1eh969ijpohj304g04gaa8.jpg',
        'http://ww3.sinaimg.cn/bmiddle/005uQRNCgw1eh969iovjrj304g04gq2x.jpg',
        'http://ww1.sinaimg.cn/bmiddle/005uQRNCgw1eh969iv7xxj304g04g0sy.jpg',
        'http://ww1.sinaimg.cn/bmiddle/005uQRNCgw1eh969j13zjj304g04gwer.jpg',
        'http://ww1.sinaimg.cn/bmiddle/005uQRNCgw1eh969syd9zj304g04gq31.jpg',
        'http://ww4.sinaimg.cn/bmiddle/005uQRNCgw1eh969t4cpej304g04gt8w.jpg',
        'http://ww4.sinaimg.cn/bmiddle/005uQRNCgw1eh969tb4d9j304g04gdg0.jpg',
        'http://ww3.sinaimg.cn/bmiddle/005uQRNCgw1eh969to07rj304g04g0sx.jpg',
        'http://ww2.sinaimg.cn/bmiddle/005uQRNCgw1eh969tnhc9j304g04gglr.jpg',
        'http://ww1.sinaimg.cn/bmiddle/005uQRNCgw1eh969ts8loj304g04g74j.jpg',
        'http://ww1.sinaimg.cn/bmiddle/005uQRNCgw1eh969ty8bqj304g04gjrj.jpg',
        'http://ww4.sinaimg.cn/bmiddle/005uQRNCgw1eh969u3vddj304g04g3yn.jpg',
        'http://ww4.sinaimg.cn/bmiddle/005uQRNCgw1eh969umsgkj304g04gt8w.jpg',
        'http://ww1.sinaimg.cn/bmiddle/005uQRNCgw1eh96a2mhdlj304g04g74f.jpg',
        'http://ww4.sinaimg.cn/bmiddle/005uQRNCgw1eh96a2ryn7j304g04gq35.jpg',
        'http://ww3.sinaimg.cn/bmiddle/005uQRNCgw1eh96a32i0zj304g04g3yk.jpg',
        'http://ww3.sinaimg.cn/bmiddle/005uQRNCgw1eh96a3a56jj304g04gt8w.jpg',
        'http://ww4.sinaimg.cn/bmiddle/005uQRNCgw1eh96a3601hj304g04gaa8.jpg',
        'http://ww3.sinaimg.cn/bmiddle/005uQRNCgw1eh96a3awsej304g04gmxb.jpg',
        'http://ww2.sinaimg.cn/bmiddle/005uQRNCgw1eh96a3oeh5j304g04g3yo.jpg',
        'http://ww3.sinaimg.cn/bmiddle/005uQRNCgw1eh96a3sjzkj304g04gdg1.jpg',
        'http://ww1.sinaimg.cn/bmiddle/005uQRNCgw1eh96a3ru48j304g04gaa9.jpg',
        'http://ww3.sinaimg.cn/bmiddle/005uQRNCgw1eh96abqlg5j304g04gq36.jpg',
        'http://ww4.sinaimg.cn/bmiddle/005uQRNCgw1eh96ac4ujfj304g04gweo.jpg',
        'http://ww2.sinaimg.cn/bmiddle/005uQRNCgw1eh96ac1xxlj304g04gwen.jpg',
        'http://ww3.sinaimg.cn/bmiddle/005uQRNCgw1eh96ac74j2j304g04gq34.jpg',
        'http://ww4.sinaimg.cn/bmiddle/005uQRNCgw1eh96acd7tij304g04gglv.jpg',
        'http://ww3.sinaimg.cn/bmiddle/005uQRNCgw1eh96acw11rj304g04g3ys.jpg',
        'http://ww3.sinaimg.cn/bmiddle/005uQRNCgw1eh96aco07rj304g04gwek.jpg',
        'http://ww3.sinaimg.cn/bmiddle/005uQRNCgw1eh96acsbbpj304g04gq31.jpg',
        'http://ww2.sinaimg.cn/bmiddle/005uQRNCgw1eh96acvbkzj304g04g74c.jpg',
        'http://ww4.sinaimg.cn/bmiddle/005uQRNCgw1eh96aitmnoj304g04gq2y.jpg',
        'http://ww1.sinaimg.cn/bmiddle/005uQRNCgw1eh96aiz35kj304g04g3yq.jpg',
        'http://ww2.sinaimg.cn/bmiddle/005uQRNCgw1eh96aj2ukij304g04gdg0.jpg',
        'http://ww1.sinaimg.cn/bmiddle/005uQRNCgw1eh96ajiimfj304g04gq37.jpg',
    ];
    $url = $img[$num];
    return $url;
}

function getContent($url, $method = 'GET', $header_array = [], $post = null, $proxy = '', $return = 'html')
{
    $context = [];
    $context['http']['timeout'] = 60;
    $context['http']['method'] = $method;
    if (is_array($header_array) && count($header_array) > 0) {
        $headers = implode("\r\n", $header_array);
        $context['http']['header'] = $headers;
    }
    if ($proxy) {
        $context['http']['proxy'] = $proxy;
        $context['http']['request_fulluri'] = true;
    }
    if ($method == 'POST' && is_array($post)) {
        ksort($post);
        $context['http']['content'] = http_build_query($post, '', '&');
    }
    $context = stream_context_create($context);
    $file = @file_get_contents($url, false, $context);
    if ($return == 'html') {
        return $file;
    } else {
        return $http_response_header;
    }
}

//消息类
class message
{
    public function showErr($str, $url = 'javascript:history.go(-1);', $sec = '5')
    {
        $html = file_get_contents(cyRoom_ROOT . 'member/msg.html');
        $str = empty($str) ? '您所请求的页面地址不存在!' : $str;
        $html = str_replace('{str}', $str, $html);
        $html = str_replace('{url}', $url, $html);
        $html = str_replace('{sec}', $sec, $html);
        echo $html;
        exit();
    }
    public function show404($str = '')
    {
        $html = file_get_contents(cyRoom_ROOT . 'member/404.html');
        $str = empty($str) ? '您所请求的页面地址不存在!' : $str;
        $html = str_replace('{str}', $str, $html);
        echo $html;
        exit();
    }
    public function goUrl($url)
    {
        header('Location: ' . $url);
        exit();
    }
} // end class

//sesion操作类
class session
{
    public function set($var, $val)
    {
        $_SESSION[$var] = $val;
    }
    public function get($var)
    {
        return $_SESSION[$var];
    }
}

//cookie操作类
class cookie
{
    public function set($var, $val, $times = 86400)
    {
        setcookie($var, $val, time() + $times, '/');
    }
    public function get($var)
    {
        return isset($_COOKIE[$var]) ? $_COOKIE[$var] : null;
    }
    public function remove($var)
    {
        setcookie($var, '', time() - 1, '/');
    }
}

//替换单引号及双引号
function checkStr($str, $isdel = 9)
{
    $str = trim($str);
    if ($isdel == 9) {
        $str = ereg_replace("'", "\'", $str);
        $str = ereg_replace('"', '\"', $str);
    } else {
        $str = ereg_replace("'", '', $str);
        $str = ereg_replace('"', '', $str);
        $str = preg_replace('/([\\\]*)/is', '', $str);
    }
    return $str;
}

function getSqlMd5($sql, $array)
{
    $tmpSql = $sql;
    $index = 0;
    foreach ($array as $key => $value) {
        $tmpSql = $tmpSql . ($index === 0 ? '?' : '&') . $key . '=' . $value;
        $index++;
    }
    return md5($tmpSql);
    // return $tmpSql;
}
