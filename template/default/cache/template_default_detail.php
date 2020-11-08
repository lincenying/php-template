<?php Template::checkCacheTemplate('template/default/detail|template/default/top|template/default/footer', 1, 'template/default/detail');?>
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
    <div class="detail">
        <h4><?=$row['c_title']?></h4>
        <div class="cotnent">
            <?=$row['c_content']?>
        </div>
    </div>
<?php if(!isPjax()) { ?>
</div>
<script src="/assets/js/main.js"></script>
</body>
</html>

<?php } ?>
