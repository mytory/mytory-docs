<?php 
if( ! is_file('config.php')){
	if( ! copy('config.sample.php', 'config.php')){
		echo "It's need config.php file. Rename config.sample.php to config.php";
		exit;
	}
}
include 'config.php'; 
include 'functions.php';
require_once 'lib/php-markdown/markdown.php';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0">
	<?php
	foreach ($css_list as $css_file) { ?>
		<link rel="stylesheet" href="<?php echo $css_file ?>">
	<?php } ?>    
    <title>Mytory Docs</title>

</head>
<body><div class="wrapper">
<div class="header">
	Mytory Docs
</div>
<div class="content">
<?php 
check_config_error();
$parsed = parse_path();

if(get_cmd_type() == 'edit'){
	// 수정 
	include 'edit.php';

}else if(get_cmd_type() == 'view'){
	// 본문 
	include $template['view'];

}else if(get_cmd_type() == 'list'){
	// 목록 
	print_docs_list($parsed);

}else{
	// 최상위 
	?><ul><?php
		foreach ($doc_roots as $name => $dir) { ?>
		<li><a href="?path=list:<?php echo $name ?>"><?php echo $name?></a></li>	
		<?php } ?>
	</ul>
<?php } ?>
</div>
</div>
<script src="lib/jquery.min.js"></script>
<?php foreach ($js_list as $js_file) { ?>
	<script src="<?php echo $js_file ?>"></script>
<?php } ?>
</body>
</html>