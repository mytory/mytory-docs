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
<a class="btn" href="?r=<?php echo $root ?>&amp;p=<?php echo $relative_path ?>">목록으로 돌아가기</a>
<?php echo Markdown(file_get_contents($full_path)); ?>