<?php 
$content = file_get_contents($parsed['real_full_file']);
$filectime = filectime($parsed['real_full_file']);
?>
<textarea id="epiceditor" class="epiceditor" data-filectime="<?=$filectime?>"><?php echo $content ?></textarea>
<div class="msg"></div>
<div class="msg2"></div>
<script src="lib/EpicEditor/js/epiceditor.min.js"></script>
<script src="lib/md5.js"></script>
<script>
var auto_save_interval, auto_backup_interval;
var full_file = '<?php echo $parsed['full_file'] ?>';
// var epic = new EpicEditor({
//     clientSideStorage: false,
// 	file: {
// 		name: full_file,
// 		defaultContent: document.getElementById('epiceditor').innerHTML,
// 		autoSave: false
// 	},
// 	basePath: 'lib/EpicEditor',
// 	theme: {
// 		base: '/themes/base/epiceditor.css',
// 		preview: '/themes/preview/preview-dark.css',
// 		editor: '/themes/editor/epic-dark.css'
// 	}
// }).load();

function auto_save(){
	$.post('save.php', {
        content: $('#epiceditor').val(),
        filectime: $('#epiceditor').data('filectime'),
        real_full_file: '<?php echo $parsed['real_full_file'] ?>'
	}, function(data){
		if(data.code == 'fail'){
			$('.msg').text(data.msg);
            console.log(data.filectime);
            $('#epiceditor').data('filectime', data.filectime);
        }else if(data.code == 'file changed'){
            // $('body').html('<h1 style="color: white; padding-top: 100px; text-align: center">파일이 밖에서 변경됐습니다.</h1>');
            console.log("real", data.real_filectime, "editor", data.editor_filectime);
            $('.msg').text('File updated externally.');
            // location.reload();
		}else{
            $('#epiceditor').data('filectime', data.filectime);
            $('.msg').text(new Date().toString() + ' - 저장');
		}
	}, 'json');
}

function auto_backup(){
    $.post('backup.php', {
        content: $('#epiceditor').val(),
        path: '<?php echo $_GET['path'] ?>'
    }, function(data){
        if(data.code == 'fail'){
            $('.msg2').text("백업 실패 : " + data.msg);
        }else{
            $('.msg2').text(new Date().toString() + ' - 백업');
        }
    }, 'json');
}

function init_auto_save_backup(){
    auto_save_interval = setInterval(auto_save, 1000);
    auto_backup();
    auto_backup_interval = setInterval(auto_backup, 60*5*1000);
}

function remove_auto_save_backup(){
    clearInterval(auto_save_interval);
    clearInterval(auto_backup_interval);
}

init_auto_save_backup();

// $(window).blur(remove_auto_save_backup);
// $(window).focus(init_auto_save_backup);

$('body').css('background', 'rgb(41,41,41)');

</script>