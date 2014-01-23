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
check_config_error();
$parsed = parse_path();

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
	<nav class="navbar navbar-default" role="navigation">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" 
					data-target="#main-menu">
				<span class="sr-only">메뉴 열기</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo BASE_URL ?>" title="Mytory Docs">MD</a>
		</div>
		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="main-menu">
			<ul class="nav navbar-nav">
				<?php if(get_cmd_type() == 'edit' OR get_cmd_type() == 'view'){ ?>
					<li><a href="?path=list:<?php echo $parsed['full_path'] ?>">목록</a></li>
				<?php } ?>
				<?php if(get_cmd_type() == 'view'){ ?>
				<li><a href="?path=edit:<?php echo $parsed['full_file']?>">수정</a></li>
				<?php } ?>
			</ul>
			<?php if(get_cmd_type() == 'edit' OR get_cmd_type() == 'view'){ ?>
				<span class="navbar-text navbar-left">파일경로</span>
				<form class="navbar-form navbar-left">
					<div class="form-group">
						<input readonly class="form-control" type="text" value="<?php echo $parsed['real_full_file'] ?>">
					</div>
				</form>
			<?php } ?>
		</div>
	</nav>
</div>
<div class="content">
<?php 
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