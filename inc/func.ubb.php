<?php
mt_srand((float) microtime() * 1000000);
$logimgtitle = '';

class ubb
{
    /*
     ** $bbCodeOff 是否开启UBB
     ** $allowImgCode 是否开启图片
     ** $allowMediaCode 是否开启媒体
     */
    public $message = '';
    public $parseType = 0;
    public $bbCodeOff = 0;
    public $allowBbCode = 1;
    public $allowImgCode = 1;
    public $allowMediaCode = 1;
    public $autoLink = 0;
    public $isRss = false;

    public function setString($str)
    {
        $this->message = $str;
    }

    public function parse()
    {
        $message = $this->message;
        $message = str_replace('[/url][url', "[/url]\r\n[url", $message);
        if (!$this->bbCodeOff && $this->autoLink) {
            $message = autoAddLink($message);
        }
        $msglower = strtolower($message);
        if (!$this->bbCodeOff && $this->allowBbCode) {
            if (strpos($msglower, '[/url]') !== false) {
                $message = preg_replace_callback(
                    "/\[url=([^\s\[\]]+)\]\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]\[\/url\]\s*/is",
                    'parseImgUrl',
                    $message,
                );
                $message = preg_replace_callback(
                    "/\[url(=((https?|ftp|http|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/|www\.)([^\"']+?))?\](.+?)\[\/url\]/is",
                    'parseUrl',
                    $message,
                );
            }
            if (strpos($msglower, '[/email]') !== false) {
                $message = preg_replace_callback(
                    '/\[email(=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+))?\](.+?)\[\/email\]/is',
                    'parseEmail',
                    $message,
                );
            }
            $message = str_replace(
                [
                    '[/color]',
                    '[/size]',
                    '[/font]',
                    '[/align]',
                    '[b]',
                    '[/b]',
                    '[p]',
                    '[i=s]',
                    '[i]',
                    '[/i]',
                    '[u]',
                    '[/u]',
                    '[list]',
                    '[list=1]',
                    '[list=a]',
                    '[list=A]',
                    '[*]',
                    '[/list]',
                    '[indent]',
                    '[/indent]',
                    '[/float]',
                ],
                [
                    '</span>',
                    '</font>',
                    '</span>',
                    '</p>',
                    '<strong>',
                    '</strong>',
                    '<p>',
                    '<i class="pstatus">',
                    '<i>',
                    '</i>',
                    '<u>',
                    '</u>',
                    '<ul>',
                    '<ul type="1" class="litype_1">',
                    '<ul type="a" class="litype_2">',
                    '<ul type="A" class="litype_3">',
                    '<li>',
                    '</ul>',
                    '<blockquote>',
                    '</blockquote>',
                    '</span>',
                ],
                preg_replace(
                    [
                        '/\[\/p\]\s*/i',
                        '/\[color=([#\w]+?)\]/i',
                        '/\[size=(\d+?)\]/i',
                        '/\[size=(\d+(\.\d+)?(px|pt|in|cm|mm|pc|em|ex|%)+?)\]/i',
                        '/\[font=([^\[\<]+?)\]/i',
                        '/\[align=(left|center|right)\]/i',
                        '/\[float=(left|right)\]/i',
                        '/\[p=([\d]+), ([\d]+), (left|right|center)\]/i',
                    ],
                    [
                        '</p>',
                        "<span style='color:\\1'>",
                        "<font size='\\1'>",
                        "<span style='font-size:\\1'>",
                        "<span style='face:\\1'>",
                        "<p align=\"\\1\">",
                        "<span style=\"float: \\1;\">",
                        "<p style=\"text-align: \\3;\">",
                    ],
                    $message,
                ),
            );
            $nest = 0;
            while (strpos($msglower, '[table') !== false && strpos($msglower, '[/table]') !== false) {
                $message = preg_replace_callback('/\[table(?:=(\d{1,4}%?)(?:,([\(\)%,#\w ]+))?)?\]\s*(.+?)\s*\[\/table\]/is', 'parseTable', $message);
                if (++$nest > 4) {
                    break;
                }
            }
            if ($this->allowMediaCode) {
                if (strpos($msglower, '[/media]') !== false) {
                    $message = preg_replace_callback(
                        '/\[media=([a-z0-9A-Z]+),\s*(\d+),\s*(\d+)\](.+?)\.((rm)|(ra)|(wma)|(mp3)|(mp4)|(wmv)|(swf)|(flv)|(asf))\[\/media\]/is',
                        'parseMedia',
                        $message,
                    );
                }
                if (strpos($msglower, '[/flash]') !== false) {
                    $message = preg_replace_callback('/\[flash\]\s*(.+?)\[\/flash\]/is', 'parseFlash', $message);
                }
            }
        }
        if (!$this->bbCodeOff) {
            if (strpos($msglower, '[/img]') !== false) {
                $message = preg_replace_callback("/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", 'bbCodeUrl2', $message);
                $message = preg_replace_callback("/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]\s*/is", 'bbCodeUrl1', $message);
            }
        }
        unset($msglower);
        $message = nl2br($message);
        return str_replace(["\t", '   ', '  '], ['&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'], $message);
    }
}
function bbCodeUrl1($m)
{
    global $ubb;
    $url = $m[1];
    if (!preg_match('/\<.+?\>/s', $url)) {
        if (!in_array(strtolower(substr($url, 0, 6)), ['http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://'])) {
            $url = 'http://' . $url;
        }
    }
    if ($ubb->allowImgCode) {
        if ($ubb->isRss == true) {
            $tag =
                '<div class="p_ubbimg"><a href="' .
                $url .
                '" class="lightbox" rel="lightbox" target="_blank"><img style="max-width:100%;" src="' .
                $url .
                '" class="ubbimg jqimg" alt="" /></a></div>';
        } else {
            $tag =
            '<div class="p_ubbimg"><a href="' .
            str_replace('/700', '', $url) .
                '" class="lightbox" rel="lightbox" target="_blank"><img style="max-width:100%;"
            src="http://ww3.sinaimg.cn/bmiddle/9ca59837gw1dqtgr6b2t7g.gif" class="ubbimg jqimg" url="' .
                $url .
                '" alt="{imgalt}" /></a></div>';
        }
    } else {
        $tag = '<a href="' . $url . '" target="_blank">' . $url . '</a><br />';
    }
    return $tag;
}
function bbCodeUrl2($m)
{
    global $ubb;
    $url = $m[3];
    if (!preg_match('/\<.+?\>/s', $url)) {
        if (!in_array(strtolower(substr($url, 0, 6)), ['http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://'])) {
            $url = 'http://' . $url;
        }
    }
    if ($ubb->allowImgCode) {
        if ($ubb->isRss == true) {
            $tag = '<img class="jqimg2" src="' . $url . '" border="0" alt="" />';
        } else {
            $tag =
                '<img class="jqimg2" src="http://ww3.sinaimg.cn/bmiddle/9ca59837gw1dqtgr6b2t7g.gif" url="' . $url . '" border="0" alt="{imgalt}" />';
        }
    } else {
        $tag = '<a href="' . $url . '" target="_blank">' . $url . '</a>';
    }
    if (!empty($logimgtitle)) {
        $tag = str_replace('{imgalt}', '预览图', $tag);
    }
    return $tag;
}
function parseUrl($m)
{
    $url = $m[1];
    $text = $m[5];
    if (
        !$url &&
        preg_match("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/|www\.)[^\[\"']+/i", trim($text), $matches)
    ) {
        $url = $matches[0];
        $length = 65;
        if (strlen($url) > $length) {
            $text = substr($url, 0, intval($length * 0.5)) . ' ... ' . substr($url, -intval($length * 0.3));
        }
        $url = substr(strtolower($url), 0, 4) == 'www.' ? 'http://' . $url : $url;
    } else {
        $url = substr($url, 1);
        if (substr(strtolower($url), 0, 4) == 'www.') {
            $url = 'http://' . $url;
        }
    }
    $return = '<a href="' . $url . '" target="_blank" rel="nofollow">' . $text . '</a>';
    return $return;
}
function parseImgUrl($m)
{
    $url = $m[1];
    $img = $m[2];
    $arr_img = explode('.', $url);
    $length = count($arr_img);
    $ext = $arr_img[$length - 1];
    $dturl = strpos($url, '?');
    $ext = strtolower($ext);
    if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif') {
        $return =
            "<div class=\"p_ubbimg\"><a href=\"" .
            $url .
            "\" target=\"_blank\" class=\"lightbox\" rel=\"lightbox\"><img src=\"" .
            $img .
            "\" class=\"ubbimg\"
            alt=\"预览图\" /></a></div>";
    } else {
        $return =
            "<div class=\"p_ubbimg\"><a href=\"" .
            $url .
            "\" target=\"_blank\"><img src=\"" .
            $img .
            "\" class=\"ubbimg\" alt=\"预览图\" /></a></div>";
    }
    return $return;
}

function parseEmail($m)
{
    $email = $m[1];
    $text = $m[4];
    if (!$email && preg_match('/\s*([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\s*/i', $text, $matches)) {
        $email = trim($matches[0]);
        return '<a href="mailto:' . $email . '">' . $email . '</a>';
    } else {
        return '<a href="mailto:' . substr($email, 1) . '">' . $text . '</a>';
    }
}

function parseTable($m)
{
    $width = $m[1];
    $bgcolor = $m[2];
    $message = $m[3];
    if (
        !preg_match('/^\[tr(?:=([\(\)%,#\w]+))?\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/', $message) &&
        !preg_match('/^\<tr[^>]*?\>\s*<td[^>]*?\>/', $message)
    ) {
        return str_replace(
            '\\"',
            '"',
            preg_replace('/\[tr(?:=([\(\)%,#\w]+))?\]|\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]|\[\/td\]|\[\/tr\]/', '', $message),
        );
    }
    if (substr($width, -1) == '%') {
        $width = substr($width, 0, -1) <= 98 ? intval($width) . '%' : '98%';
    } else {
        $width = intval($width);
        $width = $width ? ($width <= 560 ? $width . 'px' : '98%') : '';
    }
    $message = preg_replace_callback('/\[tr(?:=([\(\)%,#\w]+))?\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/is', 'parseTrTr', $message);
    $message = preg_replace_callback('/\[\/td\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/is', 'parseTrTd', $message);
    $message = preg_replace('/\[\/td\]\s*\[\/tr\]\s*/i', '</td></tr>', $message);
    $return =
        '<table cellspacing="0" class="t_table" ' .
        ($width == '' ? null : 'style="width:' . $width . '"') .
        ($bgcolor ? ' bgcolor="' . $bgcolor . '">' : '>');
    $return .= $message;
    $return .= '</table>';
    return $return;
}
function parseTrTr($m)
{
    $bgcolor = 'tr';
    $colspan = $m[1];
    $rowspan = $m[2];
    $width = $m[3];
    return ($bgcolor == 'td' ? '</td>' : '<tr' . ($bgcolor ? ' bgcolor="' . $bgcolor . '"' : '') . '>') .
        '<td' .
        ($colspan > 1 ? ' colspan="' . $colspan . '"' : '') .
        ($rowspan > 1 ? ' rowspan="' . $rowspan . '"' : '') .
        ($width ? ' width="' . $width . '"' : '') .
        '>';
}
function parseTrTd($m)
{
    $bgcolor = $m[1];
    $colspan = $m[2];
    $rowspan = $m[3];
    $width = $m[4];
    return ($bgcolor == 'td' ? '</td>' : '<tr' . ($bgcolor ? ' bgcolor="' . $bgcolor . '"' : '') . '>') .
        '<td' .
        ($colspan > 1
        ? '
            colspan="' .
        $colspan .
        '"'
        : '') .
        ($rowspan > 1 ? ' rowspan="' . $rowspan . '"' : '') .
        ($width ? ' width="' . $width . '"' : '') .
        '>';
}
function parseMedia($m)
{
    global $c_type, $userid;
    $mediatype = $m[1];
    $url = $m[4] . '.' . $m[5];
    $width = $m[2];
    $height = $m[3];
    $isauto = 0;
    $width = $width == '' ? '700' : $width;
    $height = $height == '' ? '560' : $height;
    if ($mediatype == 'flv') {
        $str =
            '<div id="a1"></div>
            <script type="text/javascript" src="/ckplayer/ckplayer.js" charset="utf-8"></script>
            <script type="text/javascript">
            var flashvars = {
                    f: "' .
            $url .
            '",c:0,b:1};var params={bgcolor:"#FFF",allowFullScreen:true,allowScriptAccess:"always",wmode:"transparent"};CKobject.embedSWF("/ckplayer/ckplayer.swf","a1","ckplayer_a1","' .
            $width .
            '","' .
            $height .
            '",flashvars,params);function closelights(){}function openlights(){}
            </script>';
    } elseif ($mediatype == 'mp4') {
        $str = '<video src="' . $url . '" width="' . $width . '" height="' . $height . '" controls="controls">您的浏览器不支持 video 标签。</video>';
    } else {
        $str =
            '<div class="lock"><embed src="' .
            $url .
            '" wmode="transparent" quality="high" bgcolor="#000000" width="' .
            $width .
            '" height="' .
            $height .
            '" name="simplevideostreaming" align="middle" allowScriptAccess="sameDomain" allowFullScreen="true" type="application/x-shockwave-flash"
                    pluginspage="http://www.macromedia.com/go/getflashplayer" /></div>';
    }
    return $str;
}
function parseFlash($m)
{
    $mediatype = 'swf';
    $url = $m[1];
    $width = 700;
    $height = 560;
    $isauto = 0;
    $str =
        '<div class="lock"><embed src="' .
        $url .
        '" wmode="transparent" quality="high" bgcolor="#000000" width="' .
        $width .
        '" height="' .
        $height .
        '" name="simplevideostreaming" align="middle" allowScriptAccess="sameDomain" allowFullScreen="true" type="application/x-shockwave-flash"
                    pluginspage="http://www.macromedia.com/go/getflashplayer" /></div>';
    return $str;
}

function autoAddLink($message)
{
    $message = preg_replace(
        [
            '/((http|https):\/\/)+(\w+\.)+(\w+)[\w\/\.\-]*(jpg|gif|png)/i',
            '/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i',
        ],
        ["[img]\\1[/img]", "[url=\\0]\\0[/url]"],
        ' ' . $message,
    );
    return $message;
}
