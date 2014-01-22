<?php
$root = $_GET['r'];
$relative_path = $_GET['p'];
$file = $_GET['f'];
$full_path = $doc_roots[$root] . '/' . $relative_path . '/'. $file;

if( ! is_file($full_path)){
	echo "$file is not a real file.";
	exit;
}
?>
<nav class="navbar navbar-default" role="navigation">
	<!-- Collect the nav links, forms, and other content for toggling -->
	<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		<ul class="nav navbar-nav">
			<li><a href="?r=<?php echo $root ?>&amp;p=<?php echo $relative_path ?>">목록으로 돌아가기</a></li>
			<?php
			$modify_link = array(
				'r' => $root,
				'p' => $relative_path,
				'f' => $file,
				'c' => 'modify',
			);
			?>
			<li><a href="?<?php echo http_build_query($modify_link)?>">수정</a></li>
		</ul>
	</div>
</nav>


<?php echo Markdown(file_get_contents($full_path)); ?>