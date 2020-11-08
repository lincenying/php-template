<?php

class Template
{
    /**
     *全局成员数组
     */
    private static $sGlobal = [
        'i' => 0,
        'block_search' => [],
        'block_replace' => [],
    ];
    /**
     *选择模板目录
     */
    private static $sSelectTemplatePath = 'default';
    /**
     *模板目录
     */
    private static $sTemplateDir = 'template';
    /**
     *检测模板更新:设置0为永远不检测，设置1总是检测
     */
    private static $nTemplateRefresh = 1;

    /**
     *模板缓存时间，必须开启模板更新检测也有效
     */
    private static $nTemplateCacheTime = 3600;

    /**
     *伪静态替换
     */
    private static $TemplateIsReWrite = true;

    /**
     * 构造方法
     */
    public function __construct($Path, $ReWrite = true)
    {
        Template::$sSelectTemplatePath = $Path;
        Template::$TemplateIsReWrite = $ReWrite;
    }

    /**
     * 输出模板
     * @param string $name
     * @return string
     */
    public static function showTemplate($name)
    {
        $tpl = Template::$sTemplateDir . '/' . Template::$sSelectTemplatePath . '/' . $name;
        $objfile = Template::$sTemplateDir . '/' . Template::$sSelectTemplatePath . '/cache/' . str_replace('/', '_', $tpl) . '.php';
        $needParse = false;
        if (!file_exists(cyRoom_ROOT . $objfile)) {
            $needParse = true;
        } else {
            $r = Template::readContents($objfile);
            if (empty($r)) {
                $needParse = true;
            }
        }
        if ($needParse) {
            include_once cyRoom_ROOT . 'inc/func.template.php';
            Template::parse_template($tpl);
        }
        return $objfile;
    }

    /**
     * 子模板更新检查
     * @function checkCacheTemplate
     * @param string $subfiles,$mktime,$tpl
     * @return void
     */
    public static function checkCacheTemplate($subfiles, $mktime, $tpl)
    {
        if (Template::$nTemplateRefresh && (Template::$nTemplateRefresh == 1 || mt_rand(1, Template::$nTemplateRefresh) == 1)) {
            $subfiles = explode('|', $subfiles);
            $objfile = Template::$sTemplateDir . '/' . Template::$sSelectTemplatePath . '/cache/' . str_replace('/', '_', $tpl) . '.php';
            @$tpltime = filemtime($objfile);
            foreach ($subfiles as $subfile) {
                @$submktime = filemtime($subfile . '.html');
                if ($submktime > $tpltime) {
                    include_once cyRoom_ROOT . 'inc/func.template.php';
                    Template::parse_template($tpl);
                    break;
                }
            }
        }
    }
    /**
     * 模板解析
     * @function parse_template
     * @param string $tpl
     * @return void
     */
    private static function parse_template($tpl)
    {
        //包含模板
        Template::$sGlobal['sub_tpls'] = [$tpl];
        $tplfile = $tpl . '.html';
        $objfile = Template::$sTemplateDir . '/' . Template::$sSelectTemplatePath . '/cache/' . str_replace('/', '_', $tpl) . '.php';
        //read Template
        $template = Template::readContents($tplfile);
        if (empty($template)) {
            exit(Template::showError("Template file : $tplfile Not found or have no access!"));
        }
        //模板
        $template = preg_replace_callback(
            '/\<\!\-\-\s*\{template\s+([a-z0-9_\/]+)\}\s*\-\-\>/i',
            function ($match) {
                return Template::readTemplateContents($match[1]);
            },
            $template,
        );
        //print $template;
        //处理子页面中的代码
        $template = preg_replace_callback(
            '/\<\!\-\-\s*\{template\s+([a-z0-9_\/]+)\}\s*\-\-\>/i',
            function ($match) {
                return Template::readTemplateContents($match[1]);
            },
            $template,
        );
        //PHP代码
        $template = preg_replace_callback(
            '/\<\!\-\-\s*\{eval\s+(.+?)\s*\}\s*\-\-\>/is',
            function ($match) {
                return Template::evaltags($match[1]);
            },
            $template,
        );
        //start
        //变量
        $var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
        $template = preg_replace('/\<\!\-\-\s*\{(.+?)\}\s*\-\-\>/s', "{\\1}", $template);
        $template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
        $template = preg_replace("/(\\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\.([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/s", "\\1['\\2']", $template);
        $template = preg_replace("/\{\s*(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\s*\}/s", '<' . '?=\\1?' . '>', $template);
        $template = preg_replace_callback(
            "/$var_regexp/s",
            function ($match) {
                return Template::addquote('<' . '?=' . $match[1] . '?' . '>');
            },
            $template,
        );
        $template = preg_replace_callback(
            "/\<\?\=\<\?\=$var_regexp\?\>\?\>/s",
            function ($match) {
                return Template::addquote('<' . '?=' . $match[1] . '?' . '>');
            },
            $template,
        );
        //逻辑
        $template = preg_replace_callback(
            '/\{elseif\s+(.+?)\}/is',
            function ($match) {
                return Template::stripvtags('<' . '?php } elseif (' . $match[1] . ') { ?' . '>', '');
            },
            $template,
        );
        $template = preg_replace('/\{else\}/is', '<' . '?php } else { ?' . '>', $template);
        //循环
        for ($i = 0; $i < 5; $i++) {
            $template = preg_replace_callback(
                '/\{loop\s+(\S+)\s+(\S+)\}(.+?)\{\/loop\}/is',
                function ($match) {
                    return Template::stripvtags(
                        '<' . '?php if(is_array(' . $match[1] . ')) { foreach(' . $match[1] . ' as ' . $match[2] . ') { ?' . '>',
                        '' . $match[3] . '<' . '?php } } ?' . '>',
                    );
                },
                $template,
            );
            $template = preg_replace_callback(
                '/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}(.+?)\{\/loop\}/is',
                function ($match) {
                    return Template::stripvtags(
                        '<' . '?php if(is_array(' . $match[1] . ')) { foreach(' . $match[1] . ' as ' . $match[2] . ' => ' . $match[3] . ') { ?' . '>',
                        '' . $match[4] . '<' . '?php } } ?' . '>',
                    );
                },
                $template,
            );
            $template = preg_replace_callback(
                '/\{if\s+(.+?)\}(.+?)\{\/if\}/is',
                function ($match) {
                    return Template::stripvtags('<' . '?php if(' . $match[1] . ') { ?' . '>', '' . $match[2] . '<' . '?php } ?' . '>');
                },
                $template,
            );
        }
        //常量
        $template = preg_replace("/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/s", '<' . "? echo \\1; ?" . '>', $template);

        //替换
        if (!empty(Template::$sGlobal['block_search'])) {
            $template = str_replace(Template::$sGlobal['block_search'], Template::$sGlobal['block_replace'], $template);
        }

        //伪静态
        // if(Template::$TemplateIsReWrite) {
        //     $template = preg_replace_callback(Template::$Templatepattern, Template::$Templatereplace, $template);
        // }

        //换行
        $template = preg_replace("/ \?\>[\n\r]*\<\? /s", ' ', $template);

        //附加处理
        $nTemplateRefresh = Template::$nTemplateRefresh;

        $template =
            '<' .
            "?php Template::checkCacheTemplate('" .
            implode('|', Template::$sGlobal['sub_tpls']) .
            "', $nTemplateRefresh, '$tpl');?" .
            ">\r\n$template";
        //write
        if (!Template::writeTemplateCacheContents($objfile, $template)) {
            exit(Template::showError("File: $objfile can not be write!"));
        }
    }

    /**
     * php变量
     * @function addquote
     * @param string $var
     * @return string
     */
    private static function addquote($var)
    {
        return str_replace(
            "\\\"",
            "\"",
            preg_replace_callback(
                "/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s",
                function ($match) {
                    return "['" . $match[1] . "']";
                },
                $var,
            ),
        );
    }

    /**
     * php变量
     * @function striptagquotes
     * @param string $expr
     * @return string
     */
    private static function striptagquotes($expr)
    {
        $expr = preg_replace_callback("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr);
        $expr = str_replace("\\\"", "\"", preg_replace_callback("/\[\'([a-zA-Z0-9_\-\.\x7f-\xff]+)\'\]/s", "[\\1]", $expr));
        print $expr;
    }

    /**
     * php标签
     * @function evaltags
     * @param string $php
     * @return string
     */
    private static function evaltags($php)
    {
        $i = Template::$sGlobal['i']++;
        $search = "<!--EVAL_TAG_{$i} -->";
        Template::$sGlobal['block_search'][Template::$sGlobal['i']] = $search;
        Template::$sGlobal['block_replace'][Template::$sGlobal['i']] = '<' . '?php ' . Template::stripvtags($php) . ' ?' . '>';
        return $search;
    }

    /**
     * 替换输出标签
     * @function stripvtags
     * @param string $expr,$statement
     * @return string
     */
    private static function stripvtags($expr, $statement = '')
    {
        $expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
        $statement = str_replace("\\\"", "\"", $statement);
        return $expr . $statement;
    }

    /**
     * 读取模板
     * @function readTemplateContents
     * @function readTemplateContents
     * @param string $name
     * @return string
     */
    private static function readTemplateContents($name)
    {
        $tpl = Template::$sTemplateDir . '/' . Template::$sSelectTemplatePath . '/' . $name;
        Template::$sGlobal['sub_tpls'][] = $tpl;
        $file = $tpl . '.html';
        $content = Template::readContents($file);
        return $content;
    }

    /**
     * 读取文件内容
     * @function readContents
     * @param string $filename
     * @return string
     */
    private static function readContents($filename)
    {
        $content = '';
        if (function_exists('file_get_contents')) {
            $content = @file_get_contents($filename);
        } else {
            if (@$fp = fopen($filename, 'r')) {
                @$content = fread($fp, filesize($filename));
                @fclose($fp);
            }
        }
        return $content;
    }

    /**
     * 写入文件
     * @function writeTemplateCacheContents
     * @param string $filename,$writetext,$openmod
     * @return boolean
     */
    private static function writeTemplateCacheContents($filename, $writetext, $openmod = 'w')
    {
        if (file_exists($filename) && !filesize($filename)) {
            unlink($filename);
        }

        if (@$fp = fopen($filename, $openmod)) {
            flock($fp, 2);
            fwrite($fp, $writetext);
            fclose($fp);
            return true;
        } else {
            exit(Template::showError('error' . "File: $filename write error."));
            return false;
        }
    }

    /**
     * 显示错误信息
     * @function showError
     * @param string $message
     * @return void
     */
    private static function showError($message)
    {
        print $message;
    }

    /**
     *析构方法
     */
    public function __destruct()
    {
        //......
    }
}
