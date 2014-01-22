<?php 
$root = $_GET['r'];
$relative_path = $_GET['p'];
$file = $_GET['f'];
$full_path = $doc_roots[$root] . '/' . $relative_path . '/'. $file;

if( ! is_file($full_path)){
	echo "$file is not a real file.";
	exit;
}

$content = file_get_contents($full_path);
?>
<div id="epiceditor" class="epiceditor"><?php echo $content ?></div>
<script src="lib/EpicEditor/js/epiceditor.min.js"></script>
<script>
var editor = new EpicEditor({
	file: {
		name: '<?php echo $file ?>',
		defaultContent: document.getElementById('epiceditor').innerHTML,
		autoSave: 100
	},
	basePath: 'lib/EpicEditor',
	theme: {
		base: '/themes/base/epiceditor.css',
		preview: '/themes/preview/preview-dark.css',
		editor: '/themes/editor/epic-dark.css'
	},
}).load();
</script>