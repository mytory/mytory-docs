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

if(isset($_GET['r']) AND isset($_GET['f'])){
	// 본문 
	$root = $_GET['r'];
	$relative_path = $_GET['p'];
	$file = $_GET['f'];
	$full_path = $doc_roots[$root] . '/' . $relative_path . '/'. $file;
	if( ! is_file($full_path)){
		echo "$file is not a real file.";
		exit;
	}
	?>
	<a class="btn" href="?r=<?php echo $root ?>&amp;p=<?php echo $relative_path ?>">목록으로 돌아가기</a>
	<?php echo Markdown(file_get_contents($full_path)); ?>
	
	<?php


}else if(isset($_GET['r'])){
	// 목록 
	$root = $_GET['r'];
	$relative_path = $_GET['p'];
	$dir = realpath($doc_roots[$root] . '/' . $relative_path);
	print_docs_list($root, $relative_path);


}else{
	// 최상위 
	?><ul><?php
		foreach ($doc_roots as $name => $dir) { ?>
		<li><a href="?r=<?php echo $name ?>&amp;p=."><?php echo $name?></a></li>	
		<?php } ?>
	</ul>
<?php } ?>
</div>
</div>
<?php foreach ($js_list as $js_file) { ?>
	<script src="<?php echo $js_file ?>"></script>
<?php } ?>
</body>
</html>