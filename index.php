<?php
if (! is_file('config.php')) {
    if (! copy('config.sample.php', 'config.php')) {
        echo "It's need config.php file. Rename config.sample.php to config.php";
        exit;
    }
}
require 'vendor/autoload.php';
require 'functions.php';
require 'config.php';
$ParsedownExtra = new ParsedownExtra();
check_config_error();
$parsed = parse_path();

if (get_cmd_type() == 'new-file') {
    new_file();
    exit;
} elseif (get_cmd_type() == 'delete-file') {
    delete_file();
    exit;
}

?><!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="lib/dynatable/jquery.dynatable.css"/>
    <?php
    foreach ($css_list as $css_file) { ?>
        <link rel="stylesheet" href="<?php echo $css_file ?>">
    <?php } ?>
    <title>Mytory Docs</title>
    <script src="lib/jquery.min.js"></script>
    <script src="lib/dynatable/jquery.dynatable.js"></script>


</head>
<body class="has-counter">
<div class="wrapper">

    <?php
    include $template['header'];
    ?>

    <div class="content">
        <?php
        if (get_cmd_type() == 'edit') {
            // 수정
            include $template['edit'];
        } elseif (get_cmd_type() == 'view') {
            // 본문
            include $template['view'];
        } elseif (get_cmd_type() == 'list') {
            // 목록
            include $template['list'];
        } else {
            // 최상위?>
            <ul><?php
            foreach ($doc_roots as $name => $dir) { ?>
                <li><a href="?path=list:<?php echo $name ?>"><?php echo $name ?></a></li>
            <?php } ?>
            </ul>
        <?php
        } ?>
    </div>
</div>
<div class="footer <?= get_cmd_type() == 'edit' ? 'footer--edit' : '' ?>">
    Mytory Docs
</div>
<script src="script.js"></script>
<?php foreach ($js_list as $js_file) { ?>
    <script src="<?php echo $js_file ?>"></script>
<?php } ?>
<link rel="stylesheet"
      href="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.16.2/build/styles/default.min.css">
<script src="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.16.2/build/highlight.min.js"></script>
<script>hljs.initHighlightingOnLoad();</script>
</body>
</html>