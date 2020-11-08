<?php Template::checkCacheTemplate('template/default/index|template/default/top|template/default/footer', 1, 'template/default/index');?>
<?php if(!isPjax()) { ?>
<!DOCTYPE html>
<html>
<head>
    <title><?=$seo['title']?></title>
    <meta charset="utf-8">
    <meta name="google" content="notranslate">
    <meta name="keyword" content="<?=$seo['keyword']?>">
    <meta name="description" content="<?=$seo['desc']?>">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no"/>
    <link rel="stylesheet" type="text/css" href="/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-pjax/jquery.pjax.min.js"></script>
</head>
<body data-config='{"page":"<?=$global['script']?>"}'>
    <div id="pjax">

<?php } ?>
    <table>
        <?php if(is_array($list)) { foreach($list as $var => $val) { ?>
        <tr>
            <td><?=$val['c_id']?></td>
            <td><?=$val['c_userid']?></td>
            <td><a href="./detail.php?id=<?=$val['c_arcid']?>"><?=$val['c_arctitle']?></a></td>
            <td><?=$val['c_arcid']?></td>
            <td><?=$val['c_arctime']?></td>
            <td><?=$val['c_posttime']?></td>
        </tr>
        <?php } } ?>
    </table>
    <div class="pjax-box">
        <ul id="page">
            <?=$pages?>
        </ul>
    </div>
<?php if(!isPjax()) { ?>
</div>
<script src="/assets/js/main.js"></script>
</body>
</html>

<?php } ?>
