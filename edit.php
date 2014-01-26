<?php 
$content = file_get_contents($parsed['real_full_file']);
?>
<div id="epiceditor" class="epiceditor"><?php echo $content ?></div>
<div class="msg"></div>
<div class="msg2"></div>
<script src="lib/EpicEditor/js/epiceditor.min.js"></script>
<script src="lib/md5.js"></script>
<script>
var full_file = '<?php echo $parsed['full_file'] ?>';
var epic = new EpicEditor({
    clientSideStorage: false,
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

var content_saved_md5 = CryptoJS.MD5(epic.getFiles()[full_file].content).toString();

function auto_save(){
    var content = epic.getFiles()[full_file].content;
	$.post('save.php', {
        content_saved_md5: content_saved_md5,
		content: content,
		path: '<?php echo $_GET['path'] ?>'
	}, function(data){
		if(data.code == 'fail'){
			$('.msg').text(data.msg);
        }else if(data.code == 'file changed'){
            $('body').html('<h1 style="color: white; padding-top: 100px; text-align: center">파일이 밖에서 변경됐습니다.</h1>');
            location.reload();
		}else{
            content_saved_md5 = data.content_saved_md5;
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

$(epic.getElement('editor').body).keyup(auto_save);
auto_backup();
setInterval(auto_backup, 60*5*1000);
$('body').css('background', 'rgb(41,41,41)');
</script>