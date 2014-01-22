<?php 
$content = file_get_contents($parsed['real_full_file']);
?>
<div id="epiceditor" class="epiceditor"><?php echo $content ?></div>
<div class="msg"></div>
<script src="lib/EpicEditor/js/epiceditor.min.js"></script>
<script>
var full_file = '<?php echo $parsed['full_file'] ?>';
var epic = new EpicEditor({
	file: {
		name: full_file,
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

function autosave(){
	$.post('save.php', {
		content: epic.getFiles()[full_file].content,
		path: '<?php echo $_GET['path'] ?>'
	}, function(data){
		if(data.code == 'fail'){
			$('.msg').text(data.msg);
		}else{
			$('.msg').text(new Date().toString() + ' - 저장');
		}
	}, 'json');
}

setInterval(autosave, 1000);

</script>