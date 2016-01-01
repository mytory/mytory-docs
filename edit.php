<?php 
$content = file_get_contents($parsed['real_full_file']);
?>
<style>
    body {
        background-color: rgb(41, 41, 41);
    }
    .navbar {
        background-color: rgb(41, 41, 41);
        border-color: rgb(50, 50, 50);
    }
    .navbar-default .navbar-nav>li>a:hover {
        color: rgb(200, 200, 200);
    }
    .navbar .form-control[readonly] {
        background-color: rgb(41, 41, 41);
        color: rgb(100, 100, 100);
        border-color: rgb(50, 50, 50);
    }
    ::-webkit-scrollbar              {
        background-color: rgb(41, 41, 41);
    }
    ::-webkit-scrollbar-button       {
        background-color: rgb(50, 50, 50);
    }
    ::-webkit-scrollbar-track        {
        background-color: rgb(41, 41, 41);
    }
    ::-webkit-scrollbar-track-piece  {
        background-color: rgb(41, 41, 41);
    }
    ::-webkit-scrollbar-thumb        {
        background-color: rgb(50, 50, 50);
    }
    ::-webkit-scrollbar-corner       {
        background-color: rgb(50, 50, 50);
    }
    ::-webkit-resizer                {
        background-color: rgb(50, 50, 50);
    }
</style>
<textarea id="editor" class="editor"><?php echo $content ?></textarea>
<div class="msges">
    <div class="msg"></div>
    <div class="msg2"></div>
</div>
<script>
var auto_save_interval, 
    auto_backup_interval, 
    prev_content = $('#editor').val(),
    full_file = '<?php echo $parsed['full_file'] ?>',
    current_filemtime = '<?php echo filemtime($parsed['real_full_file']) ?>';

function auto_save(){
    var content = $('#editor').val();
    if(prev_content == content){
        auto_save_interval = setTimeout(auto_save, 1000);
        return false;
    }else{
        prev_content = content;
    }
	$.post('save.php', {
        current_filemtime: current_filemtime,
		content: content,
		path: '<?php echo $_GET['path'] ?>'
	}, function(data){
        auto_save_interval = setTimeout(auto_save, 1000);
		if(data.code == 'fail'){
			$('.msg').text(data.msg);
        }else if(data.code == 'file changed'){
            if(confirm("파일이 밖에서 변경됐습니다. 다시 로드할까요?")){
                location.reload();
            }else{
                current_filemtime = parseInt(data.real_filemtime) + 1
            }
		}else{
            current_filemtime = parseInt(data.real_filemtime);
			$('.msg').text(new Date().toString() + ' - 저장');
		}
        adjust_editor_height();
	}, 'json');
}

function auto_backup(){
    $.post('backup.php', {
        content: $('#editor').val(),
        path: '<?php echo $_GET['path'] ?>'
    }, function(data){
        if(data.code == 'fail'){
            $('.msg2').text("백업 실패 : " + data.msg);
        }else{
            $('.msg2').text(new Date().toString() + ' - 백업');
        }
        adjust_editor_height();
    }, 'json');
}

function init_auto_save_backup(){
    auto_save_interval = setTimeout(auto_save, 1000);
    auto_backup();
    auto_backup_interval = setInterval(auto_backup, 60*5*1000);
}

function adjust_editor_height(){
    var window_height,
        editor_start_point,
        msg_area_height;

    window_height = $(window).height();
    editor_start_point = $('.editor').offset().top;
    msg_area_height = $('.msges').height();

    editor_height = window_height - editor_start_point - msg_area_height;

    $('#editor').outerHeight(editor_height);
}

init_auto_save_backup();

$(document).ready(adjust_editor_height);
$(window).resize(adjust_editor_height);

$('#file_path').click(function(){
    $(this).select();
});

</script>