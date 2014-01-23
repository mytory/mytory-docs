<?php 
$content = file_get_contents($parsed['real_full_file']);
?>
<div id="epiceditor" class="epiceditor"><?php echo $content ?></div>
<div class="msg"></div>
<div class="msg2"></div>
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
	}
}).load();

function auto_save(){
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

function auto_backup(){
    $.post('backup.php', {
        content: epic.getFiles()[full_file].content,
        path: '<?php echo $_GET['path'] ?>'
    }, function(data){
        if(data.code == 'fail'){
            $('.msg2').text("백업 실패 : " + data.msg);
        }else{
            $('.msg2').text(new Date().toString() + ' - 백업');
        }
    }, 'json');
}

setInterval(auto_save, 1000);
auto_backup();
setInterval(auto_backup, 60*5*1000);

</script>