<?php 
$content = file_get_contents($parsed['real_full_file']);
?>
<div id="epiceditor" class="epiceditor"><?php echo $content ?></div>
<script src="lib/EpicEditor/js/epiceditor.min.js"></script>
<script>
var editor = new EpicEditor({
	file: {
		name: '<?php echo $parsed['file'] ?>',
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